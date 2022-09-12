<?php
namespace xnan\Trurl\Nano\Observer;
use xnan\Trurl;

class Observable {
	var $observers=array();	
	function __construct() {		
	}
	
	function addObserver(&$observer) {
		/*printf("%s observers:$s",spl_object_hash($this),count($this->observers));
		print "<pre>";
		debug_print_backtrace();		
		print "</pre>";
		print "<br><br><br>\n";*/
		foreach($this->observers as $observer1) {
			if (spl_object_hash($observer1)==spl_object_hash($observer)) return $observer;
		}
		$this->observers[]=$observer;
		return $observer;
	}


	function observeAll(&$arg) {
		//print_r($this->observers);
		foreach($this->observers as $observer) {
			//printf("observeAll observe: class:%s hash:%s\n",get_class($observer),spl_object_hash($observer));
			$observer->observe($this,$arg);
		}
	}	 
}


?>