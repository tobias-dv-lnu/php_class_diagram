<?php

namespace view;

require_once("model/Project.php");

class ClassDiagram {
	public function __construct(\model\Project $a_project) {

		echo $this->getImageLinkYUML($a_project->getClasses());
	}

	private function getImageLinkYUML(array $a_classes) {
		$baseUrl = "http://yuml.me/diagram/plain;dir:LR;scale:80;/class/";
		$string = $baseUrl;
		$imageUrls = array();

		$encodedClassNames = array();
		$maxCharsInURL = 2000;

		$first = true;
		foreach($a_classes as $fromClass) {
			foreach ($fromClass->fanout as $toClass) {
				$fromFN = $fromClass->getFullName();
				//if ($fromClass->namespace == "uiapi") {
				//	$fromFN = "uiapi";
				//}

				$toFN = $toClass->getFullName();
				//if ($toClass->namespace == "uiapi") {
				//	$toFN = "uiapi";
				//}	

				$from = $this->yumlClassName($fromFN, "");
				$to = $this->yumlClassName($toFN , "");
				$encodedClassNames[$fromFN] = true;
				$encodedClassNames[$toFN] = true;

				if ($first) {
					$first = false;
				} else {
					$string .= ",";
				}
				$newPart = urlencode("[$from]->[$to]");
				if (strlen($newPart . $string) < $maxCharsInURL) {
					$string .= $newPart;
				} else {
					$imageUrls[] = $string;
					$string = $baseUrl . $newPart;
				}
			}
		}
		if (strlen($string) > strlen($baseUrl)) {
			$imageUrls[] = $string;
		}

		// add solitary classes last
		$string = $baseUrl;
		foreach ($a_classes as $class) {
			$className = $class->getFullName();
			if (!isset($encodedClassNames[$className])) {

				if ($first) {
					$first = false;
				} else {
					$string .= ",";
				}
				$newPart = urlencode("[" . $this->yumlClassName($className, "") . "]");
				if (strlen($newPart . $string) < $maxCharsInURL) {
					$string .= $newPart;
				} else {
					$imageUrls[] = $string;
					$string = $baseUrl . $newPart;
				}
			}
		}
		if (strlen($string) > strlen($baseUrl)) {
			$imageUrls[] = $string;
		}

		$string = "";
		foreach ($imageUrls as $url) {
			$string .= "<img src='$url'/>";
		}

		return $string;
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



