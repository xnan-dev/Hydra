<?php
namespace xnan\Trurl\Nano\Check;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

class Functions { const Load=1; }

class Check {
	
	function checkFailed($msg) {
		throw new \exception("checkFailed: $msg");
	}

	function checkDiskAvailable() {
		$free=\disk_free_space(".");
		if ($free<Nano\minDiskAvailable()) {
			$this->checkFailed(sprintf("not enough disk space (%s < %s bytes)",$free,minDiskAvailable()));
		} 
	}
	
}

?>