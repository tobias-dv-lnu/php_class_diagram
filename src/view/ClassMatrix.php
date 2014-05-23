<?php

namespace view;

require_once("model/Folder.php");
require_once("model/ProjectParser.php");

class ClassMatrix {
	public function __construct(\model\Project $a_project) {
		
		
		$classes = $a_project->getClasses();
		
		echo "<table border='1'>";
		echo "<tr><td>DSM</td>";
		foreach ($classes as $class) {
			echo "<td>" . $class->namespace . "-" . $class->className."</td>";
		}
		echo "</tr>";
	

		foreach ($classes as $classFrom) {
			if (isset($classFrom->fileName)) {
				echo "<tr><td>" . $classFrom->namespace . "-" . $classFrom->className ." </td>";
				foreach ($classes as $classTo) {
					echo "<td>" . $classFrom->DepthOfIsUsingClassNode($classTo) . "</td>";
				}
				echo "</tr>";
			}
		}

		echo "</table>";
	}
}



