<?php


// returns data as an object or NULL if secret is bad or the push is not to the master

function ReadPushToMasterFromInput() {
	$request_body = file_get_contents('php://input');
	$data = json_decode($request_body);

	$secret = 'TheTruthIsOutThere';	// Possibly bad to have this here
 
	$headers = getallheaders();
	$hubSignature = $headers['X-Hub-Signature'];
 
	list($algo, $hash) = explode('=', $hubSignature, 2);
 
	$payloadHash = hash_hmac($algo, $request_body, $secret);
 
	if ($hash !== $payloadHash) {
		return NULL;
	}

	return $data;
}

// returns the git commandline string
function GetGitCommandLine($a_localRepoRootDir, $a_localRepoPath, $a_fullRemoteURL) {
	echo "Test";
	$localRepoFullPath = $a_localRepoRootDir . "/" . $a_localRepoPath;


	$commandline = "";

	if(file_exists($localRepoFullPath)) {
		if (file_exists($localRepoFullPath . "/.git")) {
	  		echo ("Local Repo Exists. Pulling..." . PHP_EOL);
		    $commandline = "cd " . escapeshellarg($localRepoFullPath) . " && git pull";
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
	$typeName =  $a_typeName;
	if($a_namespace != "") {
		$typeName = $a_namespace . "\\" . $a_typeName;	
	}
	
	$dc = "n/a";
	if (strstr($typeName, "VIEW") || strstr($a_fileName, "VIEW")) {
		$dc = "view";
	} else if (strstr($typeName, "MODEL") || strstr($a_fileName, "MODEL")) {
		$dc = "model";
	} else if (strstr($typeName, "CONTROL") || strstr($a_fileName, "CONTROL") ||
		strstr($typeName, "CTRL") || strstr($a_fileName, "CTRL")) {
		$dc = "controller";
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