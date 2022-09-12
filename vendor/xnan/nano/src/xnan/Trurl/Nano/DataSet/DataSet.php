<?php
namespace xnan\Trurl\Nano\DataSet;
use xnan\Trurl;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

class Functions { const Load=1; }

class DataSet {
	var $header=array();
	var $rows=array();
	
	function __construct($header=array("column1")) {
		$this->header=$header;
	}
	
	function addRow($row) {
		return $this->rows[]=$row;
	}

	function deleteRows() {
		$this->rows=[];
	}

	function toCsvRet() {
		$lineStr=sprintf("%s\n",implode(";",$this->header));
		$ret="".$lineStr;

		foreach($this->rows as $row) {
			$lineStr=sprintf("%s\n",implode(";",$row));
			$ret.=$lineStr;			
		}
		return $ret;
	}

	function rows() {
		return $this->rows;
	}

	function toCsv($fileName="toCsv.csv",$append=false) {
		Nano\nanoCheck()->checkDiskAvailable();
		$lineStr=sprintf("%s\n",implode(";",$this->header));

		if (!$append || !file_exists($fileName)) { //header
			$bytes=file_put_contents($fileName,$lineStr);	
			if ($bytes===false) Nano\nanoCheck()->checkFailed("cannot write csv to file: $fileName");
		}
		
		$linesStr="";
		foreach($this->rows as $row) {
			$lineStr=sprintf("%s\n",implode(";",$row));
			$linesStr.=$lineStr;			
		}				
		$bytes=file_put_contents($fileName,$linesStr,FILE_APPEND);
		if ($bytes===false) Nano\nanoCheck()->checkFailed("cannot append csv to file: $fileName");

		return true;
	}
	 
}


?>