<?php

//date_default_timezone_set("Europe/Stockholm");
$tz = new DateTimeZone("Europe/Stockholm");
$now = new DateTime("now", $tz);
$deadline = new DateTime("2014-10-22 12:05", $tz);

if ($deadline->getTimestamp() < $now->getTimestamp()) {
	die("Sorry.. deadline has passed");
}

require_once("onpush_functions.php");
$request_body = file_get_contents('php://input');
$data = ReadPushToMasterFromInput();
set_time_limit(60);
PerformAnalysis($data, false);
die("done " . time());



?>