<?php
namespace view;

require_once("model/Project.php");

class ClassClassification {

	public function __construct(\model\Project $a_project) {
		
		
		$classes = $a_project->getClasses();

		//var_dump($classes);
		
		echo "<table border='1'>";
		echo "<tr><th>Class</th><th>Rule Classification</th><th>Developer Classification</th></tr>";
		foreach ($classes as $class) {

			if (isset($class->fileName)) {
				$row = "<td>" . $class->namespace . "-" . $class->className."</td><td>";
				$depth = $class->DepthOfIsUsingNamespace("uiapi");
				$rc = "model";
				if ($depth == 0) {
					$row .= "uiapi ";
					$rc = "uiapi";
				} else if ($depth < 0) {
					$row .= "model ";
				} else if ($depth > 1) {
					$row .= "controller ";
					$rc = "controller";
				} else {
					$row .= "view ";
					$rc = "view";
				}

				$row .= "</td><td>";
				$typeName = strtoupper($class->getFullName());
				$fileName = strtoupper($class->fileName);
				$dc = "view";
				if ($rc == "uiapi") {
					$dc = "uiapi";
					$row .= "uiapi";
				} else if (strstr($typeName, "VIEW") || strstr($fileName, "VIEW")) {
					$row .= "view";
				} else if (strstr($typeName, "MODEL") || strstr($fileName, "MODEL")) {
					$row .= "model";
					$dc = "model";
				} else if (	strstr($typeName, "CONTROL") || strstr($fileName, "CONTROL") ||
							strstr($typeName, "CTRL") || strstr($fileName, "CTRL")){
					$row .= "controller";
					$dc = "controller";
				} else {
					$rc = "uiapi";
					$row .= "n/a";
				}
				$row .="</td>";

				if ($rc == "uiapi") {
					echo "<tr bgcolor='white'>";
				} else if ($rc == $dc) {
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