<?php

// Issues
//	$secret should not be in this file?
//	Should check that it is a push to the master branch?
//	paths/commands etc are windows specific
//	$data->repository->full_name & $data->repository->clone_url are possible points
//	of sending bad stuff to shell_exec - fixed with escapeshellarg in the $commandLine, is this enoug?
//	performance large amount of code (added two of the largest old project repos to the same test, no performance problems)
//	performance many users

function PerformAnalysis($data, $a_doAllowPull) {
	if ($data) {

		$commandline = "";

		$LOCAL_REPO_NAME = $data->repository->full_name;
		$LOCAL_REPO_NAME = str_replace("/", "\\", $data->repository->full_name);	// mkdir cannot use /
		//var_dump($data);

		$LOCAL_ROOT         = "repo";
		$LOCAL_REPO         = "{$LOCAL_ROOT}/{$LOCAL_REPO_NAME}";

		// check that this is a push to the master branch?
		// when the hook is created the first payload is a bit special (not a push)
		if (isset($data->ref) && $data->ref != "refs/heads/master") {
			echo "Push to branch: " . $data->ref . PHP_EOL;
			echo "Ignoring..." . PHP_EOL;
			die("");
		}

		$creds = "https://Rulzor:Gragullesgrand12@";	// avoid ÅÄÖ in username password
		$repo = str_replace("https://", $creds, $data->repository->clone_url);
		$commandline = GetGitCommandLine($LOCAL_ROOT, $LOCAL_REPO_NAME, $repo, $a_doAllowPull);

		if(strlen($commandline) <= 0) {
			return;
		}
		//echo $commandline . PHP_EOL;
		echo shell_exec($commandline);

	  // Do the analysis
	  if(file_exists($LOCAL_REPO . "/.git")) {
	  	// we have something
	  	//$repoPath = "c://hObbE/webbdev/root/rulzor/gitit/" . $LOCAL_REPO;
	  	$repoPath = getcwd() . "/" . $LOCAL_REPO;
	  	require_once("logfile.php");
	  	require_once("gitapi.php");
	  	require_once("TrackedIssues.php");
	  	$version = "First Pull";
	  	if (isset($data->after)) {
	  		$version = $data->after;
	  	}
	  	$log = new LogFile($repoPath . "/rulzorAnalysis.log", $version);

	  	if (!isset($data->repository->owner->name)) {
	  		// the payload seems to have changed...
	  		$data->repository->owner->name = $data->repository->owner->login; 
	  	}

	  	$issuesFile = $repoPath ."/rulzorissues.json";
		$gitIssues = new GitIssuesApiWrapper($data->repository->owner->name, $data->repository->name);
		$issueTracker = new TrackedIssues($issuesFile, $log, $gitIssues);

	  	chdir("../src");
	  	require_once("model/Folder.php");
		require_once("model/Project.php");
		require_once("view/ClassDiagram.php");
		require_once("view/ClassMatrix.php");
		require_once("view/ClassClassification.php");

		
		$source = new \model\Folder($repoPath);
		$parser = new \model\ProjectParser();
		$classes = $parser->getClasses($source);

		$p = new \model\Project($source, $classes);


		$classes = $p->getClasses();

		foreach ($classes as $class) {
			if (isset($class->fileName)) {

				$dc = GetDeveloperClassification(strtoupper($class->getNamespace()), strtoupper($class->getName()),strtoupper($class->fileName));
				$rc = GetRuleClassification($class->DepthOfIsUsingNamespace("uiapi"));
				
				$currentIssue = GetIssueString($dc, $rc);

				$log->Log($class->getFullName() . " in file: " . $class->fileName . " dc: " . $dc .  " rc: " . $rc .  " issue: " . $currentIssue);
				
				$issueKey = $class->fileName . "|" . $class->getFullName();
				if ($currentIssue == "mismatch" || $currentIssue == "rc n/a" || $currentIssue == "dc n/a") {
					$problemText = GetProblemString($dc, $rc, $data->repository->html_url . "/blob/master" . substr($class->fileName, strlen($repoPath)));

					// reopen or create an issue
					$issueTracker->OpenIssue($issueKey, "Problem in class " . $class->getFullName(), $problemText, $currentIssue);

				} else {
					// close issue if it exists
					$issueTracker->CloseIssue($issueKey);
				}
			}
		}

		// save the issues
		$issueTracker->Save();
		//file_put_contents($issuesFile, json_encode($oldIssues));
	  } else {
	  	echo "Error! Something went wrong no files cloned..." . PHP_EOL;
	  }
	}
}


// returns data as an object or NULL if secret is bad or the push is not to the master

function ReadPushToMasterFromInput() {
	$request_body = file_get_contents('php://input');
	$data = json_decode($request_body);

	$secret = 'TheTruthIsOutThere';	// Possibly bad to have this here
 
	$headers = getallheaders();
	if (!isset($headers['X-Hub-Signature'])) {
		return NULL;
	}
	$hubSignature = $headers['X-Hub-Signature'];
 
	list($algo, $hash) = explode('=', $hubSignature, 2);
 
	$payloadHash = hash_hmac($algo, $request_body, $secret);
 
	if ($hash !== $payloadHash) {
		return NULL;
	}

	return $data;
}

// returns the git commandline string
function GetGitCommandLine($a_localRepoRootDir, $a_localRepoPath, $a_fullRemoteURL, $a_allowPull) {
	$localRepoFullPath = $a_localRepoRootDir . "/" . $a_localRepoPath;


	$commandline = "";

	if(file_exists($localRepoFullPath)) {
		if (file_exists($localRepoFullPath . "/.git")) {
			if ($a_allowPull) {
		  		echo ("Local Repo Exists. Pulling..." . PHP_EOL);
			    $commandline = "cd " . escapeshellarg($localRepoFullPath) . " && git pull";
			} else {
				echo ("Local Repo Exists. Pulling not allowed..." . PHP_EOL);
				$commandline = "";
			}
		} else {
			echo ("Local Folders Exists but no repo. Cloning..." . PHP_EOL);
			
			$commandline =	"cd " . escapeshellarg($localRepoFullPath) . " && " . 
							"cd .. && git clone " . escapeshellarg($a_fullRemoteURL);
		}

	} else {
		echo ("No Local Repo. Cloning..." . PHP_EOL);

		$commandline =	"cd " . escapeshellarg($a_localRepoRootDir) . " && " . 
						"mkdir " . escapeshellarg($a_localRepoPath) . " && " . 
						"cd " . escapeshellarg($a_localRepoPath) ." && " .
						"cd .. && git clone " . escapeshellarg($a_fullRemoteURL);
	}

	return $commandline;
}

function GetDeveloperClassificationString($a_str) {
	$dc = "n/a";
	if (strstr($a_str, "VIEW")) {
		$dc = "view";
	} else if (strstr($a_str, "MODEL")) {
		$dc = "model";
	} else if (strstr($a_str, "CONTROL") || strstr($a_str, "CONTROL")) {
		$dc = "controller";
	}

	return $dc;	
}

function GetDeveloperClassification($a_namespace, $a_typeName, $a_fileName) {
	// find developer classification
	// first check the namespace, then the path, then the typename	
	$dc = "n/a";
	$dc = GetDeveloperClassificationString($a_namespace);
	if ($dc == "n/a") {
		$dc = GetDeveloperClassificationString($a_fileName);
		if ($dc == "n/a") {
			$dc = GetDeveloperClassificationString($a_typeName);
		}
	}

	return $dc;
}

function GetRuleClassification($a_depth) {
	$rc = "n/a";
	if ($a_depth == 0 || $a_depth == 1) {
		$rc = "view";
	} else if ($a_depth < 0) {
		$rc = "model";
	} else if ($a_depth > 1) {
		$rc = "controller";
	}

	return $rc;
}

function GetIssueString($a_devClass, $a_ruleClass) {
	$currentIssue = "unset";
	if ($a_devClass == "n/a") {
		// could not perform dev. classification bad naming?
		$currentIssue = "dc n/a";
	} else if ($a_ruleClass == "n/a") {
		// could not perform rule classification, should never happen?
		$currentIssue = "rc n/a";
	} else if ($a_ruleClass != $a_devClass) {
		$currentIssue = "mismatch";
	} else if ($a_ruleClass == $a_devClass) {
		$currentIssue = "none";
	}

	return $currentIssue;
}

function GetProblemString($a_devClass, $a_ruleClass, $a_fileURL) {

	$problemText = "You have a potential problem in file: " . $a_fileURL . PHP_EOL;
	$problemText .= "You say the class is a: " . $a_devClass . PHP_EOL;
	$problemText .= "But it looks like a: " . $a_ruleClass . PHP_EOL . PHP_EOL;
	if ($a_ruleClass == "n/a" || $a_devClass == "n/a") {
		$problemText .= "(n/a means that you either have named the class/namespace/file/path in a way that makes it hard to know what you mean (use view, model, controller) or that the analysis of the class was inconclusive in some way.)" . PHP_EOL . PHP_EOL;
	}

	if ($a_ruleClass == 'model') {
		if ($a_devClass == 'view') {
			$problemText .= "You probably do not have any direct view responsibility in this class.";
		} else if ($a_devClass == 'controller') {
			$problemText .= "You probably do not generate any output using a view in your controller.";
		}

	} else if ($a_ruleClass == 'view') {
		if ($a_devClass == 'model') {
			$problemText .= "You probably have view responsibilty in your model class. For example generating HTML or use of some function in php that is specific for HTTP.";
		} else if ($a_devClass == 'controller') {
			$problemText .= "You probably have view responsibilty in your controller class. For example generating HTML or use of some function in php that is specific for HTTP";						
		}

	} else if ($a_ruleClass == 'controller') {
		if ($a_devClass == 'view') {
			$problemText .= "You probably do not have any direct view responsibility in this class.";
		} else if ($a_devClass == 'model') {
			$problemText .= "You probably use a class that has direct view responsibility.";
		}
	}
	return $problemText;
}

?>