<?php

class LogFile {

	private $m_file;

	public function __construct($a_fileName) {
		$this->m_file = @fopen($a_fileName, "a");
		if (!$this->m_file) {
			throw new Exception($php_errormsg);
		}
		$this->LogStart();
	}

	public function __destruct() {
		$this->LogEnd();
		fclose($this->m_file);
	}

	public function LogStart() {
		$this->Log( date("Y-m-d H:i:s", time()) . ": Analysis Started");
	}

	public function LogEnd() {
		$this->Log( date("Y-m-d H:i:s", time()) . ": Analysis Done");
	}

	public function Log($a_text) {
		if (!fwrite($this->m_file, $a_text . PHP_EOL)) {
			throw new Exception($php_errormsg);
		}
	}


}

?>