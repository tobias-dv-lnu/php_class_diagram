<?php
namespace model;

interface IFindGoalStrategy {
	public function isFound(ClassNode $a_searching);
}

class FindClassNodeStrategy implements IFindGoalStrategy {

	private $m_searchFor;

	public function __construct(ClassNode $a_searchFor) {
		$this->m_searchFor = $a_searchFor;
	}

	public function isFound(ClassNode $a_searching) {
		 return ($this->m_searchFor == $a_searching);
	}
}

class FindNamespaceStrategy implements IFindGoalStrategy {
	private $m_searchForNamespace;

	public function __construct($a_searchForNamespace) {
		$this->m_searchForNamespace = $a_searchForNamespace;
	}

	public function isFound(ClassNode $a_searching) {
		 return ($this->m_searchForNamespace == $a_searching->namespace);
	}
}

class ClassNode {
	
	public function __construct($namespace, $className, $fanout) {
		$this->namespace = $namespace;
		$this->className = $className;
		$this->fanout = $fanout;
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function getName() {
		return $this->className;
	}

	public function getFullName() {
		if ($this->namespace != "")
			return $this->namespace . "\\" . $this->className;
		else
			return $this->className;
	}

	public function DepthOfIsUsingNamespace($a_namespace) {

		return $this->DepthOfIsUsing(new FindNamespaceStrategy($a_namespace));
	}

	public function DepthOfIsUsingClassNode(ClassNode $a_classNode) {
		return $this->DepthOfIsUsing(new FindClassNodeStrategy($a_classNode));
	}


	private function DepthOfIsUsing(IFindGoalStrategy $a_searchFor) {
		if ($a_searchFor->isFound($this)) {
			return 0;
		}

		$openList = $this->fanout;
		$closedList = array();
		$closedList[] = $this;

		return $this->DepthOfIsUsingRecurse($a_searchFor, $openList, $closedList);
	}

	private function DepthOfIsUsingRecurse(IFindGoalStrategy $a_searchFor, array $a_openList, array &$a_closedList) {
		$nextLevel = array();
		foreach ($a_openList as $classNode) {

			if ($a_searchFor->isFound($classNode)) {
				return 1;
			}

			foreach ($a_closedList as $checkedNode) {
				if ($checkedNode == $classNode) {
					$classNode = NULL;
					break;
				}
			}

			if ($classNode != NULL) {
				$a_closedList[] = $classNode;
				
				$nextLevel = array_merge($nextLevel, $classNode->fanout);
			}
		}

		// this level is now checked recurse to next level
		if (count($nextLevel) > 0) {
			$depth = $this->DepthOfIsUsingRecurse($a_searchFor, $nextLevel, $a_closedList);
			if ($depth > 0) {
				return 1 + $depth;
			}
		}

		// not found
		return -1;
	}

	public function getRelativeClassName($other, $classes) {

		if (strpos($other, "\\") === false) {
			$inThisClassNameSpace = $this->namespace . "\\" . $other;	

			foreach ($classes as $key => $class) {
				$sameNamespace = (strcmp($class->namespace, $this->namespace) == 0);
				$sameName = (strcmp($other, $this->className) == 0);
				if ($sameNamespace && $sameName) {
			//		echo "found $other as $inThisClassNameSpace in $class->className $this->className<br/>";
					return $inThisClassNameSpace;
				}
			}
			//echo "not found $other as $inThisClassNameSpace<br/>";
			return $other;
		}
		else
			return $other;
	}
}