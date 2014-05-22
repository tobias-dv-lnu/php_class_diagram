<?php

namespace model;


class ClassNode {
	
	public function __construct($namespace, $className, $fanout) {
		$this->namespace = $namespace;
		$this->className = $className;
		$this->fanout = $fanout;
	}

	public function getFullName() {
		if ($this->namespace != "")
			return $this->namespace . "\\" . $this->className;
		else
			return $this->className;
	}

	public function DepthOfIsUsing(ClassNode $a_node) {
		if ($a_node == $this) {
			return 0;
		}

		$openList = $this->fanout;
		$closedList = array();
		$closedList[] = $this;

		return $this->DepthOfIsUsingRecurse($a_node, $openList, $closedList);
	}

	private function DepthOfIsUsingRecurse(ClassNode $a_searchFor, array $a_openList, array &$a_closedList) {
		$nextLevel = array();
		foreach ($a_openList as $classNode) {

//echo "<br>classNode: ";
//var_dump($classNode);

			if ($classNode == $a_searchFor) {
				return 1;
			}

			foreach ($a_closedList as $checkedNode) {
				if ($checkedNode == $classNode) {
					$classNode = NULL;
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
					echo "found $other as $inThisClassNameSpace in $class->className $this->className<br/>";
					return $inThisClassNameSpace;
				}
			}
			echo "not found $other as $inThisClassNameSpace<br/>";
			return $other;
		}
		else
			return $other;
	}
}