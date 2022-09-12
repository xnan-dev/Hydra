<?php

namespace xnan\Trurl\Nano\Csv;
use xnan\Trurl;

Trurl\Functions::Load;

class Csv {

	function csvToArray($csvFileName,$lastLineOnly=false) {
			if (!file_exists($csvFileName)) checkFailed("csvToArray: csvFileName:$csvFileName msg: file does not exists");		
	 		$csv=array();
	 		$file = fopen($csvFileName, 'r');

	 		if ($file===false) return array(array("serviceError","serviceMsg"),array("fopen_fail","cannot open file $fileName"));

	 		$head = array();
	 		$index=0;

			while (($line = fgetcsv($file,null,';','\\')) !== FALSE) {
			  
			  if ($index==0) {
			  	$head=$line;
			  } else {
			  	if ($lastLineOnly) {
			  		$lastLine=array_combine($head,$line);
			  	} else {		  				  
			  		if (count($head)!=count($line)) {
			  			print_r($head);
			  			print_r($line);
			  			throw new \Exception("head and column count mismatch");
			  		}
			  		$csv[]=array_combine($head,$line);
			  	}
			  } 

			  ++$index;
			}
			fclose($file);

			return $lastLineOnly ? $lastLine : $csv;
	}

	function csvContentToArray($content='', $delimiter=',')
	{
	    if ($content==null || $content=='') throw new \Exception("csv content is empty");
	    //print "content:'$content'<br>\n\n<br>";

	    if (!(strpos($content,"Fatal error")===FALSE)) throw new \Exception("csv content has fatal error");
	    if (!(strpos($content,"Warning")===FALSE)) throw new \Exception("csv content has warning");

	    $fiveMBs = 150 * 1024 * 1024;
	    $handle = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
	    fputs($handle, $content);
	    rewind($handle);

	    $header = NULL;
	    $data = array();
	    if ($handle !== FALSE)
	    {                       
	        while (($row = fgetcsv($handle, 50000, $delimiter)) !== FALSE)
	        {			
	            if(!$header) {
	                $header = $row;
	            } else {
	            	if (count($header)!=count($row)) {        		
		            	//msg(sprintf("csvContentToArray: error: msg: header and rows don't match %s!=%s delimiter:'%s'",count($header),count($row),$delimiter));
			            /*print "<hr>";
		            	print "<pre>";
		            	print_r($header);
		            	print_r($row);
		            	print "</pre>";
		            	print "content: $content";
		            	print "<hr>";*/
		            	throw new \Exception("csv table invalid: header and rows count mismatch");	            	
	            	}
	               $data[] = array_combine($header, $row);                                
	            }                               
	        }
	        fclose($handle);
	    }            
	    if ($header==null || count($header)==0) throw new \Exception("csv header not found");
	    return $data;
	}

}


?>