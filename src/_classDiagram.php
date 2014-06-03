<?php

require_once("model/Folder.php");
require_once("model/Project.php");
require_once("view/ClassDiagram.php");
require_once("view/ClassMatrix.php");
require_once("view/ClassClassification.php");

$source = new \model\Folder($_GET["basepath"]);
$parser = new \model\ProjectParser();
$classes = $parser->getClasses($source);

$p = new \model\Project($source, $classes);

//new \view\ClassDiagram($p);
//new \view\ClassMatrix($p);
new \view\ClassClassification($p);
