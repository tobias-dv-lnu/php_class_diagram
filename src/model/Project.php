<?php
namespace model;

require_once("model/Folder.php");

class Project {
	private $m_sourceFolder;
	private $m_classNodes;

	public function __construct(Folder $a_sourceFolder, array $a_classNodes) {
		$this->m_sourceFolder = $a_sourceFolder;
		$this->m_classNodes = $a_classNodes;
	}

	public function getClasses() {
		return $this->m_classNodes;
	}

	public function getClass($a_namespace, $a_className) {
		foreach ($this->m_classNodes as $classNode) {
			if ($classNode->namespace == $a_namespace && $classNode->className == $a_className) {
				return $classNode;
			}
		}

		return NULL;
	}
}

?>