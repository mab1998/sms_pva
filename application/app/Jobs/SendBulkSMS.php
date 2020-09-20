<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\PhoneNumber;
use App\Client;
use App\IntCountryCodes;
use App\Operator;
use App\SMSHistory;
use App\SMSInbox;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Elibom\APIClient\ElibomClient;
use FSG\Goip;
use FSG\MessageVO;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use Nexmo\Client\Credentials\Basic;
use Nexmo\Client\Exception\Exception;
use Osms\Osms;
use Ovh\Sms\SmsApi;
use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\ApiException;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\Model\SendMessageRequest;
use telesign\sdk\messaging\MessagingClient;

class SendBulkSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $cl_phone;
    protected $user_id;
    protected $gateway;
    protected $gateway_credential;
    protected $sender_id;
    protected $message;
    protected $msgcount;
    protected $cg_info;
    protected $api_key;
    protected $get_sms_status;
    protected $msg_type;
    public $tries = 2;
    protected $campaign_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $cl_phone, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info = '', $api_key = '', $msg_type = 'plain', $campaign_id = '')
    {
        $this->cl_phone           = $cl_phone;
        $this->gateway            = $gateway;
        $this->gateway_credential = $gateway_credential;
        $this->sender_id          = $sender_id;
        $this->message            = $message;
        $this->msgcount           = $msgcount;
        $this->cg_info            = $cg_info;
        $this->api_key            = $api_key;
        $this->user_id            = $user_id;
        $this->msg_type           = $msg_type;
        $this->campaign_id        = $campaign_id;

    }

    private function make_stop_dup_id()
    {
        return 0;
    }

    private function make_post_body($post_fields)
    {
        $stop_dup_id = $this->make_stop_dup_id();
        if ($stop_dup_id > 0) {
            $post_fields['stop_dup_id'] = $this->make_stop_dup_id();
        }
        $post_body = '';
        foreach ($post_fields as $key => $value) {
            $post_body .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $post_body = rtrim($post_body, '&');

        return $post_body;
    }

    private function sms_unicode($message)
    {
        $hex1 = '';
        if (function_exists('iconv')) {
            $latin = @iconv('UTF−8', 'ISO−8859−1', $message);
            if (strcmp($latin, $message)) {
                $arr  = unpack('H*hex', @iconv('UTF-8', 'UCS-2BE', $message));
                $hex1 = strtoupper($arr['hex']);
            }
            if ($hex1 == '') {
                $hex2 = '';
                $hex  = '';
                for ($i = 0; $i < strlen($message); $i++) {
                    $hex = dechex(ord($message[$i]));
                    $len = strlen($hex);
                    $add = 4 - $len;
                    if ($len < 4) {
                        for ($j = 0; $j < $add; $j++) {
                            $hex = "0" . $hex;
                        }
                    }
                    $hex2 .= $hex;
                }
                return $hex2;
            } else {
                return $hex1;
            }
        } else {
            return 'failed';
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $gateway_url  = rtrim($this->gateway->api_link, '/');
        $gateway_name = $this->gateway->settings;
        $gateway_port = $this->gateway->port;
        $msg_type     = $this->msg_type;

        if ($this->gateway->custom == 'Yes' && $this->cg_info != '' && $this->gateway->type == 'http') {

            $send_custom_data = array();
            $username_param   = $this->cg_info->username_param;
            $username_value   = $this->cg_info->username_value;

            $send_custom_data[$username_param] = $username_value;

            if ($this->cg_info->password_status == 'yes') {
                $password_param = $this->cg_info->password_param;
                $password_value = $this->cg_info->password_value;

                $send_custom_data[$password_param] = $password_value;
            }

            if ($this->cg_info->action_status == 'yes') {
                $action_param = $this->cg_info->action_param;
                $action_value = $this->cg_info->action_value;

                $send_custom_data[$action_param] = $action_value;
            }

            if ($this->cg_info->source_status == 'yes') {
                $source_param = $this->cg_info->source_param;
                $source_value = $this->cg_info->source_value;

                if ($this->sender_id != null || $this->sender_id != '') {
                    $send_custom_data[$source_param] = $this->sender_id;
                } else {
                    $send_custom_data[$source_param] = $source_value;
                }
            }

            $destination_param                    = $this->cg_info->destination_param;
            $send_custom_data[$destination_param] = $this->cl_phone;

            $message_param                    = $this->cg_info->message_param;
            $send_custom_data[$message_param] = $this->message;

            if ($this->cg_info->unicode_status == 'yes' && $this->msg_type == 'unicode') {
                $unicode_param                    = $this->cg_info->unicode_param;
                $unicode_value                    = $this->cg_info->unicode_value;
                $send_custom_data[$unicode_param] = $unicode_value;
            }

            if ($this->cg_info->route_status == 'yes') {
                $route_param = $this->cg_info->route_param;
                $route_value = $this->cg_info->route_value;

                $send_custom_data[$route_param] = $route_value;
            }

            if ($this->cg_info->language_status == 'yes') {
                $language_param = $this->cg_info->language_param;
                $language_value = $this->cg_info->language_value;

                $send_custom_data[$language_param] = $language_value;
            }

            if ($this->cg_info->custom_one_status == 'yes') {
                $custom_one_param = $this->cg_info->custom_one_param;
                $custom_one_value = $this->cg_info->custom_one_value;

                $send_custom_data[$custom_one_param] = $custom_one_value;
            }

            if ($this->cg_info->custom_two_status == 'yes') {
                $custom_two_param = $this->cg_info->custom_two_param;
                $custom_two_value = $this->cg_info->custom_two_value;

                $send_custom_data[$custom_two_param] = $custom_two_value;
            }

            if ($this->cg_info->custom_three_status == 'yes') {
                $custom_three_param = $this->cg_info->custom_three_param;
                $custom_three_value = $this->cg_info->custom_three_value;

                $send_custom_data[$custom_three_param] = $custom_three_value;
            }

            $get_post_data = $this->make_post_body($send_custom_data);

            try {

                if ($gateway_port == 'POST') {

                    // Send the POST request with cURL
                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $get_post_data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $output = curl_exec($ch);


                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    curl_close($ch);

                } else {

                    $sms_sent_to_user = $gateway_url . "?" . $get_post_data;

                    $ch = curl_init($sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    curl_close($ch);
                }


                $custom_gateway_response_status = strtolower(app_config('custom_gateway_response_status'));
                $custom_gateway_response_status = explode(',', $custom_gateway_response_status);
                $custom_gateway_response_status = array_map('trim', $custom_gateway_response_status);
                $custom_gateway_response_status = implode('|', $custom_gateway_response_status);

                if (preg_match("($custom_gateway_response_status)", strtolower($output)) === 1) {
                    $get_sms_status = 'Success';
                } else {
                    $get_sms_status = trim($output);
                }

            } catch (\Exception $e) {
                $get_sms_status = $e->getMessage();
            }
        } elseif ($this->gateway->type == 'smpp') {

            $gateway_user_name = $this->gateway_credential->username;
            $gateway_password  = $this->gateway_credential->password;

            if ($gateway_name == 'ConnectBindSMPP' || $gateway_name == 'CMTelecomSMPP') {
                include_once app_path('Classes/Smpp.php');

                header('Content-Type: text/plain');

                $s        = new \smpp();
                $s->debug = 1;


                // $host,$port,$system_id,$password
                $s->open($gateway_url, $gateway_port, $gateway_user_name, $gateway_password);

                /* To send unicode*/
                if (strlen($this->message) != strlen(utf8_decode($this->message))) {
                    $utf            = true;
                    $this->message  = iconv('Windows-1256', 'UTF-16BE', $this->message);
                    $get_sms_status = $s->send_long($this->sender_id, $this->cl_phone, $this->message, $utf);
                } else {
                    // $source_addr,$destintation_addr,$short_message,$utf=0,$flash=0
                    $get_sms_status = $s->send_long($this->sender_id, $this->cl_phone, $this->message);
                }

                $s->close();

                if ($get_sms_status === true) {
                    $get_sms_status = 'Success';
                }

            } else {

                require_once(app_path('libraray/smpp/smppclient.class.php'));
                require_once(app_path('libraray/smpp/gsmencoder.class.php'));
                require_once(app_path('libraray/smpp/sockettransport.class.php'));

                $src     = $this->sender_id; // or text
                $dst     = $this->cl_phone;
                $message = $this->message;

                try {

                    // Construct transport and client
                    $transport = new \SocketTransport(array($gateway_url), $gateway_port);
                    $transport->setRecvTimeout(30000);
                    $smpp = new \SmppClient($transport);

                    // Activate binary hex-output of server interaction
                    $smpp->debug      = false;
                    $transport->debug = false;


                    // Open the connection
                    $transport->open();
                    $smpp->bindTransmitter($gateway_user_name, $gateway_password);

                    // Optional connection specific overrides
                    \SmppClient::$sms_null_terminate_octetstrings = false;
                    \SmppClient::$csms_method                     = \SmppClient::CSMS_8BIT_UDH;
                    \SmppClient::$sms_registered_delivery_flag    = \SMPP::REG_DELIVERY_SMSC_BOTH;

                    // Prepare message
                    $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
                    $encoded_msg_type  = preg_match($rtl_chars_pattern, $message);

                    if ($encoded_msg_type == 1) {
                        $encodedMessage = mb_convert_encoding($message, "UCS-2", "utf8");
                    } else {
                        $encodedMessage = \GsmEncoder::utf8_to_gsm0338($message);
                    }

                    if (ctype_digit($src) && strlen($src) <= 8) {
                        $from = new \SmppAddress($src, \SMPP::TON_NETWORKSPECIFIC);
                    } elseif (ctype_digit($src) && (strlen($src) <= 15 && strlen($src) >= 10)) {
                        $from = new \SmppAddress($src, \SMPP::TON_INTERNATIONAL, \SMPP::NPI_E164);
                    } else {
                        $from = new \SmppAddress($src, \SMPP::TON_ALPHANUMERIC);
                    }

                    $to = new \SmppAddress($dst, \SMPP::TON_INTERNATIONAL, \SMPP::NPI_E164);

                    // Send
                    $tags = "";
                    if ($encoded_msg_type == 1) {
                        $output = $smpp->sendSMS($from, $to, $encodedMessage, $tags, \SMPP::DATA_CODING_UCS2);
                    } else {
                        $output = $smpp->sendSMS($from, $to, $encodedMessage, $tags);
                    }
                    // Close connection
                    $smpp->close();

                    if (isset($output)) {
                        $get_sms_status = 'Success|' . $output;
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    // Close connection
                    $smpp->close();

                } catch (\Exception $e) {
                    $get_sms_status = $e->getMessage();
                }
            }

        } else {
            $gateway_user_name = $this->gateway_credential->username;
            $gateway_password  = $this->gateway_credential->password;
            $gateway_extra     = $this->gateway_credential->extra;

            switch ($gateway_name) {
                case 'Twilio':
                    try {
                        $client = new \Twilio\Rest\Client($gateway_user_name, $gateway_password);

                        if (is_numeric($this->cl_phone)) {
                            $phone = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                        } else {
                            $phone = $this->cl_phone;
                        }

                        $sender_id = $this->sender_id;

                        $get_response = $client->messages->create(
                            $phone, array(
                                'from' => $sender_id,
                                'body' => $this->message
                            )
                        );

                        if ($get_response->status == 'queued' || $get_response->status == 'accepted') {
                            $get_sms_status = 'Success' . '|' . $get_response->sid;
                        } else {
                            $get_sms_status = $get_response->status . '|' . $get_response->sid;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;
                case 'TwilioCopilot':
                    try {
                        $client = new \Twilio\Rest\Client($gateway_user_name, $gateway_password);

                        if (is_numeric($this->cl_phone)) {
                            $phone = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                        } else {
                            $phone = $this->cl_phone;
                        }


                        $get_response = $client->messages->create(
                            $phone, array(
                                'messagingServiceSid' => $gateway_extra,
                                'body' => $this->message
                            )
                        );

                        if ($get_response->status == 'queued' || $get_response->status == 'accepted') {
                            $get_sms_status = 'Success' . '|' . $get_response->sid;
                        } else {
                            $get_sms_status = $get_response->status . '|' . $get_response->sid;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Clickatell_Touch':

                    $phone   = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    $sms_sent_to_user = $gateway_url . "?apiKey=$gateway_user_name" . "&to=$phone" . "&content=$message";


                    if ($this->sender_id) {
                        $sender_id        = str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);
                        $sms_sent_to_user .= "&from=" . $sender_id;
                    }

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $get_result = json_decode($response);

                    if (isset($get_result->messages[0]->accepted) && $get_result->messages[0]->accepted) {
                        $get_sms_status = 'Success|' . $get_result->messages[0]->apiMessageId;
                    } elseif (isset($get_result->messages[0]->errorDescription) && $get_result->messages[0]->errorDescription != '') {
                        $get_sms_status = $get_result->messages[0]->errorDescription;
                    } elseif (isset($get_result->errorDescription) && $get_result->errorDescription != '') {
                        $get_sms_status = $get_result->errorDescription;
                    } else {
                        $get_sms_status = 'Invalid request';
                    }
                    break;

                case 'Clickatell_Central':

                    $sms_url = rtrim($gateway_url, '/');
                    try {

                        if ($msg_type == 'unicode') {
                            $type    = 1;
                            $message = $this->sms_unicode($this->message);
                        } else {
                            $type    = 0;
                            $message = urlencode($this->message);
                        }

                        $sms_sent_to_user = "$sms_url" . "/http/sendmsg?unicode=$type" . "&user=$gateway_user_name" . "&password=$gateway_password" . "&to=$this->cl_phone" . "&text=$message" . "&api_id=$gateway_extra";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count($get_sms_status, 'ID:') == 1) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }


                    break;

                case 'SMSKaufen':

                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    $sms_sent_to_user = $gateway_url . "?type=4" . "&id=$gateway_user_name" . "&apikey=$gateway_password" . "&empfaenger=$this->cl_phone" . "&absender=$sender_id" . "&text=$message";

                    $get_sms_status = file_get_contents($sms_sent_to_user);

                    $get_sms_status = str_replace("100", "Success", $get_sms_status);
                    $get_sms_status = str_replace("101", "Success", $get_sms_status);
                    $get_sms_status = str_replace("111", "What IP blocked", $get_sms_status);
                    $get_sms_status = str_replace("112", "Incorrect login data", $get_sms_status);
                    $get_sms_status = str_replace("120", "Sender field is empty", $get_sms_status);
                    $get_sms_status = str_replace("121", "Gateway field is empty", $get_sms_status);
                    $get_sms_status = str_replace("122", "Text is empty", $get_sms_status);
                    $get_sms_status = str_replace("123", "Recipient field is empty", $get_sms_status);
                    $get_sms_status = str_replace("129", "Wrong sender", $get_sms_status);
                    $get_sms_status = str_replace("130", "Gateway Error", $get_sms_status);
                    $get_sms_status = str_replace("131", "Wrong number", $get_sms_status);
                    $get_sms_status = str_replace("132", "Mobile phone is off", $get_sms_status);
                    $get_sms_status = str_replace("133", "Query not possible", $get_sms_status);
                    $get_sms_status = str_replace("134", "Number invalid", $get_sms_status);
                    $get_sms_status = str_replace("140", "No credit", $get_sms_status);
                    $get_sms_status = str_replace("150", "SMS blocked", $get_sms_status);
                    $get_sms_status = str_replace("170", "Date wrong", $get_sms_status);
                    $get_sms_status = str_replace("171", "Date too old", $get_sms_status);
                    $get_sms_status = str_replace("172", "Too many numbers", $get_sms_status);
                    $get_sms_status = str_replace("173", "Format wrong", $get_sms_status);
                    $get_sms_status = str_replace(",", " ", $get_sms_status);
                    break;

                case 'Route SMS':

                    $sender_id = urlencode($this->sender_id);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {

                        if ($msg_type == 'unicode') {
                            $type    = 2;
                            $message = $this->sms_unicode($this->message);
                        } else {
                            $type    = 0;
                            $message = urlencode($this->message);
                        }

                        $sms_sent_to_user = "$sms_url" . "/bulksms/bulksms?type=$type" . "&username=$gateway_user_name" . "&password=$gateway_password" . "&destination=$this->cl_phone" . "&source=$sender_id" . "&message=$message" . "&dlr=0";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $headers   = array();
                        $headers[] = "Content-Type: application/x-www-form-urlencoded";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {

                            $get_data = explode('|', $get_sms_status);

                            if (is_array($get_data) && array_key_exists('0', $get_data)) {
                                switch ($get_data[0]) {
                                    case '1701':
                                        $get_sms_status = 'Success';
                                        break;

                                    case '1702':
                                        $get_sms_status = 'Invalid URL';
                                        break;

                                    case '1703':
                                        $get_sms_status = 'Invalid User or Password';
                                        break;

                                    case '1704':
                                        $get_sms_status = 'Invalid Type';
                                        break;

                                    case '1705':
                                        $get_sms_status = 'Invalid SMS';
                                        break;

                                    case '1706':
                                        $get_sms_status = 'Invalid receiver';
                                        break;

                                    case '1707':
                                        $get_sms_status = 'Invalid sender';
                                        break;

                                    case '1709':
                                        $get_sms_status = 'User Validation Failed';
                                        break;

                                    case '1710':
                                        $get_sms_status = 'Internal Error';
                                        break;

                                    case '1715':
                                        $get_sms_status = 'Response Timeout';
                                        break;

                                    case '1025':
                                        $get_sms_status = 'Insufficient Credit';
                                        break;

                                    default:
                                        $get_sms_status = 'Invalid request';
                                        break;

                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'SMSGlobal':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    $sms_sent_to_user = $gateway_url . "?action=sendsms" . "&user=$gateway_user_name" . "&password=$gateway_password" . "&from=$sender_id" . "&to=$clphone" . "&text=$message";

                    if ($this->msgcount > 1) {
                        $sms_sent_to_user .= '&maxsplit=9';
                    }

                    try {
                        $get_sms_status = file_get_contents($sms_sent_to_user);

                        if (substr_count($get_sms_status, 'OK')) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = str_replace('ERROR:', '', $get_sms_status);
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Nexmo':

                    if (is_numeric($this->cl_phone)) {
                        $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    } else {
                        $clphone = $this->cl_phone;
                    }

                    if (is_numeric($this->sender_id)) {
                        $sender_id = str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);
                    } else {
                        $sender_id = $this->sender_id;
                    }


                    try {

                        $client = new \Nexmo\Client(new Basic($gateway_user_name, $gateway_password));

                        if ($msg_type == 'unicode') {
                            $type = 'unicode';
                        } else {
                            $type = 'text';
                        }

                        $sms_data = [
                            'to' => $clphone,
                            'from' => $sender_id,
                            'text' => $this->message,
                            'type' => $type
                        ];

                        $response = $client->message()->send($sms_data);

                        if ($response['status'] == 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = 'Unknown Error';
                        }

                    } catch (Exception $exception) {
                        $get_sms_status = $exception->getMessage();
                    }

                    break;

                case 'Kapow':

                    $posturl = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&mobile=$this->cl_phone" . "&sms=$this->message";

                    if ($this - $this->sender_id != '') {
                        $posturl .= '&from_id=' . urlencode($this->sender_id);
                    }

                    $handle = fopen($posturl, 'r');
                    if ($handle) {
                        $response = stream_get_contents($handle);

                        if (strstr($response, 'OK')) {
                            $get_sms_status = "Success";
                        }
                        if ($response == 'USERPASS') {
                            $get_sms_status = "Your credentials are incorrect";
                        }

                        if ($response == 'ERROR') {
                            $get_sms_status = "Error";
                        }
                        if ($response == 'NOCREDIT') {
                            $get_sms_status = "You have no credits remaining";
                        }
                    } else {
                        $get_sms_status = 'Unable to open URL';
                    }

                    break;

                case 'Zang':

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.zang.io/v2/Accounts/{$gateway_user_name}/SMS/Messages.json");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "To=$this->cl_phone&From=$this->sender_id&Body=$this->message");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_USERPWD, "{$gateway_user_name}" . ":" . "{$gateway_password}");

                    $headers   = array();
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $decoded_response = json_decode($result, true);
                    if (array_key_exists('message', $decoded_response)) {
                        $get_sms_status = $decoded_response['message'];
                    } elseif (array_key_exists('sid', $decoded_response)) {
                        $get_sms_status = 'Success|' . $decoded_response['sid'];
                    } else {
                        $get_sms_status = 'Api info not correct';
                    }

                    break;

                case 'InfoBip':

                    $message_id = _raid(19);

                    // creating an object for sending SMS
                    $destination = array("messageId" => $message_id, "to" => $this->cl_phone);
                    $message     = array(
                        "from" => $this->sender_id,
                        "destinations" => array($destination),
                        "text" => $this->message,
                        'notifyUrl' => url('sms/delivery-report-infobip'),
                        'notifyContentType' => 'application/json'
                    );
                    $postData    = array("messages" => array($message));
                    // encoding object
                    $postDataJson = json_encode($postData);


                    $ch     = curl_init();
                    $header = array("Content-Type:application/json", "Accept:application/json");

                    // setting options
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":" . $gateway_password);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);

                    // response of the POST request
                    $response = curl_exec($ch);
                    $get_data = json_decode($response, true);
                    curl_close($ch);

                    if (is_array($get_data)) {
                        if (array_key_exists('messages', $get_data)) {
                            foreach ($get_data['messages'] as $msg) {
                                $get_sms_status = $msg['status']['groupName'] . '|' . $msg['messageId'];
                            }
                        } elseif (array_key_exists('requestError', $get_data)) {
                            foreach ($get_data['requestError'] as $msg) {
                                $get_sms_status = $msg['messageId'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;

                case 'RANNH':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);

                    $sms_sent_to_user = $gateway_url . "?user=$gateway_user_name" . "&password=$gateway_password" . "&numbers=$clphone" . "&sender=$sender_id" . "&message=" . urlencode($this->message) . "&lang=en";

                    $get_sms_status = file_get_contents($sms_sent_to_user);

                    if ($get_sms_status == '1') {
                        $get_sms_status = 'Success';
                    } elseif ($get_sms_status == '0') {
                        $get_sms_status = 'Transmission error';
                    } else {
                    }

                    break;

                case 'Bulk SMS':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);


                    $url = $gateway_url . "/submission/send_sms/2/2.0?username=$gateway_user_name" . "&password=$gateway_password" . "&msisdn=$clphone" . "&repliable=1";
                    if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                        $url .= "&message=" . bin2hex(mb_convert_encoding($this->message, "UTF-16", "UTF-8"));
                        $url .= "&dca=16bit";
                    } else {
                        $url .= "&message=" . urlencode($this->message);
                    }

                    if ($sender_id != '') {
                        $url .= "&sender=$sender_id";
                    }

                    $ret = file_get_contents($url);

                    $send = explode("|", $ret);

                    if ($send[0] == '0') {
                        $get_sms_status = 'Success|' . $send['2'];
                    } elseif ($send[0] == '1') {
                        $get_sms_status = 'Scheduled';
                    } elseif ($send[0] == '22') {
                        $get_sms_status = 'Internal fatal error ';
                    } elseif ($send[0] == '23') {
                        $get_sms_status = 'Authentication failure';
                    } elseif ($send[0] == '24') {
                        $get_sms_status = 'Please choose sender or repliable, but not both';
                    } elseif ($send[0] == '25') {
                        $get_sms_status = 'You do not have sufficient credits';
                    } elseif ($send[0] == '26') {
                        $get_sms_status = 'Upstream credits not available';
                    } elseif ($send[0] == '27') {
                        $get_sms_status = 'You have exceeded your daily quota';
                    } elseif ($send[0] == '28') {
                        $get_sms_status = 'Upstream quota exceeded';
                    } elseif ($send[0] == '40') {
                        $get_sms_status = 'Temporarily unavailable';
                    } elseif ($send[0] == '201') {
                        $get_sms_status = 'Maximum batch size exceeded';
                    } elseif ($send[0] == '200') {
                        $get_sms_status = 'Success';
                    } else {
                        $get_sms_status = 'Failed';
                    }


                    break;

                /*Verson 1.1*/

                case 'Plivo':

                    if (is_numeric($this->cl_phone)) {
                        $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    } else {
                        $clphone = $this->cl_phone;
                    }

                    if (is_numeric($this->sender_id)) {
                        $sender_id = str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);
                    } else {
                        $sender_id = $this->sender_id;
                    }

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "https://api.plivo.com/v1/Account/$gateway_user_name/Message/");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"src\": \"$sender_id\",\"dst\": \"$clphone\", \"text\": \"$this->message\"}");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_USERPWD, "$gateway_user_name" . ":" . "$gateway_password");

                    $headers   = array();
                    $headers[] = "Content-Type: application/json";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $response = json_decode($result, true);

                    if (json_last_error() == JSON_ERROR_NONE) {
                        if (isset($response) && is_array($response) && array_key_exists('message', $response)) {
                            if (substr_count($response['message'], 'queued')) {
                                $get_sms_status = 'Success|' . $response['message_uuid'][0];
                            } else {
                                $get_sms_status = $response['message'];
                            }
                        } elseif (isset($response) && is_array($response) && array_key_exists('error', $response)) {
                            $get_sms_status = $response['error'];
                        } else {
                            $get_sms_status = trim($result);
                        }
                    } else {
                        $get_sms_status = trim($result);
                    }

                    break;

                case 'PlivoPowerpack':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "https://api.plivo.com/v1/Account/$gateway_user_name/Message/");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"powerpack_uuid\": \"$this->sender_id\",\"dst\": \"$clphone\", \"text\": \"$this->message\"}");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_USERPWD, "$gateway_user_name" . ":" . "$gateway_password");

                    $headers   = array();
                    $headers[] = "Content-Type: application/json";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $response = json_decode($result, true);

                    if (json_last_error() == JSON_ERROR_NONE) {
                        if (isset($response) && is_array($response) && array_key_exists('message', $response)) {
                            if (substr_count($response['message'], 'queued')) {
                                $get_sms_status = 'Success|' . $response['message_uuid'][0];
                            } else {
                                $get_sms_status = $response['message'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        $get_sms_status = trim($result);
                    }

                    break;

                case 'SMSIndiaHub':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $message = urlencode($this->message);

                    $ch = curl_init("$gateway_url?user=" . $gateway_user_name . "&password=" . $gateway_password . "&msisdn=" . $clphone . "&sid=" . $this->sender_id . "&msg=" . $message . "&fl=0");
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);
                    curl_close($ch);

                    $response       = json_decode($output);
                    $get_sms_status = $response->ErrorMessage;

                    break;

                case 'Text Local':

                    $apiKey  = urlencode($gateway_user_name);
                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sender  = urlencode($this->sender_id);
                    $message = rawurlencode($this->message);

                    // Prepare data for POST request
                    $data = array('apikey' => $apiKey, 'numbers' => $clphone, "sender" => $sender);

                    if ($msg_type == 'unicode') {
                        $data['unicode'] = true;
                        $message         = $this->sms_unicode($this->message);
                    }

                    $data['message'] = $message;

                    // Send the POST request with cURL
                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    $err      = curl_error($ch);
                    curl_close($ch);

                    if ($err) {
                        $get_sms_status = $err;
                    } else {
                        $get_data = json_decode($response, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('status', $get_data)) {
                            if ($get_data['status'] == 'failure') {
                                foreach ($get_data['errors'] as $err) {
                                    $get_sms_status = $err['message'];
                                }
                            } elseif ($get_data['status'] == 'success') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response;
                            }

                        } else {
                            $get_sms_status = $response;
                        }
                    }
                    break;

                case 'Top10sms':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);

                    $sms_sent_to_user = $gateway_url . "?action=compose" . "&username=$gateway_user_name" . "&api_key=$gateway_password" . "&to=$clphone" . "&sender=$sender_id" . "&message=" . urlencode($this->message) . "&unicode=1";

                    $get_sms_status = file_get_contents($sms_sent_to_user);
                    $get_sms_status = trim(substr($get_sms_status, 0, strpos($get_sms_status, ":")));


                    break;

                case 'msg91':
                case 'Freekasms':
                    $sender_id    = urlencode($this->sender_id);
                    $message      = urlencode($this->message);
                    $client_phone = ltrim($this->cl_phone, $gateway_extra);

                    //Define route
                    $route = $gateway_password;

                    //Prepare you post parameters
                    $postData = array(
                        'authkey' => $gateway_user_name,
                        'mobiles' => $client_phone,
                        'country' => $gateway_extra,
                        'message' => $message,
                        'sender' => $sender_id,
                        'route' => $route,
                        'response' => 'json',
                    );

                    if ($msg_type == 'unicode') {
                        $postData['unicode'] = 1;
                    }

                    // init the resource
                    $ch = curl_init();
                    curl_setopt_array($ch, array(
                        CURLOPT_URL => $gateway_url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $postData
                    ));


                    //Ignore SSL certificate verification
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                    //get response
                    $output = curl_exec($ch);

                    //Print error if any
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    curl_close($ch);

                    $get_data = json_decode($output, true);

                    if (isset($get_data) && is_array($get_data) && array_key_exists('type', $get_data)) {
                        if ($get_data['type'] == 'success') {
                            $get_sms_status = 'Success';
                        } elseif ($get_data['type'] == 'error') {
                            $get_sms_status = $get_data['message'];
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } else {
                        $get_sms_status = 'failed';
                    }

                    break;

                case 'ShreeWeb':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    $ch = curl_init("$gateway_url?username=" . $gateway_user_name . "&password=" . $gateway_password . "&mobile=" . $clphone . "&sender=" . $sender_id . "&message=" . $message . "&type=TEXT");
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);
                    curl_close($ch);

                    $output = trim($output);

                    if ($output != '') {
                        if (strpos($output, 'SUBMIT_SUCCESS') !== false) {
                            $get_sms_status = 'Success';
                        } elseif ($output == 'ERR_PARAMETER') {
                            $get_sms_status = 'Invalid  parameter';
                        } elseif ($output == 'ERR_MOBILE') {
                            $get_sms_status = 'Invalid  Phone Number';
                        } elseif ($output == 'ERR_SENDER') {
                            $get_sms_status = 'Invalid  Sender';
                        } elseif ($output == 'ERR_MESSAGE_TYPE') {
                            $get_sms_status = 'Invalid  Message Type';
                        } elseif ($output == 'ERR_MESSAGE') {
                            $get_sms_status = 'Invalid  Message';
                        } elseif ($output == 'ERR_SPAM') {
                            $get_sms_status = 'Spam  Message';
                        } elseif ($output == 'ERR_DLR') {
                            $get_sms_status = 'Dlr requisition is invalid.';
                        } elseif ($output == 'ERR_USERNAME') {
                            $get_sms_status = 'Invalid Username';
                        } elseif ($output == 'ERR_PASSWORD') {
                            $get_sms_status = 'Invalid Password';
                        } elseif ($output == 'ERR_LOGIN') {
                            $get_sms_status = 'Invalid Login Access';
                        } elseif ($output == 'ERR_CREDIT') {
                            $get_sms_status = 'Insufficient Balance';
                        } elseif ($output == 'ERR_DATETIME') {
                            $get_sms_status = 'Invalid Time format';
                        } elseif ($output == 'ERR_GMT') {
                            $get_sms_status = 'Invalid GMT';
                        } elseif ($output == 'ERR_ROUTING') {
                            $get_sms_status = 'Invalid Routing';
                        } elseif ($output == 'ERR_INTERNAL') {
                            $get_sms_status = 'Server Down For Maintenance';
                        } else {
                            $get_sms_status = 'Unknown Error';
                        }
                    } else {
                        $get_sms_status = 'Unknown Error';
                    }

                    break;


                case 'SmsGatewayMe':

                    include_once app_path('libraray/smsgatewayme/autoload.php');

                    $config = Configuration::getDefaultConfiguration();
                    $config->setApiKey('Authorization', $gateway_user_name);

                    $apiClient     = new ApiClient($config);
                    $messageClient = new MessageApi($apiClient);

                    // Sending a SMS Message
                    $sendMessageRequest1 = new SendMessageRequest([
                        'phoneNumber' => $this->cl_phone,
                        'message' => $this->message,
                        'deviceId' => $gateway_extra
                    ]);

                    try {
                        $response = $messageClient->sendMessages([
                            $sendMessageRequest1
                        ]);

                        if (is_array($response)) {
                            if (array_key_exists('0', $response)) {
                                $get_sms_status = 'Success|' . $response[0]->getId();
                            } else {
                                $get_sms_status = 'Invalid request';
                            }

                        } else {
                            $get_sms_status = 'Unknown Error';
                        }

                        $get_sms_status = trim($get_sms_status);
                    } catch (ApiException $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Elibom':
                    require_once(app_path('libraray/elibom/src/elibom_client.php'));
                    $elbom = new ElibomClient($gateway_user_name, $gateway_password);
                    try {
                        $get_sms_status = 'Success|' . $elbom->sendMessage($this->cl_phone, $this->message);
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'Hablame':
                    $gateway_url = rtrim($gateway_url, '/');
                    $gateway_url = $gateway_url . '/';
                    $clphone     = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone     = str_replace('+', '', $clphone);
                    $data        = array(
                        'cliente' => $gateway_user_name, //Numero de cliente
                        'api' => $gateway_password, //Clave API suministrada
                        'numero' => $clphone, //numero o numeros telefonicos a enviar el SMS (separados por una coma ,)
                        'sms' => $this->message, //Mensaje de texto a enviar
                    );

                    $options = array(
                        'http' => array(
                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method' => 'POST',
                            'content' => http_build_query($data)
                        )
                    );
                    $context = stream_context_create($options);
                    $result  = json_decode((file_get_contents($gateway_url, false, $context)), true);

                    if (is_array($result) && array_key_exists('resultado', $result)) {
                        if ($result["resultado"] === 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $result['resultado_t'];
                        }
                    } else {
                        $get_sms_status = 'ha ocurrido un error';
                    }

                    break;

                case 'Wavecell':

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "https://api.wavecell.com/sms/v1/$gateway_user_name/single");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "{ \"source\":\"$this->sender_id\", \"destination\":\"$this->cl_phone\", \"text\":\"$this->message\", \"encoding\":\"AUTO\" }");
                    curl_setopt($ch, CURLOPT_POST, 1);

                    $headers   = array();
                    $headers[] = "Authorization: Bearer $gateway_password";
                    $headers[] = "Content-Type: application/json";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    curl_close($ch);
                    $get_data = json_decode($result, true);

                    if (is_array($get_data) && array_key_exists('umid', $get_data)) {
                        $get_sms_status = 'Success|' . $get_data['umid'];
                    } else {
                        $get_sms_status = 'Failed';
                    }


                    break;

                case 'SIPTraffic':
                    $clphone   = "+" . str_replace(" ", "", $this->cl_phone);
                    $sender_id = "+" . $this->sender_id;

                    $sms_sent_to_user = $gateway_url . "/myaccount/sendsms.php?username=$gateway_user_name" . "&password=$gateway_password" . "&to=$clphone" . "&from=$sender_id" . "&text=" . urlencode($this->message);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $get_sms_status = curl_exec($ch);
                    curl_close($ch);

                    $xml   = simplexml_load_string($get_sms_status, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json  = json_encode($xml);
                    $array = json_decode($json, TRUE);

                    if (is_array($array) && array_key_exists('resultstring', $array)) {
                        $get_sms_status = $array['resultstring'];
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;

                case 'SMSMKT':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    if ($msg_type == 'unicode') {
                        $message = urlencode(iconv("UTF-8", "TIS-620", $this->message));
                    } else {
                        $message = urlencode($this->message);
                    }

                    $Parameter = "User=$gateway_user_name&Password=$gateway_password&Msnlist=$clphone&Msg=$message&Sender=$this->sender_id";
                    $ch        = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $Parameter);

                    $response = curl_exec($ch);
                    curl_close($ch);

                    $response = explode(',', $response);
                    $status   = explode('=', $response[0])[1];

                    if ($status == '0') {
                        $get_sms_status = 'Success';
                    } else {

                        $details        = explode('=', $response['1']);
                        $get_sms_status = $details['1'];
                    }
                    break;


                case 'MLat':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);
                    // mensajes a enviar
                    $texts = array($this->message);

                    // números correspondientes pilas formato regexp ^04(12|16|26|14|24)\d{7}$
                    $recipients = array($clphone);

                    try {

                        $mlat           = new \SoapClient($gateway_url . '?wsdl',
                            array('location' => 'https://m-lat.net/axis2/services/SMSServiceWS?wsdl'));
                        $credential     = array('user' => $gateway_user_name, 'password' => $gateway_password);
                        $get_sms_status = $mlat->sendManyTextSMS(array('credential' => $credential, 'text' => $texts, 'recipients' => $recipients));
                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }
                    break;

                case 'NRSGateway':
                    $clphone          = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone          = str_replace('+', '', $clphone);
                    $sender_id        = $this->sender_id;
                    $message          = urlencode($this->message);
                    $gateway_password = urlencode($gateway_password);

                    $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&to=$clphone" . "&from=$sender_id" . "&text=" . urlencode($this->message) . "&coding=0&dlr-mask=8";

                    $response = file_get_contents($sms_sent_to_user);
                    $result   = explode(':', trim($response));

                    if (is_array($result)) {
                        if (array_key_exists('1', $result) && $result['0'] == '0') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = trim($result['1']);
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;

                case 'Asterisk':
                    Artisan::call('ami:dongle:sms', [
                        'number' => $this->cl_phone,
                        'message' => $this->message,
                        'device' => env('SC_DEVICE'),
                    ]);

                    $get_sms_status = Artisan::output();

                    if (strpos($get_sms_status, 'queued') !== false) {
                        $get_sms_status = 'Success';
                    }

                    break;

                case 'Orange':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $sender_id = str_replace(" ", "", $this->sender_id); #Remove any whitespace
                    $sender_id = str_replace('+', '', $sender_id);

                    $config = array(
                        'clientId' => $gateway_user_name,
                        'clientSecret' => $gateway_password
                    );

                    $osms = new Osms($config);
                    $osms->setVerifyPeerSSL(false);
                    $response = $osms->getTokenFromConsumerKey();


                    if (!empty($response['access_token'])) {
                        $senderAddress   = 'tel:+' . $sender_id;
                        $receiverAddress = 'tel:+' . $clphone;
                        $message         = $this->message;

                        $get_data = $osms->sendSMS($senderAddress, $receiverAddress, $message);

                        if (empty($get_data['error'])) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data['error'];
                        }

                    } else {
                        $get_sms_status = $response['error'];
                    }

                    break;

                case 'GlobexCam':
                    $clphone          = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone          = str_replace('+', '', $clphone);
                    $sender_id        = $this->sender_id;
                    $gateway_password = urlencode($gateway_password);

                    $sms_sent_to_user = $gateway_url . "?user=$gateway_user_name" . "&password=$gateway_password" . "&APIKey=$gateway_extra" . "&number=$clphone" . "&senderid=$sender_id" . "&text=" . urlencode($this->message) . "&channel=Normal&DCS=0&flashsms=0";

                    $response = file_get_contents($sms_sent_to_user);
                    $response = json_decode($response, true);

                    if (is_array($response) && array_key_exists('ErrorMessage', $response)) {
                        if ($response['ErrorMessage'] == 'Done') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $response['ErrorMessage'];
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;

                case 'Camoo':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = $this->sender_id;

                    $sms_sent_to_user = $gateway_url . "?api_key=$gateway_user_name" . "&api_secret=$gateway_password" . "&to=$clphone" . "&from=$sender_id" . "&message=" . urlencode($this->message);

                    $response = file_get_contents($sms_sent_to_user);
                    $response = json_decode($response, true);

                    if (is_array($response) && array_key_exists('_message', $response)) {
                        if ($response['_message'] == 'succes') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $response['_message'];
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;

                case 'Kannel':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = $this->sender_id;

                    try {
                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&to=$clphone" . "&from=$sender_id" . "&text=" . urlencode($this->message);

                        $response = file_get_contents($sms_sent_to_user);

                        if (strpos($response, 'delivery') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Semysms':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = $this->sender_id;

                    $data = array(
                        "phone" => $clphone,
                        "msg" => $this->message,
                        "device" => $gateway_password,
                        "token" => $gateway_user_name
                    );

                    try {
                        $curl = curl_init($gateway_url);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                        $response = curl_exec($curl);
                        curl_close($curl);

                        $response = json_decode($response, true);

                        if (isset($response) && is_array($response) && array_key_exists('code', $response)) {
                            if ($response['code'] == 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['error'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;


                case 'Smsvitrini':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);
                    $message = urlencode($this->message);


                    $Parameter = "islem=1&user=$gateway_user_name&pass=$gateway_password&numaralar=$clphone&mesaj=$message&baslik=$this->sender_id";
                    $ch        = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $Parameter);

                    $response = curl_exec($ch);
                    curl_close($ch);

                    $result = explode('|', $response);

                    if (is_array($result)) {

                        if (array_key_exists('1', $result)) {
                            if (strpos($result['1'], 'HATA') !== false) {
                                $get_sms_status = $result['2'];
                            } elseif (strpos($result['1'], 'OK') !== false) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = 'Bilinmeyen hata';
                            }
                        } else {
                            $get_sms_status = $result['2'];
                        }

                    } else {
                        $get_sms_status = 'Bilinmeyen hata';
                    }

                    break;

                case 'Semaphore':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $ch         = curl_init();
                    $parameters = array(
                        'apikey' => $gateway_user_name, //Your API KEY
                        'number' => $clphone,
                        'message' => $this->message,
                        'sendername' => $this->sender_id
                    );
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_POST, 1);

                    //Send the parameters set above with the request
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

                    // Receive response from server
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec($ch);
                    curl_close($ch);

                    $response = json_decode($output, true);

                    //Show the server response
                    if (is_array($response)) {
                        foreach ($response as $value) {
                            $get_sms_status = $value[0];
                        }

                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;

                case 'Itexmo':

                    $ch     = curl_init();
                    $itexmo = array('1' => $this->cl_phone, '2' => $this->message, '3' => $gateway_user_name);
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                        http_build_query($itexmo));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    if ($response == 0) {
                        $get_sms_status = 'Success';
                    } elseif ($response == '') {
                        $get_sms_status = 'No response from server';
                    } else {
                        $get_sms_status = "Error Num " . $response . " was encountered!";
                    }

                    break;

                case 'Chikka':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $arr_post_body = array(
                        "message_type" => "SEND",
                        "mobile_number" => $clphone,
                        "shortcode" => $this->sender_id,
                        "message_id" => _raid(32),
                        "message" => urlencode($this->message),
                        "client_id" => $gateway_user_name,
                        "secret_key" => $gateway_password
                    );

                    $query_string = "";
                    foreach ($arr_post_body as $key => $frow) {
                        $query_string .= '&' . $key . '=' . $frow;
                    }

                    $curl_handler = curl_init();
                    curl_setopt($curl_handler, CURLOPT_URL, $gateway_url);
                    curl_setopt($curl_handler, CURLOPT_POST, count($arr_post_body));
                    curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $query_string);
                    curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, TRUE);
                    $response = curl_exec($curl_handler);
                    curl_close($curl_handler);

                    $response = json_decode($response, true);

                    if ($response['status'] == '200') {
                        $get_sms_status = 'Success';
                    } else {
                        $get_sms_status = $response['message'];
                    }

                    break;

                case '1s2u':

                    $arrContextOptions = array(
                        "ssl" => array(
                            "verify_peer" => false,
                            "verify_peer_name" => false,
                        ),
                    );


                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $message = $this->message;

                    if ($this->api_key != '') {
                        $message = urldecode($message);
                    }

                    if ($msg_type == 'unicode') {
                        $mt      = 1;
                        $message = bin2hex(mb_convert_encoding($this->message, "UTF-16", "UTF-8"));
                    } else {
                        $mt = 0;
                    }

                    $parameters = [
                        "username" => $gateway_user_name,
                        "password" => $gateway_password,
                        "mno" => $clphone,
                        "msg" => $message,
                        "sid" => $this->sender_id,
                        "mt" => $mt,
                        "fl" => 0
                    ];

                    $gateway_url = $gateway_url . '?' . http_build_query($parameters);

                    try {
                        $get_sms_status = file_get_contents($gateway_url, false, stream_context_create($arrContextOptions));
                        if (strpos($get_sms_status, 'OK') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            switch ($get_sms_status) {
                                case '0000':
                                    $get_sms_status = 'Message not sent';
                                    break;
                                case '0005':
                                    $get_sms_status = 'Invalid Sender';
                                    break;
                                case '0010':
                                    $get_sms_status = 'Username not provided';
                                    break;
                                case '0011':
                                    $get_sms_status = 'Password not provided';
                                    break;
                                case '00':
                                    $get_sms_status = 'Invalid username/password';
                                    break;
                                case '0020':
                                    $get_sms_status = 'Insufficient Credits';
                                    break;
                                case '0030':
                                    $get_sms_status = 'Invalid Sender ID';
                                    break;
                                case '0040':
                                    $get_sms_status = 'Mobile number not provided';
                                    break;
                                case '0041':
                                    $get_sms_status = 'Invalid mobile number';
                                    break;
                                case '0066':
                                case '0042':
                                    $get_sms_status = 'Network not supported';
                                    break;
                                case '0050':
                                    $get_sms_status = 'Invalid message';
                                    break;
                                case '0060':
                                    $get_sms_status = 'Invalid quantity specified';
                                    break;
                                default:
                                    $get_sms_status = 'Unknown Error';
                                    break;
                            }

                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;

                case 'Kaudal':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);
                    $message = urlencode($this->message);


                    $Parameter = "user=$gateway_user_name&password=$gateway_password&receive=$clphone&sms=$message&sender=$this->sender_id";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $Parameter);

                    $response = curl_exec($ch);
                    curl_close($ch);

                    $error_data = $response;

                    $response = filter_var($response, FILTER_SANITIZE_NUMBER_INT);
                    $response = str_replace('-', '', $response);


                    if ($response == '0') {
                        $get_sms_status = 'Success';
                    } else {
                        $details        = explode('-', $error_data);
                        $get_sms_status = trim(strip_tags($details['2']));
                    }
                    break;

                case 'CMSMS':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);
                    $clphone = '+' . $clphone;

                    $xml = new \SimpleXMLElement('<MESSAGES/>');

                    $authentication = $xml->addChild('AUTHENTICATION');
                    $authentication->addChild('PRODUCTTOKEN', $gateway_user_name);

                    $msg = $xml->addChild('MSG');
                    $msg->addChild('FROM', $this->sender_id);
                    $msg->addChild('TO', $clphone);

                    $msg->addChild('MINIMUMNUMBEROFMESSAGEPARTS', 1);
                    $msg->addChild('MAXIMUMNUMBEROFMESSAGEPARTS', 8);

                    $msg->addChild('BODY', $this->message);

                    if ($msg_type == 'unicode') {
                        $msg->addChild('DCS', 8);
                    }


                    $sms_data = $xml->asXML();


                    $ch = curl_init(); // cURL v7.18.1+ and OpenSSL 0.9.8j+ are required
                    curl_setopt_array($ch, array(
                            CURLOPT_URL => $gateway_url,
                            CURLOPT_HTTPHEADER => array('Content-Type: application/xml'),
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $sms_data,
                            CURLOPT_RETURNTRANSFER => true
                        )
                    );

                    $response = curl_exec($ch);

                    curl_close($ch);

                    if (strpos($response, 'OK') !== false || $response == "") {
                        $get_sms_status = 'Success';
                    } else {

                        $status = explode(':', $response);

                        if (array_key_exists('1', $status)) {
                            $get_sms_status = $status['1'];
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    }

                    break;
                case 'SendOut':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"api_id\": \"$gateway_user_name\",\"api_token\": \"$gateway_password\" ,\"debug\": \"true\",\"to\": [\"$clphone\"],\"sms\": \"$this->message\"} ");
                    curl_setopt($ch, CURLOPT_POST, 1);

                    $headers   = array();
                    $headers[] = "Content-Type: application/json";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $get_sms_status = 'Unknown Error';
                    $result         = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    curl_close($ch);

                    if ($result) {
                        $get_result = json_decode($result);
                        if (array_key_exists('status', $get_result)) {
                            $get_sms_status = $get_result['status'];
                        }
                    }

                    break;


                case 'ViralThrob':

                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {
                        $sms_sent_to_user = "$sms_url" . "?api_access_token=$gateway_user_name" . "&number=$this->cl_phone" . "&mask=$sender_id" . "&message=$message" . "&saas_account=" . $gateway_password;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_status = curl_exec($ch);
                        curl_close($ch);

                        $get_status     = json_decode($get_status);
                        $get_sms_status = $get_status->message;

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Masterksnetworks':

                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {
                        $sms_sent_to_user = "$sms_url" . "?username=$gateway_user_name" . "&mobile=$this->cl_phone" . "&sender=$sender_id" . "&message=$message" . "&password=" . $gateway_password . "&type=TEXT";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'MessageBird':
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "recipients=$this->cl_phone&originator=$this->sender_id&body=$this->message&datacoding=auto");
                    curl_setopt($ch, CURLOPT_POST, 1);

                    $headers   = array();
                    $headers[] = "Authorization: AccessKey $gateway_user_name";
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $response = json_decode($result, true);

                    if (is_array($response) && array_key_exists('id', $response)) {
                        $get_sms_status = 'Success|' . $response['id'];
                    } elseif (is_array($response) && array_key_exists('errors', $response)) {
                        $get_sms_status = $response['errors'][0]['description'];
                    } else {
                        $get_sms_status = 'Unknown Error';
                    }

                    break;


                case 'FortDigital':
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {
                        $sms_sent_to_user = "$sms_url" . "?username=$gateway_user_name" . "&to=$this->cl_phone" . "&from=$sender_id" . "&message=$message" . "&password=" . $gateway_password;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($get_sms_status, 'OK') !== false) {
                            $get_sms_status = 'Success';
                        } elseif (strpos($get_sms_status, 'ERROR:304') !== false) {
                            $get_sms_status = 'Authentication failed';
                        } elseif (strpos($get_sms_status, 'ERROR:000') !== false) {
                            $get_sms_status = 'Credit Balance Not Enough';
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'SMSPRO':
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    $sms_url   = rtrim($gateway_url, '/');
                    $defDate   = date('Ymdhis', time());

                    try {
                        $sms_sent_to_user = "$sms_url" . "?customerID=$gateway_extra" . "&userName=$gateway_user_name" . "&userPassword=$gateway_password" . "&originator=$sender_id&messageType=Latin" . "&defDate=$defDate&blink=false&flash=false&private=true" . "&smsText=$message" . "&recipientPhone=$this->cl_phone";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        $xml   = simplexml_load_string($get_sms_status, "SimpleXMLElement", LIBXML_NOCDATA);
                        $json  = json_encode($xml);
                        $array = json_decode($json, TRUE);

                        if (array_key_exists('Result', $array)) {
                            $get_response = $array['Result'];
                            if ($get_response = 'OK') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = preg_replace('/\D/', '', $get_response);
                            }


                        } else {
                            $get_sms_status = 'Unknown Error';
                        }


                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                        $get_sms_status = preg_replace('/\D/', '', $get_sms_status);
                    }


                    break;


                case 'CNIDCOM':
                    $message = urlencode($this->message);
                    $sms_url = rtrim($gateway_url, '/');

                    try {

                        $sms_sent_to_user = "$sms_url" . "?api_key=$gateway_user_name" . "&numero=$this->cl_phone" . "&remitente=$this->sender_id" . "&texto=$message" . "&api_secret=" . $gateway_password;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);


                        $get_data = json_decode($get_sms_status, true);


                        if (json_last_error() == JSON_ERROR_NONE) {
                            if (array_key_exists('Saldo', $get_data)) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = trim($get_sms_status);
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'VoiceTrading':
                    $clphone   = "+" . str_replace(" ", "", $this->cl_phone);
                    $sender_id = "+" . $this->sender_id;

                    $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&to=$clphone" . "&from=$sender_id" . "&text=" . urlencode($this->message);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $get_sms_status = curl_exec($ch);
                    curl_close($ch);

                    $xml   = simplexml_load_string($get_sms_status, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json  = json_encode($xml);
                    $array = json_decode($json, TRUE);
                    if (isset($array) && is_array($array) && array_key_exists('resultstring', $array)) {
                        $get_sms_status = $array['resultstring'];
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;


                case 'Dialog':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);


                    if (strlen($clphone) > 11) {
                        $client_phone = ltrim($clphone, '94');
                    } else {
                        $client_phone = $clphone;
                    }

                    $message   = urlencode($this->message);
                    $sms_url   = rtrim($gateway_url, '/');
                    $sender_id = urlencode($this->sender_id);
                    $msgcount  = strlen(preg_replace('/\s+/', ' ', trim($message)));

                    if ($msgcount < 161) {
                        $api_key = $gateway_user_name;
                    } else {
                        $api_key = $gateway_password;
                    }

                    try {

                        $sms_sent_to_user = "$sms_url" . "?q=$api_key" . "&from=$sender_id" . "&destination=$client_phone" . "&message=" . $message;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if ($get_sms_status == 0) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'AmazonSNS':

                    $params = array(
                        'credentials' => array(
                            'key' => $gateway_user_name,
                            'secret' => $gateway_password,
                        ),
                        'region' => $gateway_extra, // < your aws from SNS Topic region
                        'version' => 'latest'
                    );

                    $sns = new SnsClient($params);


                    $args = array(
                        'MessageAttributes' => [
                            'AWS.SNS.SMS.SenderID' => [
                                'DataType' => 'String',
                                'StringValue' => $this->sender_id
                            ]
                        ],
                        "SMSType" => "Transational",
                        "PhoneNumber" => '+' . $this->cl_phone,
                        "Message" => $this->message
                    );


                    try {

                        $result = $sns->publish($args)->toArray();

                        if (is_array($result) && array_key_exists('MessageId', $result)) {
                            $get_sms_status = 'Success|' . $result['MessageId'];
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } catch (SnsException $e) {
                        $get_sms_status = $e->getAwsErrorMessage();
                    }

                    break;


                case 'NusaSMS':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => $gateway_url,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => array(
                            'user' => $gateway_user_name,
                            'password' => $gateway_password,
                            'SMSText' => $this->message,
                            'GSM' => $clphone,
                            'output' => 'json'
                        )
                    ));
                    $resp = curl_exec($curl);
                    if (!$resp) {
                        $get_sms_status = 'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
                    } else {
                        $get_sms_status = $resp;
                    }
                    curl_close($curl);

                    $get_sms_status = json_decode($get_sms_status, true);

                    if (is_array($get_sms_status) && array_key_exists('results', $get_sms_status)) {
                        $status = $get_sms_status['results'][0]['status'];

                        switch ($status) {
                            case '0':
                                $get_sms_status = 'Success';
                                break;
                            case '-1':
                                $get_sms_status = 'Error in processing the request';
                                break;
                            case '-2':
                                $get_sms_status = 'Not enough credits on a specific account';
                                break;
                            case '-3':
                                $get_sms_status = 'Targeted network is not covered on specific account';
                                break;
                            case '-5':
                                $get_sms_status = 'Username or password is invalid';
                                break;
                            case '-6':
                                $get_sms_status = 'Destination address is missing in the request';
                                break;
                            case '-7':
                                $get_sms_status = 'Balance has expired';
                                break;
                            case '-13':
                            case '-11':
                                $get_sms_status = 'Number is not recognized by NusaSMS platform';
                                break;
                            case '-12':
                                $get_sms_status = 'Message is missing in the request';
                                break;
                            case '-22':
                                $get_sms_status = 'Incorrect XML format, caused by syntax error';
                                break;
                            case '-23':
                                $get_sms_status = 'General error, reasons may vary';
                                break;
                            case '-26':
                                $get_sms_status = 'General API error, reasons may vary';
                                break;
                            case '-27':
                                $get_sms_status = 'Invalid scheduling parametar';
                                break;
                            case '-28':
                                $get_sms_status = 'Invalid PushURL in the request';
                                break;
                            case '-30':
                                $get_sms_status = 'Invalid APPID in the request';
                                break;
                            case '-33':
                                $get_sms_status = 'Duplicated MessageID in the request';
                                break;
                            case '-34':
                                $get_sms_status = 'Sender name is not allowed';
                                break;
                            case '-40':
                                $get_sms_status = 'Client IP Address Not In White List';
                                break;
                            case '-99':
                                $get_sms_status = '	Error in processing request, reasons may vary';
                                break;

                            default:
                                $get_sms_status = 'Unknown error';
                                break;
                        }

                    }


                    break;


                case 'SMS4Brands':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {
                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&phone=$clphone" . "&sender=$sender_id" . "&message=$message";


                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        $get_sms_status = trim($get_sms_status);

                        if ($get_sms_status == 'Sent') {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'CheapGlobalSMS':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $post_data = array(
                        'sub_account' => $gateway_user_name,
                        'sub_account_pass' => $gateway_password,
                        'action' => 'send_sms',
                        'sender_id' => $this->sender_id,
                        'recipients' => $clphone,
                        'message' => $this->message
                    );

                    if ($msg_type == 'unicode') {
                        $post_data['unicode'] = 1;
                    }

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $response      = curl_exec($ch);
                    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($response_code != 200) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);
                    if ($response_code != 200) {
                        $get_sms_status = "HTTP ERROR $response_code: $response";
                    } else {
                        $json = @json_decode($response, true);

                        if ($json === null) {
                            $get_sms_status = "INVALID RESPONSE: $response";
                        } elseif (!empty($json['error'])) {
                            $get_sms_status = $json['error'];
                        } else {
                            $get_sms_status = 'Success|' . $json['batch_id'];
                        }
                    }

                    break;


                case 'ExpertTexting':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);
                    $message = urlencode($this->message);

                    try {
                        $count_field  = 6;
                        $field_string = "username=$gateway_user_name&password=$gateway_password&api_key=$gateway_extra&FROM=$this->sender_id&to=$clphone&text=$message";
                        if ($msg_type == 'unicode') {
                            $field_string .= '&type=unicode';
                        }

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_POST, $count_field);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $get_response   = curl_exec($ch);
                        $get_sms_status = curl_error($ch);
                        curl_close($ch);
                        $result = json_decode($get_response, true);

                        if (is_array($result) && array_key_exists('Response', $result)) {
                            if ($result['Response'] == null) {
                                $get_sms_status = $result['ErrorMessage'];
                            }

                            if ($result['Status'] == 0) {
                                $get_sms_status = 'Success';
                            }

                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'LightSMS':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $sms_url   = rtrim($gateway_url, '/');
                    $timestamp = file_get_contents('https://www.lightsms.com/external/get/timestamp.php');

                    $params = [
                        'login' => $gateway_user_name,
                        'phone' => $clphone,
                        'sender' => $this->sender_id,
                        'text' => $this->message,
                        'timestamp' => $timestamp,
                        'return' => 'json'
                    ];
                    ksort($params);


                    $signature = md5(implode($params) . $gateway_password);

                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);


                    try {
                        $url          = "$sms_url" . "?login=$gateway_user_name" . "&signature=$signature" . "&phone=$clphone" . "&sender=$sender_id" . '&timestamp=' . $timestamp . "&return=json" . "&text=$message";
                        $get_response = file_get_contents($url);

                        $result = json_decode($get_response, true);

                        if (is_array($result) && array_key_exists($clphone, $result)) {

                            if (!empty($result[$clphone]) && is_array($result[$clphone]) && array_key_exists('error', $result[$clphone])) {
                                if ($result[$clphone]['error'] != 0) {
                                    $get_sms_status = $result[$clphone]['error'];
                                } else {
                                    $get_sms_status = 'Success';
                                }
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'Adicis':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sms_url   = rtrim($gateway_url, '/');
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    $rand      = str_random(2);

                    try {
                        $sms_sent_to_user = "$sms_url" . "?user=$gateway_user_name" . "&pass=$gateway_password" . "&phone=$clphone" . "&sender=$sender_id" . '&msg_uid=' . $rand . "&action=submit" . "&msg_text=$message";
                        $ch               = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_response = curl_exec($ch);
                        curl_close($ch);
                        if (strpos($get_response, 'successfully') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_response;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Smsconnexion':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sms_url   = rtrim($gateway_url, '/');
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);


                    $Url = $sms_url . "?action=send&username=" . $gateway_user_name . "&passphrase=" . $gateway_password . "&message=" . $message . "&phone=" . $clphone;
                    if (!empty($sender_id)) {
                        $Url = $Url . "&from=" . $sender_id;
                    }
                    // is curl installed?
                    if (!function_exists('curl_init')) {
                        $get_sms_status = 'CURL is not installed';
                    }

                    // create a new curl resource
                    $ch = curl_init();

                    // set URL to download
                    curl_setopt($ch, CURLOPT_URL, $Url);

                    // remove header? 0 = yes, 1 = no
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    // should curl return or print the data? true = return, false = print
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // timeout in seconds
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

                    // download the given URL, and return output
                    $get_sms_status = curl_exec($ch);


                    if ($get_sms_status) {

                        $get_sms_status = trim(str_replace(',', '', $get_sms_status));

                        if (ctype_digit($get_sms_status)) {
                            $get_sms_status = 'Success';
                        }
                    } else {
                        $get_sms_status = curl_error($ch);
                    }
                    // close the curl resource, and free system resources
                    curl_close($ch);


                    break;


                case 'BrandedSMS':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {
                        if ($msg_type == 'unicode') {
                            $type = 'Urdu';
                        } else {
                            $type = 'English';
                        }

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&phone=$clphone" . "&sender=$sender_id" . "&message=$message" . "&type=$type";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        if ($get_sms_status) {
                            $get_sms_status = trim($get_sms_status);
                            if (strpos($get_sms_status, 'sent') !== false) {
                                $get_sms_status = 'Success';
                            }
                        } else {
                            $get_sms_status = curl_error($ch);
                        }
                        // close the curl resource, and free system resources
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Ibrbd':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {
                        $sms_sent_to_user = $gateway_url . "?user=$gateway_user_name" . "&pass=$gateway_password" . "&number=$clphone" . "&yourid=$sender_id" . "&content=$message";


                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_response = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($get_response, true);

                        if (is_array($result) && array_key_exists('status', $result)) {
                            $get_sms_status = $result['status'];
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'TxtNation':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    // These are the POST parameters to set to send a free
                    // msg
                    $req = 'reply=0';
                    $req .= '&id=' . uniqid();
                    $req .= '&number=' . $clphone;
                    $req .= '&network=INTERNATIONAL';
                    $req .= '&message=' . $this->message;
                    $req .= '&value=0';
                    $req .= '&currency=GBP';
                    $req .= '&cc=' . $gateway_user_name;
                    $req .= '&title=' . $this->sender_id;
                    $req .= '&ekey=' . $gateway_password;

                    // Now use the cURL library to make the POST happen
                    $ch = curl_init();

                    // Set the options to make it POST and return the
                    // result (also timeout if no connection after 10
                    // seconds)
                    curl_setopt_array($ch, array(

                        CURLOPT_URL => $gateway_url,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $req,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => 10
                    ));

                    // Do the send
                    $result = curl_exec($ch);

                    // Now we have a result we can free the connection
                    curl_close($ch);

                    // Check to see if the post is a success
                    if (strstr($result, 'SUCCESS')) {
                        $get_sms_status = 'Success';
                    } elseif (strstr($result, '103')) {
                        $get_sms_status = 'An invalid E-Key';
                    } elseif (strstr($result, '101')) {
                        $get_sms_status = 'Duplicate post back for message ID';
                    } elseif (strstr($result, '102')) {
                        $get_sms_status = 'Binary transaction requested, but no UDH specified';
                    } elseif (strstr($result, '104')) {
                        $get_sms_status = 'Invalid details. Please check and try again';
                    } elseif (strstr($result, '415')) {
                        $get_sms_status = 'Invalid company code sent';
                    } elseif (strstr($result, 'BARRED')) {
                        $get_sms_status = 'MSISDN has sent a STOP request or is blacklisted';
                    } elseif (strstr($result, 'NO CREDITS')) {
                        $get_sms_status = 'Inefficient balance';
                    } else {
                        $get_sms_status = 'Unknown error';
                    }


                    break;

                case 'TeleSign':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $messaging_client = new MessagingClient($gateway_user_name, $gateway_password);
                    $response         = $messaging_client->message($clphone, $this->message, 'ARN');
                    $get_status       = $response->json;

                    if (is_array($get_status) && array_key_exists('status', $get_status)) {
                        if (is_array($get_status['status']) && array_key_exists('description', $get_status['status']) && array_key_exists('code', $get_status['status'])) {
                            if ($get_status['status']['code'] == '290') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_status['status']['description'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }


                    break;

                case 'JasminSMS':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = urlencode($clphone);
                    $message = urlencode($this->message);

                    try {
                        $sms_sent_to_user = $gateway_url . ':' . $gateway_port . "/send?username=$gateway_user_name" . "&password=$gateway_password" . "&to=$clphone" . "&content=$message";

                        $get_sms_status = file_get_contents($sms_sent_to_user);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Ezeee':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {
                        $sms_sent_to_user = $gateway_url . "?Username=$gateway_user_name" . "&Password=$gateway_password" . "&to=$clphone" . "&From=$sender_id" . "&Message=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);
                        if (substr_count($get_sms_status, 'Sent Successfully')) {
                            $get_sms_status = 'Success';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Moreify':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    $postParams = array(
                        'project' => $gateway_user_name,
                        'password' => $gateway_password,
                        'phonenumber' => $clphone,
                        'message' => $this->message,
                        'tag' => $this->sender_id
                    );

                    $curl = curl_init($gateway_url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postParams);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                    $get_response = curl_exec($curl);
                    curl_close($curl);

                    $result = json_decode($get_response, true);

                    if (is_array($result) && array_key_exists('success', $result)) {
                        if ($result['success'] != '') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $result['errorMessage'];
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    break;

                case 'Digitalreachapi':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace('+', '', $clphone);

                    $url = 'https://digitalreachapi.dialog.lk/refresh_token.php';

                    // DATA JASON ENCODED
                    $data      = array("u_name" => $gateway_user_name, "passwd" => $gateway_password);
                    $data_json = json_encode($data);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    // DATA ARRAY
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);

                    if ($response === false) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $response = json_decode($response, true);

                    if (is_array($response) && array_key_exists('access_token', $response)) {
                        $access_token = $response['access_token'];

                        $data      = array(
                            "msisdn" => $clphone,
                            "channel" => "1",
                            "mt_port" => $this->sender_id,
                            "s_time" => date("Y-m-d H:i:s"),
                            "e_time" => date('Y-m-d H:i:s', strtotime("+30 minutes")),
                            "msg" => $this->message,
                        );
                        $data_json = json_encode($data);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);

                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Authorization:$access_token"));
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                        // DATA ARRAY
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);

                        if ($response === false) {
                            $get_sms_status = curl_error($ch);
                        }
                        curl_close($ch);

                        $response = json_decode($response, true);

                        if (is_array($response) && array_key_exists('error', $response)) {
                            switch ($response['error']) {

                                case 0:
                                    $get_sms_status = 'Success';
                                    break;

                                case '101':
                                    $get_sms_status = 'Error in parameter';
                                    break;

                                case '102':
                                    $get_sms_status = 'Global throttle exceeds';
                                    break;

                                case '103':
                                    $get_sms_status = 'User wise throttle exceeds';
                                    break;

                                case '104':
                                    $get_sms_status = 'Invalid token';
                                    break;

                                case '105':
                                    $get_sms_status = 'User is blocked';
                                    break;

                                case '106':
                                    $get_sms_status = 'Invalid channel type';
                                    break;

                                case '107':
                                    $get_sms_status = 'Invalid Sender ID';
                                    break;

                                case '108':
                                    $get_sms_status = 'Error in time frame';
                                    break;

                                case '109':
                                    $get_sms_status = 'Insufficient balance';
                                    break;

                                case '110':
                                    $get_sms_status = 'Invalid Number';
                                    break;

                                case '111':
                                    $get_sms_status = 'Invalid message type';
                                    break;

                                case '112':
                                    $get_sms_status = 'Max ad length allowed for selected channel exceed';
                                    break;

                                default:
                                    $get_sms_status = 'Unknown error';
                                    break;

                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } elseif (is_array($response) && array_key_exists('error', $response)) {
                        if ($response['error'] == 100) {
                            $get_sms_status = 'Invalid credentials';
                        } elseif ($response['error'] == 101) {
                            $get_sms_status = 'Error in parameter';
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;

                case 'Tropo':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    try {
                        $sms_sent_to_user = $gateway_url . "?action=create&token=$gateway_user_name" . "&numberToDial=$clphone" . "&msg=$this->message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($ch);
                        curl_close($ch);

                        $xml      = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
                        $json     = json_encode($xml);
                        $get_data = json_decode($json, TRUE);
                        if (is_array($get_data) && array_key_exists('success', $get_data)) {
                            if ($get_data['success'] == false) {
                                $get_sms_status = $get_data['reason'];
                            } else {
                                $get_sms_status = 'Success';
                            }
                        } else {
                            $get_sms_status = 'Invalid Request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'CheapSMS':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {
                        $sms_sent_to_user = $gateway_url . "?loginID=$gateway_user_name" . "&password=$gateway_password" . "&mobile=$clphone" . "&senderid=$sender_id" . "&text=$message" . "&route_id=7";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= "&Unicode=1";
                        } else {
                            $sms_sent_to_user .= "&Unicode=0";
                        }

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $get_data = json_decode($get_data, true);
                        if (is_array($get_data) && array_key_exists('LoginStatus', $get_data)) {
                            if ($get_data['LoginStatus'] == 'Success') {
                                if ($get_data['Transaction_ID'] != '') {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $get_data['MsgStatus'];
                                }
                            } else {
                                $get_sms_status = $get_data['LoginStatus'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'CCSSMS':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace('+', '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&dnis=$clphone" . "&ani=$sender_id" . "&message=$message" . "&command=submit&longMessageMode=1";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= "&dataCoding=1";
                        } else {
                            $sms_sent_to_user .= "&dataCoding=0";
                        }

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        if ($get_data === false) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $get_data = json_decode($get_data, true);

                            if (isset($get_data) && is_array($get_data)) {
                                if (array_key_exists('message_id', $get_data)) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = (string)$get_data;
                                }
                            } else {
                                $get_sms_status = 'Enable your port number for outgoing and incoming from your firewall';
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'MyCoolSMS':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    $postParams = array(
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'function' => 'sendSms',
                        'number' => $clphone,
                        'senderid' => $this->sender_id,
                        'message' => $this->message
                    );

                    $curl = curl_init($gateway_url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postParams));
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                    $get_response = curl_exec($curl);
                    curl_close($curl);

                    $result = json_decode($get_response, true);

                    if (is_array($result) && array_key_exists('success', $result)) {
                        if ($result['success'] == true) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $result['description'];
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    break;


                case 'SmsBump':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "/$gateway_user_name.json?to=$clphone" . "&from=$sender_id" . "&message=$message" . "&type=sms";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($get_data, true);

                        if (is_array($result) && array_key_exists('status', $result)) {
                            if ($result['status'] == 'OK' || $result['status'] == 'queued') {
                                $get_sms_status = 'Success';
                            } elseif ($result['status'] == 'error') {
                                $get_sms_status = $result['message'];
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'BSG':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    $postParams = array(
                        'destination' => 'phone',
                        'originator' => $this->sender_id,
                        'body' => $this->message,
                        'msisdn' => $clphone,
                        'reference' => _raid(10),
                    );


                    $data_json = json_encode($postParams);


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "X-API-KEY:$gateway_user_name"));
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    // DATA ARRAY
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);

                    if ($response === false) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $get_data = json_decode($response, true);


                    if (isset($get_data) && is_array($get_data) && array_key_exists('error', $get_data)) {
                        if ($get_data['error'] == 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data['errorDescription'];
                        }
                    } elseif (isset($get_data) && is_array($get_data) && array_key_exists('result', $get_data)) {
                        if ($get_data['result']['error'] == 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data['result']['errorDescription'];
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;


                case 'SmsBroadcast':
                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&to=$clphone" . "&from=$sender_id" . "&message=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $response_lines = explode("\n", $get_data);

                        if (is_array($response_lines)) {
                            foreach ($response_lines as $data_line) {

                                $get_response = explode(':', $data_line);

                                if ($get_response[0] == "OK") {
                                    $get_sms_status = 'Success';
                                } elseif ($get_response[0] == "BAD") {
                                    $get_sms_status = $get_response[2];
                                } elseif ($get_response[0] == "ERROR") {
                                    $get_sms_status = $get_response[1];
                                } else {
                                    $get_sms_status = 'Unknown error';
                                }
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }


                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'BullSMS':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?user=$gateway_user_name" . "&password=$gateway_password" . "&msisdn=$clphone" . "&sid=$sender_id" . "&msg=$message" . "&fl=0";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($get_data, true);

                        if (is_array($result) && array_key_exists('ErrorMessage', $result)) {
                            $get_sms_status = $result['ErrorMessage'];
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'Skebby':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url .
                            '/login?username=' . $gateway_user_name .
                            '&password=' . $gateway_password);

                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($ch);
                        $info     = curl_getinfo($ch);
                        curl_close($ch);

                        $obj = explode(";", $response);

                        if (is_array($obj) && array_key_exists('1', $obj)) {

                            $sendSMS = array(
                                "message" => $this->message,
                                "message_type" => 'GP',
                                "returnCredits" => true,
                                "recipient" => array($clphone),
                                "sender" => $this->sender_id
                            );

                            if ($msg_type == 'unicode') {
                                $sendSMS['encoding'] = 'ucs2';
                            }

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $gateway_url . '/sms');
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-type: application/json',
                                'user_key: ' . $obj[0],
                                'Session_key: ' . $obj[1]
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                            $response = curl_exec($ch);
                            $info     = curl_getinfo($ch);
                            curl_close($ch);
                            $get_data = json_decode($response);

                            if ($get_data && $get_data->result == "OK") {
                                $get_sms_status = 'Success';
                            } else {
                                if (is_array($info)) {
                                    if ($info['http_code'] == '400') {
                                        $get_sms_status = 'Invalid input.';
                                    } elseif ($info['http_code'] == '401') {
                                        $get_sms_status = 'User_key, Token or Session_key are invalid or not provided';
                                    } elseif ($info['http_code'] == '404') {
                                        $get_sms_status = 'The User_key was not found';
                                    } else {
                                        $get_sms_status = 'Unknown error';
                                    }
                                } else {
                                    $get_sms_status = 'Invalid request';
                                }
                            }
                        } else {
                            if (is_array($info)) {
                                if ($info['http_code'] == '400') {
                                    $get_sms_status = 'Invalid input.';
                                } elseif ($info['http_code'] == '401') {
                                    $get_sms_status = 'User_key, Token or Session_key are invalid or not provided';
                                } elseif ($info['http_code'] == '404') {
                                    $get_sms_status = 'The User_key was not found';
                                } else {
                                    $get_sms_status = 'Unknown error';
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Tyntec':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['(', ')', '-'], '', $clphone);

                    try {
                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n \"from\":\"$this->sender_id\",\n \"to\":\"$clphone\",\n  \"message\":\"$this->message\"\n     }");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_USERPWD, "$gateway_user_name" . ":" . "$gateway_password");

                        $headers   = array();
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($result, true);

                        if (is_array($result) && array_key_exists('requestId', $result)) {
                            $get_sms_status = 'Success';
                        } elseif (is_array($result) && array_key_exists('status', $result)) {
                            $get_sms_status = $result['message'];
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Onehop':

                    $clphone  = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone  = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $get_data = "mobile_number=$clphone&sms_text=$this->message&label=$gateway_password&sender_id=$this->sender_id";


                    if ($msg_type == 'unicode') {
                        $get_data .= "&encoding=unicode";
                    } else {
                        $get_data .= "&encoding=plaintext";
                    }


                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url . '/');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $get_data);
                        curl_setopt($ch, CURLOPT_POST, 1);

                        $headers   = array();
                        $headers[] = "Apikey: $gateway_user_name";
                        $headers[] = "Content-Type: application/x-www-form-urlencoded";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('status', $response)) {
                            if ($response['status'] == 'error') {
                                $get_sms_status = $response['message'];
                            } else {
                                $get_sms_status = 'Success';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }


                    break;


                case 'TigoBeekun':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $message = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?key=$gateway_user_name" . "&msisdn=$clphone" . "&message=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($get_data, true);

                        if (is_array($result) && array_key_exists('message', $result)) {
                            if ($result['message'] == 'error') {
                                $get_sms_status = $result['detail'];
                            } else {
                                $get_sms_status = 'Success';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'MubasherSMS':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?un=$gateway_user_name" . "&pwd=$gateway_password" . "&tonum=$clphone" . "&sender=$sender_id" . "&msg=$message";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= '&lang=1';
                        } else {
                            $sms_sent_to_user .= '&lang=0';
                        }


                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'Advansystelecom':

                    $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone   = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&request_id=" . _raid(5) . "&Mobile_No=$clphone&type=2" . "&sender=$sender_id" . "&message=$message" . "&operator=$gateway_extra";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= '&encoding=2';
                        } else {
                            $sms_sent_to_user .= '&encoding=1';
                        }

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        switch ($get_data) {
                            case '-1':
                                $get_sms_status = 'Invalid user name or password';
                                break;

                            case '-2':
                                $get_sms_status = 'Request ID is duplicated';
                                break;

                            case '-3':
                                $get_sms_status = 'Invalid mobile number';
                                break;

                            case '-4':
                                $get_sms_status = 'Invalid type ID';
                                break;

                            case '-5':
                                $get_sms_status = 'Invalid message';
                                break;

                            case '-6':
                                $get_sms_status = 'Invalid encoding';
                                break;

                            case '-7':
                                $get_sms_status = 'Invalid sender';
                                break;

                            case '-8':
                                $get_sms_status = 'Invalid mobile operator';
                                break;

                            case '-9':
                                $get_sms_status = 'No credit available for account';
                                break;

                            case 'OK':
                                $get_sms_status = 'Success';
                                break;

                            default:
                                $get_sms_status = 'Unknown error';
                                break;

                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Beepsend':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    $sendSMS = array(
                        "body" => $this->message,
                        "receive_dlr" => '0',
                        "to" => $clphone,
                        "from" => $this->sender_id
                    );

                    if ($msg_type == 'unicode') {
                        $sendSMS['encoding'] = 'UTF-8';
                    }


                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url . '/');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                        curl_setopt($ch, CURLOPT_POST, 1);


                        $headers   = array();
                        $headers[] = "Authorization: Token $gateway_user_name";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);


                        if ($response && is_array($response)) {
                            if (array_key_exists('0', $response) && array_key_exists('id', $response[0])) {
                                $get_sms_status = 'Success|' . $response[0]['id'][0];
                            } elseif (array_key_exists('errors', $response)) {
                                $get_sms_status = $response['errors'][0];
                            } else {
                                $get_sms_status = 'Invalid request';
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Toplusms':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);

                    if ($msg_type == 'unicode') {
                        $xml = "<SingleTextSMS><UserName>$gateway_user_name</UserName><PassWord>$gateway_password</PassWord><Action>2</Action><Mesgbody>$this->message</Mesgbody><Numbers>$clphone</Numbers><Originator>$this->sender_id</Originator></SingleTextSMS>";
                    } else {
                        $xml = "<SingleTextSMS><UserName>$gateway_user_name</UserName><PassWord>$gateway_password</PassWord><Action>1</Action><Mesgbody>$this->message</Mesgbody><Numbers>$clphone</Numbers><Originator>$this->sender_id</Originator></SingleTextSMS>";
                    }


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $xml);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    $get_sms_status = curl_exec($ch);
                    curl_close($ch);

                    break;

                case 'AlertSMS':
                    $clphone      = str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);
                    $client_phone = '0' . ltrim($clphone, '40');


                    $content = array(
                        array(
                            'destinatar' => $client_phone,
                            'mesaj' => $this->message,
                            'flash' => false
                        )
                    );

                    $input = array(
                        'method' => 'TrimiteSMS',
                        'user' => $gateway_user_name,
                        'password' => $gateway_password,
                        'token' => $gateway_extra,
                        'content' => $content
                    );

                    $ch = curl_init();

                    $data = json_encode(array("input" => $input));

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                    $result = curl_exec($ch);
                    curl_close($ch);

                    $data = json_decode($result, true);

                    if (isset($data) && is_array($data) && array_key_exists('status', $data)) {
                        if ($data['status']) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $data['response'];
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;

                case 'Sersis':
                    $clphone          = str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);
                    $phone_number     = ltrim('55', $clphone);
                    $gateway_password = urlencode($gateway_password);
                    $message          = urlencode($this->message);
                    $sms_sent_to_user = $gateway_url . "?action=sendsms&lgn=$gateway_user_name" . "&pwd=$gateway_password" . "&numbers=$phone_number" . "&msg=$message";


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $get_data = curl_exec($ch);
                    curl_close($ch);
                    $get_data = json_decode($get_data, true);

                    if (isset($get_data) && is_array($get_data) && array_key_exists('status', $get_data)) {
                        if ($get_data['status'] == 1) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data['msg'];
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;


                case 'Easy':

                    $clphone = str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    $data = array(
                        'key' => $gateway_user_name,
                        'source' => $this->sender_id,// for default sender ID
                        'message' => $this->message,
                        'destination' => $clphone, // with or without country code
                    );

                    if ($msg_type == 'unicode') {
                        $data['type'] = '2';
                    } else {
                        $data['type'] = '1';
                    }

                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    $get_data = curl_exec($ch);
                    curl_close($ch);

                    $get_data = trim($get_data);

                    switch ($get_data) {
                        case '1501':
                            $get_sms_status = 'Invalid Key';
                            break;

                        case '1502':
                            $get_sms_status = 'User Temporarily Disabled';
                            break;

                        case '1503':
                            $get_sms_status = 'Invalid Sender ID';
                            break;

                        case '1504':
                            $get_sms_status = 'Invalid Message';
                            break;

                        case '1505':
                            $get_sms_status = 'Invalid Destination';
                            break;

                        case '1506':
                            $get_sms_status = 'Invalid TYPE';
                            break;

                        case '1507':
                            $get_sms_status = 'Insufficient credit';
                            break;

                        case '1701':
                            $get_sms_status = 'Success';
                            break;

                        case '1801':
                            $get_sms_status = 'Access denied temporarily due to missing connection on mobile operator’s Network / System Maintenance';
                            break;

                        default:
                            $get_sms_status = 'Unknown error';
                            break;

                    }

                    break;


                case 'ClxnetworksHTTPBasic':
                    $clphone = '+' . str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);

                    $data = array(
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'from' => $this->sender_id,// for default sender ID
                        'to' => $clphone, // with or without country code
                        'text' => $this->message,
                    );

                    if ($msg_type == 'unicode') {
                        $data['coding'] = '2';
                    }

                    $data             = http_build_query($data);
                    $sms_sent_to_user = $gateway_url . "?" . $data;
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'ClxnetworksHTTPRest':

                    $clphone   = str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);
                    $sender_id = urlencode($this->sender_id);

                    try {

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $gateway_url . "/batches");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "
                    {\n \"from\": \"$sender_id\",\n  \"to\": [\n \"$clphone\" \n],\n  \"body\": \"$this->message\"\n  }");
                        curl_setopt($ch, CURLOPT_POST, 1);

                        $headers   = array();
                        $headers[] = "Authorization: Bearer $gateway_user_name";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($result, true);
                        if (is_array($result) && array_key_exists('id', $result)) {
                            $batch_id  = $result['id'];
                            $recipient = $result['to'][0];

                            $curl = curl_init();

                            curl_setopt($curl, CURLOPT_URL, $gateway_url . "/batches/" . $batch_id . "/delivery_report/" . $recipient);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");


                            $headers   = array();
                            $headers[] = "Authorization: Bearer $gateway_user_name";
                            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                            $result = curl_exec($curl);
                            curl_close($curl);

                            $get_data = json_decode($result, true);
                            if (is_array($get_data) && array_key_exists('status', $get_data)) {
                                if ($get_data['status'] == 'Delivered' || $get_data['status'] == 'Queued' || $get_data['status'] == 'Dispatched') {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $get_data['status'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }


                    break;

                case 'Textmarketer':
                    $clphone          = str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);
                    $gateway_password = urlencode($gateway_password);
                    $message          = urlencode($this->message);
                    $sender_id        = urlencode($this->sender_id);
                    $sms_sent_to_user = $gateway_url . "/?username=$gateway_user_name" . "&password=$gateway_password" . "&orig=$sender_id" . "&to=$clphone" . "&text=$message";

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($get_sms_status, 'SUCCESS') !== false) {
                            $get_sms_status = 'Success';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Bhashsms':

                    $clphone           = str_replace(['+', '(', ')', '-', '" "'], '', $this->cl_phone);
                    $gateway_user_name = urlencode($gateway_user_name);
                    $gateway_password  = urlencode($gateway_password);
                    $message           = urlencode($this->message);
                    $sender_id         = urlencode($this->sender_id);

                    $priority = 'ndnd';
                    $stype    = 'normal';

                    $var = "user=" . $gateway_user_name . "&pass=" . $gateway_password . "&sender=" . $sender_id . "&phone=" . $clphone . "&text=" . $message . "&priority=" . $priority . "&stype=" . $stype . "";


                    $curl = curl_init($gateway_url . '?' . $var);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($curl);
                    curl_close($curl);
                    $get_sms_status = trim($response);

                    break;


                case 'KingTelecom':

                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = str_replace(['+', '(', ')', '-'], '', $clphone);
                    $message = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?login=$gateway_user_name" . "&token=$gateway_password" . "&numero=$clphone" . "&msg=$message" . "&acao=sendsms";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);
                        $result = json_decode($get_data, true);

                        if (is_array($result) && array_key_exists('status', $result)) {
                            if ($result['status'] == 'error') {
                                $get_sms_status = $result['cause'];
                            } else {
                                $get_sms_status = 'Success';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'Diafaan':
                    $gateway_user_name = urlencode($gateway_user_name);
                    $gateway_password  = urlencode($gateway_password);
                    $message           = urlencode($this->message);

                    $parameters = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'to' => '+' . $this->cl_phone,
                        'message-type' => 'sms.automatic',
                        'message' => $this->message
                    ];

                    if (isset($this->sender_id) && $this->sender_id != '') {
                        $parameters['from'] = $this->sender_id;
                    }

                    $data = http_build_query($parameters);

                    try {
                        $url = $gateway_url . ':' . $gateway_port . '/http/send-message?' . $data;
                        $ch  = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);
                        $curl_error     = curl_error($ch);
                        curl_close($ch);

                        if ($curl_error) {
                            $get_sms_status = $curl_error;
                        } else {
                            if (strpos($get_sms_status, 'OK') !== false) {
                                $msg_id         = str_replace('OK: ', '', $get_sms_status);
                                $get_sms_status = 'Success|' . $msg_id;
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Smsmisr':

                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $data    = array(
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'sender' => $this->sender_id,// for default sender ID
                        'message' => $this->message,
                        'mobile' => $clphone, // with or without country code
                    );

                    if ($msg_type == 'unicode') {
                        $data['language'] = '3';
                    } elseif ($msg_type == 'arabic') {
                        $data['language'] = '2';
                    } else {
                        $data['language'] = '1';
                    }

                    $data = http_build_query($data);

                    $gateway_url = $gateway_url . '?' . $data;

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_data = json_decode($response);
                        $get_data = $get_data->code;

                        switch ($get_data) {
                            case '1901':
                                $get_sms_status = 'Success';
                                break;

                            case '1902':
                                $get_sms_status = 'Invalid URL , This means that one of the parameters was not provided';
                                break;

                            case '1903':
                                $get_sms_status = 'Invalid value in username or password field';
                                break;

                            case '1904':
                                $get_sms_status = 'Invalid value in "sender" field';
                                break;

                            case '1905':
                                $get_sms_status = 'Invalid value in "mobile" field';
                                break;

                            case '1906':
                                $get_sms_status = 'Insufficient Credit';
                                break;

                            case '1907':
                                $get_sms_status = 'Server under updating';
                                break;

                            case '8001':
                                $get_sms_status = 'Mobile IS Null';
                                break;

                            case '8002':
                                $get_sms_status = 'Message IS Null';
                                break;

                            case '8003':
                                $get_sms_status = 'Language IS Null';
                                break;

                            case '8004':
                                $get_sms_status = 'Sender IS Null';
                                break;

                            case '8005':
                                $get_sms_status = 'Username IS Null';
                                break;

                            case '8006':
                                $get_sms_status = 'Password IS Null';
                                break;

                            default:
                                $get_sms_status = 'Unknown error';
                                break;

                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Broadnet':
                    $clphone   = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "/websms?user=$gateway_user_name" . "&pass=$gateway_password" . "&mno=$clphone" . "&sid=$sender_id" . "&text=$message";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= '&type=4';
                        } else {
                            $sms_sent_to_user .= '&type=1';
                        }


                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $get_sms_status = trim($get_data);

                        if (strpos($get_sms_status, 'Response') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            $response = explode('-->', $get_sms_status);
                            if (is_array($response)) {
                                $get_sms_status = trim($response[1]);
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Bulksmsgateway':
                    $clphone   = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    try {
                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&mobile=$clphone" . "&sendername=$sender_id" . "&message=$message&routetype=1";
                        $ch               = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $get_sms_status = trim($get_data);

                        if (strpos($get_sms_status, 'SIM') !== false) {
                            $get_sms_status = 'Success';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Textme':
                    $clphone      = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $phone_number = ltrim('972', $clphone);
                    $sender_id    = urlencode($this->sender_id);
                    $message      = urlencode($this->message);

                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $gateway_url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => "<?xml version='1.0' encoding='UTF-8'?>
                        \r\n <sms>\r\n <user>\r\n <username>$gateway_user_name</username>
                        \r\n <password>$gateway_password</password>\r\n </user>\r\n <source>$sender_id</source>
                        \r\n <destinations>\r\n <phone>$phone_number</phone>
                        \r\n </destinations>\r\n <message>$message</message>\r\n </sms>",
                            CURLOPT_HTTPHEADER => array(
                                "Cache-Control: no-cache",
                                "Content-Type: application/xml"

                            ),
                        ));

                        $response = curl_exec($curl);
                        $err      = curl_error($curl);

                        curl_close($curl);

                        if ($err) {
                            $get_sms_status = $err;
                        } else {
                            $xml      = simplexml_load_string($response);
                            $json     = json_encode($xml);
                            $get_data = json_decode($json, TRUE);

                            if (is_array($get_data) && array_key_exists('status', $get_data)) {
                                if ($get_data['status'] == 0) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $get_data['message'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'Mailjet':
                    $clphone = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                    $clphone = '+' . str_replace(['+', '(', ')', '-'], '', $clphone);

                    $sendSMS = array(
                        "From" => $this->sender_id,
                        "Text" => $this->message,
                        "To" => $clphone
                    );

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                        curl_setopt($ch, CURLOPT_POST, 1);


                        $headers   = array();
                        $headers[] = "Authorization: Bearer $gateway_user_name";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response)) {
                            if (array_key_exists('Status', $response) && array_key_exists('Code', $response['Status'])) {
                                $get_sms_status = 'Success';
                            } elseif (array_key_exists('ErrorCode', $response)) {
                                $get_sms_status = $response['ErrorMessage'];
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Alaris':

                    $sendSMS = array(
                        "from" => $this->sender_id,
                        "message" => $this->message,
                        "to" => $this->cl_phone,
                        "username" => $gateway_user_name,
                        "password" => $gateway_password,
                    );
                    $sendSMS = http_build_query($sendSMS);

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url . '/rest/send_sms?' . $sendSMS);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POST, 1);

                        $headers   = array();
                        $headers[] = "Content-Type: application/json";
                        $headers[] = "Accept: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);

                        if (curl_error($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $response = json_decode($result, true);

                            if ($response && is_array($response)) {
                                if (array_key_exists('message_id', $response)) {
                                    $get_sms_status = 'Success';
                                } elseif (array_key_exists('error_message', $response)) {
                                    $get_sms_status = $response['error_message'];
                                } else {
                                    $get_sms_status = 'Invalid request';
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Ejoin':

                    $clphone = '+' . str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $tid     = rand(1, 9);

                    $gateway_url = $gateway_url . '/goip_post_sms.html';

                    $rand    = rand();
                    $fields  = array(
                        "type" => "send-sms",
                        "task_num" => 1,
                        "tasks" => [array('tid' => $tid, "from" => $this->sender_id, "to" => $clphone, "sms" => $this->message)]
                    );
                    $headers = array(
                        'Content-Type: text/plain',
                        'Authorization: Basic ' . base64_encode($gateway_user_name . ":" . $gateway_password),
                    );
                    $ch      = curl_init();
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_URL, $gateway_url . "?username=" . $gateway_user_name . "&password=" . $gateway_password);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $get_data = curl_exec($ch);

                    if (curl_error($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {

                        $get_response = json_decode($get_data, true);

                        if (isset($get_response) && is_array($get_response) && array_key_exists('code', $get_response) && array_key_exists('reason', $get_response)) {
                            if ($get_response['code'] == 0 || $get_response['code'] == 200) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_response['reason'];
                            }
                        } else {
                            $get_sms_status = $get_response['desc'];
                        }
                    }
                    curl_close($ch);
                    break;


                case 'Mobitel':

                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                    if ($gateway_port == 'promotional') {
                        $sms_type = 1;
                    } else {
                        $sms_type = 0;
                    }

                    try {
                        require_once(app_path('libraray/mobitel/ESMSWS.php'));

                        $session  = createSession('', $gateway_user_name, $gateway_password, '');
                        $get_data = sendMessages($session, $this->sender_id, $this->message, array($clphone), $sms_type);
                        $get_data = trim($get_data);
                        closeSession($session);

                        switch ($get_data) {
                            case '0':
                            case '200':
                                $get_sms_status = 'Success';
                                break;

                            case '151':
                                $get_sms_status = 'Invalid session';
                                break;

                            case '152':
                                $get_sms_status = 'Session is still in use for previous request';
                                break;

                            case '155':
                                $get_sms_status = 'Service halted';
                                break;

                            case '156':
                                $get_sms_status = 'Other network messaging disabled';
                                break;

                            case '157':
                                $get_sms_status = 'IDD messages disabled';
                                break;

                            case '159':
                                $get_sms_status = 'Failed credit check';
                                break;

                            case '160':
                                $get_sms_status = 'No message found';
                                break;

                            case '161':
                                $get_sms_status = 'Message exceeding 160 characters';
                                break;

                            case '164':
                                $get_sms_status = 'Invalid group';
                                break;

                            case '165':
                                $get_sms_status = 'No recipients found';
                                break;

                            case '166':
                                $get_sms_status = 'Recipient list exceeding allowed limit';
                                break;

                            case '167':
                                $get_sms_status = 'Invalid long number';
                                break;

                            case '168':
                                $get_sms_status = 'Invalid short code';
                                break;

                            case '169':
                                $get_sms_status = 'Invalid alias';
                                break;

                            case '170':
                                $get_sms_status = 'Black listed numbers in number list';
                                break;

                            case '171':
                                $get_sms_status = 'Non-white listed numbers in number list';
                                break;

                            case '175':
                                $get_sms_status = 'Deprecated method';
                                break;

                            default:
                                $get_sms_status = 'Unknown error';
                                break;

                        }


                    } catch (\Exception $exception) {
                        $get_sms_status = $exception->getMessage();
                    }

                    break;


                case 'OpenVox':
                    $clphone          = str_replace([" ", '+'], "", $this->cl_phone);
                    $sms_sent_to_user = $gateway_url . "/sendsms?username=$gateway_user_name" . "&password=$gateway_password" . "&phonenumber=$clphone" . "&message=" . urlencode($this->message);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $get_sms_status = curl_exec($ch);
                    curl_close($ch);


                    $array = json_decode($get_sms_status, TRUE);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (isset($array) && is_array($array) && array_key_exists('report', $array)) {
                            $get_sms_status = $array['report'][0][1][0]['result'];
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        if (strpos($get_sms_status, 'Authentication') !== false) {
                            $get_sms_status = 'Need valid username and password';
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    }

                    break;

                case 'Smsgatewayhub':
                    $clphone          = str_replace([" ", '+'], "", $this->cl_phone);
                    $sms_sent_to_user = $gateway_url . "?APIKey=$gateway_user_name" . "&senderid=$this->sender_id" . "&channel=$gateway_password" . "&number=$clphone" . "&flashsms=0&text=" . urlencode($this->message) . "&route=" . $gateway_extra;

                    if ($msg_type == 'unicode') {
                        $sms_sent_to_user .= '&DCS=8';
                    } else {
                        $sms_sent_to_user .= '&DCS=0';
                    }


                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $get_sms_status = curl_exec($ch);
                    curl_close($ch);


                    $array = json_decode($get_sms_status, TRUE);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (isset($array) && is_array($array) && array_key_exists('ErrorMessage', $array)) {
                            $get_sms_status = $array['ErrorMessage'];
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }

                    break;


                case 'Ayyildiz':
                    include_once app_path('Classes/Ayyildiz.php');

                    $clphone    = str_replace([" ", '+'], "", $this->cl_phone);
                    $send_sms   = new \sendSMS($gateway_user_name, $gateway_password, $gateway_extra, $this->sender_id, $clphone, $this->message);
                    $get_status = $send_sms->send();

                    if (isset($get_status) && is_array($get_status) && array_key_exists('basari', $get_status)) {
                        if ($get_status['basari']) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_status['mesaj'];
                        }
                    } else {
                        $get_sms_status = 'Geçersiz istek';
                    }

                    break;

                case 'transfer.sh':

                    $sender_id = urlencode($this->sender_id);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {

                        if ($msg_type == 'unicode') {
                            $type    = 3;
                            $message = $this->sms_unicode($this->message);
                        } elseif ($msg_type == 'arabic') {
                            $type    = 2;
                            $message = urlencode($this->message);
                        } else {
                            $type    = 1;
                            $message = urlencode($this->message);
                        }

                        $sms_sent_to_user = "$sms_url" . "?username=$gateway_user_name" . "&password=$gateway_password" . "&mobile=$this->cl_phone" . "&sender=$sender_id" . "&message=$message" . "&language=$type";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count($get_sms_status, 'OK') == 1) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'BulkGate':

                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                    try {

                        if ($msg_type == 'unicode') {
                            $type = true;
                        } else {
                            $type = false;
                        }

                        $curl = curl_init();

                        curl_setopt_array($curl, [
                            CURLOPT_URL => $gateway_url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => json_encode([
                                'application_id' => $gateway_user_name,
                                'application_token' => $gateway_password,
                                'number' => $clphone,
                                'text' => $this->message,
                                'sender_id' => 'gText',
                                'sender_id_value' => $this->sender_id
                            ]),
                            CURLOPT_HTTPHEADER => [
                                'Content-Type: application/json'
                            ],
                        ]);

                        $response = curl_exec($curl);

                        if ($error = curl_error($curl)) {
                            $get_sms_status = $error;
                        } else {
                            $response = json_decode($response, true);
                        }
                        curl_close($curl);

                        if (isset($response) && is_array($response)) {
                            if (array_key_exists('data', $response)) {
                                $get_sms_status = 'Success';
                            } else if (array_key_exists('error', $response)) {
                                $get_sms_status = $response['error'];
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }


                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Diamondcard':

                    include_once app_path('Classes/nusoap.php');

                    $sender_id = urlencode($this->sender_id);
                    $sms_url   = rtrim($gateway_url, '/');
                    $clphone   = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                    try {

                        if (strlen($clphone) > 11) {
                            $client_phone = ltrim($clphone, '44');
                        } else {
                            $client_phone = $clphone;
                        }

                        $params = array('AccId' => $gateway_user_name,                   // Account Id
                            'PinCode' => $gateway_password,               // Pin code
                            'MsgTxt' => $this->message,                 // Message to sent
                            'SendFrom' => $sender_id,             // Send from
                            'Destination' => $client_phone,         // Send SMS to the list of destinations
                        );

                        $client                   = new \nusoap_client($sms_url, 'wsdl');
                        $client->soap_defencoding = 'UTF-8';
                        $client->decode_utf8      = false;

                        $response = $client->call('send', array('inParams' => $params));

                        if (isset($response) && is_array($response) && array_key_exists('out', $response)) {
                            if (is_array($response['out']) && array_key_exists('ErrCode', $response['out'])) {
                                if ($response['out']['ErrCode'] == '') {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $response['out']['ErrMsg'];
                                }
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Tellegroup':

                    $sms_url = rtrim($gateway_url, '/');
                    $message = urlencode($this->message);

                    try {

                        $unique_id = _raid(20);

                        if (strlen($this->cl_phone) > 11) {
                            $client_phone = substr($this->cl_phone, -11);
                        } else {
                            $client_phone = $this->cl_phone;
                        }


                        $sms_sent_to_user = "$sms_url" . "?&dispatch=send&account=$gateway_user_name" . "&code=$gateway_password" . "&to=$client_phone" . "&msg=$message" . "&id=$unique_id" . "&tipoEnvio=2" . "&type=E";

                        if ($this->sender_id) {
                            $sms_sent_to_user .= "&from=" . $this->sender_id;
                        }

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = trim($get_response);
                        $get_response = (int)filter_var($get_response, FILTER_SANITIZE_NUMBER_INT);

                        switch ($get_response) {

                            case '000':
                                $get_sms_status = 'Success|' . $unique_id;
                                break;

                            case '010':
                                $get_sms_status = 'Mensagem vazia';
                                break;

                            case '012':
                                $get_sms_status = 'Corpo da mensagem excedeu o limite';
                                break;

                            case '013':
                                $get_sms_status = 'Número do destinatário incompleto ou inválido';
                                break;

                            case '014':
                                $get_sms_status = 'Número do destinatário vazio';
                                break;

                            case '015':
                                $get_sms_status = 'Data de agendamento em formato incorreto';
                                break;

                            case '016':
                                $get_sms_status = 'Código da mensagem excedeu o limite de 50 caracteres';
                                break;

                            case '017':
                                $get_sms_status = 'Código da mensagem vazio';
                                break;

                            case '021':
                                $get_sms_status = 'Código da mensagem é obrigatório ao consultar';
                                break;

                            case '080':
                                $get_sms_status = 'Código da mensagem duplicado';
                                break;

                            case '100':
                                $get_sms_status = 'Mensagem aguardando envio';
                                break;

                            case '110':
                                $get_sms_status = 'Mensagem enviada para a operadora';
                                break;

                            case '120':
                                $get_sms_status = 'Mensagem entregue';
                                break;

                            case '130':
                                $get_sms_status = 'Mensagem bloqueada';
                                break;

                            case '131':
                                $get_sms_status = 'Analise de envio';
                                break;

                            case '138':
                                $get_sms_status = 'Em analise';
                                break;

                            case '139':
                                $get_sms_status = 'Agendado';
                                break;

                            case '146':
                                $get_sms_status = 'Mensagem não entregue';
                                break;

                            case '150':
                                $get_sms_status = 'Mensagem expirou na operadora. Celular pode estar desligado, fora da área de cobertura ou indisponível no sistema';
                                break;

                            case '161':
                                $get_sms_status = 'Mensagem rejeitada pela operadora';
                                break;

                            case '162':
                                $get_sms_status = 'Mensagem cancelada ou bloqueada pela operadora';
                                break;

                            case '163':
                                $get_sms_status = 'Analise de envio operadora';
                                break;

                            case '171':
                                $get_sms_status = 'Número invalido';
                                break;

                            case '173':
                                $get_sms_status = 'Número fixo';
                                break;

                            case '174':
                                $get_sms_status = 'Usuário sem crédito';
                                break;

                            case '180':
                                $get_sms_status = 'Não há mensagem com o código fornecido';
                                break;

                            case '200':
                                $get_sms_status = 'Mensagens enviadas com sucesso';
                                break;

                            case '240':
                                $get_sms_status = 'Arquivo vazio ou não enviado';
                                break;

                            case '241':
                                $get_sms_status = 'Arquivo muito grande';
                                break;

                            case '242':
                                $get_sms_status = 'Arquivo corrompido ou com formatação inválida';
                                break;

                            case '900':
                                $get_sms_status = 'Login ou senha inválidos';
                                break;

                            case '901':
                                $get_sms_status = 'IP inválido';
                                break;

                            case '999':
                                $get_sms_status = 'Erro desconhecido. Contate nosso suporte';
                                break;

                            default:
                                $get_sms_status = 'Unknown error';
                                break;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'MaskSMS':

                    $sms_url   = rtrim($gateway_url, '/');
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);

                    try {
                        $sms_sent_to_user = "$sms_url" . "?action=send-sms&api_key=$gateway_user_name" . "&from=$sender_id" . "&to=$this->cl_phone" . "&sms=$message";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= "&unicode=1";
                        } else {
                            $sms_sent_to_user .= "&unicode=0";
                        }

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($get_response);

                        if ($response->code == 'ok') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $response->message;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'AdvanceMessage':

                    $sms_url   = rtrim($gateway_url, '/');
                    $sender_id = urlencode($this->sender_id);

                    $message = $this->message;

                    try {

                        if ($msg_type == 'unicode') {
                            $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
                            $is_rtl            = preg_match($rtl_chars_pattern, $this->message);

                            if ($is_rtl) {
                                $type = 4;
                            } else {
                                $message = $this->sms_unicode($this->message);
                                $type    = 2;
                            }
                        } else {

                            $is_special = preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $message);

                            if ($is_special) {
                                $type = 3;
                            } else {
                                $message = urlencode($this->message);
                                $type    = 1;
                            }
                        }

                        $sms_sent_to_user = "$sms_url" . "/websms?user=$gateway_user_name" . "&pass=$gateway_password" . "&sid=$sender_id" . "&mno=$this->cl_phone" . "&type=$type" . "&text=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_data = curl_exec($ch);
                        curl_close($ch);


                        $get_sms_status = trim($get_data);

                        if (strpos($get_sms_status, 'Response') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            $response = explode('-->', $get_sms_status);
                            if (is_array($response)) {
                                $get_sms_status = trim($response[1]);
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;
                case 'EblogUs':

                    $sms_url   = rtrim($gateway_url, '/');
                    $sender_id = urlencode($this->sender_id);
                    $clphone   = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $message   = base64_encode($this->message);

                    try {

                        $base_url   = $sms_url . "?api_key=" . $gateway_user_name . "&user_id=" . $gateway_password . "&";
                        $url        = $base_url . "action=create_message&message=" . $message;
                        $msg_result = json_decode(file_get_contents($url), true);

                        if (isset($msg_result) && is_array($msg_result) && array_key_exists('message_id', $msg_result)) {
                            $msg_id = $msg_result['message_id'];

                            $url          = $base_url . "action=send_message&message=" . $msg_id . "&sender=" . $sender_id . "&receiver=" . $clphone;
                            $get_response = json_decode(file_get_contents($url), true);
                            if (isset($get_response) && is_array($get_response) && array_key_exists('code', $get_response)) {
                                if ($get_response['code'] == 1) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $get_response['msg'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;
                case 'Connectmedia':
                    $text     = trim($this->message);
                    $username = $gateway_user_name;
                    $password = $gateway_password;
                    $postUrl  = $gateway_url;
                    $action   = "send";
                    $post     = [
                        'action' => "$action", //Please don’t change
                        'to' => array($this->cl_phone), //Please don’t change
                        'username' => "$username", //Please don’t change
                        'password' => "$password", //Please don’t change
                        'sender' => $this->sender_id, //Please don’t change
                        'message' => urlencode("$text"),//Please don’t change
                    ];
                    $ch       = curl_init($postUrl); //Please don’t change
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $jsonarray[] = array();
                    $jsonarray   = json_decode($response, true);

                    if (isset($jsonarray) && is_array($jsonarray) && array_key_exists('message', $jsonarray)) {
                        if ($jsonarray['code'] == '201') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $jsonarray['message'];
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }


                    break;

                case 'WhatsAppChatApi':
                    $sms_url = rtrim($gateway_url, '/');
                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                    $data = [
                        'phone' => $clphone,
                        'body' => $this->message
                    ];
                    $json = json_encode($data);

                    $url     = $sms_url . '/message?token=' . $gateway_user_name;
                    $options = stream_context_create(['http' => [
                        'method' => 'POST',
                        'header' => 'Content-type: application/json',
                        'content' => $json
                    ]
                    ]);

                    try {
                        $result = file_get_contents($url, false, $options);

                        $json_array[] = array();
                        $json_array   = json_decode($result, true);

                        if (isset($json_array) && is_array($json_array) && array_key_exists('sent', $json_array)) {
                            if ($json_array['sent'] == true) {
                                $get_sms_status = 'Success|' . $json_array['queueNumber'];
                            } else {
                                $get_sms_status = $json_array['message'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;

                case 'Evyapan':
                    include_once app_path('Classes/Evyapaniletisim.php');

                    $smsapi = new \Smsapi($gateway_user_name, $gateway_password);

                    $to_list = array($this->cl_phone);
                    $message = $this->message;
                    $from    = $this->sender_id;

                    if ($msg_type == 'unicode') {
                        $data_encoding = 'UCS2';
                    } else {
                        $data_encoding = null;
                    }

                    $response = $smsapi->submit($to_list, $message, null, null, null, $data_encoding);

                    if ($response->status) {
                        if ($response->payload->Status->Code == 200) {
                            $get_sms_status = 'Success|' . $response->payload->MessageId;
                        } else {
                            $get_sms_status = $response->payload->Status->Code . "-" . $response->payload->Status->Description;
                        }
                    } else {
                        $get_sms_status = $response->error;
                    }

                    break;

                case 'BudgetSMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&userid=$gateway_password" . "&to=$clphone" . "&msg=$message" . "&handle=$gateway_extra" . "&from=$this->sender_id";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = trim($get_response);
                        $result       = explode(" ", $get_response);
                        if (isset($result) && is_array($result) && count($result) > 0) {

                            if ($result['0'] == 'OK') {
                                $get_sms_status = 'Success|' . $result['1'];
                            } else {
                                switch ($result['1']) {
                                    case '1001':
                                        $get_sms_status = 'Not enough credits to send messages';
                                        break;

                                    case '1002':
                                        $get_sms_status = 'Identification failed. Wrong credentials';
                                        break;

                                    case '1003':
                                        $get_sms_status = 'Account not active, contact BudgetSMS';
                                        break;

                                    case '1004':
                                        $get_sms_status = 'This IP address is not added to this account. No access to the API';
                                        break;

                                    case '1005':
                                        $get_sms_status = 'No handle provided';
                                        break;

                                    case '1006':
                                        $get_sms_status = 'No UserID provided';
                                        break;

                                    case '1007':
                                        $get_sms_status = 'No Username provided';
                                        break;

                                    case '2001':
                                        $get_sms_status = 'SMS message text is empty';
                                        break;

                                    case '2002':
                                        $get_sms_status = 'SMS numeric senderid can be max. 16 numbers';
                                        break;

                                    case '2003':
                                        $get_sms_status = 'SMS alphanumeric sender can be max. 11 characters';
                                        break;

                                    case '2004':
                                        $get_sms_status = 'SMS senderid is empty or invalid';
                                        break;

                                    case '2005':
                                        $get_sms_status = 'Destination number is too short';
                                        break;

                                    case '2006':
                                        $get_sms_status = 'Destination is not numeric';
                                        break;

                                    case '2007':
                                        $get_sms_status = 'Destination is empty';
                                        break;

                                    case '2008':
                                        $get_sms_status = 'SMS text is not OK';
                                        break;

                                    case '2009':
                                        $get_sms_status = 'Parameter issue';
                                        break;

                                    case '2010':
                                        $get_sms_status = 'Destination number is invalidly formatted';
                                        break;

                                    case '2011':
                                        $get_sms_status = 'Destination is invalid';
                                        break;

                                    case '2012':
                                        $get_sms_status = 'SMS message text is too long';
                                        break;

                                    case '2013':
                                        $get_sms_status = 'SMS message is invalid';
                                        break;

                                    case '2014':
                                        $get_sms_status = 'SMS CustomID is used before';
                                        break;

                                    case '2015':
                                        $get_sms_status = 'Charset problem';
                                        break;

                                    case '2016':
                                        $get_sms_status = 'Invalid UTF-8 encoding';
                                        break;

                                    case '2017':
                                        $get_sms_status = 'Invalid SMSid';
                                        break;

                                    case '3001':
                                        $get_sms_status = 'No route to destination. Contact BudgetSMS for possible solutions';
                                        break;

                                    case '3002':
                                        $get_sms_status = 'No routes are setup. Contact BudgetSMS for a route setup';
                                        break;

                                    case '3003':
                                        $get_sms_status = 'Invalid destination. Check international mobile number formatting';
                                        break;

                                    case '4001':
                                        $get_sms_status = 'System error, related to customID';
                                        break;

                                    case '4002':
                                        $get_sms_status = 'System error, temporary issue. Try resubmitting in 2 to 3 minutes';
                                        break;

                                    case '4003':
                                        $get_sms_status = 'System error, temporary issue';
                                        break;

                                    case '4004':
                                        $get_sms_status = 'System error, temporary issue. Contact BudgetSMS';
                                        break;

                                    case '4005':
                                        $get_sms_status = 'System error, permanent';
                                        break;

                                    case '4006':
                                        $get_sms_status = 'Gateway not reachable';
                                        break;

                                    case '4007':
                                        $get_sms_status = 'System error, contact BudgetSMS';
                                        break;

                                    case '5001':
                                        $get_sms_status = 'Send error, Contact BudgetSMS with the send details';
                                        break;

                                    case '5002':
                                        $get_sms_status = 'Wrong SMS type';
                                        break;

                                    case '5003':
                                        $get_sms_status = 'Wrong operator';
                                        break;

                                    case '7001':
                                        $get_sms_status = 'No HLR provider present, Contact BudgetSMS';
                                        break;

                                    case '7002':
                                        $get_sms_status = 'Unexpected results from HLR provider';
                                        break;

                                    case '7003':
                                        $get_sms_status = 'Bad number format';
                                        break;

                                    case '7901':
                                        $get_sms_status = 'Unexpected error. Contact BudgetSMS';
                                        break;

                                    case '7903':
                                    case '7902':
                                        $get_sms_status = 'HLR provider error. Contact BudgetSMS';
                                        break;

                                    case '6001':
                                    default:
                                        $get_sms_status = 'Unknown error';
                                        break;
                                }
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'EasySendSMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    if (is_numeric($this->sender_id)) {
                        $sender_id = str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);
                    } else {
                        $sender_id = $this->sender_id;
                    }

                    if ($msg_type == 'unicode') {
                        $data_encoding = 1;
                        $message       = $this->sms_unicode($this->message);
                    } else {
                        $data_encoding = 0;
                        $message       = $this->message;
                    }

                    $parameters = http_build_query([
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'to' => $clphone,
                        'text' => $message,
                        'type' => $data_encoding,
                        'from' => $sender_id
                    ]);

                    try {

                        $sms_sent_to_user = $gateway_url . "?" . $parameters;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = trim($get_response);

                        if (substr_count($get_response, 'OK')) {
                            $get_sms_status = 'Success';
                        } else {

                            $data_code = (int)filter_var($get_response, FILTER_SANITIZE_NUMBER_INT);

                            switch ($data_code) {
                                case '1001':
                                    $get_sms_status = 'Invalid URL. This means that one of the parameters was not provided or left blank';
                                    break;

                                case '1002':
                                    $get_sms_status = 'Invalid username or password parameter';
                                    break;

                                case '1003':
                                    $get_sms_status = 'Invalid type parameter';
                                    break;

                                case '1004':
                                    $get_sms_status = 'Invalid message';
                                    break;

                                case '1005':
                                    $get_sms_status = 'Invalid mobile number';
                                    break;

                                case '1006':
                                    $get_sms_status = 'Invalid Sender name';
                                    break;

                                case '1007':
                                    $get_sms_status = 'Insufficient credit';
                                    break;

                                case '1008':
                                    $get_sms_status = 'Internal error';
                                    break;

                                case '1009':
                                    $get_sms_status = 'Service not available';
                                    break;

                                default:
                                    $get_sms_status = 'Unknown error';
                                    break;
                            }

                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'ClickSend':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    try {

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_HEADER, FALSE);
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$gateway_user_name&key=$gateway_password&to=$clphone&message=$message&senderid=$this->sender_id");

                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/x-www-form-urlencoded"
                        ));

                        $response = curl_exec($ch);
                        curl_close($ch);

                        $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);

                        $json  = json_encode($xml);
                        $array = json_decode($json, TRUE);

                        if (isset($array) && is_array($array) && array_key_exists('messages', $array)) {
                            $get_sms_status = $array['messages']['message']['errortext'];
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'Gatewayapi':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    $recipients = [$clphone];
                    $json       = [
                        'sender' => $this->sender_id,
                        'message' => $this->message,
                        'recipients' => [],
                    ];

                    if ($msg_type == 'unicode') {
                        $json['encoding'] = 'UCS2';
                    }

                    foreach ($recipients as $msisdn) {
                        $json['recipients'][] = ['msisdn' => $msisdn];
                    }
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
                    curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $json = json_decode($result, true); // convert to object

                    if (isset($json) && is_array($json)) {
                        if (array_key_exists('ids', $json)) {
                            $get_sms_status = 'Success|' . $json['ids'][0];
                        } else {
                            $get_sms_status = $json['message'];
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    break;

                case 'CoinSMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&recipient=$clphone" . "&message=$message" . "&sender=$this->sender_id";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = trim($get_response);

                        $result = explode("<br>", $get_response);

                        if (isset($result) && is_array($result) && count($result) > 0) {

                            if (substr_count($get_response, 'OK') !== 0) {
                                $get_sms_status = 'Success';
                            } else {

                                switch ($result['0']) {

                                    case '2904':
                                        $get_sms_status = 'SMS Sending Failed';
                                        break;

                                    case '2905':
                                        $get_sms_status = 'Invalid username/password combination';
                                        break;

                                    case '2906':
                                        $get_sms_status = 'Credit exhausted';
                                        break;

                                    case '2907':
                                        $get_sms_status = 'Gateway unavailable';
                                        break;

                                    case '2908':
                                        $get_sms_status = 'Invalid schedule date format';
                                        break;

                                    case '2909':
                                        $get_sms_status = 'Unable to schedule';
                                        break;

                                    case '2910':
                                        $get_sms_status = 'Username is empty';
                                        break;

                                    case '2911':
                                        $get_sms_status = 'Password is empty';
                                        break;

                                    case '2912':
                                        $get_sms_status = 'Recipient is empty';
                                        break;

                                    case '2913':
                                        $get_sms_status = 'Message is empty';
                                        break;

                                    case '2914':
                                        $get_sms_status = 'Sender is empty';
                                        break;

                                    case '2915':
                                        $get_sms_status = 'One or more required fields are empty';
                                        break;

                                    default:
                                        $get_sms_status = 'Unknown error';
                                        break;
                                }
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Futureland':

                    $clphone = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $data = http_build_query([
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'destinatari' => $clphone,
                        'dati' => $this->message,
                        'mittente' => $this->sender_id,
                        'tipo' => 'SMS_MAX'
                    ]);

                    try {

                        $sms_sent_to_user = $gateway_url . "?" . $data;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count($get_response, 'OK') !== 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_response = trim($get_response);
                            $result       = explode(":", $get_response);
                            if (isset($result) && is_array($result) && count($result) > 0) {
                                $get_sms_status = trim($result['1']);
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'MessageWhiz':

                    $sms_url = rtrim($gateway_url, '/') . '/broadcasts/single';
                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    try {
                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "{  \n   \"message_body\": \"$this->message\",  \n   \"recipient\": \"$clphone\",  \n \"sender\": \"$this->sender_id\"  \n }");
                        curl_setopt($ch, CURLOPT_POST, 1);

                        $headers   = array();
                        $headers[] = "Content-Type: application/json";
                        $headers[] = "Accept: application/json";
                        $headers[] = "apikey: $gateway_user_name";

                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count(strtolower($result), 'id')) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = 'Request not found';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'GlobalSMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    if ($msg_type == 'unicode') {
                        $sms_type = 'unicode';
                        $message  = urlencode(html_entity_decode($this->message, ENT_QUOTES, "UTF-8"));
                    } else {
                        $sms_type = 'text';
                        $message  = urlencode($this->message);
                    }

                    try {

                        $sms_sent_to_user = $gateway_url . "?key=$gateway_user_name" . "&contacts=$clphone" . "&msg=$message" . "&senderid=$this->sender_id" . "&type=$sms_type";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count($get_response, 'api') !== 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = trim($get_response);
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'LRTelecom':

                    $clphone   = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);

                    try {


                        if ($msg_type == 'unicode') {
                            $sms_type = 'Urdu';
                        } else {
                            $sms_type = 'English';
                        }

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&phone=$clphone" . "&message=$message" . "&sender=$sender_id" . "&type=$sms_type";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count(strtolower($get_response), 'message is sent') !== 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = trim($get_response);
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'AccessYou':

                    $clphone   = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);

                    try {

                        $sms_sent_to_user = "$gateway_url?msg=$message&phone=$clphone&pwd=$gateway_password&accountno=$gateway_user_name";

                        if ($this->sender_id) {
                            $sms_sent_to_user .= "&from=" . $this->sender_id;
                        }

                        $handle   = fopen($sms_sent_to_user, "r");
                        $contents = trim(fread($handle, 8192));
                        if (is_numeric($contents)) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = trim($contents);
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Montnets':

                    $clphone      = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message      = urlencode($this->message);
                    $client_phone = '00' . $clphone;

                    try {

                        $sms_sent_to_user = $gateway_url . "?userid=$gateway_user_name" . "&pwd=$gateway_password" . "&mobile=$client_phone" . "&content=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        if (substr_count(strtolower($get_response), 'result=0') !== 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = trim($get_response);
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'CARMOVOIPSHORT':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    if (strlen($clphone) > 11) {
                        $client_phone = ltrim($clphone, '55');
                    } else {
                        $client_phone = $clphone;
                    }

                    try {

                        $sms_sent_to_user = $gateway_url . "?cpf=$gateway_user_name" . "&password=$gateway_password" . "&numbers=$client_phone" . "&messages=$message&type=short";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        if ($get_response) {
                            if (substr($get_response, 0, 1) == '0') {
                                $get_sms_status = 'Success|' . substr($get_response, 2);
                            } else {
                                $get_sms_status = trim($get_response);
                            }
                        } else {
                            $get_sms_status = 'IP Address and port block by hosting provider';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'CARMOVOIPLONG':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    if (strlen($clphone) > 11) {
                        $client_phone = ltrim($clphone, '55');
                    } else {
                        $client_phone = $clphone;
                    }

                    try {

                        $sms_sent_to_user = $gateway_url . "?cpf=$gateway_user_name" . "&password=$gateway_password" . "&numbers=$client_phone" . "&messages=$message&type=long";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        if ($get_response) {
                            if (substr_count(strtolower($get_response), 'OK') !== 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = trim($get_response);
                            }
                        } else {
                            $get_sms_status = 'IP Address and port block by hosting provider';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'ShotBulkSMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    $sms_sent_to_user = $gateway_url . "?user=$gateway_user_name" . "&password=$gateway_password" . "&PhoneNumber=$clphone" . "&Sender=$this->sender_id";

                    if ($msg_type == 'unicode') {
                        $sms_sent_to_user .= "&Data=" . $message . "&DCS=8";
                    } else {
                        $sms_sent_to_user .= "&Text=" . $message;
                    }

                    try {

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $res            = preg_match("/<title>(.*)<\/title>/siU", $get_response, $title_matches);
                        $title          = preg_replace('/\s+/', ' ', $title_matches[1]);
                        $get_sms_status = trim($title);

                        if ($get_sms_status == 'Message Submitted') {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'KarixIO':

                    $clphone = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $parameters = [
                        'channel' => 'sms',
                        'source' => $this->sender_id,
                        'destination' => [$clphone],
                        'content' => [
                            'text' => $this->message
                        ]
                    ];

                    try {

                        $headers = array(
                            'Content-Type:application/json'
                        );

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":" . $gateway_password);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = json_decode($response, true);

                        if (isset($get_response) && is_array($get_response)) {
                            if (array_key_exists('objects', $get_response)) {
                                if ($get_response['objects']['0']['status'] == 'queued') {
                                    $get_sms_status = 'Success|' . $get_response['objects']['0']['account_uid'];
                                } else {
                                    $get_sms_status = $get_response['objects']['0']['status'];
                                }
                            } elseif (array_key_exists('error', $get_response)) {
                                $get_sms_status = $get_response['error']['message'];
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'ElasticEmail':

                    $sms_url = rtrim($gateway_url, '/');
                    $message = urlencode($this->message);

                    try {
                        $sms_sent_to_user = "$sms_url" . "?apikey=$gateway_user_name" . "&to=$this->cl_phone" . "&body=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($get_response);

                        if ($response->success === true) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $response->error;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'OnnorokomSMS':

                    $sms_url   = rtrim($gateway_url, '/');
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);


                    if ($msg_type == 'unicode') {
                        $sms_type = 'UCS';
                    } else {
                        $sms_type = 'TEXT';
                    }


                    try {

                        $sms_sent_to_user = $sms_url . "?op=OneToOne&type=$sms_type" . "&username=$gateway_user_name" . "&password=$gateway_password" . "&mobile=$this->cl_phone" . "&smsText=$message" . "&campaignName=";

                        if ($sender_id != '') {
                            $sms_sent_to_user .= "&maskName=$sender_id";
                        }


                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);
                        $get_data = explode('||', $get_sms_status);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('0', $get_data)) {
                            switch ($get_data[0]) {
                                case '1900':
                                    $get_sms_status = 'Success';
                                    break;

                                case '1901':
                                    $get_sms_status = 'Parameter content missing';
                                    break;

                                case '1902':
                                    $get_sms_status = 'Invalid User or Password';
                                    break;

                                case '1903':
                                    $get_sms_status = 'Not enough balance';
                                    break;

                                case '1905':
                                    $get_sms_status = 'Invalid destination number';
                                    break;

                                case '1906':
                                    $get_sms_status = 'Operator Not found';
                                    break;

                                case '1907':
                                    $get_sms_status = 'Invalid mask Name';
                                    break;

                                case '1908':
                                    $get_sms_status = 'Sms body too long';
                                    break;

                                case '1909':
                                    $get_sms_status = 'Duplicate campaign Name';
                                    break;

                                case '1910':
                                    $get_sms_status = 'Invalid message';
                                    break;

                                case '1911':
                                    $get_sms_status = 'Too many Sms Request. Please try less than 500 in one request';
                                    break;

                                default:
                                    $get_sms_status = 'Invalid request';
                                    break;

                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'PowerSMS':

                    $fields = array(
                        'userId' => urlencode($gateway_user_name),
                        'password' => urlencode($gateway_password),
                        'smsText' => urlencode($this->message),
                        'commaSeperatedReceiverNumbers' => $this->cl_phone
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_POST, count($fields));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    $result = curl_exec($ch);

                    if ($result === false) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $json_result = json_decode($result);
                        if ($json_result->isError) {
                            $get_sms_status = $json_result->message;
                        } else {
                            $get_sms_status = 'Success';
                        }
                    }
                    curl_close($ch);
                    break;


                case 'Ovh':
                    require_once(app_path('libraray/ovh/src/SmsApi.php'));
                    require_once(app_path('libraray/ovh/src/Message.php'));
                    require_once(app_path('libraray/ovh/src/MessageForResponse.php'));
                    require_once(app_path('libraray/ovh/src/Sms.php'));

                    $client_phone = '+' . $this->cl_phone;


                    $smsClient = new SmsApi($gateway_user_name, $gateway_password, $gateway_port, $gateway_extra);
                    $accounts  = $smsClient->getAccounts();
                    $smsClient->setAccount($accounts[0]);

                    $message = $smsClient->createMessage();

                    if ($this->sender_id != '') {
                        // Get declared senders
                        $senders = $smsClient->getSenders();

                        if (isset($senders) && is_array($senders) && count($senders) > 0 && in_array($this->sender_id, $senders)) {
                            $sender_id = array_search($this->sender_id, $senders);
                            $message->setSender($senders[$sender_id]);
                        } else {
                            $get_sms_status = 'Invalid Sender ID';
                        }
                    }

                    $message->addReceiver($client_phone);
                    $message->setIsMarketing(false);
                    $result = $message->send($this->message);

                    if (isset($result) && is_array($result)) {
                        if (array_key_exists('ids', $result)) {
                            if (count($result['ids']) !== 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = 'Failed';
                            }
                        } else {
                            $get_sms_status = 'Failed';
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    break;


                case '46ELKS':

                    if (is_numeric($this->cl_phone)) {
                        $phone = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    } else {
                        $phone = $this->cl_phone;
                    }

                    if (is_numeric($this->sender_id)) {
                        $sender_id = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);
                    } else {
                        $sender_id = $this->sender_id;
                    }

                    $gateway_url = rtrim($gateway_url, '/') . '/SMS';

                    $sms = [
                        "from" => $sender_id, /* Can be up to 11 alphanumeric characters */
                        "to" => $phone, /* The mobile number you want to send to */
                        "message" => $this->message
                    ];

                    $context = stream_context_create(
                        array('http' => array(
                            'method' => 'POST',
                            'header' => 'Authorization: Basic ' . base64_encode($gateway_user_name . ':' . $gateway_password) . "\r\n" . "Content-type: application/x-www-form-urlencoded\r\n",
                            'content' => http_build_query($sms),
                            'timeout' => 10)
                        )
                    );

                    $response = file_get_contents($gateway_url, false, $context);

                    if (!strstr($http_response_header[0], "200 OK")) {
                        $get_sms_status = $http_response_header[0];
                    } else {
                        $get_sms_status = json_decode($response);
                        $get_sms_status = 'Success|' . $get_sms_status->id;
                    }

                    break;

                case 'Send99':

                    $sms_url   = rtrim($gateway_url, '/');
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);
                    if ($gateway_port == 'promotional') {
                        $sms_type = 'P';
                    } else {
                        $sms_type = 'T';
                    }

                    $encoding = 'T';

                    if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                        $encoding = 'U';
                    }

                    try {

                        $sms_sent_to_user = $sms_url . "?api_id=$gateway_user_name" . "&api_password=$gateway_password" . "&phonenumber=$this->cl_phone" . "&sender_id=$sender_id" . "&textmessage=$message" . "&sms_type=$sms_type" . "&encoding=$encoding";


                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = json_decode($response);

                        if ($get_response->status == 'S') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_response->remarks;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'ChikaCampaign':
                    $phone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sms   = [
                        "from" => $this->sender_id, /* Can be up to 11 alphanumeric characters */
                        "to" => $phone, /* The mobile number you want to send to */
                        "text" => $this->message
                    ];

                    try {

                        $headers = array(
                            'Content-Type:application/json'
                        );

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":" . $gateway_password);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = json_decode($response, true);

                        if (isset($get_response) && is_array($get_response)) {
                            if (array_key_exists('requestError', $get_response)) {
                                $get_sms_status = $get_response['requestError']['serviceException']['text'];
                            } else {
                                $get_sms_status = 'Success';
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'PreciseSMS':

                    $phone   = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?apikey=$gateway_user_name" . "&recipients=$phone" . "&sender=$this->sender_id" . "&smstext=$message" . "&apiType=json";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = json_decode($response);

                        if ($get_response->errorDetails === null) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_response->errorDetails;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Otency':

                    $sms_url   = rtrim($gateway_url, '/');
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);
                    try {

                        $sms_sent_to_user = $sms_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&recipient=$this->cl_phone" . "&sender=$sender_id" . "&message=$message";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);
                        $get_data = strtolower(trim(ltrim($get_sms_status, '-')));

                        if (substr_count($get_data, 'ok') !== 0) {
                            $get_sms_status = 'Success';
                        } else {

                            switch ($get_data) {
                                case '2904':
                                    $get_sms_status = 'SMS Sending Failed';
                                    break;

                                case '2905':
                                    $get_sms_status = 'Invalid username/password combination';
                                    break;

                                case '2906':
                                    $get_sms_status = 'Credit exhausted';
                                    break;

                                case '2907':
                                    $get_sms_status = 'Gateway unavailable';
                                    break;

                                case '2908':
                                    $get_sms_status = 'Invalid schedule date format';
                                    break;

                                case '2909':
                                    $get_sms_status = 'Unable to schedule';
                                    break;

                                case '2910':
                                    $get_sms_status = 'Username is empty';
                                    break;

                                case '2911':
                                    $get_sms_status = 'Password is empty';
                                    break;

                                case '2912':
                                    $get_sms_status = 'Recipient is empty';
                                    break;

                                case '2913':
                                    $get_sms_status = 'Message is empty';
                                    break;

                                case '2914':
                                    $get_sms_status = 'Sender is empty';
                                    break;

                                case '2915':
                                    $get_sms_status = 'One or more required fields are empty';
                                    break;

                                default:
                                    $get_sms_status = 'Invalid request';
                                    break;
                            }
                        }


                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'MrMessaging':

                    $clphone   = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $message   = urlencode($this->message);
                    $sender_id = urlencode($this->sender_id);

                    try {

                        if ($msg_type == 'unicode') {
                            $coding = '2';
                        } else {
                            $coding = '0';
                        }

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&receiver=$clphone" . "&message=$message" . "&sender=$sender_id" . "&coding=$coding";

                        if (strlen($message) > 159) {
                            $sms_sent_to_user .= "&type=longsms";
                        }

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_response = curl_exec($ch);
                        $httpcode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($httpcode == '200') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = trim($get_response);
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Identidadsms':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $headers = array(
                        'Content-Type:application/json',
                        'Authorization: Basic ' . base64_encode("$gateway_user_name:$gateway_password")
                    );

                    $get_account = curl_init();
                    curl_setopt($get_account, CURLOPT_URL, 'https://api.identidadsms.net/rest/account');
                    curl_setopt($get_account, CURLOPT_HTTPGET, 1);
                    curl_setopt($get_account, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($get_account, CURLOPT_HTTPHEADER, $headers);
                    $account = curl_exec($get_account);
                    curl_close($get_account);

                    $get_data = json_decode($account);
                    $acc_id   = $get_data->id;

                    if ($acc_id) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'https://api.identidadsms.net/rest/auth');
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        $response = curl_exec($ch);
                        curl_close($ch);
                        $get_data = json_decode($response);

                        $get_token = $get_data->token;

                        if ($get_token) {
                            $send_sms_headers = array(
                                'Content-Type:application/x-www-form-urlencoded',
                                'Authorization: Bearer ' . $get_token
                            );

                            $post_field = http_build_query([
                                'acc_id' => $acc_id,
                                'to' => $clphone,
                                'from' => $this->sender_id,
                                'message' => $this->message
                            ]);


                            $process = curl_init($gateway_url);
                            curl_setopt($process, CURLOPT_HTTPHEADER, $send_sms_headers);
                            curl_setopt($process, CURLOPT_HEADER, 1);
                            curl_setopt($process, CURLOPT_TIMEOUT, 30);
                            curl_setopt($process, CURLOPT_POST, 1);
                            curl_setopt($process, CURLOPT_POSTFIELDS, $post_field);
                            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
                            $return = curl_exec($process);
                            curl_close($process);

                            $get_sms_data = json_decode($return);

                            if ($get_sms_data->message_id) {
                                $get_sms_status = 'Success|' . $get_sms_data->message_id;
                            } else {
                                $get_sms_status = 'Failed';
                            }
                        } else {
                            $get_sms_status = $get_data->error_message;
                        }
                    } else {
                        $get_sms_status = $get_data->error_message;
                    }

                    break;


                case 'Bulkness':
                case 'IntelTele':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $data    = http_build_query([
                        'username' => $gateway_user_name,
                        'api_key' => $gateway_password,
                        'from' => $this->sender_id,
                        'to' => $clphone,
                        'message' => $this->message
                    ]);

                    $gateway_url  = $gateway_url . '?' . $data;
                    $get_response = file_get_contents($gateway_url);
                    $response     = json_decode($get_response);

                    if (isset($response) && isset($response->reply)) {
                        if (isset($response->reply[0]->status)) {
                            if ($response->reply[0]->status == 'OK') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response->reply[0]->status;
                            }
                        } else {
                            $get_sms_status = $get_response;
                        }
                    } else if (isset($response) && isset($response->error)) {
                        if (isset($response->message)) {
                            $get_sms_status = $response->message;
                        } elseif (isset($response->status)) {
                            $get_sms_status = $response->message;
                        } else {
                            $get_sms_status = $get_response;
                        }
                    } else {
                        $get_sms_status = $get_response;
                    }

                    break;


                case 'SMSKitNet':
                case 'PenSMS':

                    $clphone   = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);

                    if ($msg_type == 'unicode') {
                        $msg_type = 'unicode';
                    } else {
                        $msg_type = 'sms';
                    }

                    try {

                        $sms_sent_to_user = $gateway_url . "?sendsms&apikey=$gateway_user_name" . "&apitoken=$gateway_password" . "&from=$sender_id" . "&to=$clphone" . "&text=$message" . "&type=$msg_type";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $result = json_decode($get_data, true);

                        if (isset($result) && is_array($result) && array_key_exists('status', $result)) {
                            if ($result['status'] == 'queued') {
                                $get_sms_status = 'Success';
                            } elseif ($result['status'] == 'error') {
                                $get_sms_status = $result['message'];
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'PhilmoreSMS':
                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $data = [
                        'action' => 'compose',
                        'username' => $gateway_user_name,
                        'api_key' => $gateway_password,
                        'sender' => $this->sender_id,
                        'to' => $clphone,
                        'message' => $this->message,
                    ];

                    if ($msg_type == 'unicode') {
                        $data['unicode'] = 1;
                    }

                    $data = http_build_query($data);

                    try {

                        $gateway_url = rtrim($gateway_url, '/') . '/?' . $data;

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($get_sms_status, 'Sent Successfully') !== false) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Clockworksms':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $data = [
                        'key' => $gateway_user_name,
                        'to' => $clphone,
                        'content' => $this->message
                    ];

                    if ($this->sender_id) {
                        $data['from'] = $this->sender_id;
                    }

                    if (strlen($this->message) > 159) {
                        $data['long'] = 1;
                    }

                    $data = http_build_query($data);


                    try {

                        $gateway_url = $gateway_url . '?' . $data;

                        $get_sms_status = file_get_contents($gateway_url);

                        if (strpos($get_sms_status, 'ID') !== false) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'OnewaySMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $data = [
                        'apiusername' => $gateway_user_name,
                        'apipassword' => $gateway_password,
                        'mobileno' => $clphone,
                    ];

                    if ($this->sender_id) {
                        $data['senderid'] = $this->sender_id;
                    }

                    if ($msg_type == 'unicode') {
                        $data['languagetype'] = '2';
                        $data['message']      = $this->sms_unicode($this->message);
                    } else {
                        $data['languagetype'] = '1';
                        $data['message']      = stripcslashes($this->message);
                    }

                    $data = http_build_query($data);

                    try {
                        $gateway_url    = $gateway_url . '?' . $data;
                        $get_sms_status = file_get_contents($gateway_url);

                        $get_sms_status = trim($get_sms_status);

                        if ($get_sms_status >= 0) {
                            $get_sms_status = 'Success';
                        } elseif ($get_sms_status == '-100') {
                            $get_sms_status = 'Api username or password is invalid';
                        } elseif ($get_sms_status == '-200') {
                            $get_sms_status = 'Sender ID parameter is invalid';
                        } elseif ($get_sms_status == '-300') {
                            $get_sms_status = 'Mobile Number parameter is invalid';
                        } elseif ($get_sms_status == '-400') {
                            $get_sms_status = 'Language type is invalid';
                        } elseif ($get_sms_status == '-500') {
                            $get_sms_status = 'Invalid characters in message';
                        } elseif ($get_sms_status == '-600') {
                            $get_sms_status = 'Insufficient credit balance';
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'SignalWire':

                    $clphone   = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sender_id = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);

                    $post_field = http_build_query([
                        'From' => trim($sender_id),
                        'Body' => $this->message,
                        'To' => trim($clphone)
                    ]);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "$gateway_url/api/laml/2010-04-01/Accounts/$gateway_user_name/Messages.json");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field);
                    curl_setopt($ch, CURLOPT_USERPWD, "$gateway_user_name" . ":" . "$gateway_password");

                    $get_response = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);


                    $result = json_decode($get_response, true);

                    if (isset($result) && is_array($result) && array_key_exists('status', $result) && array_key_exists('error_code', $result)) {
                        if ($result['status'] == 'queued' && $result['error_code'] === null) {
                            $get_sms_status = 'Success|' . $result['sid'];
                        } else {
                            $get_sms_status = $result['error_message'];
                        }
                    } else if (isset($result) && is_array($result) && array_key_exists('status', $result) && array_key_exists('message', $result)) {
                        $get_sms_status = $result['message'];
                    } else {
                        $get_sms_status = $get_response;
                    }

                    if ($get_sms_status === null) {
                        $get_sms_status = 'Check your settings';
                    }
                    break;

                case 'MsgOnDND':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    if ($msg_type == 'unicode') {
                        $msg_type = 'unicode';
                    } else {
                        $msg_type = 'english';
                    }

                    $data = http_build_query([
                        'AUTH_KEY' => $gateway_user_name,
                        'message' => $this->message,
                        'senderId' => $this->sender_id,
                        'routeId' => $gateway_password,
                        'mobileNos' => $clphone,
                        'smsContentType' => $msg_type
                    ]);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $result = json_decode($get_data, true);

                        if (isset($result) && is_array($result) && array_key_exists('responseCode', $result)) {
                            if ($result['responseCode'] == '3001') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $result['response'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'Mocean':

                    $fields = [
                        'mocean-api-key' => $gateway_user_name,
                        'mocean-api-secret' => $gateway_password,
                        'mocean-from' => $this->sender_id,
                        'mocean-to' => $this->cl_phone,
                        'mocean-text' => $this->message,
                        'mocean-resp-format' => 'json',
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_POST, count($fields));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $result = curl_exec($ch);

                    if ($result === false) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $json_result = json_decode($result, true);

                        if (isset($json_result) && is_array($json_result)) {

                            if (array_key_exists('messages', $json_result) && isset($json_result['messages']['0']['status'])) {
                                if ($json_result['messages']['0']['status'] == 0) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $json_result['messages'];
                                }
                            } else {
                                $get_sms_status = $json_result['err_msg'];
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    }
                    curl_close($ch);

                    break;


                case 'Telnyx':

                    $phone     = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sender_id = str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);

                    if (is_numeric($sender_id)) {
                        $sender_id = '+' . $sender_id;
                    } else {
                        $sender_id = $this->sender_id;
                    }

                    $sms = [
                        "from" => $sender_id,
                        "to" => $phone,
                        "text" => $this->message,
                        "messaging_profile_id" => $gateway_password
                    ];

                    try {

                        $headers = array(
                            'Content-Type:application/json',
                            'Accept: application/json',
                            'Authorization: Bearer ' . $gateway_user_name
                        );

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        $response = curl_exec($ch);
                        curl_close($ch);
                        $get_response = json_decode($response, true);

                        if (isset($get_response) && is_array($get_response)) {
                            if (array_key_exists('data', $get_response) && isset($get_response['data']['to'][0]['status']) && $get_response['data']['to'][0]['status'] == 'queued') {
                                $get_sms_status = 'Success';
                            } elseif (array_key_exists('errors', $get_response) && isset($get_response['errors'][0]['title'])) {
                                $get_sms_status = $get_response['errors'][0]['title'];
                            } else {
                                $get_sms_status = $response;
                            }
                        } else {
                            $get_sms_status = $response;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'Mobivate':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);


                    $data = http_build_query([
                        'USER_NAME' => $gateway_user_name,
                        'PASSWORD' => $gateway_password,
                        'MESSAGE_TEXT' => $this->message,
                        'ORIGINATOR' => $this->sender_id,
                        'ROUTE' => 'mglobal',
                        'RECIPIENT' => $clphone
                    ]);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($get_data, 'true') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'Digimiles':

                    $sender_id = urlencode($this->sender_id);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {

                        if ($msg_type == 'unicode') {
                            $type    = 2;
                            $message = $this->sms_unicode($this->message);
                        } else {
                            $type    = 0;
                            $message = urlencode($this->message);
                        }

                        $sms_sent_to_user = "$sms_url" . "/bulksms/bulksms?type=$type" . "&username=$gateway_user_name" . "&password=$gateway_password" . "&destination=$this->cl_phone" . "&source=$sender_id" . "&message=$message" . "&dlr=0";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $headers   = array();
                        $headers[] = "Content-Type: application/x-www-form-urlencoded";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);
                        $get_data = explode('|', $get_sms_status);
                        if (is_array($get_data) && array_key_exists('0', $get_data)) {
                            switch ($get_data[0]) {
                                case '1701':
                                    $get_sms_status = 'Success';
                                    break;

                                case '1702':
                                    $get_sms_status = 'Invalid URL';
                                    break;

                                case '1703':
                                    $get_sms_status = 'Invalid User or Password';
                                    break;

                                case '1704':
                                    $get_sms_status = 'Invalid Type';
                                    break;

                                case '1705':
                                    $get_sms_status = 'Invalid SMS';
                                    break;

                                case '1706':
                                    $get_sms_status = 'Invalid receiver';
                                    break;

                                case '1707':
                                    $get_sms_status = 'Invalid sender';
                                    break;

                                case '1709':
                                    $get_sms_status = 'User Validation Failed';
                                    break;

                                case '1710':
                                    $get_sms_status = 'Internal Error';
                                    break;

                                case '1715':
                                    $get_sms_status = 'Response Timeout';
                                    break;

                                case '1025':
                                    $get_sms_status = 'Insufficient Credit';
                                    break;

                                default:
                                    $get_sms_status = 'Invalid request';
                                    break;

                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'Sendpulse':

                    try {

                        $sp_api_client = new \Sendpulse\RestApi\ApiClient($gateway_user_name, $gateway_password);

                        $phones = [
                            $this->cl_phone
                        ];

                        $params = [
                            'sender' => $this->sender_id,
                            'body' => $this->message,
                            'transliterate' => 0
                        ];

                        $get_data = $sp_api_client->sendSmsByList($phones, $params, []);

                        if (isset($get_data) && isset($get_data->result) && $get_data->result == 1) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = (string)$get_data;
                        }
                    } catch (\Exception $exception) {
                        $get_sms_status = $exception->getMessage();
                    }
                    break;


                case 'APIWHA':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);


                    $data = http_build_query([
                        'apikey' => $gateway_user_name,
                        'number' => $clphone,
                        'text' => $this->message
                    ]);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        $get_data = json_decode($get_data);

                        if (isset($get_data) && isset($get_data->result_code) && $get_data->result_code == 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data->description;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'SMSAPIOnline':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);


                    if ($msg_type == 'unicode') {
                        $type    = 'unicode';
                        $message = utf8_encode($this->message);
                    } else {
                        $type    = 'text';
                        $message = $this->message;
                    }

                    $data = http_build_query([
                        'key' => $gateway_user_name,
                        'campaign' => 'international',
                        'routeid' => $gateway_password,
                        'type' => $type,
                        'contacts' => $clphone,
                        'senderid' => $this->sender_id,
                        'msg' => $this->message
                    ]);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($get_sms_status, 'SMS-SHOOT-ID') !== false) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'ImapServiceOnline':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $data = [
                        'action' => 'send-sms',
                        'api_key' => $gateway_user_name,
                        'to' => $clphone,
                        'from' => $this->sender_id,
                        'sms' => $this->message
                    ];

                    if ($msg_type == 'unicode') {
                        $data['unicode'] = 1;
                    }

                    $data = http_build_query($data);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;
                        $ch          = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        $get_data = json_decode($get_sms_status);

                        if (isset($get_data) && isset($get_data->code) && $get_data->code == 'ok') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $get_data->message;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'FairPlayerSMS':

                    include_once app_path('Classes/FairPlayerSms.php');

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    try {
                        // Prepare the result
                        $result = \FairPlayerSms::getInstance(
                        // API SMS Gateway URL
                            $gateway_url,
                            // API Key
                            $gateway_user_name,
                            // API Salt
                            $gateway_password
                        )->send($clphone, $this->message);

                        if (isset($result) && is_array($result) && array_key_exists('1', $result)) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $exc) {
                        // SMS not sent
                        $get_sms_status = $exc->getMessage();
                    }

                    break;

                case 'IntelliMessaging':
                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $data = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'recipient' => $clphone,
                        'text' => $this->message
                    ];

                    if ($this->sender_id != '') {
                        $data['sender'] = $this->sender_id;
                    }

                    $data = http_build_query($data);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;
                        $ch          = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);
                        curl_close($ch);

                        $get_data = json_decode($get_sms_status);

                        if (isset($get_data) && isset($get_data->messageStatus)) {
                            $status = trim($get_data->messageStatus);
                            switch ($status) {
                                case '10':
                                    $get_sms_status = 'Success';
                                    break;

                                case '-999':
                                    $get_sms_status = 'Internal error';
                                    break;

                                case '-910':
                                    $get_sms_status = 'SMSC Error';
                                    break;

                                case '-240':
                                    $get_sms_status = 'Invalid replyto value';
                                    break;

                                case '-230':
                                    $get_sms_status = 'Documentation id not match';
                                    break;

                                case '-210':
                                    $get_sms_status = 'Insufficient credits';
                                    break;

                                case '-202':
                                    $get_sms_status = 'User blocked';
                                    break;

                                case '-201':
                                    $get_sms_status = 'User disabled';
                                    break;

                                case '-200':
                                    $get_sms_status = 'Invalid username/password';
                                    break;

                                case '-109':
                                    $get_sms_status = 'Invalid rotation algorithm provided';
                                    break;

                                case '-108':
                                    $get_sms_status = 'Both sender and replyTo provided';
                                    break;

                                case '-107':
                                    $get_sms_status = 'Missing sender or replyTo';
                                    break;

                                case '-106':
                                    $get_sms_status = 'Empty text';
                                    break;

                                case '-105':
                                    $get_sms_status = 'Invalid source';
                                    break;

                                case '-104':
                                    $get_sms_status = 'Invalid recipient';
                                    break;

                                case '-103':
                                    $get_sms_status = 'No document id';
                                    break;

                                case '-102':
                                    $get_sms_status = 'No password';
                                    break;

                                case '-101':
                                    $get_sms_status = 'No username';
                                    break;

                                default:
                                    $get_sms_status = 'Invalid request';
                                    break;
                            }

                        } else {
                            $get_sms_status = $get_data->message;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'FlowRoute':
                    $phone     = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);
                    $sender_id = str_replace(['+', '(', ')', '-', " "], '', $this->sender_id);

                    $sms = [
                        "from" => $sender_id,
                        "to" => $phone,
                        "body" => $this->message
                    ];

                    try {

                        $headers   = array();
                        $headers[] = 'Content-Type: application/vnd.api+json';

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ':' . $gateway_password);

                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = json_decode($response, true);

                        if (isset($get_response) && is_array($get_response)) {
                            if (array_key_exists('data', $get_response)) {
                                $get_sms_status = 'Success';
                            } elseif (array_key_exists('errors', $get_response)) {
                                $get_sms_status = $get_response['errors'][0]['detail'];
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }
                    break;


                case 'Trumpia':

                    $phone       = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $gateway_url = rtrim($gateway_url, '/') . "/" . $gateway_user_name . "/sms";


                    if (strlen($phone) > 10) {
                        $client_phone = ltrim($phone, '1');
                    } else {
                        $client_phone = $phone;
                    }

                    $sms = array(
                        "mobile_number" => $client_phone,
                        "message" => $this->message
                    );

                    try {

                        $headers = array(
                            'Content-Type:application/json',
                            'X-Apikey: ' . $gateway_password
                        );

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        $get_response = json_decode($response, true);

                        if (isset($get_response) && is_array($get_response) && array_key_exists('status_code', $get_response)) {
                            if ($get_response['status_code'] == 'MPCE4001') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_response['status_code'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'FortyTwo':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'destinations' => [
                            [
                                'number' => $clphone
                            ]
                        ],
                        'sms_content' => [
                            'sender_id' => $this->sender_id,
                            'message' => $this->message,
                            'route' => $gateway_extra
                        ]
                    ];

                    if ($msg_type == 'unicode') {
                        $parameters['sms_content']['encoding'] = 'UCS2';
                    }

                    $data = json_encode($parameters);

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $gateway_url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $data,
                        CURLOPT_HTTPHEADER => array(
                            "authorization: Token $gateway_user_name",
                            "content-type: application/json; charset=utf-8"
                        ),
                    ));
                    $response = curl_exec($curl);
                    $err      = curl_error($curl);
                    curl_close($curl);

                    if ($err) {
                        $get_sms_status = $err;
                    } else {
                        $response = json_decode($response, true);
                        if (isset($response) && is_array($response) && array_key_exists('result_info', $response)) {
                            if (is_array($response['result_info']) && array_key_exists('status_code', $response['result_info']) && array_key_exists('description', $response['result_info'])) {
                                if ($response['result_info']['status_code'] == 200) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $response['result_info']['description'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    }
                    break;

                case 'SMSCountry':
                    if ($msg_type == 'unicode') {
                        $type = 'LNG';
                    } else {
                        $type = 'N';
                    }

                    $parameters = http_build_query([
                        'User' => $gateway_user_name,
                        'passwd' => $gateway_password,
                        'mobilenumber' => $this->cl_phone,
                        'message' => $this->message,
                        'sid' => $this->sender_id,
                        'mtype' => $type
                    ]);


                    $ch = curl_init();

                    $ret = curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
                    $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    $get_sms_status = curl_exec($ch); // execute

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    curl_close($ch); // close cURL handler

                    if (preg_match("(sent|ok)", strtolower($get_sms_status)) === 1) {
                        $get_sms_status = 'Success';
                    } else {
                        $get_sms_status = trim($get_sms_status);
                    }

                    break;

                case 'GoIpHTTP':
                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $data = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'recipients' => $clphone,
                        'sms' => $this->message
                    ];

                    $data = http_build_query($data);

                    $gateway_url = $gateway_url . '?' . $data;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);

                    $get_sms_status = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch); // close cURL handler

                    $get_data = json_decode($get_sms_status, true);

                    if (isset($get_data) && is_array($get_data) && array_key_exists('code', $get_data)) {
                        if ($get_data['code'] == 0) {
                            $get_sms_status = 'Success';
                        } elseif (array_key_exists('reason', $get_data)) {
                            $get_sms_status = $get_data['reason'];
                        } elseif (array_key_exists('desc', $get_data)) {
                            $get_sms_status = $get_data['desc'];
                        } else {
                            $get_sms_status = 'Failed';
                        }
                    }
                    break;


                case '019SMS':

                    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sms/>');

                    $authentication = $xml->addChild('user');
                    $authentication->addChild('username', $gateway_user_name);
                    $authentication->addChild('password', $gateway_password);

                    $xml->addChild('source', $this->sender_id);

                    if ($this->sender_id){
                        $xml->addChild('response', 1);
                    }else {
                        $xml->addChild('response', 0);
                    }

                    $destination = $xml->addChild('destinations');
                    $destination->addChild('phone', $this->cl_phone);
                    $xml->addChild('message', $this->message);
                    $sms_data = $xml->asXML();

                    $CR = curl_init();
                    curl_setopt($CR, CURLOPT_URL, $gateway_url);
                    curl_setopt($CR, CURLOPT_POST, 1);
                    curl_setopt($CR, CURLOPT_FAILONERROR, true);
                    curl_setopt($CR, CURLOPT_POSTFIELDS, $sms_data);
                    curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($CR, CURLOPT_HTTPHEADER, array("charset=utf-8"));
                    $result = curl_exec($CR);
                    $error  = curl_error($CR);

                    if (!empty($error)) {
                        $get_sms_status = $error;
                    } else {
                        $response = new \SimpleXMLElement($result);
                        if (isset($response) && isset($response->status)) {
                            if ($response->status == 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response->message;
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    }

                    break;

                case 'Bics':

                    $clphone = 'tel:+' . $this->cl_phone;

                    $url = $gateway_url . '/' . $this->sender_id . '/requests';

                    $parameters = [
                        'outboundSMSMessageRequest' => [
                            'address' => [$clphone],
                            'senderAddress' => $this->sender_id,
                            'outboundSMSTextMessage' => [
                                'message' => $this->message
                            ],
                            "clientCorrelator" => uniqid(),
                            'senderName' => $this->sender_id
                        ]
                    ];

                    $json = json_encode($parameters);

                    try {
                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                        curl_setopt($ch, CURLOPT_VERBOSE, true);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Authorization: Bearer $gateway_user_name",
                            "Content-Type: application/json",
                            "Content-Length: " . strlen($json)
                        ));

                        $result = curl_exec($ch);
                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }
                        curl_close($ch);

                        $get_data = json_decode($result, true);

                        if (is_array($get_data) && array_key_exists('outboundSMSMessageRequest', $get_data)) {
                            if (isset($get_data['outboundSMSMessageRequest']['deliveryInfoList']['deliveryInfo']['0']['deliveryStatus'])) {
                                if ($get_data['outboundSMSMessageRequest']['deliveryInfoList']['deliveryInfo']['0']['deliveryStatus'] == 'MessageWaiting' || $get_data['outboundSMSMessageRequest']['deliveryInfoList']['deliveryInfo']['0']['deliveryStatus'] == 'DeliveredToNetwork') {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $get_data['outboundSMSMessageRequest']['deliveryInfoList']['deliveryInfo']['0']['deliveryStatus'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Movistar':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $headers = array(
                        'Content-Type:application/json',
                        'X-Show-Extended-Info: True',
                        'Content-Length: 0',
                        'Connection: Keep-Alive',
                        'Authorization: Basic ' . base64_encode("$gateway_user_name:$gateway_password")
                    );

                    $sms_data = http_build_query([
                        'msisdn' => trim($clphone),
                        'texto' => trim($this->message)
                    ]);

                    $url = $gateway_url . '/SmsApi/TargetCall/Default/EnviarSms?' . $sms_data;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $get_data = json_decode($response, true);

                    if (is_array($get_data) && array_key_exists('Respuesta', $get_data)) {
                        if (isset($get_data['Respuesta']['Status'])) {
                            if ($get_data['Respuesta']['Status'] == '200') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['Respuesta']['Descripcion'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }
                    break;

                case 'GoIP':

                    $context = stream_context_create(array(
                        'http' => array(
                            'header' => "Authorization: Basic " . base64_encode("$gateway_user_name:$gateway_password")
                        )
                    ));

                    $message = urlencode($this->message);

                    try {
                        $get_sms_status = file_get_contents($gateway_url . "/default/en_US/send.html?u=$gateway_user_name&p=$gateway_password&l=1&n=$this->cl_phone&m=$message", false, $context);

                        if (strpos($get_sms_status, 'Sending') !== false) {
                            $get_sms_status = 'Success';
                        }
                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }
                    break;


                case 'SmsJust':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'username' => $gateway_user_name,
                        'pass' => $gateway_password,
                        'senderid' => $this->sender_id,
                        'dest_mobileno' => $clphone,
                        'message' => $this->message,
                        'response' => 'Y',
                    ];

                    if ($msg_type == 'unicode') {
                        $parameters['msgType'] = 'UNI';
                    } else {
                        $parameters['msgType'] = 'TXT';
                    }


                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $get_sms_status = str_replace(['-', '_'], '', $get_sms_status);

                        if (ctype_digit($get_sms_status)) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'MultiTexter':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'sender' => $this->sender_id,
                        'recipients' => $clphone,
                        'message' => $this->message,
                    ];

                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $get_data = trim($get_sms_status);

                        switch ($get_data) {
                            case '100':
                                $get_sms_status = 'Success';
                                break;

                            case '-1':
                                $get_sms_status = 'Incorrect / badly formed URL data';
                                break;

                            case '-2':
                                $get_sms_status = 'Incorrect username and/or password';
                                break;

                            case '-3':
                                $get_sms_status = 'Not enough credit units in user account';
                                break;

                            case '-4':
                                $get_sms_status = 'Invalid sender name';
                                break;

                            case '-5':
                                $get_sms_status = 'No valid recipient';
                                break;

                            case '-6':
                                $get_sms_status = 'Invalid message length/No message content';
                                break;

                            case '-10':
                                $get_sms_status = 'Unknown/Unspecified error';
                                break;

                            default:
                                $get_sms_status = 'Invalid request';
                                break;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'TheSMSWorks':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $authentication = [
                        'customerid' => $gateway_user_name,
                        'key' => $gateway_password,
                        'secret' => $gateway_extra
                    ];

                    $auth_data = json_encode($authentication);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, 'https://api.thesmsworks.co.uk/v1/auth/token');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $auth_data);
                    curl_setopt($ch, CURLOPT_POST, 1);

                    $headers   = array();
                    $headers[] = "Content-Type: application/json";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }
                    curl_close($ch);

                    $get_data = json_decode($result, true);

                    if (isset($get_data) && is_array($get_data) && array_key_exists('token', $get_data)) {
                        $auth_token = $get_data['token'];
                        $parameters = [
                            'sender' => $this->sender_id,
                            'destination' => $clphone,
                            'content' => $this->message,
                            'schedule' => ''
                        ];

                        $url = rtrim($gateway_url, '/') . '/message/send';

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
                        curl_setopt($ch, CURLOPT_POST, 1);

                        $headers   = array();
                        $headers[] = "Authorization: $auth_token";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }
                        curl_close($ch);

                        $get_data = json_decode($result, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('status', $get_data)) {
                            if ($get_data['status'] == 'SENT') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['message'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } else {
                        $get_sms_status = 'Auth token not found';
                    }
                    break;

                case 'XOXZO':

                    $phone = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sms   = [
                        "sender" => $this->sender_id, /* Can be up to 11 alphanumeric characters */
                        "recipient" => $phone, /* The mobile number you want to send to */
                        "message" => $this->message
                    ];

                    $data = http_build_query($sms);

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":" . $gateway_password);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_POSTREDIR, 3);

                        $get_sms_status = curl_exec($ch);
                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }
                        curl_close($ch);

                        if (strpos($get_sms_status, 'msgid') !== false) {
                            $get_sms_status = 'Success';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Iletimx':

                    $phone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $strXML = "<MainmsgBody><UserName>$gateway_user_name</UserName><PassWord>$gateway_password</PassWord><Action>0</Action><Mesgbody>$this->message</Mesgbody><Numbers>$phone</Numbers><Originator>$this->sender_id</Originator><SDate></SDate></MainmsgBody>";

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_POST, TRUE);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $strXML);

                        $result = curl_exec($ch);

                        curl_close($ch);

                        $result = trim($result);

                        if (strpos($result, 'ID:') !== false) {
                            $get_sms_status = 'Success';
                        } else {
                            switch ($result) {
                                case '01':
                                    $get_sms_status = 'Username or password is incorrect';
                                    break;

                                case '02':
                                    $get_sms_status = 'Low Credit';
                                    break;
                                case '03':
                                    $get_sms_status = 'Action parameter is incorrect';
                                    break;

                                case '05':
                                    $get_sms_status = 'Xml is incorrect';
                                    break;

                                case '06':
                                    $get_sms_status = 'Sender name incorrect';
                                    break;

                                case '07':
                                    $get_sms_status = 'messageID is incorrect';
                                    break;

                                case '09':
                                    $get_sms_status = 'Date is incorrect';
                                    break;

                                case '10':
                                    $get_sms_status = 'System error';
                                    break;

                                default:
                                    $get_sms_status = 'Invalid request';
                                    break;
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'SMSHubsNet':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'email' => $gateway_user_name,
                        'key' => $gateway_password,
                        'sender' => $this->sender_id,
                        'recipient' => $clphone,
                        'message' => $this->message
                    ];

                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $response = new \SimpleXMLElement($get_sms_status);


                        if (isset($response) && isset($response->statusCode) && isset($response->statusMsg)) {
                            if ($response->statusCode == 1606) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response->statusMsg;
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'WhereWeChat':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'access_token' => $gateway_user_name,
                        'service' => $gateway_password,
                        'sender' => $this->sender_id,
                        'to' => $clphone,
                        'message' => $this->message
                    ];

                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '/sms/send?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $get_data = json_decode($get_sms_status, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('status', $get_data)) {
                            if ($get_data['status'] == '200') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['message'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'KSMSUS':


                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'action' => 'send-sms',
                        'api_key' => $gateway_user_name,
                        'from' => $this->sender_id,
                        'to' => $clphone,
                        'sms' => $this->message
                    ];

                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $get_data = json_decode($get_sms_status, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('code', $get_data)) {
                            if ($get_data['code'] == 'ok') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['message'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;

                case 'ZamtelCoZm':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'orgid' => $gateway_extra,
                        'jobid' => time(),
                        'senderid' => $this->sender_id,
                        'msisdn' => $clphone,
                        'message' => $this->message
                    ];


                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;


                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $get_sms_status = trim($get_sms_status);

                        if (strpos($get_sms_status, 'delivery00') !== false) {
                            $get_sms_status = 'Success';
                        } else {

                            switch ($get_sms_status) {
                                case '01':
                                    $get_sms_status = 'Invalid Password';
                                    break;

                                case '02':
                                    $get_sms_status = 'Invalid Organisation ID';
                                    break;

                                case '03':
                                    $get_sms_status = 'SMS Account Balance Depleted/Invalid Sender ID';
                                    break;

                                case '10':
                                    $get_sms_status = 'Invalid MSISDN';
                                    break;

                                case '11':
                                    $get_sms_status = 'Invalid number of digits in MSISDN';
                                    break;

                                case '12':
                                    $get_sms_status = 'Invalid MSISDN format';
                                    break;

                                case '20':
                                    $get_sms_status = 'Invalid Message';
                                    break;

                                default:
                                    $get_sms_status = 'Invalid request';
                                    break;
                            }
                        }
                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }
                    break;


                case 'AfricasTalking':

                    $clphone = '+' . str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $AT = new AfricasTalking($gateway_user_name, $gateway_password);

                    $sms = $AT->sms();

                    $parameters = [
                        'to' => $clphone,
                        'message' => $this->message
                    ];

                    if ($this->sender_id != '') {
                        $parameters['from'] = $this->sender_id;
                    }

                    try {

                        $get_data = $sms->send($parameters);
                        if (isset($get_data) && is_array($get_data) && array_key_exists('data', $get_data) && array_key_exists('status', $get_data) && isset($get_data['data']->SMSMessageData)) {

                            if (strpos($get_data['data']->SMSMessageData->Message, 'Sent') !== false) {
                                $get_sms_status = 'Success|' . $get_data['data']->SMSMessageData->Recipients[0]->messageId;
                            } else {
                                $get_sms_status = $get_data['data']->SMSMessageData->Message;
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'Apifon':

                    $endpoint = "/services/sms/send";

                    $parameters = [
                        'message' => [
                            'text' => $this->message,
                            'sender_id' => $this->sender_id
                        ],
                        'subscribers' => [
                            [
                                'number' => $this->cl_phone
                            ]
                        ]
                    ];

                    $body = json_encode($parameters);

                    $dateTime = new \DateTime();
                    $dateTime->setTimezone(new \DateTimeZone('GMT'));
                    $requestDate = $dateTime->format('D, d M Y H:i:s T');

                    $message = "POST" . "\n"
                        . $endpoint . "\n"
                        . $body . "\n"
                        . $requestDate;

                    $signature = base64_encode(hash_hmac('SHA256', $message, $gateway_user_name, true));

                    $header   = array();
                    $header[] = "Content-type: application/json";
                    $header[] = "Authorization: ApifonWS " . $gateway_password . ":" . $signature;
                    $header[] = "X-ApifonWS-Date: " . $requestDate;


                    try {

                        $curl = curl_init($gateway_url);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        $response = curl_exec($curl);

                        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                        if (curl_errno($curl)) {
                            $get_sms_status = curl_error($curl);
                        }

                        curl_close($curl);

                        $http_code = trim($httpcode);

                        switch ($http_code) {
                            case '200':
                                $get_data = json_decode($response, true);

                                if (isset($get_data) && is_array($get_data) && array_key_exists('result_info', $get_data)) {
                                    if ($get_data['result_info']['status_code'] == '200') {
                                        $get_sms_status = 'Success';
                                    } else {
                                        $get_sms_status = $get_data['result_info']['description'];
                                    }
                                } else {
                                    $get_sms_status = 'Invalid request';
                                }
                                break;

                            case '400':
                                $get_sms_status = 'Bad Request – The request is invalid and was not understood by the API.';
                                break;

                            case '401':
                                $get_sms_status = 'Unauthorized – Header “Authorization” missing, invalid, or revoked; and / or, your host IP is not in the authorized IPs list.';
                                break;

                            case '403':
                                $get_sms_status = 'The request contains invalid or illegal values.';
                                break;

                            case '404':
                                $get_sms_status = 'Not Found – The endpoint on which the request was sent to, does not exist, or does not implement the API requested.';
                                break;

                            case '405':
                                $get_sms_status = 'Method Not Allowed – If the endpoint received a request using an HTTP method (ex. GET instead of POST) that is not allowed by that endpoint.';
                                break;

                            case '415':
                                $get_sms_status = 'Unsupported Media Type – If the request was in a content-type not supported by the endpoint (ex. text/plain instead of application/json).';
                                break;

                            case '429':
                                $get_sms_status = 'Too many requests (Throttling).';
                                break;

                            case '500':
                                $get_sms_status = 'Internal Server Error.';
                                break;

                            default:
                                $get_sms_status = 'Invalid Request';
                                break;

                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }
                    break;

                case 'BrilliantComBD':

                    $parameters = [
                        'to' => $this->cl_phone,
                        'from' => $this->sender_id,
                        'content' => $this->message
                    ];


                    if ($msg_type == 'unicode') {
                        $parameters['Coding'] = '8';
                    } else {
                        $parameters['Coding'] = '0';
                    }


                    $body = json_encode($parameters);

                    $header   = array();
                    $header[] = "Content-type: application/json";
                    $header[] = "login: " . $gateway_user_name;
                    $header[] = "password: " . $gateway_password;

                    try {

                        $curl = curl_init($gateway_url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        $response = curl_exec($curl);

                        if (curl_errno($curl)) {
                            $get_sms_status = curl_error($curl);
                        }

                        curl_close($curl);

                        $get_data = json_decode($response, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('errorcode', $get_data)) {
                            if ($get_data['errorcode'] == '0') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['errormsg'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;

                case 'ProSMSGr':

                    $parameters = [
                        'originator' => $this->sender_id,
                        'mobile_number' => $this->cl_phone,
                        'text' => $this->message
                    ];

                    if (strlen($this->message) > 159) {
                        $parameters['concatenated'] = true;
                    }

                    $data = http_build_query($parameters);

                    $gateway_url = $gateway_url . '?' . $data;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":" . $gateway_password);
                    $get_sms_status = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    curl_close($ch);


                    if (strpos($get_sms_status, '1|') !== false) {
                        $get_sms_status = 'Success';
                    }

                    break;

                case 'MovileCom':

                    $parameters = [
                        'destination' => $this->cl_phone,
                        'messageText' => $this->message
                    ];

                    $body = json_encode($parameters);

                    $header   = array();
                    $header[] = "authenticationtoken: " . $gateway_password;
                    $header[] = "username: " . $gateway_user_name;
                    $header[] = "Content-type: application/json";

                    try {

                        $curl = curl_init($gateway_url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        $response = curl_exec($curl);

                        if (curl_errno($curl)) {
                            $get_sms_status = curl_error($curl);
                        }
                        curl_close($curl);


                        $get_data = json_decode($response, true);


                        if (isset($get_data) && is_array($get_data)) {
                            if (array_key_exists('id', $get_data)) {
                                $get_sms_status = 'Success|' . $get_data['id'];
                            } elseif (array_key_exists('errorMessage', $get_data)) {
                                $get_sms_status = $get_data['errorMessage'];
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;

                case 'SMSTO':

                    $parameters = [
                        'to' => '+' . $this->cl_phone,
                        'body' => $this->message,
                        'sender_id' => $this->sender_id
                    ];

                    $body = json_encode($parameters);

                    $header   = array();
                    $header[] = "Authorization: Bearer " . $gateway_user_name;
                    $header[] = "Accept: application/json";
                    $header[] = "Content-type: application/json";

                    try {

                        $curl = curl_init($gateway_url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        $response = curl_exec($curl);

                        if (curl_errno($curl)) {
                            $get_sms_status = curl_error($curl);
                        }
                        curl_close($curl);

                        $get_data = json_decode($response, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('success', $get_data)) {
                            if ($get_data['success'] == true) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['message'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;


                case 'TwizoCom':

                    $data = array(
                        'recipients' => array(
                            $this->cl_phone,
                        ),
                        'body' => $this->message,
                        'sender' => $this->sender_id,
                    );


                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_USERPWD, 'twizo' . ':' . $gateway_user_name);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'Content-Length: ' . strlen(json_encode($data)))
                        );

                        $response = curl_exec($ch);

                        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                        curl_close($ch);

                        $http_code = trim($httpcode);

                        $get_data = json_decode($response, true);

                        if ($http_code == 200 || $http_code == 201) {
                            if (isset($get_data['_embedded']['items']['0']['statusCode']) && $get_data['_embedded']['items']['0']['statusCode'] == 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['_embedded']['items']['0']['status'];
                            }
                        } else {
                            if (isset($get_data) && is_array($get_data) && array_key_exists('status', $get_data)) {
                                if ($get_data['status'] == 200) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $get_data['detail'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }


                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;


                case 'iBulky':

                    $message = urlencode($this->message);

                    $gateway_url = $gateway_url . "?username=" . $gateway_user_name . "&password=" . $gateway_password . "&recipient=" . $this->cl_phone . "&sender=" . $this->sender_id . "&message=" . $message . "&msgtype=text";

                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $output = curl_exec($ch);
                    curl_close($ch);

                    $get_sms_status = trim($output);


                    if (strpos($get_sms_status, 'SENT') !== false) {
                        $get_sms_status = 'Success';
                    } else {
                        switch ($get_sms_status) {

                            case '2499':
                                $get_sms_status = 'API is currently NOT active';
                                break;

                            case '2500':
                                $get_sms_status = 'API is currently active';
                                break;

                            case '2501':
                                $get_sms_status = 'Success';
                                break;

                            case '2502':
                                $get_sms_status = 'Invalid URL Error, This means that one of the required parameters was not provided or left blank';
                                break;

                            case '2503':
                                $get_sms_status = 'Invalid value in "msgtype" field';
                                break;

                            case '2504':
                                $get_sms_status = 'Invalid Message';
                                break;

                            case '2505':
                                $get_sms_status = 'Invalid value for "recipient" field';
                                break;

                            case '2506':
                                $get_sms_status = 'Sender ID is not allowed';
                                break;

                            case '2507':
                                $get_sms_status = 'Invalid Sender ID';
                                break;

                            case '2508':
                                $get_sms_status = 'User validation failed';
                                break;

                            case '2509':
                                $get_sms_status = 'Internal Error or Service is NOT available at the time of request';
                                break;

                            case '2510':
                                $get_sms_status = 'Insufficient Credit';
                                break;
                        }
                    }

                    break;

                case 'Ytel':

                    $parameters = [
                        'From' => '+' . $this->cl_phone,
                        'Body' => $this->message,
                        'To' => '+' . $this->sender_id
                    ];

                    $body = http_build_query($parameters);

                    $header   = array();
                    $header[] = $gateway_user_name . ":" . $gateway_password;
                    $header[] = "Accept: application/json";
                    $header[] = "Content-type: application/x-www-form-urlencoded";

                    try {

                        $curl = curl_init($gateway_url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        $response = curl_exec($curl);

                        if (curl_errno($curl)) {
                            $get_sms_status = curl_error($curl);
                        }
                        curl_close($curl);

                        $get_data = json_decode($response, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('Message360', $get_data)) {
                            $get_sms_status = $get_data['Message360']['Errors']['Error'][0]['Message'];
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;


                case 'IDigitalM':

                    $sms = [
                        "sender" => $this->sender_id, /* Can be up to 11 alphanumeric characters */
                        "recipient" => $this->cl_phone, /* The mobile number you want to send to */
                        "message" => $this->message
                    ];

                    $data = http_build_query($sms);

                    $header   = array();
                    $header[] = "Accept: application/json";
                    $header[] = "Content-type: application/json";

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ":" . $gateway_password);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                        $get_sms_status = curl_exec($ch);
                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }
                        curl_close($ch);

                        $get_data = json_decode($get_sms_status, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('status', $get_data)) {
                            if ($get_data['status'] == 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['errors'][0]['message'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'BetSMSGuru':


                    if (strlen($this->cl_phone) > 10) {
                        $client_phone = substr($this->cl_phone, -10);
                    } else {
                        $client_phone = $this->cl_phone;
                    }

                    $xml = new \SimpleXMLElement('<SMS/>');

                    $authentication = $xml->addChild('oturum');
                    $authentication->addChild('kullanici', $gateway_user_name);
                    $authentication->addChild('sifre', $gateway_password);

                    $msg = $xml->addChild('mesaj');
                    $msg->addChild('baslik', $this->sender_id);
                    $msg->addChild('alicilar', $client_phone);
                    $msg->addChild('metin', $this->message);

                    $sms_data = $xml->asXML();


                    $ch = curl_init(); // cURL v7.18.1+ and OpenSSL 0.9.8j+ are required
                    curl_setopt_array($ch, array(
                            CURLOPT_URL => $gateway_url,
                            CURLOPT_HTTPHEADER => array('Content-Type: application/xml'),
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $sms_data,
                            CURLOPT_RETURNTRANSFER => true
                        )
                    );

                    $get_sms_status = curl_exec($ch);

                    curl_close($ch);

                    if (substr_count($get_sms_status, 'OK')) {
                        $get_sms_status = 'Success';
                    }

                    break;

                case 'TeleAPI':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $parameters = [
                        'token' => $gateway_user_name,
                        'source' => $this->sender_id,
                        'destination' => $clphone,
                        'message' => $this->message
                    ];


                    $data = http_build_query($parameters);

                    try {
                        $gateway_url = $gateway_url . '?' . $data;


                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        $response = json_decode($result, true);

                        if (json_last_error() == JSON_ERROR_NONE) {
                            if (isset($response) && is_array($response) && array_key_exists('status', $response)) {
                                if ($response['status'] == 'success') {
                                    $get_sms_status = 'Success';
                                } elseif ($response['status'] == 'error') {
                                    $get_sms_status = $response['data'];
                                } else {
                                    $get_sms_status = trim($result);
                                }
                            } else {
                                $get_sms_status = 'Unknown error';
                            }
                        } else {
                            $get_sms_status = trim($result);
                        }


                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }

                    break;


                case 'GreenSMSTop':

                    $sender_id = urlencode($this->sender_id);
                    $sms_url   = rtrim($gateway_url, '/');

                    try {

                        if ($msg_type == 'unicode') {
                            $type    = 2;
                            $message = $this->sms_unicode($this->message);
                        } else {
                            $type    = 0;
                            $message = urlencode($this->message);
                        }

                        $sms_sent_to_user = "$sms_url" . "?type=$type" . "&username=$gateway_user_name" . "&password=$gateway_password" . "&destination=$this->cl_phone" . "&source=$sender_id" . "&message=$message" . "&dlr=0";

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $headers   = array();
                        $headers[] = "Content-Type: application/x-www-form-urlencoded";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


                        $result = curl_exec($ch);


                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        curl_close($ch);

                        if (!isset($get_sms_status)) {
                            $get_data = explode('|', $result);
                            if (is_array($get_data) && array_key_exists('0', $get_data)) {
                                switch ($get_data[0]) {
                                    case '1701':
                                        $get_sms_status = 'Success';
                                        break;

                                    case '1702':
                                        $get_sms_status = 'Invalid URL';
                                        break;

                                    case '1703':
                                        $get_sms_status = 'Invalid User or Password';
                                        break;

                                    case '1704':
                                        $get_sms_status = 'Invalid Type';
                                        break;

                                    case '1705':
                                        $get_sms_status = 'Invalid SMS';
                                        break;

                                    case '1706':
                                        $get_sms_status = 'Invalid receiver';
                                        break;

                                    case '1707':
                                        $get_sms_status = 'Invalid sender';
                                        break;

                                    case '1709':
                                        $get_sms_status = 'User Validation Failed';
                                        break;

                                    case '1710':
                                        $get_sms_status = 'Internal Error';
                                        break;

                                    case '1715':
                                        $get_sms_status = 'Response Timeout';
                                        break;

                                    case '1025':
                                        $get_sms_status = 'Insufficient Credit';
                                        break;

                                    default:
                                        $get_sms_status = 'Invalid request';
                                        break;

                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }


                    break;

                case 'CallFire':

                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                    $fields = array(
                        "phoneNumber" => $clphone,
                        "message" => $this->message,
                    );

                    if ($this->sender_id != null) {
                        $fields['fromNumber'] = $this->sender_id;
                    }

                    $headers = array(
                        'Content-Type: application/json',
                        'Authorization: Basic ' . base64_encode($gateway_user_name . ":" . $gateway_password),
                    );
                    $ch      = curl_init();
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $get_data = curl_exec($ch);
                    $sss      = curl_getinfo($ch);


                    if (curl_error($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {

                        $get_response = json_decode($get_data, true);

                        if (isset($get_response) && is_array($get_response) && array_key_exists('code', $get_response) && array_key_exists('reason', $get_response)) {
                            if ($get_response['code'] == 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_response['status'][0]['status'];
                            }
                        } else {
                            $get_sms_status = $get_response['desc'];
                        }
                    }
                    curl_close($ch);
                    break;

                case 'Telenor':

                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                    $auth_url  = "https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp?msisdn=$gateway_user_name&password=$gateway_password";
                    $auth_data = simplexml_load_file($auth_url);

                    if (isset($auth_data) && isset($auth_data->response) && $auth_data->response == 'OK') {

                        $gateway_url = $gateway_url . "?session_id=$auth_data->data&to=$clphone&text=$this->message";

                        if ($this->sender_id != null) {
                            $gateway_url .= "&mask=" . $this->sender_id;
                        }

                        if ($msg_type == 'unicode') {
                            $gateway_url .= "&unicode=" . true;
                        }

                        $response = simplexml_load_file($gateway_url);


                        if (isset($response) && isset($response->response) && $response->response == 'OK') {
                            $get_sms_status = 'Success';
                        } elseif (isset($response) && isset($response->response) && $response->response == 'Error') {
                            $get_sms_status = $response->data;
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } elseif (isset($auth_data) && isset($auth_data->response) && $auth_data->response == 'Error') {
                        $get_sms_status = $auth_data->data;
                    } else {
                        $get_sms_status = 'Invalid request';
                    }


                    break;

                case 'InfoBipLegacy':

                    $parameters = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'to' => $this->cl_phone,
                        'from' => $this->sender_id,
                        'text' => $this->message
                    ];

                    $gateway_url .= '?' . http_build_query($parameters);

                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    // response of the POST request
                    $response = curl_exec($ch);
                    $get_data = json_decode($response, true);
                    curl_close($ch);

                    if (isset($get_data) && is_array($get_data)) {
                        if (array_key_exists('messages', $get_data)) {
                            foreach ($get_data['messages'] as $msg) {
                                if ($msg['status']['name'] == 'MESSAGE_ACCEPTED' || $msg['status']['name'] == 'PENDING_ENROUTE') {
                                    $get_sms_status = 'Success|' . $msg['messageId'];
                                } else {
                                    $get_sms_status = $msg['status']['description'];
                                }
                            }
                        } elseif (array_key_exists('requestError', $get_data)) {
                            foreach ($get_data['requestError'] as $msg) {
                                $get_sms_status = $msg['text'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;

                case 'Bandwidth':

                    $sender_id = str_replace(['+', '(', ')', '-', ' '], '', $this->sender_id);

                    $parameters = [
                        'from' => '+' . $sender_id,
                        'to' => ['+' . $this->cl_phone],
                        'text' => $this->message,
                        'applicationId' => $gateway_extra
                    ];

                    try {

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ':' . $gateway_password);

                        $headers   = array();
                        $headers[] = 'Content-Type: application/json';
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {

                            $result = json_decode($result, true);

                            if (isset($result) && is_array($result)) {
                                if (array_key_exists('id', $result)) {
                                    $get_sms_status = 'Success|' . $result['id'];
                                } elseif (array_key_exists('error', $result)) {
                                    $get_sms_status = $result['error'];
                                } else {
                                    $get_sms_status = implode(" ", $result);
                                }
                            } else {
                                $get_sms_status = $result;
                            }
                        }
                        curl_close($ch);
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'LinkMobility':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $parameters = array();
                    $campaign   = array("name" => 'Campaign_' . time(),
                        "media" => "SMS",
                        "campaign_content" => $this->message,
                        "id_production_account" => $gateway_user_name,
                        "sender_id" => $this->sender_id);


                    $import = array("name" => $clphone,
                        "content" => "SMS\r\n$clphone");

                    $parameters["campaign"] = $campaign;
                    $parameters["import"]   = $import;


                    $gateway_url .= "/campaign/send_sms_mailing?apikey=$gateway_password";


                    try {

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $gateway_url);
                        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($curl, CURLOPT_POST, true);

                        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));

                        curl_setopt($curl, CURLOPT_HEADER, false);

                        curl_setopt($curl, CURLOPT_HTTPHEADER,
                            array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen(json_encode($parameters))));


                        $response = curl_exec($curl);

                        if ($response === false) {
                            $get_sms_status = curl_error($curl);
                        } else {

                            $response = json_decode($response, true);

                            if (json_last_error() == JSON_ERROR_NONE) {
                                if (isset($response) && is_array($response)) {
                                    if (array_key_exists('error', $response)) {
                                        $get_sms_status = $response['status'];
                                    } elseif (array_key_exists('id', $response)) {
                                        $get_sms_status = 'Success';
                                    } else {
                                        $get_sms_status = (string)$response;
                                    }
                                }
                            } else {

                                $pattern = "/<h1>(.*?)<\/h1>/";
                                preg_match_all($pattern, $response, $matches);
                                if (isset($matches) && is_array($matches)) {
                                    $get_sms_status = trim($matches['1'][0]);
                                } else {
                                    $get_sms_status = (string)$response;
                                }
                            }
                        }

                        curl_close($curl);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Teletopiasms':

                    $parameters = [
                        'recipient' => $this->cl_phone,
                        'text' => $this->message
                    ];

                    if ($this->sender_id != '') {
                        $parameters['sender'] = $this->sender_id;
                    }

                    $gateway_url .= "?username=$gateway_user_name&password=$gateway_password&" . http_build_query($parameters);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $headers   = array();
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


                    $get_sms_status = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        if (substr_count($get_sms_status, 'accepted')) {
                            $get_sms_status = 'Success';
                        }
                    }

                    curl_close($ch);
                    break;

                case 'Releans':

                    $sendSMS = array(
                        "senderId" => $this->sender_id,
                        "message" => $this->message,
                        "mobileNumber" => '+' . $this->cl_phone
                    );

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                        curl_setopt($ch, CURLOPT_POST, 1);


                        $headers   = array();
                        $headers[] = "Authorization: Bearer $gateway_user_name";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('type', $response)) {

                            if ($response['type'] == 'danger') {
                                $get_sms_status = $response['message'];
                            } else {
                                $get_sms_status = 'Success';
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'ComAPI':

                    $sendSMS = [
                        'to' => [
                            'phoneNumber' => $this->cl_phone
                        ],
                        'body' => $this->message,
                        'rules' => [
                            'sms'
                        ]
                    ];

                    $gateway_url .= "/apispaces/$gateway_user_name/messages";

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                        curl_setopt($ch, CURLOPT_POST, 1);


                        $headers   = array();
                        $headers[] = "Authorization: Bearer $gateway_password";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('type', $response)) {

                            if ($response['type'] == 'danger') {
                                $get_sms_status = $response['message'];
                            } else {
                                $get_sms_status = 'Success';
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'Interaktif':

                    $postData = "" .
                        "<sms>" .
                        "<username>$gateway_user_name</username>" .
                        "<password>$gateway_password</password>" .
                        "<header>$this->sender_id</header>" .
                        "<validity>2880</validity>" .
                        "<message>" .
                        "<gsm>" .
                        "<no>$this->cl_phone</no>" .
                        "</gsm>" .
                        "<msg>$this->message</msg>" .
                        "</message>" .
                        "</sms>";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml; charset=UTF-8"));

                    $response = curl_exec($ch);
                    curl_close($ch);

                    if (strpos($response, '00') !== false) {
                        $get_sms_status = 'Success';
                    } elseif ($response == '99') {
                        $get_sms_status = 'Henüz dokümante edilmemiş bir hatayı belirtir.';
                    } elseif ($response == '97') {
                        $get_sms_status = 'İsteği HTTP POSTile yollayınız';
                    } elseif ($response == '91') {
                        $get_sms_status = 'POST verisi okunamadı veyayok';
                    } elseif ($response == '95') {
                        $get_sms_status = 'İsteği HTTP GET ile yollayınız';
                    } elseif ($response == '93') {
                        $get_sms_status = 'GET parametrelerinde eksik var';
                    } elseif ($response == '87') {
                        $get_sms_status = 'Kullanıcı adı veya şifre hatalı';
                    } elseif ($response == '85') {
                        $get_sms_status = 'Belirttiğiniz mesaj başlığı bulunamadı veya onaylanmamış';
                    } elseif ($response == '83') {
                        $get_sms_status = 'Mesaj metni ve numaralar incelendikten sonra sistem yollanacak bir SMS oluşturmaya yetecek en az 1 numara ve en az 1 karakterden oluşan mesaj metnine sahip olamadı. Gönderim yapılacak verilerin yeterli olmadığına karar verdi.';
                    } elseif ($response == '81') {
                        $get_sms_status = 'Gönderilecek olan mesaj için yeterli krediye sahip değilsiniz.';
                    } elseif ($response == '77') {
                        $get_sms_status = 'Son 2 dakika içinde aynı SMS\'in gönderilmesi durumu (Sistem bunu engeller)';
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    break;


                case 'DidForSale':

                    $sendSMS = [
                        'to' => $this->cl_phone,
                        'from' => $this->sender_id,
                        'text' => $this->message
                    ];

                    $headers = array(
                        'Content-Type:application/json',
                        'Authorization: Basic ' . base64_encode("$gateway_user_name:$gateway_password")
                    );


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);

                    curl_close($ch);

                    $response = json_decode($result, true);

                    if ($response && is_array($response) && array_key_exists('status', $response)) {

                        if ($response['status'] === true) {
                            $get_sms_status = 'Success|' . $response['referenceid'];
                        } else {
                            $get_sms_status = $response['message'];
                        }

                    } else {
                        $get_sms_status = 'Invalid request';
                    }
                    break;

                case 'EpsinsmsIn':

                    $message = urlencode($this->message);

                    $url = "$gateway_url?AUTH_KEY=$gateway_user_name&message=$message&senderId=$this->sender_id&routeId=$gateway_password&mobileNos=$this->cl_phone";

                    if ($msg_type == 'unicode') {
                        $url .= '&smsContentType=unicode';
                    } else {
                        $url .= '&smsContentType=english';
                    }


                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => array(
                            "Cache-Control: no-cache"
                        ),
                    ));

                    $response = curl_exec($curl);
                    $err      = curl_error($curl);

                    curl_close($curl);


                    if ($err) {
                        $get_sms_status = $err;
                    } else {
                        $get_data = json_decode($response, true);

                        if (isset($get_data) && is_array($get_data) && array_key_exists('responseCode', $get_data)) {
                            if ($get_data['responseCode'] == '3001') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data['response'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }

                    }

                    break;

                case 'BeeTelekom':

                    $sendSMS = [
                        'to' => $this->cl_phone,
                        'from' => $this->sender_id,
                        'text' => $this->message
                    ];

                    $headers = array(
                        'Content-Type:application/json'
                    );


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);

                    curl_close($ch);

                    $get_data = json_decode($result, true);


                    if (isset($get_data) && is_array($get_data)) {
                        if (array_key_exists('messages', $get_data)) {
                            foreach ($get_data['messages'] as $msg) {
                                if ($msg['status']['name'] == 'MESSAGE_ACCEPTED' || $msg['status']['name'] == 'PENDING_ENROUTE') {
                                    $get_sms_status = 'Success|' . $msg['messageId'];
                                } else {
                                    $get_sms_status = $msg['status']['description'];
                                }
                            }
                        } elseif (array_key_exists('requestError', $get_data)) {
                            foreach ($get_data['requestError'] as $msg) {
                                $get_sms_status = $msg['text'];
                            }
                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                    break;


                case 'Afocampos':
                    $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);
                    $message = urlencode($this->message);

                    try {

                        $sms_sent_to_user = $gateway_url . "?numero=$clphone" . "&texto=$message";


                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        $err      = curl_error($ch);

                        curl_close($ch);

                        if ($err) {
                            $get_sms_status = $err;
                        } else {
                            $get_sms_status = trim($get_data);
                            if (strpos($get_sms_status, 'Response') !== false) {
                                $get_sms_status = 'Success';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'SpeedsmsVN':
                    $sender_id = $this->sender_id;

                    if ($sender_id == 'Default' || $sender_id == 'default') {
                        $type = 4;
                    } elseif (!is_numeric($sender_id)) {
                        $type = 3;
                    } else {
                        $type = 2;
                    }


                    $json = json_encode(array('to' => $this->cl_phone, 'content' => $this->message, 'sms_type' => $type, 'sender' => $sender_id));

                    $headers = array('Content-type: application/json');


                    $ch = curl_init($gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ':x');
                    $result = curl_exec($ch);

                    if (curl_error($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $get_sms_status = json_decode($result, true);
                        if (isset($get_sms_status) && is_array($get_sms_status) && array_key_exists('status', $get_sms_status)) {
                            if ($get_sms_status['status'] == 'success') {
                                $get_sms_status = 'Success';
                            } elseif ($get_sms_status['status'] == 'error') {
                                $get_sms_status = $get_sms_status['message'];
                            } else {
                                $get_sms_status = 'Invalid Access';
                            }
                        }
                    }
                    curl_close($ch);
                    break;

                case 'Safaricom':

                    require_once(app_path('libraray/safaricom/lib/sdp_utils.php'));

                    //create an instance of the service
                    $sdp_service    = new \SDPService();
                    $kmp_timestamp  = date("Ymd");
                    $kmp_correlator = time();
                    $kmp_sppwd      = md5($gateway_extra . $gateway_password . $kmp_timestamp);

                    $result = $sdp_service->sendSms($gateway_extra, $kmp_sppwd, $gateway_user_name, $kmp_timestamp, $this->cl_phone, $kmp_correlator, $this->sender_id, $this->message);

                    break;

                case 'Api4sms':

                    $parameters = [
                        'id' => $gateway_user_name,
                        'pw' => $gateway_password,
                        'to' => $this->cl_phone,
                        'text' => $this->message,
                        'encoding' => 'auto'
                    ];

                    if ($this->sender_id) {
                        $parameters['sender'] = $this->sender_id;
                    }

                    $data = http_build_query($parameters);

                    $gateway_url = $gateway_url . '?' . $data;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);

                    $get_sms_status = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {


                        $xml   = simplexml_load_string($get_sms_status, "SimpleXMLElement", LIBXML_NOCDATA);
                        $json  = json_encode($xml);
                        $array = json_decode($json, TRUE);


                        if (is_array($array) && array_key_exists('error', $array)) {
                            $get_sms_status = $array['error'];

                            switch ($get_sms_status) {
                                case '0':
                                    $get_sms_status = 'Success';
                                    break;

                                case '1':
                                    $get_sms_status = 'The parameter id is missing';
                                    break;

                                case '7':
                                    $get_sms_status = 'Invalid username or password';
                                    break;

                                case '10':
                                    $get_sms_status = 'Invalid sender id';
                                    break;

                                case '14':
                                    $get_sms_status = 'Invalid destination or destination not covered';
                                    break;

                                case '15':
                                    $get_sms_status = 'Insufficient credit';
                                    break;

                                case '13':
                                case '9':
                                default:
                                    $get_sms_status = 'System error, please retry';
                                    break;
                            }

                        } else {
                            $get_sms_status = 'Unknown error';
                        }
                    }

                    curl_close($ch);
                    break;


                case 'NigeriaBulkSMS':

                    $parameters = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'mobiles' => $this->cl_phone,
                        'message' => $this->message,
                        'sender' => $this->sender_id
                    ];

                    $data           = http_build_query($parameters);
                    $gateway_url    = $gateway_url . '?' . $data;
                    $get_sms_status = file_get_contents($gateway_url);
                    $output         = json_decode($get_sms_status, TRUE);

                    if (isset($output) && is_array($output)) {
                        if (array_key_exists('status', $output) && $output['status'] == 'OK') {
                            $get_sms_status = 'Success';
                        } elseif (array_key_exists('error', $output)) {
                            $get_sms_status = $output['error'];
                        } else {
                            $get_sms_status = 'Invalid Request';
                        }
                    } else {
                        $get_sms_status = 'Invalid Request';
                    }
                    break;

                case 'Bondsms':

                    $parameters = [
                        'username' => $gateway_user_name,
                        'apiId' => $gateway_password,
                        'destination' => $this->cl_phone,
                        'text' => $this->message,
                        'source' => $this->sender_id,
                        'json' => 'True'
                    ];

                    $data        = http_build_query($parameters);
                    $gateway_url = $gateway_url . '?' . $data;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);

                    $get_sms_status = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {

                        $output = json_decode($get_sms_status, TRUE);

                        if (isset($output) && is_array($output) && array_key_exists('ErrorCode', $output)) {
                            if ($output['ErrorCode'] == '0') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $output['Description'];
                            }
                        } else {
                            $get_sms_status = 'Invalid Request';
                        }
                    }

                    curl_close($ch);
                    break;


                case 'SMSEdgeIO':

                    $sendSMS = [
                        'api_key' => $gateway_user_name,
                        'to' => $this->cl_phone,
                        'from' => $this->sender_id,
                        'text' => $this->message,
                        'shorten_url' => 0,
                        'preferred_route_id' => $gateway_password
                    ];

                    $headers = array(
                        'content-type: application/x-www-form-urlencoded'
                    );


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);

                    curl_close($ch);

                    $get_data = json_decode($result, true);

                    if (isset($get_data) && is_array($get_data) && array_key_exists('0', $get_data)) {
                        if (isset($get_data[0]) && is_array($get_data[0]) && array_key_exists('success', $get_data[0])) {
                            if ($get_data[0]['success']) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $get_data[0]['errors']['0'];
                            }
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }

                    break;

                case 'sms77IO':

                    $params = array(
                        'p' => $gateway_user_name,
                        'to' => $this->cl_phone,
                        'type' => 'economy',
                        'text' => $this->message,
                        'from' => $this->sender_id
                    );

                    if ($msg_type == 'unicode') {
                        $params['unicode'] = 1;
                    }

                    $url            = $gateway_url . '?' . http_build_query($params);
                    $get_sms_status = file_get_contents($url);

                    switch ($get_sms_status) {
                        case '100':
                            $get_sms_status = 'Success';
                            break;

                        case '101':
                            $get_sms_status = 'Transmission to at least one recipient failed';
                            break;

                        case '201':
                            $get_sms_status = 'Sender invalid. A maximum of 11 alphanumeric or 16 numeric characters are allowed';
                            break;

                        case '202':
                            $get_sms_status = 'Recipient number invalid';
                            break;

                        case '401':
                            $get_sms_status = 'Message is too long';
                            break;

                        case '402':
                            $get_sms_status = 'Reload Lock – this SMS has already been sent within the last 180 seconds';
                            break;

                        case '403':
                            $get_sms_status = 'Max. limit per day reached for this number';
                            break;

                        case '500':
                            $get_sms_status = 'Too little credit available';
                            break;

                        case '600':
                            $get_sms_status = 'Carrier delivery failed';
                            break;

                        case '700':
                            $get_sms_status = 'Unknown error';
                            break;

                        case '900':
                            $get_sms_status = 'Authentication failed. Please check user and api key';
                            break;

                        case '902':
                            $get_sms_status = 'http API disabled for this account';
                            break;

                        case '903':
                            $get_sms_status = 'Server IP is wrong';
                            break;

                        case '11':
                            $get_sms_status = 'SMS carrier temporarily not available';
                            break;

                        default:
                            $get_sms_status = 'Invalid error';
                            break;
                    }

                    break;


                case 'SMSSmart':

                    $sendSMS = array(
                        "from" => $this->sender_id,
                        "message" => $this->message,
                        "to" => $this->cl_phone,
                        "username" => $gateway_user_name,
                        "password" => $gateway_password,
                    );

                    $sendSMS = http_build_query($sendSMS);

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url . '/rest/send_sms?' . $sendSMS);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POST, 1);

                        $headers   = array();
                        $headers[] = "Content-Type: application/json";
                        $headers[] = "Accept: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);

                        if (curl_error($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $response = json_decode($result, true);

                            if ($response && is_array($response)) {

                                if ($this->msgcount > 1) {
                                    if (isset($response[0]) && is_array($response[0]) && array_key_exists('message_id', $response[0])) {
                                        $get_sms_status = 'Success';
                                    } elseif (array_key_exists('error_message', $response)) {
                                        $get_sms_status = $response['error_message'];
                                    } else {
                                        $get_sms_status = 'Invalid request';
                                    }
                                } else {
                                    if (array_key_exists('message_id', $response)) {
                                        $get_sms_status = 'Success';
                                    } elseif (array_key_exists('error_message', $response)) {
                                        $get_sms_status = $response['error_message'];
                                    } else {
                                        $get_sms_status = 'Invalid request';
                                    }
                                }

                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'SilverStreet':

                    $parameters = [
                        'recipients' => [$this->cl_phone],
                        'body' => $this->message,
                        'sender' => $this->sender_id
                    ];

                    $sendSMS = json_encode($parameters);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $sendSMS);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_USERPWD, 'silverstreet' . ':' . $gateway_user_name);

                    $headers   = array();
                    $headers[] = 'Accept: application/json';
                    $headers[] = 'Content-Type: application/json; charset=utf8';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $response = json_decode($result, true);
                        if ($response && is_array($response)) {

                            if (array_key_exists('total_items', $response)) {
                                $get_sms_status = 'Success';
                            } elseif (array_key_exists('title', $response)) {
                                $get_sms_status = $response['title'];
                            } else {
                                $get_sms_status = 'Invalid Request';
                            }

                        } else {
                            $get_sms_status = 'Invalid Request';
                        }

                    }
                    curl_close($ch);
                    break;

                case 'RouteeNet':

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://auth.routee.net/oauth/token",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "grant_type=client_credentials",
                        CURLOPT_HTTPHEADER => array(
                            "authorization: Basic " . base64_encode($gateway_user_name . ":" . $gateway_password),
                            "content-type: application/x-www-form-urlencoded"
                        ),
                    ));

                    $response = curl_exec($curl);
                    $err      = curl_error($curl);

                    curl_close($curl);

                    if ($err) {
                        $get_sms_status = $err;
                    } else {
                        $response = json_decode($response, true);

                        if (isset($response) && is_array($response) && array_key_exists('access_token', $response)) {
                            $access_token = $response['access_token'];

                            $parameters = [
                                'body' => $this->message,
                                'to' => '+' . $this->cl_phone,
                                'from' => $this->sender_id
                            ];

                            $sendSMS = json_encode($parameters);
                            $curl    = curl_init();

                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $gateway_url,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => $sendSMS,
                                CURLOPT_HTTPHEADER => array(
                                    "authorization: Bearer " . $access_token,
                                    "content-type: application/json"
                                ),
                            ));

                            $response = curl_exec($curl);
                            $err      = curl_error($curl);

                            curl_close($curl);

                            if ($err) {
                                $get_sms_status = $err;
                            } else {
                                $response = json_decode($response, true);
                                if (isset($response) && is_array($response) && array_key_exists('status', $response)) {
                                    if ($response['status'] == 'Queued') {
                                        $get_sms_status = 'Success';
                                    } else {
                                        $get_sms_status = $response['status'];
                                    }
                                } else {
                                    $get_sms_status = 'Invalid Request';
                                }
                            }

                        } else {
                            $get_sms_status = 'Access token not found';
                        }
                    }
                    break;

                case 'GeniqTech':

                    $sendSMS = [
                        'recipient' => $this->cl_phone,
                        'originator' => $this->sender_id,
                        'message' => $this->message
                    ];

                    $headers = array(
                        'Content-Type:application/x-www-form-urlencoded',
                        'Authorization: Basic ' . base64_encode("$gateway_user_name:$gateway_password")
                    );


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);

                    curl_close($ch);

                    $response = json_decode($result, true);

                    if ($response && is_array($response) && array_key_exists('responseMessage', $response)) {
                        $get_sms_status = $response['responseMessage'];
                    } else {
                        $get_sms_status = 'Invalid request';
                    }
                    break;

                case 'TigoBusiness':

                    $phone   = ltrim($this->cl_phone, '504');
                    $sendSMS = [
                        'country' => $gateway_extra,
                        'encoding' => 'GSM7',
                        'messageFormat' => 1,
                        'message' => $this->message,
                        'addresseeList' => [
                            [
                                'mobile' => $phone
                            ]
                        ]
                    ];

                    $parameters = json_encode($sendSMS);

                    $headers = array(
                        'Authorization: Basic ' . base64_encode("$gateway_user_name:$gateway_password"),
                        "content-type: application/json"
                    );


                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);
                    curl_close($ch);

                    $response = json_decode($result, true);

                    if ($response && is_array($response) && array_key_exists('status', $response)) {
                        if ($response['status'] == 1) {

                            if (isset($response['result']['totalFailed']) && $response['result']['totalFailed'] == 0) {
                                $get_sms_status = 'Success';
                            } elseif (isset($response['result']['totalFailed']) && $response['result']['totalFailed'] == 1) {
                                $get_sms_status = $response['result']['failedRequests']['0']['reason'];
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        } elseif (isset($response['reason'])) {
                            $get_sms_status = $response['reason'];
                        } elseif (isset($response['message'])) {
                            $get_sms_status = $response['message'];
                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    } else {
                        $get_sms_status = 'Invalid request';
                    }
                    break;

                case 'Tobeprecisesms':

                    $base_url   = $gateway_url . '/api/sendsms/singleSMS?username=' . $gateway_user_name . '&Password=' . $gateway_password;
                    $mobile_nos = array($this->cl_phone);

                    $request_parameters = array(
                        "MobileNumbers" => $mobile_nos,
                        "Message" => $this->message,
                        "SenderName" => $this->sender_id
                    );

                    $ch = curl_init($base_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_parameters));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',));
                    $result_json = curl_exec($ch);
                    curl_close($ch);

                    if ($result_json === false) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $result = json_decode($result_json, true);

                        if (isset($result) && is_array($result) && array_key_exists('status', $result)) {

                            if ($result['status'] == 'OK') {
                                if (isset($result['data']) && is_array($result['data'])) {
                                    if ($result['data'][0]['status'] == 'OK') {
                                        $get_sms_status = 'Success';
                                    } else {
                                        $get_sms_status = $result['data'][0]['details'];
                                    }
                                } else {
                                    $get_sms_status = 'Invalid request';
                                }
                            } elseif ($result['status'] == 'Error') {
                                if (isset($result['data']) && is_array($result['data'])) {
                                    $get_sms_status = $result['data'][0]['details'];
                                }
                            } elseif ($result['status'] == 'ERROR') {
                                $get_sms_status = $result['errorDescription'];
                            } else {
                                $get_sms_status = 'Invalid Request';
                            }
                        } else {
                            $get_sms_status = 'Invalid Request';
                        }
                    }
                    break;

                case 'RawMobility':

                    $parameters = [
                        'USER_NAME' => $gateway_user_name,
                        'PASSWORD' => $gateway_password,
                        'ROUTE' => $gateway_extra,
                        'RECIPIENT' => $this->cl_phone,
                        'MESSAGE_TEXT' => $this->message,
                        'ORIGINATOR' => $this->sender_id
                    ];

                    $data        = http_build_query($parameters);
                    $gateway_url = $gateway_url . '?' . $data;

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);

                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {

                            $xml    = simplexml_load_string($get_sms_status, "SimpleXMLElement", LIBXML_NOCDATA);
                            $json   = json_encode($xml);
                            $output = json_decode($json, TRUE);

                            if (isset($output) && is_array($output) && array_key_exists('detailedResponse', $output) && array_key_exists('routeId', $output)) {
                                if ($output['detailedResponse'] == 'true' && $output['routeId'] == $gateway_extra) {
                                    $get_sms_status = 'Success';
                                }
                            } else {
                                $get_sms_status = 'Invalid Request';
                            }
                        }

                        curl_close($ch);

                    } catch (\Exception $ex) {
                        $get_sms_status = $ex->getMessage();
                    }
                    break;


                case 'TripleClickSMS':

                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);

                    $data = http_build_query([
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'sender' => $this->sender_id,
                        'recipient' => $clphone,
                        'message' => $this->message,
                        'option' => 'com_spc',
                        'comm' => 'spc_api'
                    ]);

                    try {

                        $gateway_url = $gateway_url . '?' . $data;

                        $get_sms_status = file_get_contents($gateway_url);

                        if (substr_count($get_response, 'OK') !== 0) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = ltrim($get_sms_status, '-');
                            switch ($get_sms_status) {

                                case '2904':
                                    $get_sms_status = 'SMS Sending Failed';
                                    break;

                                case '2905':
                                    $get_sms_status = 'Invalid username/password combination';
                                    break;

                                case '2906':
                                    $get_sms_status = 'Credit exhausted';
                                    break;

                                case '2907':
                                    $get_sms_status = 'Gateway unavailable';
                                    break;

                                case '2908':
                                    $get_sms_status = 'Invalid schedule date format';
                                    break;

                                case '2909':
                                    $get_sms_status = 'Unable to schedule';
                                    break;

                                case '2910':
                                    $get_sms_status = 'Username is empty';
                                    break;

                                case '2911':
                                    $get_sms_status = 'Password is empty';
                                    break;

                                case '2912':
                                    $get_sms_status = 'Recipient is empty';
                                    break;

                                case '2913':
                                    $get_sms_status = 'Message is empty';
                                    break;

                                case '2914':
                                    $get_sms_status = 'Sender is empty';
                                    break;

                                case '2915':
                                    $get_sms_status = 'One or more required fields are empty';
                                    break;

                                default:
                                    $get_sms_status = 'Unknown error';
                                    break;
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'AdnSMS':

                    $data = array(
                        'api_key' => $gateway_user_name,
                        'api_secret' => $gateway_password,
                        'mobile' => $this->cl_phone,
                        'message_body' => $this->message,
                    );

                    $data             = http_build_query($data);
                    $sms_sent_to_user = $gateway_url . "?" . $data;
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);
                        $curl_error     = curl_error($ch);
                        curl_close($ch);

                        if ($curl_error) {
                            $get_sms_status = $curl_error;
                        } else {
                            $get_sms_status = json_decode($get_sms_status, true);

                            if (isset($get_sms_status) && is_array($get_sms_status) && array_key_exists('api_response_message', $get_sms_status)) {
                                if ($get_sms_status['api_response_message'] == 'SUCCESS') {
                                    $get_sms_status = 'Success';
                                } elseif ($get_sms_status['api_response_message'] == 'FAILED') {
                                    $get_sms_status = $get_sms_status['error']['error_message'];
                                } else {
                                    $get_sms_status = 'Invalid request';
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    };

                    break;


                case 'Thinq':

                    $gateway_url = rtrim($gateway_url, '/') . '/' . $gateway_extra . '/product/origination/sms/send';

                    $sendSMS = [
                        'to_did' => $this->cl_phone,
                        'from_did' => $this->sender_id,
                        'message' => $this->message
                    ];

                    $headers = array(
                        'Content-Type:application/json',
                        'Authorization: Basic ' . base64_encode("$gateway_user_name:$gateway_password")
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result     = curl_exec($ch);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    if ($curl_error) {
                        $get_sms_status = $curl_error;
                    } else {
                        $response = json_decode($result, true);

                        if ($response && is_array($response)) {

                            if ((array_key_exists('code', $response) && array_key_exists('message', $response) && $response['code'] == 200) || array_key_exists('guid', $response)) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['message'];
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    }
                    break;

                case 'Envialosimplesms':

                    $sendSMS = [
                        'api_token' => $gateway_user_name,
                        'destino' => '+' . $this->cl_phone,
                        'mensaje' => $this->message
                    ];

                    $headers[] = "Content-Type: application/x-www-form-urlencoded";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result     = curl_exec($ch);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    if ($curl_error) {
                        $get_sms_status = $curl_error;
                    } else {
                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('result', $response)) {

                            if ($response['result'] == 1) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['error'];
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    }
                    break;

                case 'SlingComNg':

                    $sendSMS = [
                        'api_token' => $gateway_user_name,
                        'to' => $this->cl_phone,
                        'channel' => $this->sender_id,
                        'message' => $this->message
                    ];


                    $headers = array(
                        'Accept:application/json',
                        'Content-Type:application/x-www-form-urlencoded',
                        'Authorization: Basic ' . $gateway_user_name
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendSMS));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result     = curl_exec($ch);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    if ($curl_error) {
                        $get_sms_status = $curl_error;
                    } else {
                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('status', $response)) {

                            if ($response['status'] == "sent" || $response['status'] == "queued") {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['details'];
                            }

                        } else {
                            $get_sms_status = 'Invalid request';
                        }
                    }
                    break;

                case '360nrs':
                    $post = [
                        'to' => [$this->cl_phone],
                        'from' => $this->sender_id,
                        'message' => $this->message
                    ];

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Accept: application/json",
                            "Authorization: Basic " . base64_encode($gateway_user_name . ":" . $gateway_password)));
                        $result = curl_exec($ch);
                        $result = json_decode($result, true);

                        if (isset($result) && is_array($result)) {
                            if (array_key_exists('result', $result) && is_array($result['result']) && array_key_exists('0', $result['result'])) {
                                if ($result['result'][0]['accepted'] == true) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = 'Failed';
                                }
                            } elseif (array_key_exists('error', $result) && is_array($result['error']) && array_key_exists('description', $result['error'])) {
                                $get_sms_status = $result['error']['description'];
                            } else {
                                $get_sms_status = 'Failed';
                            }
                        } else {
                            $get_sms_status = 'Failed';
                        }
                    } catch (Exception $exc) {
                        $get_sms_status = $exc->getTraceAsString();
                    }
                    break;


                case 'PineAppleSMS':
                    $sender_id = urlencode($this->sender_id);
                    $message   = urlencode($this->message);
                    try {

                        $sms_sent_to_user = $gateway_url . "?username=$gateway_user_name" . "&password=$gateway_password" . "&dnis=$this->cl_phone" . "&ani=$sender_id" . "&message=$message" . "&command=submit&longMessageMode=cut";

                        if ($msg_type == 'unicode') {
                            $sms_sent_to_user .= "&dataCoding=1";
                        } else {
                            $sms_sent_to_user .= "&dataCoding=0";
                        }

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $get_data = curl_exec($ch);
                        curl_close($ch);

                        if ($get_data === false) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $get_data = json_decode($get_data, true);

                            if (isset($get_data) && is_array($get_data)) {
                                if (array_key_exists('message_id', $get_data)) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = (string)$get_data;
                                }
                            } else {
                                $get_sms_status = 'Enable your port number for outgoing and incoming from your firewall';
                            }
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'ApiFonica':

                    $gateway_url .= "/$gateway_user_name/messages";

                    $post_data = json_encode([
                        'from' => $this->sender_id,
                        'to' => $this->cl_phone,
                        'text' => $this->message
                    ]);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_USERPWD, $gateway_user_name . ':' . $gateway_password);

                    $headers   = array();
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    }

                    $result = json_decode($result, true);

                    if (isset($result) && is_array($result) && array_key_exists('status_code', $result)) {
                        if ($result['status_code'] == '201') {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $result['status_message'];
                        }
                    } else {
                        $get_sms_status = 'Invalid Request';
                    }

                    curl_close($ch);

                    break;


                case 'SmsMessengerComAu':

                    $data = [
                        'uname' => $gateway_user_name,
                        'pword' => $gateway_password,
                        'msg' => $this->message,
                        'to' => $this->cl_phone,
                    ];

                    if ($this->sender_id != '') {
                        $data['sid'] = $this->sender_id;
                    }
                    $parameters = http_build_query($data);

                    $sms_sent_to_user = $gateway_url . "?" . $parameters;
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        }

                        if (substr_count($get_sms_status, 'COMPLETE')) {
                            $get_sms_status = 'Success';
                        }

                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Voyant':

                    $gateway_url .= "?sessionId=$gateway_user_name";


                    $parameters = [
                        'text' => $this->message,
                        'to' => [$this->cl_phone],
                        'from' => $this->sender_id,
                        'referenceId' => time()
                    ];

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Content-Type: application/json"
                    ));

                    $result = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $result = json_decode($result, true);

                        if (isset($result) && is_array($result) && array_key_exists('success', $result)) {
                            if ($result['success'] == true) {
                                $get_sms_status = 'Success|' . $result['result']['referenceId'];
                            } else {
                                $get_sms_status = $result['detail'];
                            }
                        } else {
                            $get_sms_status = 'Invalid Request';
                        }
                    }
                    curl_close($ch);
                    break;

                case 'SMS123':

                    $data = [
                        'apiKey' => $gateway_user_name,
                        'messageContent' => $this->message,
                        'recipients' => $this->cl_phone,
                        'referenceID' => time()
                    ];

                    $parameters = http_build_query($data);

                    $sms_sent_to_user = $gateway_url . "?" . $parameters;


                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $result = json_decode($result, true);

                            if (isset($result) && is_array($result) && array_key_exists('status', $result)) {
                                if ($result['status'] == 'ok') {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $result['statusMsg'];
                                }
                            } else {
                                $get_sms_status = 'Invalid Request';
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'VoxCpaas':

                    $parameters = [
                        'projectid' => $gateway_user_name,
                        'authtoken' => $gateway_password,
                        'body' => $this->message,
                        'to' => '+' . $this->cl_phone,
                    ];

                    if ($this->sender_id) {
                        $parameters['from'] = $this->sender_id;
                    }

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                    $result = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $result = json_decode($result, true);

                        if (isset($result) && is_array($result) && array_key_exists('status', $result)) {
                            if ($result['status'] == 'Success') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $result['status'];
                            }
                        } else {
                            $get_sms_status = 'Invalid Request';
                        }
                    }
                    curl_close($ch);
                    break;

                case 'CellVoz':

                    $parameters = [
                        'user' => $gateway_user_name,
                        'key' => $gateway_password,
                        'sms' => $this->message,
                        'num' => $this->cl_phone,
                    ];

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                    $get_sms_status = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $get_sms_status = curl_error($ch);
                    } else {
                        $get_sms_status = (int)filter_var($get_sms_status, FILTER_SANITIZE_NUMBER_INT);

                        if (strlen($get_sms_status) > 4) {
                            $get_sms_status = 'Success';
                        } else {
                            switch ($get_sms_status) {
                                case '10':
                                    $get_sms_status = 'Descripción de cuenta inválido';
                                    break;

                                case '11':
                                    $get_sms_status = 'Número de cuenta vacío';
                                    break;

                                case '20':
                                    $get_sms_status = 'Llave inválida';
                                    break;

                                case '21':
                                    $get_sms_status = 'Llave vacía';
                                    break;

                                case '40':
                                    $get_sms_status = 'IP no autorizada';
                                    break;

                                case '50':
                                    $get_sms_status = 'Número inválido';
                                    break;

                                case '51':
                                    $get_sms_status = 'Número vacío';
                                    break;

                                case '52':
                                    $get_sms_status = 'Longitud del número inválido';
                                    break;

                                case '61':
                                    $get_sms_status = 'Mensaje vacío';
                                    break;

                                case '63':
                                    $get_sms_status = 'Mensaje con carácteres especiales';
                                    break;

                                case '70':
                                    $get_sms_status = 'Fondos insuficientes';
                                    break;

                                case '71':
                                    $get_sms_status = 'Sin tarifa';
                                    break;

                                case '72':
                                case '73':
                                case '74':
                                case '75':
                                case '76':
                                case '77':
                                case '78':
                                    $get_sms_status = 'Error general';
                                    break;

                                case '80':
                                    $get_sms_status = 'Error tiempo de espera alcanzado';
                                    break;

                                case '81':
                                    $get_sms_status = 'Error de respuesta';
                                    break;

                                default:
                                    $get_sms_status = 'Error code not found';
                                    break;

                            }

                        }

                    }
                    curl_close($ch);
                    break;


                case 'EngageSpark':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $sendSMS = array(
                        "orgId" => $gateway_password,
                        "to" => $clphone,
                        "message" => $this->message,
                    );

                    if ($this->sender_id != '') {
                        $sendSMS['from'] = $this->sender_id;
                    }


                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                        curl_setopt($ch, CURLOPT_POST, 1);


                        $headers   = array();
                        $headers[] = "Authorization: Token $gateway_user_name";
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('error', $response)) {
                            if ($response['error'] == '') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['error'];
                            }
                        } else {
                            $get_sms_status = $result;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'MoviderCo':

                    $sendSMS = array(
                        "api_key" => $gateway_user_name,
                        "api_secret" => $gateway_password,
                        "to" => $this->cl_phone,
                        "text" => $this->message,
                    );

                    $postFields = http_build_query($sendSMS);

                    try {

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $gateway_url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => $postFields,
                            CURLOPT_HTTPHEADER => array(
                                "Accept: ",
                                "Content-Type: application/x-www-form-urlencoded",
                                "cache-control: no-cache"
                            ),
                        ));

                        $response = curl_exec($curl);
                        $err      = curl_error($curl);

                        curl_close($curl);

                        if ($err) {
                            $get_sms_status = $err;
                        } else {
                            $response = json_decode($response, true);
                            if (isset($response) && is_array($response)) {
                                if (array_key_exists('remaining_balance', $response)) {
                                    $get_sms_status = 'Success';
                                } elseif (array_key_exists('error', $response)) {
                                    $get_sms_status = $response['error']['description'];
                                } else {
                                    $get_sms_status = 'Invalid request';
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'BroadcasterSMS':

                    $phone = substr($this->cl_phone, -8);

                    $data = [
                        'user' => $gateway_user_name,
                        'message' => $this->message,
                        'msisdn' => $phone
                    ];

                    if ($this->sender_id) {
                        $data['tag'] = $this->sender_id;
                    }

                    $parameters = http_build_query($data);

                    $sms_sent_to_user = $gateway_url . "?" . $parameters;

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $headers   = array();
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $result = json_decode($result, true);

                            if (isset($result) && is_array($result) && array_key_exists('success', $result)) {
                                if ($result['success'] == true) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $result['info'];
                                }
                            } else {
                                $get_sms_status = 'Invalid Request';
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'SMSLive247':

                    $parameters = [
                        'cmd' => 'sendquickmsg',
                        'owneremail' => $gateway_user_name,
                        'subacct' => $gateway_password,
                        'subacctpwd' => $gateway_extra,
                        'message' => $this->message,
                        'sender' => $this->sender_id,
                        'sendto' => $this->cl_phone,
                        'msgtype' => 0
                    ];

                    $data             = http_build_query($parameters);
                    $sms_sent_to_user = $gateway_url . "?" . $data;
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            if (substr_count($get_sms_status, 'OK')) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = str_replace('ERR: ', '', $get_sms_status);
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'TheTexting':

                    $sendSMS = array(
                        "api_key" => $gateway_user_name,
                        "api_secret" => $gateway_password,
                        "to" => $this->cl_phone,
                        "text" => $this->message,
                    );

                    if ($this->sender_id != '') {
                        $sendSMS['from'] = $this->sender_id;
                    }

                    if ($msg_type == 'unicode') {
                        $sendSMS['type'] = 'unicode';
                    } else {
                        $sendSMS['type'] = 'text';
                    }

                    $postFields = http_build_query($sendSMS);

                    try {

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $gateway_url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => $postFields,
                            CURLOPT_HTTPHEADER => array(
                                "cache-control: no-cache",
                                "content-type: application/x-www-form-urlencoded",
                            ),
                        ));


                        $response = curl_exec($curl);
                        $err      = curl_error($curl);

                        curl_close($curl);

                        if ($err) {
                            $get_sms_status = $err;
                        } else {
                            $response = json_decode($response, true);

                            if (isset($response) && is_array($response) && array_key_exists('Status', $response)) {
                                if ($response['Status'] == 0) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $response['ErrorMessage'];
                                }
                            } else {
                                $get_sms_status = 'Invalid request';
                            }
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'VoxoloGy':

                    $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);

                    $sendSMS = array(
                        "api_no" => $this->sender_id,
                        "caller_no" => $clphone,
                        "text_message" => $this->message,
                    );

                    try {

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
                        curl_setopt($ch, CURLOPT_POST, 1);


                        $headers   = array();
                        $headers[] = "X-API-Key: $gateway_user_name";
                        $headers[] = "Content-Type: application/json";
                        $headers[] = "Cache-Control: no-cache";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('status', $response)) {
                            if ($response['status'] == 'accepted') {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['message'];
                            }
                        } else {
                            $get_sms_status = $result;
                        }

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'EtisalatLk':

                    $parameters = [
                        'USER' => $gateway_user_name,
                        'PWD' => $gateway_password,
                        'MASK' => $this->sender_id,
                        'MSG' => $this->message,
                        'NUM' => $this->cl_phone,
                    ];

                    $data             = http_build_query($parameters);
                    $sms_sent_to_user = $gateway_url . "?" . $data;
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            if (substr_count($get_sms_status, 'SUCCESS')) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = trim(str_replace('ERROR :', '', $get_sms_status));
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Wablas':

                    $data = [
                        'phone' => $this->cl_phone,
                        'message' => $this->message,
                    ];

                    try {
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_HTTPHEADER,
                            array(
                                "Authorization: $gateway_user_name",
                            )
                        );
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                        curl_setopt($curl, CURLOPT_URL, $gateway_url);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                        $result = curl_exec($curl);
                        curl_close($curl);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('status', $response)) {
                            if ($response['status'] == true) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['message'];
                            }
                        } else {
                            $get_sms_status = $result;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'Waboxapp':

                    $data = [
                        'token' => $gateway_user_name,
                        'uid' => $this->cl_phone,
                        'to' => $this->cl_phone,
                        'custom_uid' => time(),
                        'text' => $this->message,
                    ];

                    try {
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                        curl_setopt($curl, CURLOPT_URL, $gateway_url);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                        $result = curl_exec($curl);
                        curl_close($curl);

                        $response = json_decode($result, true);

                        if ($response && is_array($response)) {
                            if (array_key_exists('success', $response) && $response['success'] == true) {
                                $get_sms_status = 'Success';
                            } elseif (array_key_exists('error', $response)) {
                                $get_sms_status = $response['error'];
                            } else {
                                $get_sms_status = $result;
                            }
                        } else {
                            $get_sms_status = $result;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'SmsGateWay24':

                    $postarray = [
                        'token' => $gateway_user_name,
                        'sendto' => '+' . $this->cl_phone,
                        'body' => $this->message,
                        'device_id' => $gateway_password,
                        'sim' => $gateway_extra
                    ];


                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $gateway_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
                        $result = curl_exec($ch);
                        curl_close($ch);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('error', $response)) {
                            if ($response['error'] == 0) {
                                $get_sms_status = 'Success';
                            } else {
                                $get_sms_status = $response['message'];
                            }
                        } else {
                            $get_sms_status = $result;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'TelIntel':

                    $parameters = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'destinationNumber' => $this->cl_phone,
                        'message' => $this->message,
                        'from' => $this->sender_id
                    ];

                    if ($msg_type == 'unicode'){
                        $parameters['datacoding'] = 8;
                    }else {
                        $parameters['datacoding'] = 1;
                    }

                    $options = array(
                        'http' => array(
                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method' => 'POST',
                            'content' => json_encode($parameters),
                        ),
                    );
                    try{
                        $context = stream_context_create($options);
                        $result  = file_get_contents($gateway_url, false, $context);
                        $response = json_decode($result, true);

                        if (isset($response) && is_array($response) && array_key_exists('succeded', $response)){
                            if($response['succeded'] == true){
                                $get_sms_status = 'Success';
                            }else {
                                $get_sms_status = $response['transactionDetails'];
                            }
                        }else {
                            $get_sms_status = $result;
                        }
                    }catch (\Exception $ex){
                        $get_sms_status = $ex->getMessage();
                    }
                    break;

                case 'SmartBuyEstoreComAu':

                    $data = [
                        'key' => $gateway_user_name,
                        'devices' => $gateway_password,
                        'message' => $this->message,
                        'number' => $this->cl_phone
                    ];

                    $parameters = http_build_query($data);
                    $sms_sent_to_user = $gateway_url . "?" . $parameters;

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $headers   = array();
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $result = json_decode($result, true);

                            if (isset($result) && is_array($result) && array_key_exists('success', $result)) {
                                if ($result['success'] == true) {
                                    $get_sms_status = 'Success';
                                } else {
                                    $get_sms_status = $result['error']['message'];
                                }
                            } else {
                                $get_sms_status = $result;
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;


                case 'AirtelSrilanka':

                    $data = [
                        'username' => $gateway_user_name,
                        'password' => $gateway_password,
                        'msg' => $this->message,
                        'mobile_no' => $this->cl_phone,
                        'origin_addr' => $this->sender_id,
                        'msgtype' => 'UTF-8'
                    ];

                    $parameters = http_build_query($data);
                    $sms_sent_to_user = $gateway_url . "?" . $parameters;

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {

                            var_dump($result);
                            exit();

                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'SMSiPanel':

                    $data = [
                        'api' => $gateway_user_name,
                        'text' => $this->message,
                        'phone' => $this->cl_phone,
                        'sender' => $this->sender_id
                    ];

                    $parameters = http_build_query($data);

                    $sms_sent_to_user = $gateway_url . "?" . $parameters;

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            $get_sms_status = str_replace(urlencode($this->message), '', $result);
                            if (substr_count($get_sms_status, 'SMS Sent')) {
                                $get_sms_status = 'Success';
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;

                case 'TeleTalkComBD':

                    $data = [
                        'op' => 'SMS',
                        'user' => $gateway_user_name,
                        'pass' => $gateway_password,
                        'mobile' => $this->cl_phone,
                        'sms' => $this->message
                    ];

                    if ($msg_type == 'unicode'){
                        $data['charset'] = 'UTF-8';
                    }

                    $parameters = http_build_query($data);

                    $sms_sent_to_user = $gateway_url . "?" . $parameters;

                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $sms_sent_to_user);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $get_sms_status = curl_exec($ch);

                        if (curl_errno($ch)) {
                            $get_sms_status = curl_error($ch);
                        } else {
                            if (substr_count($get_sms_status, 'SUCCESS')) {
                                $get_sms_status = 'Success';
                            }else {
                                $get_sms_status = str_replace(['<reply>', 'FAILED,'], '', $get_sms_status);
                            }
                        }
                        curl_close($ch);

                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }

                    break;

                case 'TextBelt':

                    $data = [
                        'key' => $gateway_user_name,
                        'phone' => $this->cl_phone,
                        'message' => $this->message,
                    ];

                    try {
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $gateway_url);
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                        $result = curl_exec($curl);
                        curl_close($curl);

                        $response = json_decode($result, true);

                        if ($response && is_array($response) && array_key_exists('success', $response)) {
                            if ($response['success'] == true){
                                $get_sms_status = 'Success';
                            }else {
                                $get_sms_status = $response['error'];
                            }
                        } else {
                            $get_sms_status = $result;
                        }
                    } catch (\Exception $e) {
                        $get_sms_status = $e->getMessage();
                    }
                    break;


                case 'default':
                    $get_sms_status = 'Gateway not found';
                    break;

            }

        }

        if ($this->api_key != '') {
            $send_by = 'api';
        } else {
            $send_by = 'sender';
        }

        $sender_id = str_replace(['(', ')', '+', '-', ' '], '', trim($this->sender_id));
        $receiver  = str_replace(['(', ')', '+', '-', ' '], '', trim($this->cl_phone));

        $check = SMSHistory::where('sender', $sender_id)->where('receiver', $receiver)->where('send_by', 'receiver')->where('userid', $this->user_id)->first();

        if (!is_numeric($sender_id)) {
            $sender_id = $this->sender_id;
        }

        if ($check) {
            $sms_inbox = [
                'msg_id' => $check->id,
                'amount' => $this->msgcount,
                'message' => $this->message,
                'status' => htmlentities($get_sms_status),
                'send_by' => 'sender',
            ];
        } else {

            $sms_info = SMSHistory::create([
                'userid' => $this->user_id,
                'sender' => $sender_id,
                'receiver' => $receiver,
                'message' => $this->message,
                'amount' => $this->msgcount,
                'status' => htmlentities($get_sms_status),
                'sms_type' => $this->msg_type,
                'api_key' => $this->api_key,
                'use_gateway' => $this->gateway->id,
                'send_by' => $send_by
            ]);

            $sms_inbox = [
                'msg_id' => $sms_info->id,
                'amount' => $this->msgcount,
                'message' => $this->message,
                'status' => htmlentities($get_sms_status),
                'send_by' => 'sender',
            ];
        }

        SMSInbox::create($sms_inbox);

        if ($this->campaign_id != '') {
            $campaign_list = CampaignSubscriptionList::find($this->campaign_id);

            if ($campaign_list) {
                $campaign_list->status = $get_sms_status;
                $campaign_list->save();

                $campaign = Campaigns::where('campaign_id', $campaign_list->campaign_id)->first();
                if ($campaign) {
                    if (substr_count(strtolower($get_sms_status), 'success') == 0) {
                        $campaign->total_failed += 1;
                    } else {
                        $campaign->total_delivered += 1;
                    }

                    $campaign->save();
                }
            }
        }

        if ($this->user_id != '0') {
            if ($this->gateway->settings != 'InfoBip') {
                if (substr_count(strtolower($get_sms_status), 'success') == 0) {

                    $client = Client::find($this->user_id);

                    $phone    = $this->cl_phone;
                    $msgcount = $this->msgcount;
                    $c_phone  = PhoneNumber::get_code($phone);
                    $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

                    if ($sms_cost) {

                        $phoneUtil         = PhoneNumberUtil::getInstance();
                        $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                        $area_code_exist   = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                        if ($area_code_exist) {
                            $format            = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                            $get_format_data   = explode(" ", $format);
                            $operator_settings = explode('-', $get_format_data[1])[0];

                        } else {
                            $carrierMapper     = PhoneNumberToCarrierMapper::getInstance();
                            $operator_settings = $carrierMapper->getNameForNumber($phoneNumberObject, 'en');
                        }

                        $get_operator = Operator::where('operator_setting', $operator_settings)->where('coverage_id', $sms_cost->id)->first();

                        if ($get_operator) {
                            $total_cost = ($get_operator->plain_price * $msgcount);
                        } else {
                            $total_cost = ($sms_cost->plain_tariff * $msgcount);
                        }

                        $client->sms_limit += $total_cost;
                        $client->save();

                    }

                }
            }
        }

        $this->get_sms_status = $get_sms_status;

    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->get_sms_status;
    }
}
