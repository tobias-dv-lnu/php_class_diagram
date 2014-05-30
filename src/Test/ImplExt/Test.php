<?php
namespace model;

interface Foo {

}

class Bar {

}

class FooBar extends Bar implements Foo {
	public function __construct() {
		parent::__construct();
	}
}

?>