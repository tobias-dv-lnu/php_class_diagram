<?php

require_once("ThisIsAModel.php");
require_once("ThisIsABaseView.php");

class ThisIsAView extends ThisIsABaseView {
	public function Render(ThisIsAModel $a_model) {
		echo "<h1>" . $a_model->GetString() . "</h1>";
	}
}

?>