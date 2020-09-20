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
use Nexmo\Client\Credentials\Basic;
use Nexmo\Client\Exception\Exception;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class SendBulkVoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $cl_phone;
    protected $user_id;
    protected $gateway;
    protected $gateway_credential;
    protected $sender_id;
    protected $message;
    protected $msgcount;
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
    public function __construct($user_id, $cl_phone, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $api_key = '', $msg_type = 'voice', $campaign_id = '')
    {
        $this->cl_phone           = $cl_phone;
        $this->gateway            = $gateway;
        $this->gateway_credential = $gateway_credential;
        $this->sender_id          = $sender_id;
        $this->message            = $message;
        $this->msgcount           = $msgcount;
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
        $gateway_name      = $this->gateway->settings;
        $gateway_user_name = $this->gateway_credential->username;
        $gateway_password  = $this->gateway_credential->password;

        $get_sms_status = 'Unknown error';

        switch ($gateway_name) {

            case 'Twilio':

                $directory = trim(base_path(''), 'application');
                $directory = rtrim($directory, '/') . '/voice';

                if (!file_exists($directory) && !is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                $file_name = date('Ymdhis') . '.xml';
                $file_path = $directory . '/' . $file_name;

                try {
                    $twilio    = new Client($gateway_user_name, $gateway_password);
                    $phone     = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                    $sender_id = $this->sender_id;

                    $string = '<Response>
    <Say voice="alice">' . $this->message . '</Say>
</Response>';

                    $get_voice_data = new \SimpleXMLElement($string);
                    file_put_contents($file_path, $get_voice_data->asXML());

                    $get_file_path = asset('/voice') . '/' . $file_name;

                    $get_response = $twilio->calls->create($phone, $sender_id, array(
                        'url' => $get_file_path
                    ));

                    if ($get_response->status == 'queued') {
                        $get_sms_status = 'Success' . '|' . $get_response->sid;
                    } else {
                        $get_sms_status = $get_response->status . '|' . $get_response->sid;
                    }

                } catch (TwilioException $e) {
                    $get_sms_status = $e->getMessage();
                }
                break;

            case 'Plivo':

                $directory = trim(base_path(''), 'application');
                $directory = rtrim($directory, '/') . '/voice';

                if (!file_exists($directory) && !is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                $file_name = date('Ymdhis') . '.xml';
                $file_path = $directory . '/' . $file_name;

                $string = '<Response>
          <Speak>' . $this->message . '</Speak>
</Response>';

                $get_voice_data = new \SimpleXMLElement($string);
                file_put_contents($file_path, $get_voice_data->asXML());

                $get_file_path = asset('/voice') . '/' . $file_name;

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, "https://api.plivo.com/v1/Account/$gateway_user_name/Call/");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"to\": \"$this->cl_phone\",\"from\": \"$this->sender_id\", \"answer_url\": \"$get_file_path\", \"answer_method\": \"GET\"}");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERPWD, "$gateway_user_name" . ":" . "$gateway_password");

                $headers = array();
                $headers[] = "Content-Type: application/json";
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    $get_sms_status = curl_error($ch);
                }
                curl_close ($ch);

                $response = json_decode($result,true);

                if (json_last_error() == JSON_ERROR_NONE) {
                    if (isset($response) && is_array($response) && array_key_exists('message', $response)) {
                        if (substr_count($response['message'], 'fired') == 0) {
                            $get_sms_status = 'Success|'.$response['request_uuid'];
                        }else{
                            $get_sms_status = $response['message'];
                        }
                    } else {
                        $get_sms_status = 'Unknown error';
                    }
                } else {
                    $get_sms_status = trim($result);
                }

                break;

            case 'Nexmo':
                $directory = trim(base_path(''), 'application');
                $directory = rtrim($directory, '/') . '/voice';

                if (!file_exists($directory) && !is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                $file_name = date('Ymdhis') . '.xml';
                $file_path = $directory . '/' . $file_name;


                $phone     = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->cl_phone);
                $sender_id = '+' . str_replace(['(', ')', '+', '-', ' '], '', $this->sender_id);

                try {

                    $json_data = [
                        'action' => 'talk',
                        'voiceName' => 'Russell',
                        'text' => $this->message
                    ];

                    file_put_contents($file_path, json_encode($json_data));

                    $get_file_path = asset('/voice') . '/' . $file_name;

                    $client = new \Nexmo\Client(new Basic($gateway_user_name, $gateway_password));

                    $response = $client->calls()->create([
                        'to' => [[
                            'type' => 'phone',
                            'number' => $phone
                        ]],
                        'from' => [
                            'type' => 'phone',
                            'number' => $sender_id
                        ],
                        'answer_url' => [$get_file_path],
                    ]);

                    if ($response['status'] == 0) {
                        $get_sms_status = 'Success';
                    } else {
                        $get_sms_status = 'Unknown Error';
                    }

                } catch (Exception $exception) {
                    $get_sms_status = $exception->getMessage();
                }

                break;

            case 'InfoBip':

                $message_id = _raid(19);

                // creating an object for sending SMS
                $destination = array("messageId" => $message_id, "to" => $this->cl_phone);
                $message     = array(
                    "from" => $this->sender_id,
                    "destinations" => array($destination),
                    "text" => $this->message
                );
                $postData    = array("messages" => array($message));
                // encoding object
                $postDataJson = json_encode($postData);


                $ch     = curl_init();
                $header = array("Content-Type:application/json", "Accept:application/json");

                // setting options
                curl_setopt($ch, CURLOPT_URL, 'https://api.infobip.com/tts/3/advanced');
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

            case 'MessageBird':

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.messagebird.com/voicemessages');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "recipients=$this->cl_phone&originator=$this->sender_id&body=$this->message");
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

                if (isset($response) && is_array($response) && array_key_exists('id', $response)) {
                    $get_sms_status = 'Success|' . $response['id'];
                } elseif (is_array($response) && array_key_exists('errors', $response)) {
                    $get_sms_status = $response['errors'][0]['description'];
                } else {
                    $get_sms_status = 'Unknown Error';
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
            'send_by' => $send_by
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

            if (substr_count($get_sms_status, 'Success') == 0) {

                $client = \App\Client::find($this->user_id);

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
                        $total_cost = ($get_operator->voice_price * $msgcount);
                    } else {
                        $total_cost = ($sms_cost->voice_tariff * $msgcount);
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
