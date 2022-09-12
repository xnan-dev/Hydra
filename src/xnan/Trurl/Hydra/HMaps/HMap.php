<?php
namespace xnan\Trurl\Hydra\HMaps;

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
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Hydra\HMatrix;

//Uses: End

class HMap implements  \Serializable {
	var $name=null;
	var $id;
	var $values;	
	var $changed;

	function __construct($id,$name=null) {		
		if ($id===null) Nano\nanoCheck()->checkFailed("id cannot be null");
		$this->id=$id;
		$this->changed=true;
		$this->name=$name;
	}

	function id() {
		return $this->id;
	}

	function name() {
		return $this->name;
	}
	function isHydrated() {
		return !($this->values===null);
	}

	function hydrate() {
		Hydra\hydra()->performance()->track("HMap.hydrate");
		if (!file_exists($this->mapFolder())) mkdir($this->mapFolder());
		if (file_exists($this->mapFile($this->id() ) )) {
			
			$r=\unserialize(file_get_contents($this->mapFile($this->id)));
			$this->id=$r[0];		
			$this->name=$r[1];	
			$this->values=$r[2];
			
			if ($this->values===null) $this->values=[];
			$this->changed=false;
		} else {
			$this->values=[];
			$this->changed=true;
		}
		(HMaps\HMaps::instance())->notifyHydrated($this);
		Hydra\hydra()->performance()->track("HMap.hydrate");
	}

	function dehydrate() {		
		if ($this->changed) {
			Hydra\hydra()->performance()->track("HMap.dehydrate");
			$lock=new Lock\FileLock($this->mapLockFile($this->id));
			$lock->writerLock();

			if (!file_exists($this->mapFolder())) mkdir($this->mapFolder());

			Hydra\hydra()->performance()->track("HMap.serialize");			
			$content=\serialize([$this->id,$this->name,$this->values]);			
			Hydra\hydra()->performance()->track("HMap.serialize");
		
			Hydra\hydra()->performance()->track("HMap.write");
			$bytes=file_put_contents($this->mapTmpFile($this->id),$content,LOCK_EX);			
			Hydra\hydra()->performance()->track("HMap.write");

			if ($bytes===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write file %%s",$this->refTmpFile($this->id)));
			@unlink($this->mapFile($this->id));
			rename($this->mapTmpFile($this->id),$this->mapFile($this->id));

			$lock->writerUnlock();
			Hydra\hydra()->performance()->track("HMap.dehydrate");
		}
		$this->values=null;		
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

	function markChanged() {
		$this->hydrateIfReq();
		$this->changed=true;
	}
	

	function hasKey($key) {
		$this->hydrateIfReq();
		if ($this->values===NULL) Nano\nanoCheck()->checkFailed("values cannot be null");
		return array_key_exists($key,$this->values);
	}


	function contains(&$value) {
		$this->hydrateIfReq();
		return in_array($value,$this->values);
	}

	function values($values=null) {
		$this->hydrateIfReq();
		if ($values!=null) {
			$this->values=$values;
			$this->changed=true;
		} 		
		return array_values($this->values);
	}

	function keys($keys=null) {
		$this->hydrateIfReq();
		if ($keys!=null) {
			$this->values=array_flip($keys);
		}
		return array_keys($this->values);
	}

	function get($key) {
		$this->hydrateIfReq();
		return $this->values[$key];
	}

	function reset() {
		$this->hydrateIfReq();
		$this->changed=true;
		return $this->values=[];
	}

	function count() {
		$this->hydrateIfReq();
		return count($this->values);
	}
	function shift() {
		$this->hydrateIfReq();
		array_shift($this->values);
		$this->changed=true;
	}

	function set($key,&$value) {
		$this->hydrateIfReq();
		$this->values[$key]=$value;
		$this->changed=true;
	}

	function insert(&$value) {
		$this->hydrateIfReq();
		$this->values[]=$value;
		$this->changed=true;
	}

	function mapFolder() {
		return "content/Hydra/HMap";
	}

	function mapFile($key) {
		return sprintf("%s/HMap.%s.serialized",$this->mapFolder(),$this->id());
	}

	function mapTmpFile($key) {
		return sprintf("%s/HMap.%s.serialized.tmp",$this->mapFolder(),$this->id());
	}

	function mapLockFile($key) {
		return sprintf("%s/HMap.%s.serialized.lock",$this->mapFolder(),$this->id());
	}

	function serialize() {
		return \serialize([$this->name,$this->id]);
	}
	
	function unserialize($serialized) {
		$arr=\unserialize($serialized);
		$this->name=$arr[0];
		$this->id=$arr[1];
	}
}




?>