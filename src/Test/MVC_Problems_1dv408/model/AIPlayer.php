<?php

namespace model;

class AIPlayer {
	
	/**
	* Slightly evil AI player
	* @param int $amountOfSticksLeft
	* @return \model\StickSelection
	*/
	public function getSelection($amountOfSticksLeft) {


		$desiredAmountAfterDraw = array(21, 17, 13, 9, 5, 1);

		foreach ($desiredAmountAfterDraw as $desiredStics) {
			if ($amountOfSticksLeft > $desiredStics ) {
				$difference = $amountOfSticksLeft - $desiredStics;

				if ($difference > 3 || $difference < 1) {
					$drawInteger = rand() % 3 + 1; // [1-3]

					echo "<p>AIPlayer - \"Grr...\" </p>";
				} else {
					$drawInteger = $difference;
					echo "<p>AIPlayer - \"Got you, you have already lost!!!\"</p>  ";
				}
				break;
			}
			
		}
	

		//change from integer into valid StickSelection
		switch ($drawInteger) {
			case 1 : return StickSelection::One(); break;
			case 2 : return StickSelection::Two(); break;
			case 3 : return StickSelection::Three(); break;
		}

		//should never go here
		assert(false); 
	}
}