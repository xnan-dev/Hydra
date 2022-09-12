<?php

namespace xnan\Trurl\Nano\Telegram;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;


class Telegram {


	function telegramBot() {
		return "5138012656:AAG6mFxtlvZsGRNUnSm0sCcqd8Hkei1KFgg";
	}

	function telegram($msg) {
	        $telegrambot=$this->telegramBot();
	        $telegramchatid=$this->telegramChatId();
	        $url='https://api.telegram.org/bot'.$telegrambot.'/sendMessage';$data=array('chat_id'=>$telegramchatid,'text'=>$msg,'parse_mode'=>"MarkDown"); //HTML
	        $options=array('http'=>array('method'=>'POST','header'=>"Content-Type:application/x-www-form-urlencoded\r\n",'content'=>http_build_query($data),),);
	        $context=stream_context_create($options);
	        $result=file_get_contents($url,false,$context);
	        return $result;
	}
}

?>