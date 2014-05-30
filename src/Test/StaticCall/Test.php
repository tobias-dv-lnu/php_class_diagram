<?php

class Foo {

	private static function EchoHello() {
		echo "Hello";
	}

	public static function DoStuff() {
		self::EchoHello();
	}
}

class Bar {
	private static $g_const = 'World';

	public function CallDoStuff() {
		Foo::DoStuff();
		echo self::$g_const;
	}
}


$b = new Bar();
$b->CallDoStuff();

?>