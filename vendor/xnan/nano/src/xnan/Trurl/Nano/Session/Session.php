<?php

namespace namespace xnan\Trurl\Nano\Session;

class Functions {const Load=1;}

function sessionGet($key) {	
	 return array_key_exists($key,$_SESSION) ? $_SESSION[$key] : null;
}

function sessionSet($key,$value) {
	return $_SESSION[$key]=$value;
}

?>