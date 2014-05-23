<?php
namespace test_ns;
require_once("ThisIsAModel.php");


	class ThisIsAThirdModel {

		public function GetString() {
			$m = new \ThisIsAModel();
			return "Third Was Here: " . $m->GetString();
		}
	}

	class ThisIsAFourthModel {

		public function GetString() {
			$m = new ThisIsAThirdModel();
			return "Fourth Was Here: " . $m->GetString();
		}
	}


?>