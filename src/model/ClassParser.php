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
		if (count($statements) != 1) {
			return $ret;
		}

		//var_dump($statements);
		
		if ($this->dependsOnHTML($statements)) {
			$ret["uiapi\\HTML"] = "uiapi\\HTML";
		}

		$variableStatements = $this->findNodes("PHPParser_Node_Expr_Variable", $statements);
		$viewArrays = array("_GET", "_POST", "_REQUEST", "_FILES", "_SERVER");

		foreach ($variableStatements as $variable) {
			foreach ($viewArrays as $key => $value) {
				if (strpos($variable->name, $value) !== FALSE) {
					$value = "uiapi\\" . $value;
					$ret[$value] = $value;
					unset($viewArrays[$key]);
					break;
				}
			}
		}

		$functionStatements = $this->findNodes("PHPParser_Node_Expr_FuncCall", $statements);
		$viewFunctions = array("header", "get_headers", "parse_url", "urldecode", "urlencode", "rawurldecode", "rawurlencode", "http_build_query", "setcookie");
		foreach ($functionStatements as $function) {
			foreach ($viewFunctions as $key => $value) {
				$name = $function->name;
				if ($name->getType() == "Name") {
					if (strpos($name, $value) !== FALSE) {
						$value = "uiapi\\" . $value;
						$ret[$value] = $value;
						unset($viewFunctions[$key]);
						break;
					}
				} else {
					// this is the situation where whe a callable argument is used
					// like:
					//	function DoFunction(callable $a_function) {
					//		$a_function(...);
					//	}
					// should not strategy pattern be used here instead?
				}
			}
		}		
	
		$useAliases = $this->getUseAliases();

		$newStatements = $this->findNodes("PHPParser_Node_Expr_New", $statements);
		$methodStatements = $this->findNodes("PHPParser_Node_Stmt_ClassMethod", $statements);
		$namespace = rtrim($this->getNamespace(), "\\");
		$extends = $statements[0]->extends;
		$implements = $statements[0]->implements;
		$staticCalls = $this->findNodes("PHPParser_Node_Expr_StaticCall", $statements);

		//var_dump($staticCalls);
		

		foreach ($newStatements as $node) {
			$class = $node->class;
			if ($class) {
				$typeName = $this->getTypeNameFromNode($class, $useAliases, $namespace);
				if ($typeName) {
					$ret[$typeName] = $typeName;
				}
			}
		}

		foreach ($methodStatements as $node) {
			$params = $node->params;
			if ($params) {
				foreach ($params as $param) {
					$type = $param->type;
					if ($type && !is_string($type)) {	// "array" is treated as a string
						$parts = $type->parts;
						if ($parts) {
							if ($parts[0] == "callable") {
								continue;
							}
						}
						$typeName = $this->getTypeNameFromNode($type, $useAliases, $namespace);
						if ($typeName) {
							$ret[$typeName] = $typeName;
						}
					}
				}
			}
		}

		if ($extends != NULL) {
			$typeName = $this->getTypeNameFromNode($extends, $useAliases, $namespace);
			if ($typeName) {
				$ret[$typeName] = $typeName;
			}
		}

		if ($implements != NULL) {
			foreach ($implements as $interface) {
				$typeName = $this->getTypeNameFromNode($interface, $useAliases, $namespace);
				if ($typeName) {
					$ret[$typeName] = $typeName;
				}
			}
		}

		foreach ($staticCalls as $node) {
			$class = $node->class;
			if ($class) {
				$parts = $class->parts;
				if ($parts) {
					if ($parts[0] == "parent" || $parts[0] == "self") {
						continue;
					}
				}
				$typeName = $this->getTypeNameFromNode($class, $useAliases, $namespace);
				if ($typeName) {
					$ret[$typeName] = $typeName;
				}
			}
		}

		//var_dump($newStatements);
		

		return $ret;
	}

	private function getTypeNameFromNode(\PHPParser_Node_Name $a_node, array $a_useAliases, $a_namespace) {
		$parts = $a_node->parts;
		if ($parts) {
			$typeName = $this->getTypeNameFromParts($parts);
			if ($a_node->getType() == "Name" && strlen($a_namespace) > 0) {	// not a fully qualified name
				$typeName = $a_namespace . "\\" . $typeName;
			}
			if (isset($a_useAliases[$parts[0]])) {
				$typeName = $a_useAliases[$parts[0]];
			}
			return $typeName;
		}
		return NULL;
	}

	private function containsHTML($a_string) {

		$string = preg_replace ('/<[^>]*>/', '', $a_string);

		//echo $a_string . " " . $string . "<br>";

		// this is not perfect and will catch opening "<"
		if (strlen($a_string) != strlen($string)) {
				// check for both < and >
			return true;
		}
		return false;
	}

	private function dependsOnHTML(array $a_statements) {
		$stringStatements = $this->findNodes("PHPParser_Node_Scalar_String", $a_statements);	
		foreach ($stringStatements as $stringStatement) {
			
			if ($this->containsHTML($stringStatement->value)) {
				return true;
			}
		}

		// this is for checking encapsed scalar strings like "<img src='$string'/>"
		$stringStatements = $this->findNodes("PHPParser_Node_Scalar_Encapsed", $a_statements);	
		foreach ($stringStatements as $stringStatement) {
			foreach ($stringStatement->parts as $part) {
				if (is_string($part)) {
					if ($this->containsHTML($part)) {
						return true;
					}					
				}
			}
		}

		return false;
	}
	
	/*private function getCalledFunctions($a_namespace, array $a_statements) {
		$ret = array();
		$nodes = $this->findNodes("PHPParser_Node_Expr_FuncCall", $a_statements);
		foreach ($nodes as $function) {
			$parts = $function->name->parts;
			if ($parts) {
				if (count($parts) > 1) {
					$ret[] = $this->getTypeNameFromParts($parts);
				} else {
					$ret[] = $a_namespace . $this->getTypeNameFromParts($parts);
				}
			}
		}
		return $ret;
	}*/
	
	/*public function getArguments() {
		$ret = array();
		$nodes = $this->findNodes("PHPParser_Node_Param", 
								  $this->statements);
		
		foreach ($nodes as $parameter) {
			if ($parameter->type != null) {

				$ret[] = ($this->getTypeNameFromParts($parameter->type->parts));
			}
		}
		return $ret;
	}*/
	
	public function  getNamespace() {
		
		$nodes = $this->findNodes("PHPParser_Node_Stmt_Namespace", $this->statements);
		
		if (count($nodes) > 0) {
			$node = $nodes[0];
			$ret = $this->getTypeNameFromParts($node->name->parts);
			return $ret;
		} else {
			return "";
		}
	}

	public function getUseAliases() {
		$nodes = $this->findNodes("PHPParser_Node_Stmt_UseUse", $this->statements);
		$aliases = array();

		foreach ($nodes as $node) {
			$name = $this->getTypeNameFromParts($node->name->parts);
			$alias = $node->alias;
			$aliases[$alias] = $name;
		}

		return $aliases;
	}
	
	public function getTypes() {
		$ret = array();
		
		$classNodes = $this->findNodes("PHPParser_Node_Stmt_Class", $this->statements);
		$interfaceNodes = $this->findNodes("PHPParser_Node_Stmt_Interface", $this->statements);
		$types = array_merge($classNodes, $interfaceNodes);
		foreach ($types as $node) {
			$ret[] = $node->name;
		}

		return $ret;
	}
	
	/**
	* @param array $parts
	* @return String
	*/
	static public function getTypeNameFromParts($parts) {
		$ret = "";
		foreach($parts as $part) {
			if (strlen($ret) > 0) {
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
			unset($parts[count($parts) - 1]);
			return ClassParser::getTypeNameFromParts($parts);
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