<?php

namespace view;

require_once("model/Folder.php");
require_once("model/ProjectParser.php");

class ClassMatrix {
	public function __construct(\model\Folder $source) {
		

		$parser = new \model\ProjectParser($source);
		
		$classes = $parser->getClasses();

		//$classes[] = new \model\ClassNode("", "\\HTML", array());
		
		echo "<table border='1'>";
		echo "<tr><td>DSM</td>";
		foreach ($classes as $class) {
			echo "<td>" . $class->namespace . "-" . $class->className."</td>";
		}
		echo "</tr>";
	

		foreach ($classes as $classFrom) {
			echo "<tr><td>" . $classFrom->namespace . "-" . $classFrom->className ." </td>";
			foreach ($classes as $classTo) {
				echo "<td>" . $classFrom->DepthOfIsUsing($classTo) . "</td>";
			}
			echo "</tr>";
		}

		echo "</table>";
	}

	private function DepthOfIsUsing(\model\ClassNode $a_from, \model\ClassNode $a_to) {
		if ($a_from == $a_to) {
			return "";
		}

		// direct dependencies
		foreach ($a_from->fanout as $name) {
			if ($a_to->className == $name) {
				return 1;
			}
		}

		// search decendants
		foreach ($a_from->fanout as $name) {

		}

		return 0;
	}
}



