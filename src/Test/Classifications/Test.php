<?php

class View_GET {
	public function Funk() {
		$v = $_GET["a"];
	}
}

class View_header {
	public function Funk() {
		header("dret");
	}
}

class View_http_build_query {
	public function Funk() {
		http_build_query("dret");
	}
}

class View_REQUEST {
	public function Funk() {
		$v = $_REQUEST["a"];
	}
}

class View_CompleteHTML {
	public function Funk() {
		$v = "<h1></h1>";
	}
}

class View_CompleteHTMLWithContents {
	public function Funk() {
		$v = "<h1>Header</h1>";
	}
}

class View_SingleHTML {
	public function Funk() {
		$v = "<h1>";
	}
}

class View_CompleteHTMLWithVariable {
	public function Funk() {
		$hello = "Hello";
		$v = "<h1>" . $hello . "</h1>";
	}
}

class Model_REGEX {
	private static $REGEX_NOTE = "/^(?!\-).*(?<!\:)$/i";
}

class Model_SQL {
	private static $REGEX_NOTE = "SELECT * FROM Users WHERE uid <= 17";
}

?>