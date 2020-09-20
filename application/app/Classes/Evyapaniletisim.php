<?php

class Smsapi
{
	/* Constants */
	const URL = 'gw.barabut.com';
	const USE_HTTPS = false;
	const DEBUG = true;

	/* Username of the account */
	private $username;

	/* The current password of the account */
	private $password;

	/*
	 * The source/sender address that the message will appear to come from. Valid international format number between 1 and 16
	 * characters long, or 11 character alphanumeric string. If account is not allowed to use dynamic sender, it must be
	 * predefined at SMS gateway. If this field is left empty default sender will be used.
	 * */
	private $from = '';

	/*
	 * This parameter specifies the scheduled date time at which the message delivery should be first attempted. When empty
	 * message will be delivered immediately.
	 * Date time format should be ISO 8601 (2011-05-01T00:00:00 or 2011-05-01T00:00:00+01:00).
	 * If time zone offset is not specified, system will consider that the time is in UTC time zone.
	 * */
	private $scheduled_delivery_time = '';

	/*
	 * The validity period in minutes relative to the time in $scheduled_delivery_time field. Minimum 60 min, maximum 1710 min.
	 * */
	private $validity_period = 1440;

	/*
	 * Enum: Default or UCS2
	 * Default: GSM 7 bit default alphabet, http://en.wikipedia.org/wiki/GSM_03.38
	 * UCS2   : Unicode encoding, http://en.wikipedia.org/wiki/UCS-2
	 * */
	private $data_coding = 'Default';


	function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}

	/*
	 * Send one SMS (text) to many cell phone numbers. (1toN)
	 * */
	public function submit($to_list, $message, $from = null, $scheduled_delivery_time = null, $validity_period = null, $data_coding = null){

		if(count($to_list) == 0){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Enter at least one cell phone number';

			return $response;
		}

		if(trim($message) == ''){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Message text is empty.';

			return $response;
		}

		$this->checkMessageHeader($from, $scheduled_delivery_time, $validity_period, $data_coding);

		// Create XML
		$writer = $this->startDocument("Submit");
		$this->writeCredential($writer);
		$this->writeDataCoding($writer);
		$this->writeHeader($writer);
		$this->writeMessage($writer, $message, $to_list);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("Submit", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

/*
	 * Send one binary SMS (paylod + udh) to many cell phone numbers. (1toN)
	 * */
	public function submitData($to_list, $parts, $from = null, $scheduled_delivery_time = null, $validity_period = null){

		if(count($to_list) == 0){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Enter at least one cell phone number';

			return $response;
		}

		if(count($parts) == 0){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Data parts should have at least one element.';

			return $response;
		}

		$this->checkMessageHeader($from, $scheduled_delivery_time, $validity_period, null);

		// Create XML
		$writer = $this->startDocument("SubmitData");
		$this->writeCredential($writer);
		$this->writeData($writer, $parts);
		$this->writeHeader($writer);
		$this->writeToList($writer, $to_list);

		$xml = $this->endDocument($writer);

		$response = $this->httpPost("SubmitData", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

	/*
	 * Send personalized SMS messages. (NtoN)
	 * */
	public function submitMulti($envelop_list, $from = null, $scheduled_delivery_time = null, $validity_period = null, $data_coding = null){

		if(count($envelop_list) == 0){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Enter at least one record of cell phone number and message text';

			return $response;
		}

		$this->checkMessageHeader($from, $scheduled_delivery_time, $validity_period, $data_coding);

		// Create XML
		$writer = $this->startDocument("SubmitMulti");
		$this->writeCredential($writer);
		$this->writeDataCoding($writer);
		$this->writeEnvelope($writer, $envelop_list);
		$this->writeHeader($writer);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("SubmitMulti", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

	/*
	 * Send personalized binary SMS. (NtoN)
	 * */
	public function submitDataMulti($envelop_list, $from = null, $scheduled_delivery_time = null, $validity_period = null){

		if(count($envelop_list) == 0){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Enter at least one record of cell phone number and binary message	';

			return $response;
		}

		$this->checkMessageHeader($from, $scheduled_delivery_time, $validity_period, null);

		// Create XML
		$writer = $this->startDocument("SubmitDataMulti");
		$this->writeCredential($writer);
		$this->writeDataEnvelope($writer, $envelop_list);
		$this->writeHeader($writer);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("SubmitDataMulti", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

	/*
	 * Query message status
	 * */
	public function query($message_id, $msisdn = null){

		if(is_null($message_id) || !is_long($message_id)){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'No message id to query';

			return $response;
		}

		if(is_null($msisdn)){
			$msisdn = '';
		}

		// Create XML
		$writer = $this->startDocument("Query");
		$this->writeCredential($writer);
		$this->writeMSISDN($writer, $msisdn);
		$this->writeMessageId($writer, $message_id);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("Query", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}


	public function query_multi($MessageId,$Begin=null , $End=null){
	// Create XML

	$writer = $this->startDocument("QueryMulti");
	$this->writeCredential($writer);
	$this->writeMessageId($writer, $MessageId);
	$this->writeRange($writer,$Begin,$End);
	$xml = $this->endDocument($writer);
	$response = $this->httpPost("QueryMulti",$xml);

	if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}
		return $response;
	}
	/*
	 * This command is issued by the client to cancel one or more previously submitted short messages that are pending delivery.
	 * */
	public function cancel($message_id){

		if(is_null($message_id) || !is_long($message_id)){
			$response = new SmsApiResponse();

			$response->status = false;
			$response->error = 'Provide message id to cancel';

			return $response;
		}

		// Create XML
		$writer = $this->startDocument("Cancel");
		$this->writeCredential($writer);
		$this->writeMessageId($writer, $message_id);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("Cancel", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

	/*
	 * Get account settings/information
	 * */
	public function getSettings(){
		// Create XML
		$writer = $this->startDocument("GetSettings");
		$this->writeCredential($writer);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("GetSettings", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

	/*
	 * Get account balance
	 * */
	public function getBalance(){
		// Create XML
		$writer = $this->startDocument("GetBalance");
		$this->writeCredential($writer);
		$xml = $this->endDocument($writer);

		$response = $this->httpPost("GetBalance", $xml);

		if(self::DEBUG){
			echo "XML:\r\n" . $xml . "\r\n";
			echo "FUNCTION STATUS:" . $response->status . " - " . $response->error . "\r\n";
			print_r($response->payload);
		}

		return $response;
	}

	/*
	 * XML chunks
	 * */

	private function startDocument($root_name){
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(4);

		$writer->startElement($root_name);
		$writer->writeAttribute('xmlns:i', 'http://www.w3.org/2001/XMLSchema-instance');
		$writer->writeAttribute('xmlns', 'SmsApi');

		return $writer;
	}

	private function endDocument($writer){
		$writer->endElement();
		$writer->endDocument();;

		return $writer->outputMemory();
	}

	private function writeCredential($writer){
		$writer->startElement('Credential');
		$writer->writeElement('Password', $this->password);
		$writer->writeElement('Username', $this->username);
		$writer->endElement(); // Credential
	}

	private function writeRange($writer,$begin , $end){
	$writer->startElement('Range');
	if(is_null($begin)){
	$writer->writeElement('Begin i:nil="true"');
	 	}
		else{
		$writer->writeElement('Begin',$begin);
		}
		if(is_null($end)){
		$writer->writeElement('End i:nil="true"');
	 	}
	else{
	$writer->writeElement('End',$end);
	}
	$writer->endElement();

	}

	private function writeHeader($writer){
		$writer->startElement('Header');
		$writer->writeElement('From',  $this->from);
		$writer->startElement('ScheduledDeliveryTime');

		if($this->scheduled_delivery_time == ''){
			$writer->writeAttribute('i:nil', 'true');
		}

		$writer->text($this->scheduled_delivery_time);
		$writer->endElement();
		$writer->writeElement('ValidityPeriod', $this->validity_period);
		$writer->endElement(); // Header
	}

	private function writeDataCoding($writer){
		$writer->writeElement('DataCoding', $this->data_coding);
	}

	private function writeMessage($writer, $message, $to_list){
		$writer->writeElement('Message', $message);
		$this->writeToList($writer, $to_list);
	}

	private function writeToList($writer, $to_list){
		$writer->startElement('To');
		$writer->writeAttribute('xmlns:d2p1', 'http://schemas.microsoft.com/2003/10/Serialization/Arrays');
		foreach ($to_list as $to_item) {
			$writer->writeElement('d2p1:string', $to_item);
		}
		$writer->endElement(); // To
	}

	private function writeEnvelope($writer, $envelop_list){
		$writer->startElement('Envelopes');

		foreach ($envelop_list as $envelop) {
			$writer->startElement('Envelope');
			$writer->writeElement('Message', $envelop->message);
			$writer->writeElement('To', $envelop->to);
			$writer->endElement(); // Envelope
		}

		$writer->endElement(); // Envelopes
	}

	private function writeData($writer, $parts){
		$writer->startElement('Data');
		$this->writeDataParts($writer, $parts);
		$writer->endElement(); // Data
	}

	private function writeDataParts($writer, $parts){
		foreach ($parts as $part) {
			$writer->startElement('DataItem');
			$writer->writeElement('Payload', $part->payload);
			$writer->writeElement('Xser', $part->xser);
			$writer->endElement(); // DataItem
		}
	}

	private function writeDataEnvelope($writer, $envelop_list){
		$writer->startElement('Envelopes');

		foreach ($envelop_list as $envelop) {
			$writer->startElement('DataEnvelope');
			$this->writeData($writer, $envelop->message);
			$writer->writeElement('To', $envelop->to);
			$writer->endElement(); // DataEnvelope
		}

		$writer->endElement(); // Envelopes
	}

	private function writeMSISDN($writer, $msisdn){
		$writer->writeElement('MSISDN', $msisdn);
	}

	private function writeMessageId($writer, $message_id){
		$writer->writeElement('MessageId', $message_id);
	}

	/*
	 * Basic validation. Programmer is responsible for detailed input validation.
	 * */
	private function checkMessageHeader($from, $scheduled_delivery_time, $validity_period, $data_coding){

		if(is_null($from)){
			$this->from = '';
		}
		else{
			$this->from = $from;
		}

		if(is_null($scheduled_delivery_time)){
			$this->scheduled_delivery_time = '';
		}
		else{
			$this->scheduled_delivery_time = $scheduled_delivery_time;
		}

		if(is_null($validity_period) || !is_int($validity_period)){
			$this->validity_period = 1440;
		}
		else{
			$this->validity_period = ($validity_period < 60 || $validity_period > 1710) ? 1440 : $validity_period;
		}

		if(is_null($data_coding)){
			$this->data_coding = 'Default';
		}
		else{
			$this->data_coding = ($data_coding === "UCS2") ? $data_coding : 'Default';
		}
	}

	/*
	 * HTTP Helper
	 * */
	private function httpPost($action, $content) {
		$response = new SmsApiResponse();

	    if (empty($content)) {
	    	$response->status = false;
			$response->error = 'XML string is empty';

			return $response;
	    }

	   	$content_len = strlen($content);

	    $payload  = "POST /v1/xml/syncreply/$action HTTP/1.1\r\n";
	    $payload .= "Host: " . self::URL . "\r\n";
	    $payload .= "Content-Type: application/xml; charset=utf-8\r\n";
	    $payload .= "Content-Length: $content_len\r\n";
	    $payload .= "Connection: close\r\n\r\n";
	    $payload .= "$content\r\n";

	    if(self::USE_HTTPS){
	    	$port = 443;
	    	$host = 'ssl://' . self::URL;
	    }
	    else{
	    	$port = 80;
	    	$host = self::URL;
	    }

	    // Open socket, provide error report vars and timeout of 300 seconds.
	    $fp  = @fsockopen($host, $port, $errno, $errstr, 300);

	    // If we don't have a stream resource, abort.
	    if (!(get_resource_type($fp) == 'stream')) {
	    	$response->status = false;
			$response->error = "Error at stream resource: $errno, $errstr";

			return $response;
	    }

	    // Send headers and content.
	    if (!fwrite($fp, $payload)) {
	        fclose($fp);
	        $response->status = false;
			$response->error = "Writing data to stream failed.";

			return $response;
		}

	    // Read all of response into $response and close the socket.
	    $socket_response = '';
	    while(!feof($fp)) {
	    	$socket_response .= fgets($fp, 8192);
	    }
	    fclose($fp);

	    // Check response
		if (empty($socket_response)) {
			$response->status = false;
			$response->error = "No Response. Check your internet connection :).";

			return $response;
		}

    	// Split into array, headers and content.
    	$chunks = explode("\r\n\r\n", trim($socket_response));

    	if (!is_array($chunks) or count($chunks) < 2) {
    		$response->status = false;
			$response->error = "Invalid response:\r\n" . implode($chunks);

        	return $response;
        }

    	$header  = $chunks[count($chunks) - 2];
    	$body    = $chunks[count($chunks) - 1];
    	$headers = explode("\n",$header);

    	unset($chunks);
    	unset($header);

		if (!is_array($headers) or count($headers) < 1) {
			$response->status = false;
			$response->error  = "Invalid header:\r\n" . implode($headers);
		}
		else if(trim($headers[0]) != "HTTP/1.1 200 OK"){
			$response->status = false;
			$response->error  = "HTTP Status Code NOK\r\n";
			$response->error .= "\r\nResponse header:\r\n" . implode($headers);
			$response->error .= "\r\nResponse body:\r\n" . $body;
		}
		else{
			$response->status = true;
			$response->error = '';
			$response->xml = $body;

			$response->payload = simplexml_load_string($body,'SimpleXMLElement', LIBXML_NOWARNING)->Response;
		}

    	return $response;
    }
}

class SmsApiResponse
{
	public $status;				// true if we receive HTTP status code 200, else false (possible error caused by client)
	public $error;				// if $status is false, holds information about cause
	public $xml;				// holds raw xml which we received

	public $payload; 			// interesting data is here
}

class Envelop
{
	public $message;
	public $to;

	function __construct($to, $message) {
		$this->to = $to;
		$this->message = $message;
	}
}

class DataItem
{
	public $payload;
	public $xser;

	function __construct($payload, $xser) {
		$this->payload = $payload;
		$this->xser = $xser;
	}
}

