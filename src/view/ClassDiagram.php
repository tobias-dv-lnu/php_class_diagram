<?php

namespace view;

require_once("model/Folder.php");
require_once("model/ProjectParser.php");

class ClassDiagram {
	public function __construct(\model\Folder $source) {
		
		$parser = new \model\ProjectParser($source);
		$classes = $parser->getClasses();

		echo $this->getImageLinkYUML($classes);
	}

	private function getImageLinkYUML(array $a_classes) {
		$string = "http://yuml.me/diagram/plain;dir:LR;scale:80;/class/";

		$encodedClassNames = array();

		$first = true;
		foreach($a_classes as $fromClass) {
			foreach ($fromClass->fanout as $toClass) {
				$fromFN = $fromClass->getFullName();
				$toFN = $toClass->getFullName();	

				$from = $this->yumlClassName($fromFN, "");
				$to = $this->yumlClassName($toFN , "");
				$encodedClassNames[$fromFN] = true;
				$encodedClassNames[$toFN] = true;

				if ($first) {
					$first = false;
				} else {
					$string .= ",";
				}
				$string .= urlencode("[$from]->[$to]");
			}
		}

		// add solitary classes last
		foreach ($a_classes as $class) {
			$className = $class->getFullName();
			if (!isset($encodedClassNames[$className])) {

			if ($first) {
				$first = false;
			} else {
				$string .= ",";
			}

				$string .= urlencode("[" . $this->yumlClassName($className, "") . "]");
			}
		}

		return "<img src='$string'/>";
	}



	
	
	private function yumlClassName($className, $namespace) {
		
		$color = $this->getColor($className, $namespace);
		
		if (strpos($className, "\\") === FALSE) {
			$className = $namespace . "\\" . $className;
		}
		
		$name = str_replace("\\", "-", $className);
		
		
		
		return $name . $color;
	}
	
	private $namespacesFound = array();
	
	private function getColor($className, $namespace) {

		if (strpos($className, "\\") !== FALSE) {
			$last = strrpos($className, "\\");
			$namespace = substr($className, 0, $last);
		}
		
		$colors = array("green", "orange", "red", "blue", "gray");
		
		
		for ($i = 0; $i < count($this->namespacesFound); $i++) {
			if ($this->namespacesFound[$i] == $namespace) {
				$color = $colors[$i % (count($colors))];
				return "{bg:$color}";
			}
		} 
		if ($namespace != "") {
			$this->namespacesFound[] = $namespace;
			
			$i = count($this->namespacesFound)-1;
			$color = $colors[$i % (count($colors))];
			
			
			return "{bg:$color}";
		} else {
			return "";
		}
		
	}
	
	public static function ajaxIncludeImage($className, \model\Folder $sourceFolder) {
		$basepath = $sourceFolder->getFullName();
		return "<div id='minDivTag'>ClassDiagram</div><script>
function callback(serverData, serverStatus, id) {
        if(serverStatus == 200){
                document.getElementById(id).innerHTML = serverData;
        } else {
                document.getElementById(id).innerHTML = 'Loading diagram...'; 
        }
}
 
function ajaxRequest(openThis, id) {
 
   var AJAX = null; 
   if (window.XMLHttpRequest) { 
      AJAX=new XMLHttpRequest(); 
   } else {
      AJAX=new ActiveXObject('Microsoft.XMLHTTP'); 
   }
   if (AJAX == null) { 
      return false; 
   }
   AJAX.onreadystatechange = function() { 
      if (AJAX.readyState == 4 || AJAX.readyState == 'complete') { 
         callback(AJAX.responseText, AJAX.status, id);
      }  else { 
		  document.getElementById(id).innerHTML = 'Loading...'; 
      } 
   }
   
   var url= openThis; 
   AJAX.open('GET', url, true); 
   AJAX.send(null); 
}
 
ajaxRequest('_classDiagram.php?basepath=$basepath&selected=$className', 'minDivTag');
</script>

 
";
	}
}



