<?php



require_once("onpush_functions.php");
$request_body = file_get_contents('php://input');
$data = ReadPushToMasterFromInput();
set_time_limit(60);
PerformAnalysis($data, true);
die("done " . time());



?>