<?php
namespace view;
require_once("Foo.php");
use \model\Foo;
use \model\Foo as FooBar;

/*Class FooBar {
	public function getData() {
		return 16;
	}
}*/

class Bar {

	public function render() {
		$model = new Foo();
		echo "<h1>" . $model->getData() . "</h1>";
		$model = new FooBar();
		echo "<h1>" . $model->getData() . "</h1>";
	}
}

$v = new Bar();
$v->render();


?>