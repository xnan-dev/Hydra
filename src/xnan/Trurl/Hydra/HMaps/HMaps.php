<?php
namespace xnan\Trurl\Hydra\HMaps;
//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Nano
use xnan\Trurl;
//use xnan\Trurl\Nano;
use xnan\Trurl\Nano\Log;
use xnan\Trurl\Nano\Performance;
use xnan\Trurl\Nano\Lock;
Trurl\Functions::Load;
Log\Functions::Load;

// Uses: Hydra
use xnan\Trurl\Hydra;
//use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Hydra\HMatrixes;

//Uses: End

class HMaps {
	var $maxMapId=null;
	var $hmaps=null;	
	static $instance=null;
	var $performance=null;
	function __construct() {				
	}

	static function instance() {
		if (HMaps::$instance==null) HMaps::$instance=new HMaps();
		return HMaps::$instance;
	}

	function createHMap($name=null) {
		$this->hydrateIfReq();
		$m=new HMap($this->maxMapId,$name);
		$this->hmaps[$this->maxMapId]=$m;
		++$this->maxMapId;
		return $m;
	}

	function retrieveHMap($id) {
		$this->hydrateIfReq();
		$m=new HMap($id);
		$this->hmaps[$id]=&$m;
		if($id>=$this->maxMapId) $this->maxMapId=$id+1;
		return $m;
	}

	function retrieveOrCreateHMap($hmapId=null,$name=null) {
		$this->hydrateIfReq();
		if ($hmapId===null) {			
			$m=$this->createHMap($name);
		} else {			
			$m=$this->retrieveHMap($hmapId);
		}
		return $m;
	}

	function isHydrated() {		
		return !($this->maxMapId===null || $this->hmaps===null);
	}

	function hydrate() {		
		if (!file_exists($this->mapsFolder())) mkdir($this->mapsFolder());
		if (file_exists($this->mapsFile() )) {
			$m=\unserialize(file_get_contents($this->mapsFile()));			
			$this->maxMapId=$m->maxMapId;
			//$this->hmaps=$m->hmaps;
			if ($this->maxMapId===null) $this->maxMapId=0; // TODO : deberia scannear por si se pierde ? 
			if ($this->hmaps===null) $this->hmaps=[];			
		} else {
			$this->maxMapId=0;			
			$this->hmaps=[];

		}
	}

	function dehydrate($lockAll=false) {
		$lock=new Lock\FileLock($this->mapsLockFile());

		if (!file_exists($this->mapsFolder())) mkdir($this->mapsFolder());

		if ($lockAll) $lock->writerLock();

		if ($this->hmaps!=null) {		
			foreach ($this->hmaps as $hmap) {			
				$hmap->dehydrateIfReq();			
			}
		}

		$this->hmaps=null;

		$lock->writerLock();

		$content=\serialize($this);
		$bytes=file_put_contents($this->mapsTmpFile(),$content);
		if ($bytes===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write file %%s",$this->refsTmpFile()));
		@unlink($this->mapsFile());
		rename($this->mapsTmpFile(),$this->mapsFile());
		$this->maxMapId=null;

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

	function notifyHydrated($hmap) {
		$this->hydrateIfReq();
		if (!array_key_exists($hmap->id(),$this->hmaps)) $this->hmaps[$hmap->id()]=$hmap;
	}

	function mapsFolder() {
		return "content/Hydra/HMaps";
	}

	function mapFolder() {
		return "content/Hydra/HMap";
	}

	function mapsFile() {
		return sprintf("%s/HMaps.serialized",$this->mapsFolder());
	}

	function mapsTmpFile() {
		return sprintf("%s/HMaps.serialized.tmp",$this->mapsFolder());
	}

	function mapsLockFile() {
		return sprintf("%s/HMaps.serialized.lock",$this->mapsFolder());
	}

	function kill() {
		Nano\nanoFile()->recursiveRmDir($this->mapsFolder());
		Nano\nanoFile()->recursiveRmDir($this->mapFolder());
	}
}


?>