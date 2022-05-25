<?php
namespace xnan\Trurl\Hydra\HRefs;
//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Nano
use xnan\Trurl;
//use xnan\Trurl\Nano;
use xnan\Trurl\Nano\Log;
use xnan\Trurl\Nano\Lock;
Trurl\Functions::Load;
Log\Functions::Load;

// Uses: Hydra
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
//use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Hydra\HMatrix;

//Uses: End

class HRefs{
	var $maxRefId=null;
	var $hrefs=null;
	var $hobjects=null;
	static $instance=null;

	function __construct() {		
	}

	static function instance() {
		if (HRefs::$instance==null) HRefs::$instance=new HRefs();
		return HRefs::$instance;
	}

	function createHRef($name=null,&$value=null) {
		$this->hydrateIfReq();
		//debug_print_backtrace();
		$m=new HRef($this->maxRefId,$name,$value);
		$this->hrefs[$this->maxRefId]=$m;
		++$this->maxRefId;		
		return $m;
	}

	function retrieveHRef($id) {
		$this->hydrateIfReq();
		$m=new HRef($id);
		$this->hrefs[$id]=&$m;
		if($id>=$this->maxRefId) $this->maxRefId=$id+1;
		return $m;
	}

	function retrieveOrCreateHRef($hrefId=null,$name=null) {
		if ($hrefId===null) {			
			$m=$this->createHRef($name);
		} else {			
			$m=$this->retrieveHRef($hrefId);
		}
		return $m;
	}

	function isHydrated() {
		return !($this->maxRefId===null);
	}

	function hydrate() {				

		if (!file_exists($this->refsFolder())) mkdir($this->refsFolder());
		
		if (file_exists($this->refsFile() )) {
			$m=\unserialize(file_get_contents($this->refsFile()));			

			$this->maxRefId=$m->maxRefId;
			if ($this->hrefs===null) $this->hrefs=[];
			if ($this->hobjects===null) $this->hobjects=[];
		} else {
			$this->maxRefId=0;			
			if ($this->hrefs===null) $this->hrefs=[];
			if ($this->hobjects===null) $this->hobjects=[];
		}

	}

	function dehydrate($lockAll=false) {
		$lock=new Lock\FileLock($this->refsLockFile());

		if (!file_exists($this->refsFolder())) mkdir($this->refsFolder());
		
		if ($lockAll) $lock->writerLock();


		if (!($this->hrefs===null)) {
			foreach ($this->hrefs as $href) {			
				$href->dehydrateIfReq();		
			}
		}

		$lock->writerLock();

		$content=\serialize($this);
		$bytes=file_put_contents($this->refsTmpFile(),$content);
		if ($bytes===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write file %%s",$this->refsTmpFile()));
		@unlink($this->refsFile());
		rename($this->refsTmpFile(),$this->refsFile());
		$this->maxRefId=null;
		$this->hrefs=null;
		$this->hobjects=null;

		$lock->writerUnlock();
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

	function retrieveHObject($hobjectId) {
		$this->hydrateIfReq();		
		if (!array_key_exists($hobjectId,$this->hobjects)) {
			$hobject=\unserialize(file_get_contents($this->refObjFile($hobjectId))); 
			if (!($hobject===false)) {
				$this->hobjects[$hobject->hobjectId]=$hobject;
				return $hobject;
			} else {
				Nano\nanoCheck()->checkFailed("retrieveHObject: hobjectId: $hobjectId msg: unable to recover hobject");
			}			
		} else {
			return $this->hobjects[$hobjectId];
		}
	}

	function notifyHRefHydrated($href) {
		if (!array_key_exists($href->id(),$this->hrefs)) $this->hrefs[$href->id()]=$href;
	}

	function notifyHObjectSet(&$hobject) {
		$this->hydrateIfReq();
		$this->hobjects[$hobject->hobjectId]=$hobject;
	}

	function refsFolder() {
		return "content/Hydra/HRefs";
	}

	function refFolder() {
		return "content/Hydra/HRef";
	}

	function objFolder() {
		return "content/Hydra/HObject";
	}

	function refObjFile($key) {
		return sprintf("%s/HObject.%s.serialized",$this->objFolder(),$key);
	}


	function refsFile() {
		return sprintf("%s/HRefs.serialized",$this->refsFolder());
	}

	function refsTmpFile() {
		return sprintf("%s/HRefs.serialized.tmp",$this->refsFolder());
	}

	function refsLockFile() {
		return sprintf("%s/HRefs.serialized.lock",$this->refsFolder());
	}

	function kill() {
		Nano\nanoFile()->recursiveRmDir($this->refsFolder());
		Nano\nanoFile()->recursiveRmDir($this->refFolder());
	}
}


?>