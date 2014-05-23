<?php
require_once("ThisIsAModel.php");
require_once("ThisIsAView.php");

class ThisIsAController {

	public function DoScenario() {
		$m = new ThisIsAModel();
		$v = new ThisIsAView();
		$v->Render($m);
		$v->RenderAnotherTing();
	}
}

class ThisIsAnotherControllerInSameFile {
	public function DoScenario() {
		
		$a = $_SESSION['a'];
		$a = $_POST['a'];
		$a = $_GET['a'];
		$a = $_REQUEST['a'];

	}
}

echo "<h1>Hello World</h1>";
$c = new ThisIsAController();
$c->DoScenario();
?>