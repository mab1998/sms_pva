<?php


//raw post data - see 
$data=$post;

/*
* Flags to be used by XML parser to extact SOAP header parameters from raw post data
* Parameters to be used notifySmsDeliveryReceipt and notifySmsReception methods
*/
$timeStampflag=0;
$subReqIDflag=0;
$traceUniqueIDflag=0;
$spRevIdflag=0;
$spRevpasswordflag=0;
$spIdflag=0;
$serviceIdflag=0;
$linkidflag=0;

$buffer="";

//Variables to store all header parameters after they are extracted by the parser
$timeStamp="";
$subReqID="";
$traceUniqueID="";
$spRevId="";
$spRevpassword="";
$spId="";
$serviceId="";
$linkid="";

/*
* The method called when a start tag is encountered.
*/
function sax_start($sax, $tag, $attr) {
	global $timeStampflag;
	global $subReqIDflag;
	global $traceUniqueIDflag;
	global $spRevIdflag;
	global $spRevpasswordflag;
	global $spIdflag;
	global $serviceIdflag;
	global $linkidflag;
	global $buffer;
	global $timeStamp;
	global $subReqID;
	global $traceUniqueID;
	global $spRevId;      
	global $spRevpassword;
	global $spId;         
	global $serviceId;    
	global $linkid;       
	
	//if (strpos($a,'are') !== false) {
	if(strpos($tag,'traceUniqueID') !== false){
		$traceUniqueIDflag=1;
	}	
	else if (strpos($tag,'timeStamp') !== false){
		$timeStampflag=1;
	}
	else if(strpos($tag,'subReqID') !== false){
		$subReqIDflag=1;
	}
	else if(strpos($tag,'spRevId') !== false){
		$spRevIdflag=1;
	}
	else if(strpos($tag,'spRevpassword') !== false){
		$spRevpasswordflag=1;
	}
	else if(strpos($tag,'spId') !== false){
		$spIdflag=1;
	}
	else if(strpos($tag,'serviceId') !== false){
		$serviceIdflag=1;
	}
	else if(strpos($tag,'linkid') !== false){
		$linkidflag=1;
	}
}


/*
* The method called when a end tag is encountered.
*/
function sax_end($sax, $tag) {
	
	global $timeStampflag;
	global $subReqIDflag;
	global $traceUniqueIDflag;
	global $spRevIdflag;
	global $spRevpasswordflag;
	global $spIdflag;
	global $serviceIdflag;
	global $linkidflag;
	global $buffer;
	global $timeStamp;
	global $subReqID;
	global $traceUniqueID;
	global $spRevId;      
	global $spRevpassword;
	global $spId;         
	global $serviceId;    
	global $linkid; 
	
	if(strpos($tag,'traceUniqueID') !== false){
		$traceUniqueID=$buffer;
		$traceUniqueIDflag=0;
		$buffer="";
	}	
	else if (strpos($tag,'timeStamp') !== false){
		$timeStamp=$buffer;
		$timeStampflag=0;
		$buffer="";
	}
	else if(strpos($tag,'subReqID') !== false){
		$subReqID=$buffer;
		$subReqIDflag=0;
		$buffer="";
	}
	else if(strpos($tag,'spRevId') !== false){
		$spRevId=$buffer;
		$spRevIdflag=0;
		$buffer="";
	}
	else if(strpos($tag,'spRevpassword') !== false){
		$spRevpassword=$buffer;
		$spRevpasswordflag=0;
		$buffer="";
	}
	else if(strpos($tag,'spId') !== false){
		$spId=$buffer;
		$spIdflag=0;
		$buffer="";
	}
	else if(strpos($tag,'serviceId') !== false){
		$serviceId=$buffer;
		$serviceIdflag=0;
		$buffer="";
	}
	else if(strpos($tag,'linkid') !== false){
		$linkid=$buffer;
		$linkidflag=0;
		$buffer="";
	}
	
}


/*
* The method called when data is encountered.
*/
function sax_cdata($sax, $data) {

	global $timeStampflag;
	global $subReqIDflag;
	global $traceUniqueIDflag;
	global $spRevIdflag;
	global $spRevpasswordflag;
	global $spIdflag;
	global $serviceIdflag;
	global $linkidflag;
	global $buffer;
	global $timeStamp;
	global $subReqID;
	global $traceUniqueID;
	global $spRevId;      
	global $spRevpassword;
	global $spId;         
	global $serviceId;    
	global $linkid; 																																							
																																									
	if($traceUniqueIDflag==1){ $buffer.=$data; } 																																								
	else if($subReqIDflag==1){ $buffer.=$data; } 																																								
	else if($timeStampflag==1){ $buffer.=$data; } 																																								
	else if($spRevIdflag==1){ $buffer.=$data; } 																																								
	else if($spRevpasswordflag==1){ $buffer.=$data; } 																																								
	else if($spIdflag==1){ $buffer.=$data; } 																																								
	else if($serviceIdflag==1){ $buffer.=$data; } 																																								
	else if($linkidflag==1){ $buffer.=$data; } 																																								
}


/*
* Create a parser and set options
*/
$sax = xml_parser_create();
xml_parser_set_option($sax, XML_OPTION_CASE_FOLDING, false);
xml_parser_set_option($sax, XML_OPTION_SKIP_WHITE,true);
xml_set_element_handler($sax, 'sax_start','sax_end');
xml_set_character_data_handler($sax, 'sax_cdata');

/*
* Parser the $data
*/
xml_parse($sax, $data ,true);

/*
* Free resources
*/
xml_parser_free($sax);
?>