<?php

namespace model;

require_once("ClassParser.php");
require_once("File.php");
require_once("Folder.php");
require_once("ClassNode.php");



class ProjectParser {
	
	private $m_classes;
	
	public function __construct() {
		//$this->path = $path;
		$this->m_classes = array();	
	}
	
	public function getClasses($a_file) {
		//echo $a_file->getFullName();
		if ($a_file->isDirectory() == false ) {
			try {
				$classParser = new ClassParser($a_file->getCode());
				$namespace = $classParser->getNamespace();
				
				$classes = $classParser->getTypes();
				//echo $a_file->getFullName();

				foreach($classes as $class) {
					//print_r($class);
					// possibly we should check if a class is already parsed
					// this could avoid duplicate class declarations

					$fanout = $classParser->getDependencies($class);

					$fanoutClasses = array();
					foreach ($fanout as $typeName) {
						$fanOutClass = $this->FindClass($typeName);
						if ($fanOutClass == NULL) {
							$ns = $classParser->getNamespaceName($typeName);
							$name = $classParser->getClassName($typeName);
							$fanOutClass = new ClassNode($ns, $name, array());

							$this->m_classes[] = $fanOutClass;
						}
						$fanoutClasses[] = $fanOutClass;
					}
					//echo (": got Dependencies<br>");
				
					$typeName = $classParser->getTypeNameFromParts(array($namespace, $class));
					$newClass = $this->FindClass($typeName);
					if ($newClass == NULL) {
						$newClass = new ClassNode($namespace, $class, $fanoutClasses);
						$this->m_classes[] = $newClass;
					} else {
						$newClass->fanout = $fanoutClasses;
					}
					$newClass->fileName = $a_file->getFullName();
				}
			}catch(\Exception $e) {
				echo "Exception when handling file: " . $a_file->getFullName() . " :<br>";
				echo $e;
				echo "<br>Will continue with next file...<br><br>";
				//throw $e;
			}

			//echo "All Done!";
			
		} else {
			$files = $a_file->getFiles();
			foreach($files as $file)  {
				$this->getClasses($file);
			}
		}


		return $this->m_classes;
	}

	private function FindClass($typeName) {


		foreach ($this->m_classes as $classNode) {
			//echo "Comparing: " . $classNode->getFullName() . " - " . $typeName . "</br>";
			if (strcmp($classNode->getFullName(), $typeName) == 0) {
			
				return $classNode;
			}
		}
		return NULL;
	}
}