<?php



function CurlPublicGet($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "tobias-dv-lnu");	// name of user or application is recomended
    $result = curl_exec($ch);
	curl_close($ch);

	return $result;
}

function GetPrivateRepoBranch($user, $name, $branch) {
	$apiUrl = "https://api.github.com/repos/" . $user . "/" . $name . "/branches/" . $branch;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERPWD, "tobias-dv-lnu:bamse1zorba");
    curl_setopt($ch, CURLOPT_USERAGENT, "tobias-dv-lnu");	// name of user or application is recomended
    $result = curl_exec($ch);
	curl_close($ch);

	return $result;
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
    curl_setopt($ch, CURLOPT_USERPWD, "tobias-dv-lnu:bamse1zorba");
    curl_setopt($ch, CURLOPT_USERAGENT, "tobias-dv-lnu");	// name of user or application is recomended
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

//echo "Hello GithUb API test";
//echo GetPublicRepos("tobias-dv-lnu");
//echo GetPublicRepo("tobias-dv-lnu", "1dv407");
//echo GetRepoBranch("tobias-dv-lnu", "1dv407", "master");
//echo GetPrivateRepoBranch("tobias-dv-lnu", "TestWebHooks", "master");
//echo GetPrivateRepoBranch("tstjostudent", "1ik415-test1", "master");
$result = PostIssue("tobias-dv-lnu", "TestWebHooks", "TestIssue", "Hello World!");

$data = json_decode($result);

var_dump($data);

?>