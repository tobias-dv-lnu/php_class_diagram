<?php

// Issues
//	$secret should not be in this file?
//	Should check that it is a push to the master branch?
//	paths/commands etc are windows specific
//	$data->repository->full_name & $data->repository->clone_url are possible points
//	of sending bad stuff to shell_exec - fixed with escapeshellarg in the $commandLine, is this enoug?
//	performance large amount of code (added two of the largest old project repos to the same test, no performance problems)
//	performance many users

$request_body = file_get_contents('php://input');
$data = json_decode($request_body);

$secret = 'TheTruthIsOutThere';	// Possibly bad to have this here
 
$headers = getallheaders();
$hubSignature = $headers['X-Hub-Signature'];
 
list($algo, $hash) = explode('=', $hubSignature, 2);
 
$payloadHash = hash_hmac($algo, $request_body, $secret);
 
if ($hash !== $payloadHash) {
	// we have a bad secret
	// possibly log this
    die("");
}
 

if ($data) {
	$commandline = "";

	$LOCAL_REPO_NAME = $data->repository->full_name;
	$LOCAL_REPO_NAME = str_replace("/", "\\", $data->repository->full_name);	// mkdir cannot use /
	//var_dump($data);

	$LOCAL_ROOT         = "repo";
	$LOCAL_REPO         = "{$LOCAL_ROOT}/{$LOCAL_REPO_NAME}";
	$REMOTE_REPO        = "https://github.com/{$data->repository->full_name}";
	$BRANCH             = "master";

	// check that this is a push to the master branch?
	// when the hook is created the first payload is a bit special (not a push)
	if (isset($data->ref) && $data->ref != "refs/heads/master") {
		echo "Push to branch: " . $data->ref . PHP_EOL;
		echo "Ignoring..." . PHP_EOL;
		die("");

	}


	$creds = "https://Rulzor:Gragullesgrand12@";	// avoid ÅÄÖ in username password
	$repo = str_replace("https://", $creds, $data->repository->clone_url);
	if(file_exists($LOCAL_REPO)) {
		if (file_exists($LOCAL_REPO . "/.git")) {
	  		echo ("Local Repo Exists. Pulling..." . PHP_EOL);
		    $commandline = "cd " . escapeshellarg($LOCAL_REPO) . " && git pull";
		} else {
			echo ("Local Folders Exists but no repo. Cloning..." . PHP_EOL);
			
			$commandline =	"cd " . escapeshellarg($LOCAL_REPO) . " && " . 
							"cd .. && git clone " . escapeshellarg($repo);
		}

	} else {
		echo ("No Local Repo. Cloning..." . PHP_EOL);

		$commandline =	"cd " . escapeshellarg($LOCAL_ROOT) . " && " . 
						"mkdir " . escapeshellarg($LOCAL_REPO_NAME) . " && " . 
						"cd " . escapeshellarg($LOCAL_REPO_NAME) ." && " .
						"cd .. && git clone " . escapeshellarg($repo);

	//$commandline = "cd {$LOCAL_ROOT} && mkdir {$LOCAL_REPO_NAME} && cd {$LOCAL_REPO_NAME} && cd .. && git clone {$repo}";

	//$commandline = "cd {$LOCAL_ROOT} && mkdir {$LOCAL_REPO_NAME} && cd {$LOCAL_REPO_NAME} && cd .. && mkdir dret && git clone {$repo}";

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
  	$version = "First Pull";
  	if (isset($data->after)) {
  		$version = $data->after;
  	}
  	$log = new LogFile($repoPath . "/rulzorAnalysis.log", $version);

  	if (!isset($data->repository->owner->name)) {
  		// the payload seems to have changed...
  		$data->repository->owner->name = $data->repository->owner->login; 
  	}

  	chdir("../src");
  	require_once("model/Folder.php");
	require_once("model/Project.php");
	require_once("view/ClassDiagram.php");
	require_once("view/ClassMatrix.php");
	require_once("view/ClassClassification.php");

	
	//echo $repoPath;
	$source = new \model\Folder($repoPath);
	$parser = new \model\ProjectParser();
	$classes = $parser->getClasses($source);

	$p = new \model\Project($source, $classes);

	$issuesFile = $repoPath ."/rulzorissues.json";

	$oldIssues = json_decode(@file_get_contents($issuesFile), true);
	if (!is_array($oldIssues)) {
		$log->Log("No Old Issues Found");
		$log->Log($oldIssues);
		$oldIssues = array();
	}

	$classes = $p->getClasses();

	foreach ($classes as $class) {
		if (isset($class->fileName)) {

			// if the user already marked an issue as wontfix there is no need to analyze it.
			$issueKey = $class->fileName . "|" . $class->getFullName();
			$issue = null;
			$gitHubIssue = null;
			if (isset($oldIssues[$issueKey])) {
				$issue = $oldIssues[$issueKey];
			}

			// find developer classification
			$typeName = strtoupper($class->getFullName());
			$fileName = strtoupper($class->fileName);
			$dc = "n/a";
			if (strstr($typeName, "VIEW") || strstr($fileName, "VIEW")) {
				$dc = "view";
			} else if (strstr($typeName, "MODEL") || strstr($fileName, "MODEL")) {
				$dc = "model";
			} else if (strstr($typeName, "CONTROL") || strstr($fileName, "CONTROL") ||
				strstr($typeName, "CTRL") || strstr($fileName, "CTRL")) {
				$dc = "controller";
			}

			// perform rule classification
			$depth = $class->DepthOfIsUsingNamespace("uiapi");
			$rc = "n/a";
			if ($depth == 0 || $depth == 1) {
				$rc = "view";
			} else if ($depth < 0) {
				$rc = "model";
			} else if ($depth > 1) {
				$rc = "controller";
			}

			$currentIssue = "unset";
			if ($dc == "n/a") {
				// could not perform dev. classification bad naming?
				$currentIssue = "dc n/a";
			} else if ($rc == "n/a") {
				// could not perform rule classification, should never happen?
				$currentIssue = "rc n/a";
			} else if ($rc != $dc) {
				$currentIssue = "mismatch";
			} else if ($rc == $dc) {
				$currentIssue = "none";
			}

			$log->Log($class->getFullName() . " in file: " . $class->fileName . " dc: " . $dc .  " rc: " . $rc .  " issue: " . $currentIssue);
			
			if ($currentIssue == "mismatch" || $currentIssue == "rc n/a" || $currentIssue == "dc n/a") {

				$problemText = "You have a potential problem in file: " . $data->repository->html_url . "/blob/master" . substr($class->fileName, strlen($repoPath)) . PHP_EOL;
				$problemText .= "You say the class is a: " . $dc . PHP_EOL;
				$problemText .= "But it looks like a: " . $rc . PHP_EOL . PHP_EOL;
				if ($rc == "n/a" || $dc == "n/a") {
					$problemText .= "(n/a means that you either have named the class/namespace/file/path in a way that makes it hard to know what you mean (use view, model, controller) or that the analysis of the class was inconclusive in some way.)" . PHP_EOL . PHP_EOL;
				}

				if ($rc == 'model') {
					if ($dc == 'view') {
						$problemText .= "You probably do not have any direct view responsibility in this class.";
					} else if ($dc == 'controller') {
						$problemText .= "You probably do not generate any output using a view in your controller.";
					}

				} else if ($rc == 'view') {
					if ($dc == 'model') {
						$problemText .= "You probably have view responsibilty in your model class. For example generating HTML or use of some function in php that is specific for HTTP.";
					} else if ($dc == 'controller') {
						$problemText .= "You probably have view responsibilty in your controller class. For example generating HTML or use of some function in php that is specific for HTTP";						
					}

				} else if ($rc == 'controller') {
					if ($dc == 'view') {
						$problemText .= "You probably do not have any direct view responsibility in this class.";
					} else if ($dc == 'model') {
						$problemText .= "You probably use a class that has direct view responsibility.";
					}
				}

				// ignore, reopen or create an issue
				if (isset($oldIssues[$issueKey]) && $oldIssues[$issueKey]["number"] >= 0) {
					$result = GetIssue($data->repository->owner->name, $data->repository->name, $oldIssues[$issueKey]["number"]);
					$issue = json_decode($result);
					if (isset($issue->number)) {
						if ($issue->state == "closed") {
							// reopen if it's not a wontfix

							$wontfix = false;
							if (isset($issue->labels) && is_array($issue->labels)) {
								foreach ($issue->labels as $label) {
									if ($label->name == "wontfix") {
										$wontfix = true;
										break;
									}
								}
							}

							if (!$wontfix) {
								$result = SetIssue($data->repository->owner->name, $data->repository->name, $oldIssues[$issueKey]["number"], $problemText, true);
								$issue = json_decode($result);
								if (!isset($issue->number)) {
									$log->Log("Could Not Reopen Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);		
								} else {
									$log->Log("Reopened Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);			
								}
							}
						} else if ($issue->body != $problemText) {

							// issue could have changed
							$result = SetIssue($data->repository->owner->name, $data->repository->name, $oldIssues[$issueKey]["number"], $problemText, true);
							$issue = json_decode($result);
							if (!isset($issue->number)) {
								$log->Log("Could Not Set Issue Body on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);		
							} else {
								$log->Log("Set Issue Body on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);			
							}
						}
					} else {
						$log->Log("Could Not Find Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);
						$log->Log("Response fromg GitHub:" . $result);
					}


				} else {
					$result = PostIssue($data->repository->owner->name, $data->repository->name, "Problem in class " . $class->getFullName(), $problemText);
					$issue = json_decode($result);
					if (isset($issue->number)) {
						$issue = array("number" => $issue->number, "issue" => $currentIssue);
						$log->Log("Created Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issue/" . $issue["number"]);
						$oldIssues[$issueKey] = $issue;	
					} else {
						$log->Log("Could Not Create Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name);
						$log->Log("Response fromg GitHub:" . $result);
					}
				}
			} else {
				// close existing issue
				if (isset($oldIssues[$issueKey])) {
					$result = GetIssue($data->repository->owner->name, $data->repository->name, $oldIssues[$issueKey]["number"]);
					$issue = json_decode($result);
					if (isset($issue->number)) {
						if ($issue->state == "open") {
							// close the issue
							$result = SetIssue($data->repository->owner->name, $data->repository->name, $oldIssues[$issueKey]["number"], "", false);
							$issue = json_decode($result);
							if (!isset($issue->number)) {
								$log->Log("Could Not Close Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);		
							} else {
								$log->Log("Closed Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);	
							}
						}
					} else {
						$log->Log("Could Not Find Issue on GitHub: " . $data->repository->owner->name . "/" . $data->repository->name . "/issues/" . $oldIssues[$issueKey]["number"]);
						$log->Log("Response fromg GitHub:" . $result);
					}
				}
			}
		}
	}

	// save the issues
	file_put_contents($issuesFile, json_encode($oldIssues));
  } else {
  	echo "Something went wrong no files cloned..." . PHP_EOL;
  }

  die("done " . time());
}

?>