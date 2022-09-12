<?php
namespace xnan\Trurl\Nano;
use xnan\Trurl\Nano\Log;
use xnan\Trurl\Nano\Performance;
use xnan\Trurl\Nano\Formatter;
use xnan\Trurl\Nano\Check;
use xnan\Trurl\Nano\Lock;
use xnan\Trurl\Nano\Csv;
use xnan\Trurl\Nano\File;
use xnan\Trurl\Nano\NString;
use xnan\Trurl\Nano\Telegram;
use xnan\Trurl\Nano\Url;

include_once("settings.php");	
require("autoloader.php");	

class Functions { const Load=1; }

Log\Functions::Load;

class Nano {
	var $log=null;
	var $performance=null;
	var $textFormatter=null;
	var $check=null;
	var $csv=null;
	var $file=null;
	var $string=null;
	var $url=null;
	static $instance=null;

	function __construct() {
		$this->log=new Log\Log();
		$this->performance=new Performance\Performance();
		$this->textFormatter=newTextFormatter();
		$this->check=new Check\Check();
		$this->csv=new Csv\Csv();
		$this->file=new File\File();
		$this->string=new NString\NString();
		$this->url=new Url\Url();
	}

	static function instance() {
		if (self::$instance==null) self::$instance=new Nano();
		return self::$instance;
	}

	function log() {
		return $this->log;
	}

	function performance() {
		return $this->performance;
	}

	function textFormatter() {
		return $this->textFormatter;
	}

	function check() {
		return $this->check;
	}

	function csv() {
		return $this->csv;
	}

	function file() {
		return $this->file;
	}

	function string() {
		return $this->string;
	}

	function telegram() {
		return $this->telegram;
	}

	function url() {
		return $this->url;
	}
}

// Nano Shortcuts

function nano() {
	return Nano::instance();
}

function nanoLog() {
	return Nano::instance()->log();
}

function nanoPerformance() {
	return nano()->performance();
}

function nanoCheck() {
	return Nano::instance()->check();
}

function nanoCsv() {
	return Nano::instance()->csv();
}

function nanoFile() {
	return Nano::instance()->file();
}

function nanoString() {
	return Nano::instance()->string();	
}

function nanoTextFormatter() {
	return Nano::instance()->textformatter();	
}

function nanoTelegram() {
	return Nano::instance()->telegram();
}

function nanoUrl() {
	return Nano::instance()->url();
}

function msg($msg) {
	nano()->log()->msg($msg);
}

function newTextFormatter() {
	return new Formatter\TextFormatter();
}

function newFileLock($fileName) {
	return new Lock\FileLock($fileName);
}

?>