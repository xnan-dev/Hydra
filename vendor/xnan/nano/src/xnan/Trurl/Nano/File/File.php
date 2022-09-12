<?php

namespace xnan\Trurl\Nano\File;
use xnan\Trurl;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

class File {
	function httpContext($timeoutFn=null) {
		if ($timeoutFn==null || $timeoutFn=="") $timeoutFn="\\xnan\\Trurl\\Nano\\fileGetContentsTimeout";
		
		return stream_context_create(array(
		  'http'=>array(
		    'method'=>"GET",                	    
	      'timeout' => $timeoutFn(), //seconds
		    'header'=>"Accept: text/html,application/xhtml+xml,application/xml,text/csv\r\n" .
		              "Accept-Charset: ISO-8859-1,utf-8\r\n" .
		              "Accept-Encoding: gzip,deflate,sdch\r\n" .
		              "Accept-Language: en-US,en;q=0.8\r\n",
		    'user_agent'=>"User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.66 Safari/535.11\r\n"              
		 )
		)); 
	}

	function fileGetContents($url,$timeoutFn=null) {
		$context=$this->httpContext($timeoutFn);	
		$c=@file_get_contents($url,false,$context);
		if($c === FALSE) {
			checkFailed("url: $url msg:cannot retrieve content");
		}
		return $c;
	}

	function recursiveRmDir($src) {
		if (!file_exists($src)) return;

	    $dir = opendir($src);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            $full = $src . '/' . $file;
	            if ( is_dir($full) ) {
	                recursiveRmDir($full);
	            }
	            else {
	                unlink($full);
	            }
	        }
	    }
	    closedir($dir);
	    rmdir($src);
	}
}

?>