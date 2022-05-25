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
use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Hydra\HMatrix;

//Uses: End

class HRef implements \Serializable {
	var $name=null;
	var $id;
	var $value;	
	var $changed;
	var $hobjectId;

	function __construct($id,$name=null,&$value=null) {		
		$this->id=$id;
		$this->changed=true;
		$this->name=$name;
		$this->value=$value;

		$this->setObjectIdIfReq();
	}

	function setObjectIdIfReq() {
		if (is_object($this->value)) {
			if ( (!property_exists($this->value,"hobjectId") || $this->value->hobjectId==null )) {
				//debug_print_backtrace();
				$hobjectId=spl_object_hash($this->value);
				$this->value->hobjectId=$hobjectId;
				$this->hobjectId=$hobjectId;
				(HRefs\HRefs::instance())->notifyHObjectSet($this->value);
			} else {
				$this->hobjectId=$this->value->hobjectId;
			}
		}
	}

	function id() {
		return $this->id;
	}

	function name() {
		return $this->name;
	}

	function isHydrated() {
		$ret=!($this->value===null);
		if ($ret) {
//			print "isHydrated: $ret , hobjectId:$hobjectId value:$this->value<br>";
		}
		return $ret;
	}

	function hydrate() {		
		Hydra\hydra()->performance()->track("HRef.hydrate");

		if (!file_exists($this->refFolder())) mkdir($this->refFolder());

		if (file_exists($this->refFile($this->id() ) )) {		
			$r=\unserialize(file_get_contents($this->refFile($this->id)));
			$this->id=$r[0];					
			$this->name=$r[1];
			$this->hobjectId=$r[2];			
			if ($this->hobjectId!=null) {				
				$this->value=HRefs\HRefs::instance()->retrieveHObject($this->hobjectId);
			}
			$this->changed=false;
		} else {
			$this->value=null; // TODO - chequear OK ?
			$this->hobjectId=null;
			$this->changed=true;
		}
		(HRefs\HRefs::instance())->notifyHRefHydrated($this);
		Hydra\hydra()->performance()->track("HRef.hydrate");
	}

	function dehydrate() {
		Hydra\hydra()->performance()->track("HRef.dehydrate");
		if ($this->changed) {	
			//print "############ dehydrate-refid:".$this->id()."<br>";

			$lock=new Lock\FileLock($this->refLockFile($this->id));
			$lock->writerLock();
		
			if (!file_exists($this->refFolder())) mkdir($this->refFolder());
			if (!file_exists($this->objFolder())) mkdir($this->objFolder());
			
			$valueObj=is_object($this->value);

			Hydra\hydra()->performance()->track("HRef.serialize");

			$content=\serialize([$this->id,$this->name,$this->hobjectId]);
			if ($valueObj) $objContent=\serialize($this->value);
			
			Hydra\hydra()->performance()->track("HRef.serialize");

			Hydra\hydra()->performance()->track("HRef.write");
			$bytes=file_put_contents($this->refTmpFile($this->id),$content,LOCK_EX);
			if ($valueObj) $bytesObj=file_put_contents($this->refTmpObjFile($this->hobjectId),$objContent,LOCK_EX);
			Hydra\hydra()->performance()->track("HRef.write");

			if ($bytes===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write file %%s",$this->refTmpFile($this->id)));
			if ($bytesObj===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write obj file %%s",$this->refTmpObjFile($this->hobjectId)));
			@unlink($this->refFile($this->id));
			if ($valueObj) @unlink($this->refObjFile($this->id));
			rename($this->refTmpFile($this->id),$this->refFile($this->id));
			if ($valueObj) rename($this->refTmpObjFile($this->hobjectId),$this->refObjFile($this->hobjectId));

			$lock->writerUnlock();
		} else {
			//print "############ NOT CHANGED: dehydrate-refid:".$this->id()." changed:$this->changed objid:".spl_object_id($this)."<br>";
		}
		$this->value=null;
		$this->hobjectId=null;
		Hydra\hydra()->performance()->track("HRef.dehydrate");
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

	function hasValue() {
		$this->hydrateIfReq();
		return !($this->value===null);
	}

	function get() {
		$this->hydrateIfReq();
		return $this->value;
	}


	function reset() {
		$this->hydrateIfReq();
		$this->changed=true;
		$this->value=null;
		$this->hobjectId=null;
	}

	function set(&$value) {		
		$this->hydrateIfReq();
		$this->value=$value;
		$this->setObjectIdIfReq();

		$this->changed=true;		
		if ($this->value===null) exit("fallo!");
	}

	function markChanged() {
		$this->hydrateIfReq();
		$this->changed=true;
		//echo "MARK-CHANGED $this->id $this->changed<br>";
	}

	function serialize() {		
		//print "############ SERIALIZE: refid:".$this->id()." changed:$this->changed<br>";
		return \serialize([$this->name,$this->id,$this->hobjectId]);
	}
	
	function unserialize($serialized) {
		$arr=\unserialize($serialized);
		$this->name=$arr[0];
		$this->id=$arr[1];
		$this->hobjectId=$arr[2];
		//print "############ UNSERIALIZE: refid:".$this->id()." changed:$this->changed<br>";
		//debug_print_backtrace();
	}	

	function refFolder() {
		return "content/Hydra/HRef";
	}


	function refFile($key) {
		return sprintf("%s/HRef.%s.serialized",$this->refFolder(),$this->id());
	}

	function refTmpFile($key) {
		return sprintf("%s/HRef.%s.serialized.tmp",$this->refFolder(),$this->id());
	}

	function objFolder() {
		return "content/Hydra/HObject";
	}

	function refObjFile($key) {
		return sprintf("%s/HObject.%s.serialized",$this->objFolder(),$key);
	}

	function refTmpObjFile($key) {
		return sprintf("%s/HObject.%s.serialized.tmp",$this->objFolder(),$key);
	}

	function refLockFile($key) {
		return sprintf("%s/HRef.%s.serialized.lock",$this->refFolder(),$this->id());
	}

}

?>