<?php
namespace xnan\Trurl\Nano\Lock;

//Uses: Start

// Uses: Nano
use xnan\Trurl;
use xnan\Trurl\Nano;
use xnan\Trurl\Nano\Log;
use xnan\Trurl\Nano\Lock;
Trurl\Functions::Load;
Log\Functions::Load;

//Uses: End
class FileLock {
	var $fileName;
	var $lockHandler;
	var $locked=false;
	function __construct($fileName) {
		if ($fileName==null || strlen($fileName)==0) checkFailed("fileName cannot be empty");
		$this->fileName=$fileName;
		if ($this->fileName==null || strlen($this->fileName)==0) checkFailed("fileName cannot be empty");
	}

	function isLocked() {
		return $this->locked;
	}

	function writerLockTry() {
			if ($this->fileName==null || strlen($this->fileName)==0) checkFailed("fileName cannot be empty");

		if (!file_exists($this->fileName)) file_put_contents($this->fileName,time());

		$this->lockHandler = fopen($this->fileName, "r+");
		$ret=false;
		if (flock($this->lockHandler, LOCK_EX)) {
		    ftruncate($this->lockHandler, 0);
		    fwrite($this->lockHandler, time());
		    fflush($this->lockHandler); // flush output before releasing the lock
		    $ret=true;
		} else {			
			$ret=false;	    
		}
		return $ret;
	}

	function writerLock() {		
		if ($this->locked) return;
		$this->locked=false;
		$tries=0;

		do  {		
			if (!$this->locked) $this->locked=$this->writerLockTry();						

			if (!$this->locked) {
				time_nanosleep(1, 100*1000+rand(0,100*1000)); //1seg+[0,100]microsegundos
				++$tries;
				$this->writerUnlock();

			}
		} while (!$this->locked && $tires<10);
		if (!$this->locked) checkFailed("writeLock: msg: cannot get exclusive lock");
	}

	function writerUnlock() {	    		
		if ($this->lockHandler!=null) {
			$this->locked=false;
			flock($this->lockHandler, LOCK_UN);    // release the lock
			fclose($this->lockHandler);	
			@unlink($this->fileName);
			$this->lockHandler=null;
		} 	
	}

}

?>
