<?php

require_once("ThisIsAModel.php");

class ThisIsAView {
	public function Render(ThisIsAModel $a_model) {
		echo "<h1>" . $a_model->GetString() . "</h1>";
	}
}

?>