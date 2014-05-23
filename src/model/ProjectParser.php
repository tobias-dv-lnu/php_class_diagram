<?php

namespace model;

require_once("ClassParser.php");
require_once("File.php");
require_once("Folder.php");
require_once("ClassNode.php");



class ProjectParser {
	
	private $m_classes;
	
	public function __construct(Folder $path) {
		$this->path = $path;
		$this->m_classes = array();	
	}
	
	public function getClasses() {
		
		$files = $this->path->getFiles();
		
		foreach($files as $file)  {
			
			if ($file->isDirectory() == false ) {
				try {
					$classParser = new ClassParser($file->getCode());
					$namespace = $classParser->getNamespace();
					
					$classes = $classParser->getClasses();

					


					foreach($classes as $class) {

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
					
						$typeName = $classParser->getTypeNameFromParts(array($namespace, $class));
						$newClass = $this->FindClass($typeName);
						if ($newClass == NULL) {
							$newClass = new ClassNode($namespace, $class, $fanoutClasses);
							$this->m_classes[] = $newClass;
						} else {
							$newClass->fanout = $fanoutClasses;
						}
						$newClass->fileName = $file->getFullName();
					}
				}catch(\Exception $e) {
				}
				
			}
			if ($file->isDirectory()) {
				$pp = new ProjectParser($file);
				
				$pp->getClasses();
				$this->m_classes = array_merge($this->m_classes, $pp->m_classes);
			}
		}

		return $this->m_classes;
	}

	private function FindClass($typeName) {
		foreach ($this->m_classes as $classNode) {
			if (strcmp($classNode->getFullName(), $typeName) == 0) {
			
				return $classNode;
			}
		}
		return NULL;
	}
}