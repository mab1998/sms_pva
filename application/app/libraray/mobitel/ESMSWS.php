<?php

/* This php file contains the methods which access the ESMS web services
*Author: Izzath Dilshana
*
*
*=================sample code for sending SMS===========================================
*
*$session=createSession('','username','password','');
*sendMessages($session,'alias','message text',array('71xxxxxxx','71xxxxxxx'),1); // 1 for promotional messages, 0 for normal message
*closeSession($session);
*
*=======================================================================================
*
*
*==============sample code for retrieving SMS===========================================
*
*$session=createSession('','username','password','');
*getMessagesFromShortCode($session,"shortcode");
*getMessagesFromLongNumber($session,"longnum");
*closeSession($session);
*
*=======================================================================================
**/

//====================================ESMS WEB SERVCIES START	================================



//create soap client
function getClient()
{

	ini_set("soap.wsdl_cache_enabled", "0");
// options for ssl in php 5.6.5
	$opts = array(
		'ssl' => array(
			'ciphers' => 'RC4-SHA',
			'verify_peer' => false,
			'verify_peer_name' => false
		)
	);

// SOAP 1.2 client
	$params = array(
		'encoding' => 'UTF-8',
		'verifypeer' => false,
		'verifyhost' => false,
		'soap_version' => SOAP_1_2,
		'trace' => 1,
		'exceptions' => 1,
		'connection_timeout' => 180,
		'stream_context' => stream_context_create($opts)
	);

	$wsdlUrl = 'http://smeapps.mobitel.lk:8585/EnterpriseSMSV3/EnterpriseSMSWS?wsdl';

	$client = new SoapClient($wsdlUrl, $params);

	return $client;

}


//serviceTest
function serviceTest($id,$username,$password,$customer)
{

	$client = getClient();

	$user = new stdClass();
	$user->id = '';
	$user->username = $username;
	$user->password = $password;
	$user->customer = '';

	$serviceTest = new stdClass();
	$serviceTest->arg0 = $user;

	return $client->serviceTest($serviceTest);

}

//create session
function createSession($id,$username,$password,$customer)
{

	$client = getClient();

	$user = new stdClass();
	$user->id = $id;
	$user->username = $username;
	$user->password = $password;
	$user->customer = $customer;

	$createSession = new stdClass();
	$createSession->user = $user;

	$createSessionResponse = new stdClass();
	$createSessionResponse = $client->createSession($createSession);

	return $createSessionResponse->return;

}

//check if session is valid
function isSession($session)
{

	$client = getClient();

	$isSession = new stdClass();
	$isSession->session = $session;

	$isSessionResponse = new stdClass();
	$isSessionResponse = $client->isSession($isSession);

	return $isSessionResponse->return;
}

//send SMS to recipients
function sendMessages($session,$alias,$message,$recipients,$messageType)
{
	$client=getClient();

	$smsMessage= new stdClass();
	$smsMessage->message=$message;
	$smsMessage->messageId="";
	$smsMessage->recipients=$recipients;
	$smsMessage->retries="";
	$smsMessage->sender=$alias;
	$smsMessage->messageType=$messageType;
	$smsMessage->sequenceNum="";
	$smsMessage->status="";
	$smsMessage->time="";
	$smsMessage->type="";
	$smsMessage->user="";

	$sendMessages = new stdClass();
	$sendMessages->session = $session;
	$sendMessages->smsMessage = $smsMessage;

	$sendMessagesResponse = new stdClass();
	$sendMessagesResponse = $client->sendMessages($sendMessages);

	return $sendMessagesResponse->return;
}

//send Unicoded SMS to recipients
function sendMessagesMultiLang($session,$alias,$message,$recipients,$messageType)
{
	$client=getClient();

	$smsMessageMultiLang = new stdClass();
	$smsMessageMultiLang->message=$message;
	$smsMessageMultiLang->messageId="";
	$smsMessageMultiLang->recipients=$recipients;
	$smsMessageMultiLang->retries="";
	$smsMessageMultiLang->sender=$alias;
	$smsMessageMultiLang->messageType=$messageType;
	$smsMessageMultiLang->sequenceNum="";
	$smsMessageMultiLang->status="";
	$smsMessageMultiLang->time="";
	$smsMessageMultiLang->type="";
	$smsMessageMultiLang->user="";

	$sendMessagesMultiLang = new stdClass();
	$sendMessagesMultiLang->session = $session;
	$sendMessagesMultiLang->smsMessageMultiLang = $smsMessageMultiLang;

	$sendMessagesMultiLangResponse = new stdClass();
	$sendMessagesMultiLangResponse = $client->sendMessagesMultiLang($sendMessagesMultiLang);

	return $sendMessagesMultiLangResponse->return;
}

//send Campaign SMS to recipients
function sendCampaignMessages($session,$alias,$message,$recipients,$datetime,$multilanguage,$messageType)
{
	$client=getClient();

	$smsCampaignMessage = new stdClass();
	$smsCampaignMessage->message = $message;
	$smsCampaignMessage->messageId = "";
	$smsCampaignMessage->recipients = $recipients;
	$smsCampaignMessage->retries = "";
	$smsCampaignMessage->sender = $alias;
	$smsCampaignMessage->messageType=$messageType;
	$smsCampaignMessage->sequenceNum = "";
	$smsCampaignMessage->status = "";
	$smsCampaignMessage->time = $datetime;
	$smsCampaignMessage->type = "";
	$smsCampaignMessage->user = "";
	$smsCampaignMessage->esmClass = $multilanguage;

	$sendCampaignMessages=new stdClass();
	$sendCampaignMessages->session=$session;
	$sendCampaignMessages->smsCampaignMessage=$smsCampaignMessage;


	$sendCampaignMessagesResponse = new stdClass();
	$sendCampaignMessagesResponse = $client->sendCampaignMessages($sendCampaignMessages);

	return $sendCampaignMessagesResponse->return;
}

//renew session
function renewSession($session)
{

	$client = getClient();

	$renewSession = new stdClass();
	$renewSession->session = $session;

	$renewSessionResponse = new stdClass();
	$renewSessionResponse = $client->renewSession($renewSession);

	return $renewSessionResponse->return;

}


//close session
function closeSession($session)
{

	$client = getClient();

	$closeSession = new stdClass();
	$closeSession->session = $session;

	$client->closeSession($closeSession);

}

//retrieve messages from shortcode
function getMessagesFromShortCode($session,$shortCode)
{

	$client = getClient();

	$getMessagesFromShortCode = new stdClass();
	$getMessagesFromShortCode->session = $session;
	$getMessagesFromShortCode->shortcode = $shortCode;

	$getMessagesFromShortcodeResponse = new stdClass();
	$getMessagesFromShortcodeResponse->return = "";
	$getMessagesFromShortcodeResponse = $client->getMessagesFromShortcode($getMessagesFromShortCode);

	if(property_exists($getMessagesFromShortcodeResponse,'return'))
		return $getMessagesFromShortcodeResponse->return;

	else return NULL;

}

//retrieve delivery report
function getDeliveryReports($session,$alias)
{

	$client = getClient();

	$getDeliveryReports = new stdClass();
	$getDeliveryReports->session = $session;
	$getDeliveryReports->alias = $alias;

	$getDeliveryReportsResponse = new stdClass();
	$getDeliveryReportsResponse->return = "";
	$getDeliveryReportsResponse = $client->getDeliveryReports($getDeliveryReports);

	if(property_exists($getDeliveryReportsResponse,'return'))
		return $getDeliveryReportsResponse->return;

	else return NULL;

}

//retrieve messages from longnumber
function getMessagesFromLongNumber($session,$longNumber)
{

	$client = getClient();

	$getMessagesFromLongNumber = new stdClass();
	$getMessagesFromLongNumber->session = $session;
	$getMessagesFromLongNumber->longNumber=$longNumber;

	$getMessagesFromLongNumberResponse = new stdClass();
	$getmessagesFromLongNumberResponse->return = "";
	$getMessagesFromLongNumberResponse = $client->getMessagesFromLongNumber($getMessagesFromLongNumber);

	if(property_exists($getMessagesFromLongNumberResponse,'return'))
		return $getMessagesFromLongNumberResponse->return;

	else return NULL;

}




//==================================ESMS WEB SERVICE END=============================================================



?>
