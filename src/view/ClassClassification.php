<?php
namespace view;

require_once("model/Project.php");

class ClassClassification {

	public function __construct(\model\Project $a_project) {
		
		
		$classes = $a_project->getClasses();
		
		echo "<table border='1'>";
		echo "<tr><td>Class</td><td>Classification</td></tr>";
		foreach ($classes as $class) {

			if ($class->namespace != "uiapi") {
				echo "<tr><td>" . $class->namespace . "-" . $class->className."</td><td>";
				$depth = $class->DepthOfIsUsingNamespace("uiapi");
				if ($depth <= 0) {
					echo "model ";
				} else if ($depth > 1) {
					echo "controller ";
				} else if ($depth == 1) {
					echo "view ";
				}
				echo $depth;

				echo "</td></tr>";
			}

		}
		echo "</tr>";

		echo "</table>";
	}
}

?>