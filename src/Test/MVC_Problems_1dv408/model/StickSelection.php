<?php

namespace model;


/**
 * Encapsulate how many sticks a player draws
 * can be 1,2 or 3
 */
class StickSelection {
	/**
	 * @var int (1,2,3)
	 */
	private $amount;

	public static function One() {
		return new StickSelection(1);
	}

	public static function Two() {
		return new StickSelection(2);
	}

	public static function Three() {
		return new StickSelection(3);
	}

	public function  getAmount() {
		return $this->amount;
	}

	/**
	 * Private constructor makes sure we cannot create outside of 1,2,3
	 * @param [type] $amount [description]
	 */
	private function __construct($amount) {
		$this->amount = $amount;
	}



}