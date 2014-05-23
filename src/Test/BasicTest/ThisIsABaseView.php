<?php

class ThisIsABaseView {
	public function RenderHTMLDocument($a_body) {
		echo "<html><head><title>Some Crappy Title</title></head><body>";
		echo $a_body;
		echo '</body></html>';
	}
}

?>