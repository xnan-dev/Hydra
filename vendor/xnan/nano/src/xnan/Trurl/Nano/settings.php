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

function minDiskAvailable() {
	return 70*1000*1000;
}

function fileGetContentsTimeout() {
	return 2*60;
}

function serviceTimeoutShort() {
	return 5;
}

function serviceTimeoutLong() {
	return 2*60;
}

?>