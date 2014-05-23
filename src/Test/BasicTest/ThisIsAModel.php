<?php

require_once("ThisIsASecondModel.php");

class ThisIsAModel {

	private $m_secondModel;

	public function __construct() {
		$this->m_secondModel = new ThisIsASecondModel();
	}

	public function GetString() {
		//$this->m_secondModel = ;
		return $this->m_secondModel->GetString();
	}
}
?>