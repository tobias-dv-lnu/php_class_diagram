<?php

require_once("ThisIsAModel.php");
require_once("ThisIsABaseView.php");
require_once("test_ns/ThisIsAThirdModel.php");

class ThisIsAView extends ThisIsABaseView {

	public function RenderAnotherTing() {
		$a_model = new \test_ns\ThisIsAThirdModel();
		echo "<div>";
		echo "</div>";
		echo "<h1>" . $a_model->GetString() . "</h1>";
	}

	public function Render(ThisIsAModel $a_model) {
		echo "<h1>" . $a_model->GetString() . "</h1>";
	}
}

?>