<?php

namespace App\Jobs;

use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\PhoneNumber;
use App\IntCountryCodes;
use App\Operator;
use App\SMSHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use Twilio\Rest\Client;

class SendBulkMMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cl_phone;
    protected $user_id;
    protected $gateway;
    protected $gateway_credential;
    protected $sender_id;
    protected $message;
    protected $msgcount;
    protected $media_url;
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
    public function __construct($user_id, $cl_phone, $gateway, $gateway_credential, $sender_id, $message, $media_url, $api_key = '', $msg_type = 'mms', $campaign_id = '')
    {
        $this->cl_phone           = $cl_phone;
        $this->gateway            = $gateway;
        $this->gateway_credential = $gateway_credential;
        $this->sender_id          = $sender_id;
        $this->message            = $message;
        $this->media_url          = $media_url;
        $this->api_key            = $api_key;
        $this->user_id            = $user_id;
        $this->msg_type           = $msg_type;
        $this->campaign_id        = $campaign_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $gateway_url       = rtrim($this->gateway->api_link, '/');
        $gateway_name      = $this->gateway->settings;
        $gateway_user_name = $this->gateway_credential->username;
        $gateway_password  = $this->gateway_credential->password;
        $gateway_extra     = $this->gateway_credential->extra;

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($this->message)));
        if ($msgcount <= 160) {
            $msgcount = 1;
        } else {
            $msgcount = $msgcount / 157;
        }

        $this->msgcount = $msgcount;

        switch ($gateway_name) {
            case 'Twilio':
                try {
                    $client    = new Client($gateway_user_name, $gateway_password);
                    $phone     = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sender_id = $this->sender_id;

                    $get_response = $client->messages->create(
                        $phone, array(
                            'from' => $sender_id,
                            'body' => $this->message,
                            'mediaUrl' => $this->media_url
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

            case 'Text Local':

                $sender  = urlencode($this->sender_id);
                $message = rawurlencode($this->message);

                $data = array('username' => $gateway_user_name, 'hash' => $gateway_password, 'numbers' => $this->cl_phone, "sender" => $sender, "message" => $message, 'url' => $this->media_url);

                // Send the POST request with cURL
                $ch = curl_init("https://api.txtlocal.com/send_mms/");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);

                $get_data = json_decode($response, true);

                if (array_key_exists('status', $get_data)) {
                    if ($get_data['status'] == 'failure') {
                        foreach ($get_data['errors'] as $err) {
                            $get_sms_status = $err['message'];
                        }
                    } else {
                        $get_sms_status = 'Success';
                    }

                } else {
                    $get_sms_status = 'failed';
                }
                break;

            case 'SMSGlobal':
                $clphone   = str_replace(" ", "", $this->cl_phone); #Remove any whitespace
                $clphone   = str_replace('+', '', $clphone);
                $sender_id = urlencode($this->sender_id);
                $message   = urlencode($this->message);
                $file_name = basename($this->media_url);
                $mime_type = mime_content_type($this->media_url);

                $sms_sent_to_user = "https://api.smsglobal.com/mms/sendmms.php?user=$gateway_user_name" . "&password=$gateway_password" . "&from=$sender_id" . "&number=$clphone" . "&message=$message&attachmentx=$this->media_url&typex=$mime_type&namex=$file_name";

                try {
                    $get_sms_status = file_get_contents($sms_sent_to_user);
                    if (substr_count($get_sms_status, 'SUCCESS')) {
                        $get_sms_status = 'Success';
                    } else {
                        $get_sms_status = str_replace('ERROR:', '', $get_sms_status);
                    }
                } catch (\Exception $e) {
                    $get_sms_status = $e->getMessage();
                }

                break;

            case 'MessageBird':

                $sender_id = str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.messagebird.com/mms');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "recipients=$this->cl_phone&originator=$sender_id&body=$this->message&mediaUrls[]=$this->media_url");
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

                $gateway_url = rtrim($gateway_url, '/') . '/MMS';

                $sms = [
                    "from" => $sender_id, /* Can be up to 11 alphanumeric characters */
                    "to" => $phone, /* The mobile number you want to send to */
                    "image" => $this->media_url
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

            case 'WhatsAppChatApi':

                $clphone = str_replace(['+', '(', ')', '-', ' '], '', $this->cl_phone);

                $file_name = basename(parse_url($this->media_url)['path']);

                $data = [
                    'phone' => $clphone,
                    'body' => $this->media_url,
                    'filename' => $file_name
                ];

                $json = json_encode($data);

                $url     = $gateway_url . '/message?token=' . $gateway_user_name;
                $options = stream_context_create(['http' => [
                    'method' => 'POST',
                    'header' => 'Content-type: application/json',
                    'content' => $json
                ]
                ]);
                $result  = file_get_contents($url, false, $options);

                $json_array[] = array();
                $json_array   = json_decode($result, true);

                if (isset($json_array) && is_array($json_array) && array_key_exists('sent', $json_array)) {
                    if ($json_array['sent'] == true) {
                        $get_sms_status = 'Success';
                    } else {
                        $get_sms_status = $json_array['message'];
                    }
                } else {
                    $get_sms_status = 'Invalid request';
                }

                break;

            case 'APIWHA':

                $clphone = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);


                $data = http_build_query([
                    'apikey' => $gateway_user_name,
                    'number' => $clphone,
                    'text' => $this->media_url
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

            case 'FlowRoute':
                $phone     = str_replace(['+', '(', ')', '-', " "], '', $this->cl_phone);
                $sender_id = str_replace(['+', '(', ')', '-', " "], '', $this->sender_id);

                $sms = [
                    "from" => $sender_id,
                    "to" => $phone,
                    "body" => $this->message,
                    'is_mms' => true,
                    'media_urls' => [
                        $this->media_url
                    ]
                ];

                try {

                    $headers   = array();
                    $headers[] = 'Content-Type: application/vnd.api+json';

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $gateway_url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms, JSON_UNESCAPED_SLASHES));
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

            case 'SignalWire':

                $clphone   = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                $sender_id = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);

                $post_field = http_build_query([
                    'From' => trim($sender_id),
                    'Body' => $this->message,
                    'To' => trim($clphone),
                    'MediaUrl' => $this->media_url
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
                } else {
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
                }
                curl_close($ch);

                break;

            case 'Thinq':

                $gateway_url = rtrim($gateway_url, '/') . '/' . $gateway_extra . '/product/origination/mms/send';

                $sendSMS = [
                    'to_did' => $this->cl_phone,
                    'from_did' => $this->sender_id,
                    'media_url' => $this->media_url
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

                    if ($response && is_array($response) && array_key_exists('code', $response) && array_key_exists('message', $response)) {

                        if ($response['code'] == 200) {
                            $get_sms_status = 'Success';
                        } else {
                            $get_sms_status = $response['message'];
                        }

                    } else {
                        $get_sms_status = 'Invalid request';
                    }
                }
                break;


            case 'Voyant':

                $gateway_url = "https://api.voyant.com/messaging/mms/send?sessionId=$gateway_user_name";


                $parameters = [
                    'text' => $this->message,
                    'to' => [$this->cl_phone],
                    'from' => $this->sender_id,
                    'mediaUrls' => [$this->media_url],
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


            case 'Wablas':

                $get_extension = parse_url($this->media_url);
                $extension     = pathinfo($get_extension['path'], PATHINFO_EXTENSION);

                $data = [
                    'phone' => $this->cl_phone,
                    'caption' => $this->message,
                ];

                if ($extension == 'png' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'jpg') {
                    $data['image'] = $this->media_url;
                    $gateway_url   = 'https://console.wablas.com/api/send-image';
                }

                if ($extension == 'mp4' || $extension == '3gp' || $extension == 'mpg' || $extension == 'mpeg') {
                    $data['video'] = $this->media_url;
                    $gateway_url   = 'https://console.wablas.com/api/send-video';
                }

                if ($extension == 'mp3' || $extension == 'pdf') {
                    $data['document'] = $this->media_url;
                    $gateway_url      = 'https://console.wablas.com/api/send-document';
                }

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
                    'caption' => $this->message,
                    'description' => $this->message,
                    'url' => $this->media_url
                ];

                try {
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($curl, CURLOPT_URL, 'https://www.waboxapp.com/api/send/media');
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


            case 'default':

                $get_sms_status = 'Gateway not found';

                break;

        }


        if ($this->api_key != '') {
            $send_by = 'api';
        } else {
            $send_by = 'sender';
        }


        SMSHistory::create([
            'userid' => $this->user_id,
            'sender' => $this->sender_id,
            'receiver' => (string)$this->cl_phone,
            'message' => $this->message,
            'amount' => $this->msgcount,
            'status' => htmlentities($get_sms_status),
            'sms_type' => $this->msg_type,
            'api_key' => $this->api_key,
            'use_gateway' => $this->gateway->id,
            'send_by' => $send_by,
            'media_url' => $this->media_url
        ]);


        if ($this->campaign_id != '') {
            $campaign_list = CampaignSubscriptionList::find($this->campaign_id);

            if ($campaign_list) {
                $campaign_list->status = $get_sms_status;
                $campaign_list->save();

                $campaign = Campaigns::where('campaign_id', $campaign_list->campaign_id)->first();
                if ($campaign) {
                    if (substr_count($get_sms_status, 'Success') == 0) {
                        $campaign->total_failed += 1;
                    } else {
                        $campaign->total_delivered += 1;
                    }

                    $campaign->save();
                }
            }
        }

        if ($this->user_id != '0') {
            $client = \App\Client::find($this->user_id);

            if (substr_count($get_sms_status, 'Success') == 0) {

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
                        $total_cost = ($get_operator->mms_price * $msgcount);
                    } else {
                        $total_cost = ($sms_cost->mms_tariff * $msgcount);
                    }

                    $client->sms_limit += $total_cost;
                    $client->save();

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
