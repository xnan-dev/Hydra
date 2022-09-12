<?php
namespace xnan\Trurl\Nano\Formatter;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

class TextFormatter {
	var $textFormat=null; // text|html
	var $defaultDecimals=4;


   /* priority:
    * this->textFormat
    * param->textFormat
    * "text"
    */

	function textFormat($textFormat=null) {
		if ($textFormat!=null) $this->textFormat=$textFormat;
		$paramTextFormat=Nano\nanoUrl()->param("textFormat","");
		if ($textFormat!=null) return $this->textFormat;
		if ($paramTextFormat!=null) return $paramTextFormat;
		return "text";
	}

	function defaultDecimals($decimals=null) {
		if ($decimals!=null) $this->defaultDecimals=$decimals;
		return $this->defaultDecimals;
	}

	function formatString($value,$saveUrl="") {		
		if(TextFormatter::textFormat()=="text") {
			return $this->formatStringText($value,$saveUrl);
		} else {
			return $this->formatStringHtml($value,$saveUrl);
		}
	}

	function formatStringText($value,$saveUrl) {
		return sprintf("%s",$value);
	}

	function formatStringHtml($value,$saveUrl) {
		return sprintf('<span data-editText="%s" data-saveUrl="%s" class="string">%s</span>',$value,$saveUrl,$value);	
	}

	function formatLink($value,$url="") {		
		if(TextFormatter::textFormat()=="text") {
			return $this->formatLinkText($value,$url);
		} else {
			return $this->formatLinkHtml($value,$url);
		}
	}

	function formatLinkText($value,$url) {
		return sprintf("%s",$value);
	}

	function formatLinkHtml($value,$url) {
		if ($url=="") {
			return sprintf("%s",$value);
		} else {
			return sprintf('<a target="_blank" href="%s">%s</a>',$url,$value);	
		}		
	}

	function formatInt($value,$saveUrl="") {		
		if(TextFormatter::textFormat()=="text") {
			return $this->formatIntText($value,$saveUrl);
		} else {
			return $this->formatIntHtml($value,$saveUrl);
		}
	}

	function formatIntText($value,$saveUrl) {
		return sprintf("%s",$value);
	}

	function formatIntHtml($value,$saveUrl) {
		return sprintf('<span data-editText="%s" data-saveUrl="%s" class="int my-right">%s</span>',$value,$saveUrl,$value);	
	}

	function formatPercent($value,$saveUrl="") {		
		if(TextFormatter::textFormat()=="text") {
			return $this->formatPercentText($value,$saveUrl);
		} else {
			return $this->formatPercentHtml($value,$saveUrl);
		}
	}

	function formatPercentText($value,$saveUrl) {
		return sprintf("%s%s",number_format($value,2),"%");
	}

	function formatPercentHtml($value,$saveUrl) {
		return sprintf('<span data-editText="%s" data-saveUrl="%s" class="percent my-right">%s %s</span>',$value,$saveUrl,$this->formatDecimalPart(number_format($value,2)),"%");	
	}


	function formatDecimalPart($number) {
		$frags=explode(".",$number);
		$s=sprintf('<b>%s.</b><span class="decimalPart"><small class="text-muted">%s</small></span>',$frags[0],$frags[1]);
		return $s;
	}

	function formatDecimal($value,$assetId="",$saveUrl="",$decimals=null) {		
		if(TextFormatter::textFormat()=="text") {
			return $this->formatDecimalText($value,$assetId,$saveUrl,$decimals);
		} else {
			return $this->formatDecimalHtml($value,$assetId,$saveUrl,$decimals);
		}
	}

	function formatBool($value,$saveUrl="") {
		if(TextFormatter::textFormat()=="text") {
			return $this->formatBoolText($value,$saveUrl);
		} else {
			return $this->formatBoolHtml($value,$saveUrl);
		}
	}

	function formatBoolText($value,$saveUrl) {
		$value=$value ? "true":"false";
		return $value;
	}

	function formatBoolHtml($value,$saveUrl) {
		$valueHtml= $value ? "true": "false";
		return sprintf('<span data-editText="%s" data-saveUrl="%s" class="bool my-right d-inline-block w-50">%s</span>'
			,$value,$saveUrl,$valueHtml);
	}

	function formatDecimalText($value,$assetId,$saveUrl,$decimals) {
		$assetIdStr=sprintf('%s',$assetId);
		return sprintf("%s %s",$value,$assetIdStr);
	}

	function formatDecimalHtml($value,$assetId,$saveUrl,$decimals) {		
		if ($decimals==null) $decimals=$this->defaultDecimals();		
		$numberHtml=$this->formatDecimalPart(number_format($value,$decimals));
		if($assetId!="") {
			$assetIdStr=sprintf('<span class="assetId"><small class="text-muted">%s</small></span>',$assetId);
			$valueHtml=$numberHtml." ".$assetIdStr;
		} else {
			$valueHtml=$numberHtml;
		} 
		return sprintf('<span data-editText="%s" data-saveUrl="%s" class="decimal my-right d-inline-block w-50">%s</span>',$value,$saveUrl,$valueHtml);	
	}

	function formatQuantity($value,$saveUrl="",$decimals=null) {		
		if(TextFormatter::textFormat()=="text") {
			return $this->formatQuantityText($value,$saveUrl,$decimals);
		} else {		
			return $this->formatQuantityHtml($value,$saveUrl,$decimals);
		}

	}

	function formatQuantityText($value,$saveUrl,$decimals) {
		if ($decimals==null) $decimals=$this->defaultDecimals();
		$value=number_format($value,$decimals);
		return $value;
	}

	function formatQuantityHtml($value,$saveUrl,$decimals) {
		if ($decimals==null) $decimals=$this->defaultDecimals();		
		$valueHtml=$this->formatDecimalPart(number_format($value,$decimals));
		return sprintf('<span data-editText="%s" data-saveUrl="%s" class="quantity my-right d-inline-block w-50">%s</span>'
			,$value,$saveUrl,$valueHtml);
	}

	
	function quantityToCanonical($quantity) {
		return number_format($quantity,2);
	}

	function moneyLegend($amount) {
		return number_format($amount,2);
	}

	function dateLegend($time) {
		return $time!=null ? sprintf("%s",date("Y-m-d H:i:s",$time)) : "";
	}

}

?>