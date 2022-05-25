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
use xnan\Trurl\Nano\Performance;
use xnan\Trurl\Nano\Lock;
Trurl\Functions::Load;
Log\Functions::Load;

// Uses: Hydra
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HRefs;
//use xnan\Trurl\Hydra\HMatrixes;

//Uses: End

class HMatrix implements  \Serializable {
	var $name=null;
	var $id;
	var $changed;
	var $dimensions;
	var $dataValueSize;
	var $data=null;

	function __construct($id,$name=null,$dimensions=[2,2]) {
		if ($id===null) Nano\nanoCheck()->checkFailed("id cannot be null");
		$this->id=$id;
		$this->changed=true;
		if ($name!=null) $this->name=$name;
		if ($dimensions!=null) $this->dimensions=$dimensions;
	}

	function id() {
		return $this->id;
	}

	function name() {
		return $this->name;
	}
	
	function isHydrated() {
		return !($this->data===null);
	}

	function initData() {		
		$length=1;
		$strZero=pack("d",0.0);
		//print "INITDATA strZero:'$strZero'<br>\n";
		$this->dataValueSize=strlen($strZero);
		$this->data=b"";
		for ($d=0;$d<count($this->dimensions);$d++) {
			$length=$length*$this->dimensions[$d];
		}		

		for($i=0;$i<$length;$i++) $this->data.=$strZero;		
	}

	function get($coordinates) {		
		$this->hydrateIfReq();
		$this->checkCoordinates($coordinates);

		$strZero=pack("d",0.0);
		$this->dataValueSize=strlen($strZero);

		$offset=$this->offset($coordinates);				
		$v=$this->getValueLinear($offset);
//		print "get $coord offset:$offset retValue:$v<br>";
		return $v;
	}

	function lastDimension() {
		return $this->dimensions[count($this->dimensions)-1];
	}

	function checkCoordinates($coordinates) {
		for ($d=0;$d<count($coordinates)-1;$d++) {
			if ($this->dimensions[$d]<$coordinates[$d]) Nano\nanoCheck()->checkFailed("dimensions out of range");
		}
		return true;
	}
	
	function shift($coordinates) { //1 dimension menos que la matriz
		$this->hydrateIfReq();
		$this->checkCoordinates($coordinates);

		$lastDim=$this->lastDimension();		
		for($i=0;$i<$lastDim-1;$i++) {
			$coord=$coordinates;
			$coord[count($coordinates)]=$i;
			$coordNext=$coord;
			$coordNext[count($coordinates)]=$i+1;
			$this->set($coord,$this->get($coordNext));
		}
		$this->set($coordNext,0);
	}

	function set($coordinates,$value) {				
		$this->hydrateIfReq();
		$this->checkCoordinates($coordinates);

		$strZero=pack("d",0.0);
		$this->dataValueSize=strlen($strZero);

		$offset=$this->offset($coordinates);
		//$coord=sprintf("[%s %s %s]",$coordinates[0],$coordinates[1],$coordinates[2]);		
		//print "set $coord offset:$offset value:$value<br>";
		$this->setValueLinear($offset,$value);		
		$this->changed=true;
	}

	function checkOffset($offset) {
		$maxOffset=$this->dataValueSize*$this->dimensionMul(count($this->dimensions));
		$maxOffsetIndex=$maxOffset/$this->dataValueSize;
		$offsetIndex=$offset/$this->dataValueSize;
		if ( $offset>$maxOffset) Nano\nanoCheck()->checkFailed("checkOffset: offset: $offset maxOffset:$maxOffset offsetIndex:$offsetIndex maxOffsetIndex:$maxOffsetIndex msg: out of range");
	}

	function getValueLinear($offset) {		
		$this->checkOffset($offset);		
		$value=unpack("d",$this->data,$offset)[1];
		return $value;
	}

	function setValueLinear($offset,$value) {		
		$packed=pack("d",$value);
		for($i=0;$i<strlen($packed);$i++)  {
			$this->data[$offset+$i]=$packed[$i];
		}
	}

	function dimensionMul($dimension,$show=false) {
		$m=1;
		for($d=0;$d<=$dimension-1;$d++) {
			$m=$m*$this->dimensions[$d];
			if ($show) print "INMUL d:$d, m:$m<br>";
		}
		return $m;
	}

	function offset($coordinates) {		
		$this->hydrateIfReq();

		if (!is_array($coordinates)) throw new \Exception("coordinates should be an array");
		$offset=$coordinates[0];
		for($d=1;$d<=count($this->dimensions)-1;$d++) {
			$coef=$coordinates[$d];
			$mul=$this->dimensionMul($d,false);
			$offset+=($coef*$mul);
			//print "c[$d]: $coef * mul:$mul => $offset\n<br>";
		}		
		
		$ret=$offset*$this->dataValueSize;		
		//$coord=print_r($coordinates,true);
		//print "RET: $coord: off ".($ret/4)."<br>";
		return $ret;
	}

	function hydrate() {
		Hydra\hydra()->performance()->track("HMatrix.hydrate");
		if (!file_exists($this->matrixFolder())) mkdir($this->matrixFolder());
		if (file_exists($this->matrixFile($this->id() ) )) {
			$r=\unserialize(file_get_contents($this->matrixFile($this->id)));
			$this->id=$r[0];
			$this->name=$r[1];	
			$this->dimensions=$r[2];	
			$this->dataValueSize=$r[3];
			$this->data=$r[4];
			
			if ($this->data===null) $this->initData();
			$this->changed=false;
		} else {
			$this->initData();
			$this->changed=true;
		}
		(HMatrixes::instance())->notifyHMatrixHydrated($this);
		Hydra\hydra()->performance()->track("HMatrix.hydrate");		
	}


	function dehydrate() {				
		if ($this->changed) {
			Hydra\hydra()->performance()->track("HMatrix.dehydrate");

			$lock=new Lock\FileLock($this->matrixLockFile($this->id));
			$lock->writerLock();

			if (!file_exists($this->matrixFolder())) mkdir($this->matrixFolder());

			Hydra\hydra()->performance()->track("HMatrix.serialize");			
			$content=\serialize([$this->id,$this->name,$this->dimensions,$this->dataValueSize,$this->data]);
			Hydra\hydra()->performance()->track("HMatrix.serialize");
		
			Hydra\hydra()->performance()->track("HMatrix.write");
			$bytes=file_put_contents($this->matrixTmpFile($this->id),$content,LOCK_EX);			
			Hydra\hydra()->performance()->track("HMatrix.write");

			if ($bytes===false) Nano\nanoCheck()->checkFailed(sprintf("cannot write file %%s",$this->matrixTmpFile($this->id)));
			@unlink($this->matrixFile($this->id));
			rename($this->matrixTmpFile($this->id),$this->matrixFile($this->id));

			$lock->writerUnlock();
			Hydra\hydra()->performance()->track("HMatrix.dehydrate");	
		}
		$this->data=null;		
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

	function matrixFolder() {
		return "content/Hydra/HMatrix";
	}

	function matrixFile($key) {
		return sprintf("%s/HMatrix.%s.serialized",$this->matrixFolder(),$this->id());
	}

	function matrixTmpFile($key) {
		return sprintf("%s/HMatrix.%s.serialized.tmp",$this->matrixFolder(),$this->id());
	}

	function matrixLockFile($key) {
		return sprintf("%s/HMatrix.%s.serialized.lock",$this->matrixFolder(),$this->id());
	}

	function serialize() {
		return \serialize([$this->id,$this->name,$this->dimensions,$this->dataValueSize]);
	}
	
	function unserialize($serialized) {
		$arr=\unserialize($serialized);
		$this->id=$arr[0];
		$this->name=$arr[1];
		$this->dimensions=$arr[2];
		$this->dataValueSize=$arr[3];
	}
}

?>