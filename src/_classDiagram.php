<?php

require_once("model/Folder.php");
require_once("view/ClassDiagram.php");
require_once("view/ClassMatrix.php");

new \view\ClassDiagram(new \model\Folder($_GET["basepath"]));
new \view\ClassMatrix(new \model\Folder($_GET["basepath"]));
