<?php
namespace xnan\Trurl\Hydra;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Nano;
use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HMatrixes;

class Functions { const Load=1; }

class Hydra {
	static $instance=null;

	function __construct() {		
	}

	static function instance() {
		if (Hydra::$instance==null) Hydra::$instance=new Hydra();
		return Hydra::$instance;
	}

	function performance() {
		return Nano\nanoPerformance();
	}


	function hydrate() {		
		hydra()->performance()->track("Hydra.hydrate");
		if (!file_exists($this->hydraFolder())) mkdir($this->hydraFolder());

		$this->refs()->hydrate();
		$this->maps()->hydrate();
		$this->matrixes()->hydrate();
		hydra()->performance()->track("Hydra.hydrate");
	}

	function dehydrate($lockAll=false) {
		hydra()->performance()->track("Hydra.dehydrate");		
		$this->refs()->dehydrate($lockAll);
		$this->maps()->dehydrate($lockAll);
		$this->matrixes()->dehydrate($lockAll);
		hydra()->performance()->track("Hydra.dehydrate");
	}

	function refs() {
		return HRefs\HRefs::instance();
	}

	function maps() {
		return HMaps\HMaps::instance();
	}

	function matrixes() {
		return HMatrixes\HMatrixes::instance();
	}

	function hydraFolder() {
		return "content/Hydra";
	}


	function kill() {
		HRefs\HRefs::instance()->kill();
		HMaps\HMaps::instance()->kill();
		HMaps\HMatrixes::instance()->kill();
	}
}

function hydra() {
	return Hydra::instance();
}

?>