<?php

namespace view;

require_once("model/Folder.php");
require_once("model/ProjectParser.php");

class ClassMatrix {
	public function __construct(\model\Project $a_project) {
		
		
		$classes = $a_project->getClasses();
		
		echo "<table border='1'>";
		echo "<tr><th>DSM</th>";
		foreach ($classes as $class) {
			echo "<th>" . $class->namespace . "-" . $class->className."</th>";
		}
		echo "</tr>";
	

		foreach ($classes as $classFrom) {
			if (isset($classFrom->fileName)) {
				echo "<tr><td>" . $classFrom->namespace . "-" . $classFrom->className ." </td>";
				foreach ($classes as $classTo) {
					$depth = $classFrom->DepthOfIsUsingClassNode($classTo);
					if ($depth < 0) {
						$depth = " ";
					}
					echo "<td>" . $depth . "</td>";
				}
				echo "</tr>";
			}
		}

		echo "</table>";
	}
}



