<?php
namespace xnan\Trurl\Nano\Log;
class Functions { const Load=1; }

function dateLegend($time) {
    return $time!=null ? sprintf("%s",date("Y-m-d H:i:s",$time)) : "";
}
class Log {
    var $log_file=null;

    function open() {
        if (!file_exists("content/Nano")) mkdir("content/Nano");
        if (!file_exists("content/Nano/Log")) mkdir("content/Nano/Log");

        $mode = 'a';
        $this->log_file = @fopen("content/Nano/Log/Nano.log", $mode);
    }

    function close() {        
        fclose($this->log_file);
    }

    function logWrite($d) {        

        if ($this->log_file === false) {
            return 0;
        } else {
            if (is_array($d)) $d = implode($d);
            $bytes_written = fwrite($this->log_file, $d);
            return $bytes_written;
        }
    }

    function msg($msg) {
        $msgOut=sprintf("%s - $msg\n",dateLegend(time()) );
        print ($msgOut);
        $this->logWrite($msgOut);
    }

}


?>