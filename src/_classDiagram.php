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

//http://194.47.172.158:8080/rulzor/src/_classDiagram.php?basepath=%22C:/hObbE/webbdev/root/rulzor/src/Test/BasicTest%22

//new \view\ClassDiagram($p);
//new \view\ClassMatrix($p);
new \view\ClassClassification($p);
