<?php
namespace view;

require_once("model/Project.php");

class ClassClassification {

	public function __construct(\model\Project $a_project) {
		
		
		$classes = $a_project->getClasses();
		
		echo "<table border='1'>";
		echo "<tr><th>Class</th><th>Rule Classification</th><th>Developer Classification</th></tr>";
		foreach ($classes as $class) {

			if (isset($class->fileName)) {
				$row = "<td>" . $class->namespace . "-" . $class->className."</td><td>";
				$depth = $class->DepthOfIsUsingNamespace("uiapi");
				$rc = "model";
				if ($depth <= 0) {
					$row .= "model ";
				} else if ($depth > 1) {
					$row .= "controller ";
					$rc = "controller";
				} else if ($depth == 1) {
					$row .= "view ";
					$rc = "view";
				}

				$row .= "</td><td>";
				$typeName = strtoupper($class->getFullName());
				$dc = "view";
				if (strstr($typeName, "VIEW")) {
					$row .= "view";
				} else if (strstr($typeName, "MODEL")) {
					$row .= "model";
					$dc = "model";
				} else {
					$row .= "controller";
					$dc = "controller";
				}
				$row .="</td>";

				if ($rc == $dc) {
					echo "<tr bgcolor='lightgreen'>";
				} else {
					echo "<tr bgcolor='red'>";
				}
				
				echo $row;
				echo "</tr>";
			}

		}
		echo "</tr>";

		echo "</table>";
	}
}

?>