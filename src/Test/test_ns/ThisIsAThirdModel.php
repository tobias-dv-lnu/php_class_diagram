<?php
namespace test_ns {
require_once("ThisIsAModel.php");


	class ThisIsAThirdModel {

		public function GetString() {
			$m = new \ThisIsAModel();
			return "Third Was Here: " . $m->GetString();
		}
	}
}
?>