<?php

namespace xnan\Trurl\Nano\Performance;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Nano\TextFormatter;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Trurl\Functions::Load;

class Performance {
	var $lastMicrotimes=array();	
	var $costSums=array();
	var $countSums=array();
	var $textFormat="text";

	function textFormat($textFormat=null) {
		if ($textFormat!=null) $this->textFormat=$textFormat;
		return $this->textFormat;
	}

	function reset() {
		$this->lastMicrotimes=array();	
		$this->costSums=array();		
		$this->countSums=array();
	}

	function track($msg) {
		if (!array_key_exists($msg,$this->lastMicrotimes)) {
			$this->lastMicrotimes[$msg]=microtime(true);					
		} else {
			$newMicrotime=microtime(true);
			$lastMicrotime=$this->lastMicrotimes[$msg];
			$timeCost=$newMicrotime-$lastMicrotime;
			if (!array_key_exists($msg,$this->costSums)) $this->costSums[$msg]=0;
			if (!array_key_exists($msg,$this->countSums)) $this->countSums[$msg]=0;
			$this->costSums[$msg]+=$timeCost;
			$this->countSums[$msg]+=1;
			//msg(sprintf("msgPerformance: $msg timeCost:%s",number_format($timeCost,4)));
			$this->lastMicrotimes[$msg]=$newMicrotime;
		}
	}

	function summaryWrite() {
		$extras=$this->textFormat=="html" ? "<br>": "";
		Nano\msg(sprintf("Nano: Performance: ##########################$extras"));
		Nano\msg(sprintf("Nano: Performance: Summary$extras"));
		foreach ($this->costSums as $msg=>$sum) {
			$count=$this->countSums[$msg];
			Nano\msg(sprintf("Nano: Performance: $msg costSum:%s$extras count:%s",number_format($sum,4),$count));
		}
		Nano\msg(sprintf("Nano: Performance: ##########################$extras"));
	}
}

class PerformanceWrapper {
 private $object;
 private $performance;
 private $methods;

 function __construct(&$performance,&$object,$methods) {
  	$this->object = $object ;
  	$this->performance=$performance;
  	$this->methods=$methods;
  }
 
 function __call($method, $args) {
 	$track=in_array($method,$this->methods);
 	$this->performance->track(sprintf("%s.%s",get_class($this->object),$method));
  	$ret=call_user_func_array(array($this->object, $method), $args);
  	$this->performance->track(sprintf("%s.%s",get_class($this->object),$method));
  	return $ret;
 }
}

?>
