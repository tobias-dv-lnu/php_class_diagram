<?php

namespace model;

class PHPCode {
	public function __construct($strText) {
		$this->code= $strText;
	}

	public function __toString() {
		return $this->code;
	}
}