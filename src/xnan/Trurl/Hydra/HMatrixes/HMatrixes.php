<?php
namespace xnan\Trurl\Hydra\HMatrixes;
//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Nano
use xnan\Trurl;
//use xnan\Trurl\Nano;
use xnan\Trurl\Nano\Log;
use xnan\Trurl\Nano\Lock;
Log\Functions::Load;

// Uses: Hydra
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HRefs;
//use xnan\Trurl\Hydra\HMatrixes;

//Uses: End

class HMatrixes{
	var $maxMatrixId=null;
	var $hMatrixes=null;
	static $instance=null;
	
	function __construct() {		
	}

	static function instance() {
		if (HMatrixes::$instance==null) HMatrixes::$instance=new HMatrixes();
		return HMatrixes::$instance;
	}

	function createHMatrix($name=null,&$dimensions=[2,2]) {
		//print "CREATE REF $this->maxMatrixId!<br>";
		//debug_print_backtrace();
		$this->hydrateIfReq();
		$m=new HMatrix($this->maxMatrixId,$name,$dimensions);
		$this->hMatrixes[$this->maxMatrixId]=$m;
		++$this->maxMatrixId;
		return $m;
	}

	function retrieveHMatrix($id,$name=null,$dimensions=null) {
		$m=new HMatrix($id,$name,$dimensions);
		$this->hMatrixes[$id]=&$m;
//		print "MAX $this->maxMatrixId! , id:$id";
		if($id>=
			$this->maxMatrixId)
			 $this->maxMatrixId=$id+1;
		return $m;
	}

	function retrieveOrCreateHMatrix($hmatrixId=null,$name=null,$dimensions=[2,2]) {
		if ($hmatrixId===null) {								
			$m=$this->createHMatrix($name,$dimensions);
		} else {						
			$m=$this->retrieveHMatrix($hmatrixId,$name,$dimensions);
		}
		return $m;
	}

	function isHydrated() {
		return !($this->maxMatrixId===null);
	}

	function hydrate() {		
		if (!file_exists($this->MatrixesFolder())) mkdir($this->MatrixesFolder());
		
		if (file_exists($this->MatrixesFile() )) {
			$m=\unserialize(file_get_contents($this->MatrixesFile()));			
			$this->maxMatrixId=$m->maxMatrixId;
			$this->hMatrixes=[];		
		} else {
			$this->maxMatrixId=0;			
			$this->hMatrixes=[];
		}

	}

	function dehydrate($lockAll=FALSE) {
		if (!$this->isHydrated()) return;

		$lock=new Lock\FileLock($this->MatrixesLockFile());

		if (!file_exists($this->MatrixesFolder())) mkdir($this->MatrixesFolder());
		
		if ($lockAll) $lock->writerLock();

		foreach (array_reverse($this->hMatrixes) as $hmatrix) {			
			$hmatrix->dehydrateIfReq();		
		}

		$lock->writerLock();

		$this->hMatrixes=null;

		$content=\serialize($this);
		$bytes=file_put_contents($this->MatrixesTmpFile(),$content);
		if ($bytes===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write file %s",$this->MatrixesTmpFile()));
		@unlink($this->MatrixesFile());
		rename($this->MatrixesTmpFile(),$this->MatrixesFile());
		$this->maxMatrixId=null;

		$lock->writerUnlock();
		self::$instance=null;
	}

	function hydrateIfReq() {
		if (!$this->isHydrated()) {
			$this->hydrate();
		}
	}

	function dehydrateIfReq() {
		if ($this->isHydrated()) {
			$this->dehydrate();
		}
	}

	function notifyHMatrixHydrated(&$hmatrix) {
		if (!array_key_exists($hmatrix->id(),$this->hMatrixes)) $this->hMatrixes[$hmatrix->id()]=$hmatrix;
	}

	function MatrixesFolder() {
		return "content/Hydra/HMatrixes";
	}

	function matrixFolder() {
		return "content/Hydra/HMatrix";
	}

	function MatrixesFile() {
		return sprintf("%s/HMatrixes.serialized",$this->MatrixesFolder());
	}

	function MatrixesTmpFile() {
		return sprintf("%s/HMatrixes.serialized.tmp",$this->MatrixesFolder());
	}

	function MatrixesLockFile() {
		return sprintf("%s/HMatrixes.serialized.lock",$this->MatrixesFolder());
	}

	function kill() {
		Nano\nanoFile()->recursiveRmDir($this->MatrixesFolder());
		Nano\nanoFile()->recursiveRmDir($this->matrixFolder());
	}
}


?>