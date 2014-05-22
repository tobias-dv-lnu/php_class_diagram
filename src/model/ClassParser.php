<?php

namespace model;

require_once("PHPCode.php");
require '../vendors/PHP-Parser-0.9.4/lib/bootstrap.php';

class ClassParser {
	private static $phpKeyWords = array("true", "TRUE", "false", 
										"FALSE", "null", "NULL", "self");
	
	public function __construct(PHPCode $code) {
		$this->code = $code;	
		$parser = new \PHPParser_Parser(new \PHPParser_Lexer);

		try {
			  $this->statements = $parser->parse($code);
			 // var_dump($this->statements);
		} catch (\PHPParser_Error $e) {
			throw new \Exception('Parse Error: '. $e->getMessage());
		}
	}
	
	
	public function getDependencies($a_className) {
		$ret = array();
		

		$statements = $this->getStatementsInClass($a_className, $this->statements);
		
		// should not use all the code in the file
		$stringStatements = $this->findNodes("PHPParser_Node_Scalar_String", $statements);
		
		foreach ($stringStatements as $stringStatement) {
			// this is not perfect and will catch opening "<"
			if (strlen($stringStatement->value) != strlen(strip_tags($stringStatement->value))) {
				$ret["HTML"] = "uiapi\\HTML";
				break;
			}
		}

		$variableStatements = $this->findNodes("PHPParser_Node_Expr_Variable", $statements);
		$globalArrays = array("_SESSION", "_GET", "_POST", "_REQUEST");

		foreach ($variableStatements as $variable) {
			foreach ($globalArrays as $key => $value) {
				if (strpos($variable->name, $value) !== FALSE) {
					$value = "uiapi\\" . $value;
					$ret[$value] = $value;
					unset($globalArrays[$key]);
					break;
				}
			}
		}	
		
		$nodes = $this->findNodes("PHPParser_Node_Name", $statements);
		$nodesFull = $this->findNodes("PHPParser_Node_Name_FullyQualified", $statements);
		
		$nodes = array_merge($nodes, $nodesFull);
		
		$notTypes = $this->getCalledFunctions();
		$notTypes[] = $this->getNamespace();
		$notTypes = array_merge($notTypes, self::$phpKeyWords);
		$notTypes = array_merge($notTypes, get_defined_constants());
		foreach($notTypes as $notAType) {
			$notTypes[$notAType] = $notAType;
		}
		
		//var_dump($nodes);
		
		foreach ($nodes as $type) {

			$typeName = ($this->getTypeNameFromParts($type->parts));
			
			$isType = true;
			
			if (isset($notTypes[$typeName]) == false) {
				//var_dump($type);
				$ret[$typeName] = $typeName;
			}
		}
		
		
		return $ret;
	}
	
	private function getCalledFunctions() {
		$ret = array();
		$nodes = $this->findNodes("PHPParser_Node_Expr_FuncCall", 
								  $this->statements);
		foreach ($nodes as $function) {
			$ret[]= ($function->name->parts[0]);
		}
		return $ret;
	}
	
	public function getArguments() {
		$ret = array();
		$nodes = $this->findNodes("PHPParser_Node_Param", 
								  $this->statements);
		
		foreach ($nodes as $parameter) {
			if ($parameter->type != null) {
				$ret[] = ($this->getTypeNameFromParts($parameter->type->parts));
			}
		}
		return $ret;
	}
	
	public function  getNamespace() {
		
		$nodes = $this->findNodes("PHPParser_Node_Stmt_Namespace", 
								  $this->statements);
		
		
		if (count($nodes) > 0) {
			$node = $nodes[0];
			$ret = $this->getTypeNameFromParts($node->name->parts);
			return $ret;
		} else {
			return "";
		}
	}
	
	public function getClasses() {
		$ret = array();
		
		//	print_r($this->statements);
		
		
		$classNodes = $this->findNodes("PHPParser_Node_Stmt_Class", $this->statements);
		
		foreach ($classNodes as $node) {
			$ret[] = $node->name;
		}

		if (count($ret) == 2) {
			$this->getStatementsInClass("ThisIsAContoller", $this->statements);
		}

		return $ret;
	}
	
	/**
	* @param array $parts
	* @return String
	*/
	public function getTypeNameFromParts($parts) {
		$ret = "";
		foreach($parts as $part) {
			if (strlen($ret) > 0 ) {
				$ret .= "\\";
			}
			$ret .= "$part";
		}
		return $ret;
	}

	static public function getClassName($a_typeName) {
		$parts = explode("\\", $a_typeName);
		return $parts[count($parts) - 1];
	}

	static public function getNamespaceName($a_typeName) {
		$parts = explode("\\", $a_typeName);
		if (count($parts) > 1) {
			return $parts[0];
		}
		return "";
	}

	private function getStatementsInClass($a_className, array $a_statements) {
		$classes = $this->findNodes("PHPParser_Node_Stmt_Class", $a_statements);
		$ret = array();
		foreach ($classes as $class) {
			if ($class->name == $a_className) {
				$ret[] = $class;
			}
		}

		return $ret;
	}
	
	/**
	* @return array
	*/
	private function findNodes($stringNodeName, $statements) {
		$ret = array();
		foreach($statements as $object) {
			
			if (is_array($object) || is_object($object)) {
				$children = $this->findNodes($stringNodeName, $object);
				$ret = array_merge($children, $ret);
			}
			if (is_object($object) == true) {
				if (strcmp(get_class($object), $stringNodeName) == 0) {
					$ret[] = $object;
				}
			}
		}
		return $ret;
	}
	
	
}