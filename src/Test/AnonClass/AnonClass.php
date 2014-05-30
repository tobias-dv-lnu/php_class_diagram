<?php

class AnonClass {

	public function getData() {
		return 17;
	}
}

class MiddleMan {

	public function getAnonClass() {
		return new AnonClass();
	}
}

class Foo {
	public function render() {
		$mm = new MiddleMan();
		$ac = $mm->getAnonClass();

		echo "<h1>" . $ac->getData() . "</h1>";
	}
}

class Bar {
	public function render() {
		$mm = new MiddleMan();
		$this->doRender($mm->getAnonClass());
	}

	private function doRender(AnonClass $a_anon) {
		echo "<h1>" . $a_anon->getData() . "</h1>";	
	}
}

$f = new Foo();
$f->render();

$b = new Bar();
$b->render();

?>