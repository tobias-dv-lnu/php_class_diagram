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
		} catch (\PHPParser_Error $e) {
			throw new \Exception('Parse Error: '. $e->getMessage());
		}
	}
	
	
	public function getDependencies() {
		$ret = array();
		//print_r($this->statements);
		
		$globalArrays = array("_SESSION", "_GET", "_POST");

		foreach ($globalArrays as $value) {
			if (strpos($this->code, $value) !== FALSE) {
				$ret[$value] = $value;
			}
		}

		
		if (preg_match_all('@<[\/\!]*?[^<>]*?>@si', $this->code, $array) >1 ) {
				$ret["\HTML"] = "\HTML";
		}
		
		
		$nodes = $this->findNodes("PHPParser_Node_Name", 
								  $this->statements);
		$nodesFull = $this->findNodes("PHPParser_Node_Name_FullyQualified", 
								  $this->statements);
		
		$nodes = array_merge($nodes, $nodesFull);
		
		$notTypes = $this->getCalledFunctions();
		$notTypes[] = $this->getNamespace();
		$notTypes = array_merge($notTypes, self::$phpKeyWords);
		$notTypes = array_merge($notTypes, get_defined_constants());
		foreach($notTypes as $notAType) {
			$notTypes[$notAType] = $notAType;
		}
		
		//print_r($notTypes);
		
		
		
		foreach ($nodes as $type) {
			$type = ($this->getTypeNameFromParts($type->parts));
			
			$isType = true;
			
			if (isset($notTypes[$type]) == false) {
				$ret[$type] = $type;
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
		
		
		$classNodes = $this->findNodes("PHPParser_Node_Stmt_Class", 
									   $this->statements);
		
		foreach ($classNodes as $node) {
			$ret[] = $node->name;
		}
		return $ret;
	}
	
	/**
	* @param array $parts
	* @return String
	*/
	private function getTypeNameFromParts($parts) {
		$ret = "";
		foreach($parts as $part) {
			if (strlen($ret) > 0 ) {
				$ret .= "\\";
			}
			$ret .= "$part";
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