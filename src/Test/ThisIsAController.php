<?php
require_once("ThisIsAModel.php");
require_once("ThisIsAView.php");

class ThisIsAController {

	public function DoScenario() {
		$m = new ThisIsAModel();
		$v = new ThisIsAView();
		$v->Render($m);
	}
}
?>