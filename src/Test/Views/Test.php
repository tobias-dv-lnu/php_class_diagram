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

?>