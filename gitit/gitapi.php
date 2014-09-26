<?php

function GetGitHubUser() {
	return "Rulzor";
};
function GetGitHubPWD() {
	return "Gragullesgrand12";
}
function GetGitHubUserPWD() {
	return GetGitHubUser() . ":" . GetGitHubPWD();
}

function CurlPublicGet($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, GetGitHubUser());	// name of user or application is recomended
    $result = curl_exec($ch);
	curl_close($ch);

	return $result;
}

function CurlInitPrivate() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERPWD, GetGitHubUserPWD());
    curl_setopt($ch, CURLOPT_USERAGENT, GetGitHubUser());	// name of user or application is recomended
	return $ch;
}

function CurlPrivateGet($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERPWD, GetGitHubUserPWD());
    curl_setopt($ch, CURLOPT_USERAGENT, GetGitHubUser());	// name of user or application is recomended
    $result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function GetPrivateRepoBranch($user, $name, $branch) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $name . "/branches/" . $branch;
	return CurlPrivateGet($apiUrl);
}

function PostIssue($user, $repoName, $title, $body) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $repoName . "/issues";
	$post = array("title" => $title, "body" => $body, "labels" => array("bug", "rulzor"));
	$json = json_encode($post);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_USERPWD, GetGitHubUserPWD());
    curl_setopt($ch, CURLOPT_USERAGENT, GetGitHubUser());	// name of user or application is recomended
    $result = curl_exec($ch);
	curl_close($ch);

	return $result;
}

function GetRepoBranch($user, $name, $branch) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $name . "/branches/" . $branch;
	return CurlPublicGet($apiUrl);
}

function GetPublicRepo($user, $name) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $name;
	return CurlPublicGet($apiUrl);
}

function GetPublicRepos($user) {
	$apiUrl = "https://api.github.com/users/" . $user . "/repos";
	return CurlPublicGet($apiUrl);;
}

function GetIssue($user, $repoName, $issueNo) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $repoName . "/issues/" . $issueNo;
	return CurlPrivateGet($apiUrl);
}

function GetIssueFast($ch, $user, $repoName, $issueNo) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $repoName . "/issues/" . $issueNo;
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
	$result = curl_exec($ch);
	
	return $result;
}

function SetIssue($user, $repoName, $issueNo, $body, $open) {
	$status = "closed";
	if ($open) {
		$status = "open";
	}

	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $repoName . "/issues/" . $issueNo;
	$post = array("state" => $status);
	if (strlen($body) > 0) {
		$post = array("body" => $body, "state" => $status);
	}
	$json = json_encode($post);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_USERPWD, GetGitHubUserPWD());
    curl_setopt($ch, CURLOPT_USERAGENT, GetGitHubUser());
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

    $result = curl_exec($ch);
	curl_close($ch);

	return $result;

}


/*echo GetIssue("tobias-dv-lnu", "TestWebHooks", 1);
echo GetIssue("tobias-dv-lnu", "TestWebHooks", 2);
echo GetIssue("tobias-dv-lnu", "TestWebHooks", 3);
echo GetIssue("tobias-dv-lnu", "TestWebHooks", 4);
echo GetIssue("tobias-dv-lnu", "TestWebHooks", 5);
echo GetIssue("tobias-dv-lnu", "TestWebHooks", 6);
echo GetIssue("tobias-dv-lnu", "TestWebHooks", 7);*/
/*$ch = CurlInitPrivate();
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 1);
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 2);
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 3);
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 4);
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 5);
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 6);
echo GetIssueFast($ch, "tobias-dv-lnu", "TestWebHooks", 7);
curl_close($ch);*/

?>