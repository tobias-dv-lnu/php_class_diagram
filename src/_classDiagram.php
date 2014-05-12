<?php

require_once("model/Folder.php");
require_once("view/ClassDiagram.php");

new \view\ClassDiagram(new \model\Folder($_GET["basepath"]), 
					   $_GET["selected"]);