<?php
namespace xnan\Trurl\Nano\Observer;
use xnan\Trurl;

class Functions { const Load=1; }

abstract class Observer {
	function __construct() {
	}
	
	abstract function observe(&$obj,&$arg);	 
}


?>