<?php

/*
* import the nusoap library to be used for SOAP processing
*/
require_once('nusoap.php');

/*
* Load the SOAP client configuration file
*/
require_once('client_inc.php');

/*
* The class has untility functions that act as SDP client and perform various basic functions
* like send sms, reqister end point and stop end point, etc
*/
class SDPService{

	/*
	* startSmsNotification - method to send the startSmsNotification request to the SDP server for SMS notify
	* The interface is used to register the end point that should receive SMS (notifySmsReception) and delivery reports (notifySmsDeliveryReceipt)
	*
	* @parameters
	* $kmp_spid
	* $kmp_sppwd
	* $kmp_service_id
	* $kmp_timestamp
	* $kmp_notify_endpoint
	* $kmp_correlator
	* $kmp_code
	* $kmp_criteria
	*
	* @return
	* Associative array with: ResultCode, ResultDesc, and ResultDetails 
	* ResultDetails - for sucessful result code (0), the ResultDetails will be empty
	*/
	function startSmsNotification($kmp_spid,$kmp_sppwd,$kmp_service_id,$kmp_timestamp,$kmp_notify_endpoint,$kmp_correlator,$kmp_code,$kmp_criteria=''){
	
		global $kmp_start_stop_sms_notification_service_endpoint;
		global $kmp_start_stop_sms_notification_service_log_soap_messages;
		global $kmp_start_stop_sms_notification_service_log_file_prefix;
		global $kmp_start_stop_sms_notification_service_debug;
		
		$bodyxml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" 
					xmlns:loc="http://www.csapi.org/schema/parlayx/sms/notification_manager/v2_3/local">
					<soapenv:Header>
					  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
						 <spId>'.$kmp_spid.'</spId>
						 <spPassword>'.$kmp_sppwd.'</spPassword>
						 <serviceId>'.$kmp_service_id.'</serviceId>
						 <timeStamp>'.$kmp_timestamp.'</timeStamp>
					  </RequestSOAPHeader>
					</soapenv:Header>
					<soapenv:Body>
					  <loc:startSmsNotification>
						 <loc:reference>
							<endpoint>'.$kmp_notify_endpoint.'</endpoint>
							<interfaceName>startSmsNotification</interfaceName>
							<correlator>'.$kmp_correlator.'</correlator>
						 </loc:reference>
						 <loc:smsServiceActivationNumber>'.$kmp_code.'</loc:smsServiceActivationNumber>
					<loc:criteria>'.$kmp_criteria.'</loc:criteria>
						 </loc:startSmsNotification>
					</soapenv:Body>
					</soapenv:Envelope>';
		
		//create the client
		$client = new nusoap_client($kmp_start_stop_sms_notification_service_endpoint,true);	
		$bsoapaction = "";
		$client->soap_defencoding = 'utf-8';
		$client->useHTTPPersistentConnection();
		
		//send the request to the server
		$result = $client->send($bodyxml, $bsoapaction);
		
		//check whether to log requests and responses based on SOAP parameter
		if($kmp_start_stop_sms_notification_service_log_soap_messages == 1) { 
			//preparae date to be written into the log file
			$service_log_file = $kmp_start_stop_sms_notification_service_log_file_prefix.date("YmdG").".log";	
			$timestamp=date("YmdGisu");
			$operation="startSmsNotification";
			$request=$client->request;
			$response=$client->response;
			$debug_str="";

			//check whether to log the client debug str 
			if($kmp_start_stop_sms_notification_service_debug == 1){ 
				$debug_str=$client->debug_str;
			}
			$data = $timestamp."|".$operation."|".$request."|".$response."|".$debug_str."\n";
			$this->writeToFile($service_log_file,$data);
		}
		
		//check for fault and return
		if ($client->fault) {
		  return array("ResultCode"=>"1","ResultDesc"=>"SOAP Fault","ResultDetails"=>$result);
		}
		
		// check for errors and return
		$err = $client->getError();
		if ($err) {
			return array("ResultCode"=>"2","ResultDesc"=>"Error","ResultDetails"=>$err);
		}
		else{
			//check for fault code
			if(isset($result['faultcode'])){
				return array("ResultCode"=>"3","ResultDesc"=>"Fault - ".$result['faultcode'],"ResultDetails"=>$result['faultstring']);
			}
			//return success
			return array("ResultCode"=>"0","ResultDesc"=>"Operation Successful.","ResultDetails"=>$result);
		}
	}
	
	
   /*
	* startSmsNotification - method to send the startSmsNotification request to the SDP server for SMS notify
	* The interface is used to register the end point that should receive SMS (notifySmsReception) and delivery reports (notifySmsDeliveryReceipt)
	*
	* @parameters
	* $kmp_spid
	* $kmp_sppwd
	* $kmp_service_id
	* $kmp_timestamp
	* $kmp_notify_endpoint
	* $kmp_correlator
	* $kmp_code
	* $kmp_criteria
	*
	* @return
	* Associative array with: ResultCode, ResultDesc, and ResultDetails 
	* ResultDetails - for sucessful result code (0), the ResultDetails will be empty
	*/
	function stopSmsNotification($kmp_spid,$kmp_sppwd,$kmp_service_id,$kmp_timestamp,$kmp_correlator){
			
		global $kmp_start_stop_sms_notification_service_endpoint;
		global $kmp_start_stop_sms_notification_service_log_soap_messages;
		global $kmp_start_stop_sms_notification_service_log_file_prefix;
		global $kmp_start_stop_sms_notification_service_debug;

		$bodyxml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" 
					xmlns:loc="http://www.csapi.org/schema/parlayx/sms/notification_manager/v2_3/local">
					<soapenv:Header>
					  <v2:RequestSOAPHeader>
						 <spId>'.$kmp_spid.'</spId>
						 <spPassword>'.$kmp_sppwd.'</spPassword>
						 <serviceId>'.$kmp_service_id.'</serviceId>
						 <timeStamp>'.$kmp_timestamp.'</timeStamp>
					  </v2:RequestSOAPHeader>
					</soapenv:Header>
					<soapenv:Body>
					  <loc:stopSmsNotification>
						 <correlator>'.$kmp_correlator.'</correlator>
					  </loc:stopSmsNotification>
					</soapenv:Body>
					</soapenv:Envelope>';
		
		//create the client
		$client = new nusoap_client($kmp_start_stop_sms_notification_service_endpoint,true);	
		$bsoapaction = "";
		$client->soap_defencoding = 'utf-8';
		$client->useHTTPPersistentConnection();
		
		//send the request to the server
		$result = $client->send($bodyxml, $bsoapaction);
		
		//check whether to log requests and responses based on SOAP parameter
		if($kmp_start_stop_sms_notification_service_log_soap_messages == 1) { 
			//preparae date to be written into the log file
			$service_log_file = $kmp_start_stop_sms_notification_service_log_file_prefix.date("YmdG").".log";	
			$timestamp=date("YmdGisu");
			$operation="stopSmsNotification";
			$request=$client->request;
			$response=$client->response;
			$debug_str="";

			//check whether to log the client debug str 
			if($kmp_start_stop_sms_notification_service_debug == 1){ 
				$debug_str=$client->debug_str;
			}
			$data = $timestamp."|".$operation."|".$request."|".$response."|".$debug_str."\n";
			$this->writeToFile($service_log_file,$data);
		}
		
		//check for fault and return
		if ($client->fault) {
		  return array("ResultCode"=>"1","ResultDesc"=>"SOAP Fault","ResultDetails"=>$result);
		}
		
		// check for errors and return
		$err = $client->getError();
		if ($err) {
			return array("ResultCode"=>"2","ResultDesc"=>"Error","ResultDetails"=>$err);
		}
		else{
			//check for fault code
			if(isset($result['faultcode'])){
				return array("ResultCode"=>"3","ResultDesc"=>"Fault - ".$result['faultcode'],"ResultDetails"=>$result['faultstring']);
			}
			//return success
			return array("ResultCode"=>"0","ResultDesc"=>"Operation Successful.","ResultDetails"=>$result);
		}
	}
	

	/*
	* sendSms - method to Send SMS
	* The interface supports sending SMS to one or more recipient(s) in a single request
	* Maximum number of recipients set in $kmp_send_sms_service_max_recipient in client_inc.php
	*
	* @parameters
	* $kmp_spid
	* $kmp_sppwd
	* $kmp_service_id
	* $kmp_timestamp
	* $kmp_recipients
	* $kmp_correlator
	* $kmp_code
	* $kmp_message
	*
	* @return
	* Associative array with: ResultCode, ResultDesc, and ResultDetails 
	* ResultDetails - for sucessful code (0), the value is an array and ResultDetails['result'] gives the request identifier that can be used in querying delivery status.
	*/
	function sendSms($kmp_spid,$kmp_sppwd,$kmp_service_id,$kmp_timestamp,$kmp_recipients,$kmp_correlator,$kmp_code,$kmp_message,$kmp_linkid=''){
	
		//parameters set in the client_inc.php configuration file
		global $kmp_send_sms_notify_service_endpoint;
		global $kmp_send_sms_service_endpoint;
		global $kmp_send_sms_service_log_file_prefix;
		global $kmp_send_sms_service_max_recipient;
		global $kmp_send_sms_service_log_soap_messages;
		global $kmp_send_sms_service_log_file_prefix;
		global $kmp_send_sms_service_debug;
		global $kmp_send_sms_notify_delivery;
		
		//construct the SOAP request to be sent to the SDP server
		$bodyxml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" 
					xmlns:loc="http://www.csapi.org/schema/parlayx/sms/send/v2_2/local"> <soapenv:Header> <v2:RequestSOAPHeader>
					<spId>'.$kmp_spid.'</spId>
					<spPassword>'.$kmp_sppwd.'</spPassword>
					<serviceId>'.$kmp_service_id.'</serviceId>
					<timeStamp>'.$kmp_timestamp.'</timeStamp>';
					
		if(!empty($kmp_linkid)){
			$bodyxml.='<v2:linkid>'.$kmp_linkid.'</v2:linkid>';
		}
		 
		 //Check whether the recipient is empty or isset
		if(isset($kmp_recipients)){
			//if the recipient is one number, we include the OA and FA parameter in the SOAP header
			if(count($kmp_recipients)==1){	 
				$bodyxml.='<v2:OA>tel:'.$kmp_recipients.'</v2:OA><v2:FA>tel:'.$kmp_recipients.'</v2:FA>';
			}
		}
		else{
			return array("ResultCode"=>"4","ResultDesc"=>"Recipient(s) empty.","ResultDetails"=>"No recipient address(es) specified."); 
		}
		 
		$bodyxml.='</v2:RequestSOAPHeader></soapenv:Header><soapenv:Body><loc:sendSms>';
		
		//specify the address of the recipient
		$count=count($kmp_recipients);
		if($count == 1){ //one recipient
			$bodyxml.='<loc:addresses>tel:'.$kmp_recipients.'</loc:addresses>';
		}
		else if($count > $kmp_send_sms_service_max_recipient){ //too many recipients
			return array("ResultCode"=>"5","ResultDesc"=>"Too many recipients.","ResultDetails"=>"The number of recipients exceeds the maximum number."); 
		}
		else{ //more than one recipients
			foreach ($kmp_recipients as $misdn){
				$bodyxml.='<loc:addresses>tel:'.$misdn.'</loc:addresses>';
			}
		}
		
		//specify the last part of the soap request
		$bodyxml.=	'<loc:senderName>'.$kmp_code.'</loc:senderName>
					<loc:message>'.$kmp_message.'</loc:message>';
					
		//include receiptRequest part in the message
		if($kmp_send_sms_notify_delivery == 1){
			$bodyxml.=	'<loc:receiptRequest>
						<endpoint>'.$kmp_send_sms_notify_service_endpoint.'</endpoint>
						<interfaceName>SmsNotification</interfaceName>
						<correlator>'.$kmp_correlator.'</correlator>
						</loc:receiptRequest>';
		}
		
		$bodyxml.=	'</loc:sendSms>
					</soapenv:Body>
					</soapenv:Envelope>';
					
		//Create the nusoap client and set the parameters, endpoint specified in the client_inc.php
		$client = new nusoap_client($kmp_send_sms_service_endpoint,true);	
		$bsoapaction = "";
		$client->soap_defencoding = 'utf-8';
		$client->useHTTPPersistentConnection();
		
		//Send the soap request to the server
		$result = $client->send($bodyxml, $bsoapaction);
		
		//check whether to log requests and responses based on SOAP parameter
		if($kmp_send_sms_service_log_soap_messages == 1) { 
			//preparae date to be written into the log file
			$service_log_file = $kmp_send_sms_service_log_file_prefix.date("YmdG").".log";	
			$timestamp=date("YmdGisu");
			$operation="SendSms";
			$request=$client->request;
			$response=$client->response;
			$debug_str="";

			//check whether to log the client debug str 
			if($kmp_send_sms_service_debug == 1){ 
				$debug_str=$client->debug_str;
			}
			$data = $timestamp."|".$operation."|".$request."|".$response."|".$debug_str."\n";
			$this->writeToFile($service_log_file,$data);
		}
		
		//check for fault and return
		if ($client->fault) {
		  return array("ResultCode"=>"1","ResultDesc"=>"SOAP Fault","ResultDetails"=>$result);
		}
		
		// check for errors and return
		$err = $client->getError();
		if ($err) {
			return array("ResultCode"=>"2","ResultDesc"=>"Error","ResultDetails"=>$err);
		}
		else{
			//check for fault code
			if(isset($result['faultcode'])){
				return array("ResultCode"=>"3","ResultDesc"=>"Fault - ".$result['faultcode'],"ResultDetails"=>$result['faultstring']);
			}
			//return success
			return array("ResultCode"=>"0","ResultDesc"=>"Operation Successful.","ResultDetails"=>$result);
		}
	} //end of sendSms method
	
	
	/*
	* @method: getSmsDeliveryStatus - method to get SMS Delivery Status
	* Supports getting delivery status using either requestIdentifier alone or both requestIdentifier and MSISDN (address)
	*
	* @parameters
	* $spId
	* $spPassword
	* $serviceId
	* $timeStamp
	* $requestIdentifier
	* $msisdn - optional 
	*
	* @return
	* Associative array with: ResultCode, ResultDesc, and ResultDetails 
	* ResultDetails - contains additional information. For successful result code (0). The aray will contain delivery status of all the messages sent and referenced by the same $kmp_request_identifier
	*/
	function getSmsDeliveryStatus($kmp_spid,$kmp_sppwd,$kmp_service_id,$kmp_timestamp,$kmp_request_identifier,$kmp_msisdn=''){
	
		global $kmp_get_sms_delivery_status_service_endpoint;
		global $kmp_get_sms_delivery_status_service_log_soap_messages;
		global $kmp_get_sms_delivery_status_service_log_file_prefix;
		global $kmp_get_sms_delivery_status_service_debug;
		
		$bodyxml='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
					xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1"
					xmlns:loc="http://www.csapi.org/schema/parlayx/sms/send/v2_2/local">
					<soapenv:Header>
					  <v2:RequestSOAPHeader>
						 <v2:spId>'.$kmp_spid.'</v2:spId>
						 <v2:spPassword>'.$kmp_sppwd.'</v2:spPassword>
						 <v2:serviceId>'.$kmp_service_id.'</v2:serviceId>
						 <v2:timeStamp>'.$kmp_timestamp.'</v2:timeStamp>';
		
		//populate the MSISDN			 
		/*
		if(!empty($kmp_msisdn)){
			$bodyxml.='<v2:OA>tel:'.$kmp_msisdn.'</v2:OA>
						<v2:FA>tel:'.$kmp_msisdn.'</v2:FA>';
		}
		*/
	
		$bodyxml.='</v2:RequestSOAPHeader>
					</soapenv:Header>
					<soapenv:Body>
					  <loc:getSmsDeliveryStatus>
						 <loc:requestIdentifier>'.$kmp_request_identifier.'</loc:requestIdentifier>
					  </loc:getSmsDeliveryStatus>
					</soapenv:Body>
					</soapenv:Envelope>';
		
		//requestIdentifier is empty
		if(empty($kmp_request_identifier)){
			return array("ResultCode"=>"6","ResultDesc"=>"Missing Request Identifier.","ResultDetails"=>"Request Identifier Parameter is empty");
		}
		
		//Create the nusoap client and set the parameters, endpoint specified in the client_inc.php
		$client = new nusoap_client($kmp_get_sms_delivery_status_service_endpoint,true);	
		$bsoapaction = "";
		$client->soap_defencoding = 'utf-8';
		$client->useHTTPPersistentConnection();
		
		//Send the soap request to the server
		$result = $client->send($bodyxml, $bsoapaction);
		
		//check whether to log requests and responses based on SOAP parameter
		if($kmp_get_sms_delivery_status_service_log_soap_messages == 1) { 
			//preparae date to be written into the log file
			$service_log_file = $kmp_get_sms_delivery_status_service_log_file_prefix.date("YmdG").".log";	
			$timestamp=date("YmdGisu");
			$operation="getSmsDeliveryStatus";
			$request=$client->request;
			$response=$client->response;
			$debug_str="";

			//check whether to log the client debug str 
			if($kmp_get_sms_delivery_status_service_debug == 1){ 
				$debug_str=$client->debug_str;
			}
			$data = $timestamp."|".$operation."|".$request."|".$response."|".$debug_str."\n";
			$this->writeToFile($service_log_file,$data);
		}
		
		//check for fault and return
		if ($client->fault) {
		  return array("ResultCode"=>"1","ResultDesc"=>"SOAP Fault","ResultDetails"=>$result);
		}
		
		// check for errors and return
		$err = $client->getError();
		if ($err) {
			return array("ResultCode"=>"2","ResultDesc"=>"Error","ResultDetails"=>$err);
		}
		else{
			//check for fault code
			if(isset($result['faultcode'])){
				return array("ResultCode"=>"3","ResultDesc"=>"Fault - ".$result['faultcode'],"ResultDetails"=>$result['faultstring']);
			}
			//return success
			return array("ResultCode"=>"0","ResultDesc"=>"Operation Successful.","ResultDetails"=>$result);
		}
	}
	
	
	/*
	* @method writeToFile - Utility function to write file into disk for logging purposes
	* @parameter
	* $file - path/to/file
	* $data - the data to be written into the file
	*
	* @return - none
	*/
	function writeToFile($file,$data){
		if (file_exists($file)){
			file_put_contents($file,  $data, FILE_APPEND);
		}
		else{
			file_put_contents($file, $data);
		}
	}
	
} //end of class

?>