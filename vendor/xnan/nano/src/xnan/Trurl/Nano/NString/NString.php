<?php

namespace xnan\Trurl\Nano\NString;

class NString {

 	function strEndsWith( $haystack, $needle ) {
        if ( '' === $haystack && '' !== $needle ) {
            return false;
        }
        $len = strlen( $needle );
        return 0 === substr_compare( $haystack, $needle, -$len, $len ); 
	}

    function strStartsWith ( $haystack, $needle ) {
        if (is_array($needle)) {
            print "wrong needle:";
            print_r($needle);
            exit();
        }
      $ret=strpos( $haystack , $needle ) === 0;  
      return $ret;
    }
 }

?>