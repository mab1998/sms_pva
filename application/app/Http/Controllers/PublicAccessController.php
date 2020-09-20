<?php

namespace App\Http\Controllers;

use App\Admin;
use App\BlackListContact;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\PhoneNumber;
use App\Client;
use App\ContactList;
use App\CustomSMSGateways;
use App\ImportPhoneNumber;
use App\IntCountryCodes;
use App\Jobs\SendBulkMMS;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\Keywords;
use App\Operator;
use App\SenderIdManage;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use App\SpamWord;
use App\TwoWayCommunication;
use Illuminate\Http\Request;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use Nexmo\Message\InboundMessage;
use Twilio\Twiml;

class PublicAccessController extends Controller
{
    //======================================================================
    // ultimateSMSApi Function Start Here
    //======================================================================
    public function ultimateSMSApi(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'code' => '100',
                'message' => 'API option not work in demo version'
            ]);
        }

        $action        = $request->input('action');
        $api_key       = $request->input('api_key');
        $to            = $request->input('to');
        $from          = $request->input('from');
        $sms           = $request->input('sms');
        $unicode       = $request->input('unicode');
        $voice         = $request->input('voice');
        $mms           = $request->input('mms');
        $media_url     = $request->input('media_url');
        $schedule_time = $request->input('schedule');


        if ($action == '' && $api_key == '') {
            return response()->json([
                'code' => '100',
                'message' => 'Bad gateway requested'
            ]);
        }
        switch ($action) {
            case 'send-sms':

                if ($to == '' && $from == '' && $sms == '') {
                    return response()->json([
                        'code' => '100',
                        'message' => 'Bad gateway requested'
                    ]);
                }

                $results = array_filter(explode(',', $to));


                if (isset($results) && is_array($results) && count($results) <= 100) {

                    $msg_type = 'plain';

                    if ($unicode == 1) {
                        $msg_type = 'unicode';
                    }

                    if ($voice == 1) {
                        $msg_type = 'voice';
                    }

                    if ($mms == 1) {
                        $msg_type = 'mms';
                        if ($media_url == '') {
                            return response()->json([
                                'code' => '110',
                                'message' => 'Media url required'
                            ]);
                        }
                    }

                    if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'arabic' && $msg_type != 'voice' && $msg_type != 'mms') {
                        return response()->json([
                            'code' => '107',
                            'message' => 'Invalid SMS Type'
                        ]);
                    }


                    if (strlen($sms) != strlen(utf8_decode($sms))) {
                        if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                            $msg_type = 'unicode';
                        }
                    }


                    if ($schedule_time != '') {
                        if (\DateTime::createFromFormat('m/d/Y h:i A', $schedule_time) !== FALSE) {
                            $schedule_time = date('Y-m-d H:i:s', strtotime($schedule_time));
                        } else {
                            return response()->json([
                                'code' => '109',
                                'message' => 'Invalid Schedule Time'
                            ]);
                        }
                    }

                    if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($sms)));
                        if ($msgcount <= 160) {
                            $msgcount = 1;
                        } else {
                            $msgcount = $msgcount / 157;
                        }
                    }

                    if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                        $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($sms)), 'UTF-8');

                        if ($msgcount <= 70) {
                            $msgcount = 1;
                        } else {
                            $msgcount = $msgcount / 67;
                        }
                    }

                    $msgcount = ceil($msgcount);

                    if (app_config('api_key') == $api_key) {
                        $gateway = SMSGateways::find(app_config('sms_api_gateway'));

                        if (!$gateway) {
                            return response()->json([
                                'code' => '108',
                                'message' => 'SMS Gateway not active'
                            ]);
                        }

                        $gateway_credential = null;
                        $cg_info            = null;
                        if ($gateway->custom == 'Yes') {
                            if ($gateway->type == 'smpp') {
                                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                                if ($gateway_credential == null) {
                                    return response()->json([
                                        'code' => '108',
                                        'message' => 'SMS Gateway not active'
                                    ]);
                                }
                            } else {
                                $cg_info = CustomSMSGateways::where('gateway_id', $gateway->id)->first();
                            }

                        } else {
                            $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                            if ($gateway_credential == null) {
                                return response()->json([
                                    'code' => '108',
                                    'message' => 'SMS Gateway not active'
                                ]);
                            }
                        }

                        $invalid = [];

                        foreach ($results as $number) {
                            $number = str_replace(['(', ')', '+', '-', ' '], '', $number);

                            if (is_numeric($number)) {
                                $phoneUtil         = PhoneNumberUtil::getInstance();
                                $phoneNumberObject = $phoneUtil->parse('+' . $number, null);
                                $isValid           = $phoneUtil->isValidNumber($phoneNumberObject);
                            } else {
                                $isValid = false;
                            }

                            if (!$isValid) {
                                array_push($invalid, $number);
                            }
                        }

                        if (count($invalid) > 0) {
                            return response()->json([
                                'code' => '103',
                                'message' => count($invalid) . ' invalid Phone Number on your list'
                            ]);
                        }

                        $filtered_data = [];
                        $blacklist     = BlackListContact::select('numbers')->where('user_id', 0)->get()->toArray();

                        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                            $blacklist = array_column($blacklist, 'numbers');

                            array_filter($results, function ($element) use ($blacklist, &$filtered_data, $request) {
                                $element = trim($element);
                                if (!in_array($element, $blacklist)) {
                                    array_push($filtered_data, $element);
                                }
                            });

                            $results = array_values($filtered_data);
                        }

                        if (count($results) <= 0) {
                            return response()->json([
                                'code' => '112',
                                'message' => 'Destination number contain in blacklist number'
                            ]);
                        }

                        if ($schedule_time != '') {
                            $campaign_id = uniqid('C');

                            $campaign = Campaigns::create([
                                'campaign_id' => $campaign_id,
                                'user_id' => 0,
                                'sender' => $from,
                                'sms_type' => $msg_type,
                                'camp_type' => 'scheduled',
                                'status' => 'Scheduled',
                                'use_gateway' => $gateway->id,
                                'total_recipient' => count($results),
                                'run_at' => $schedule_time,
                                'media_url' => $media_url,
                            ]);

                            if ($campaign) {
                                $final_insert_data = [];
                                foreach ($results as $r) {
                                    $number    = str_replace(['(', ')', '+', '-', ' '], '', $r);
                                    $push_data = [
                                        'campaign_id' => $campaign_id,
                                        'number' => $number,
                                        'message' => $sms,
                                        'amount' => $msgcount,
                                        'status' => 'scheduled',
                                        'submitted_time' => $schedule_time
                                    ];

                                    array_push($final_insert_data, $push_data);
                                }

                                $campaign_list = CampaignSubscriptionList::insert($final_insert_data);

                                if ($campaign_list) {
                                    return response()->json([
                                        'code' => 'ok',
                                        'message' => 'SMS Scheduled successfully.',
                                        'balance' => 'Unlimited',
                                        'user' => 'Admin'
                                    ]);
                                }
                            }

                            $campaign->delete();

                            return response()->json([
                                'code' => '100',
                                'message' => 'Bad gateway requested'
                            ]);
                        }

                        foreach ($results as $r) {

                            $number = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                            if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                                $this->dispatch(new SendBulkSMS(0, $number, $gateway, $gateway_credential, $from, $sms, $msgcount, $cg_info, $api_key, $msg_type));
                            }

                            if ($msg_type == 'voice') {
                                $this->dispatch(new SendBulkVoice(0, $number, $gateway, $gateway_credential, $from, $sms, $msgcount, $api_key));
                            }
                            if ($msg_type == 'mms') {
                                $this->dispatch(new SendBulkMMS(0, $number, $gateway, $gateway_credential, $from, $sms, $media_url, $api_key));
                            }
                        }

                        return response()->json([
                            'code' => 'ok',
                            'message' => 'Successfully Send',
                            'balance' => 'Unlimited',
                            'user' => 'Admin'
                        ]);

                    } else {
                        $client = Client::where('api_key', $api_key)->where('api_access', 'Yes')->first();

                        if ($client) {

                            if ($client->status != 'Active') {
                                return response()->json([
                                    'code' => '112',
                                    'message' => 'Inactive account'
                                ]);
                            }

                            if ($client->api_access != 'Yes') {
                                return response()->json([
                                    'code' => '112',
                                    'message' => 'Permission denied. Api access not available'
                                ]);
                            }

                            if (app_config('fraud_detection') == 1) {
                                $spam_word = SpamWord::all()->toArray();
                                if (is_array($spam_word) && count($spam_word) > 0) {
                                    $spam_word   = array_column($spam_word, 'word');
                                    $search_word = implode('|', $spam_word);
                                    if (preg_match('(' . $search_word . ')', $sms) === 1) {
                                        return response()->json([
                                            'code' => '111',
                                            'message' => 'SMS contain spam word.'
                                        ]);
                                    }
                                }
                            }

                            $user_id = $client->id;

                            if ($from != '' && app_config('sender_id_verification') == '1') {

                                $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
                                $all_ids       = [];

                                foreach ($all_sender_id as $sid) {
                                    $client_array = json_decode($sid->cl_id);

                                    if (in_array('0', $client_array)) {
                                        array_push($all_ids, $from);
                                    } elseif (in_array($client->id, $client_array)) {
                                        array_push($all_ids, $sid->sender_id);
                                    }
                                }

                                $all_ids = array_unique($all_ids);

                                if (!in_array($from, $all_ids)) {
                                    return response()->json([
                                        'code' => '106',
                                        'message' => 'Invalid Sender id'
                                    ]);
                                }
                            }

                            $gateway = SMSGateways::find($client->api_gateway);

                            if (!$gateway) {
                                return response()->json([
                                    'code' => '108',
                                    'message' => 'SMS Gateway not active'
                                ]);
                            }

                            $gateway_credential = null;
                            $cg_info            = null;
                            if ($gateway->custom == 'Yes') {
                                if ($gateway->type == 'smpp') {
                                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                                    if ($gateway_credential == null) {
                                        return response()->json([
                                            'code' => '108',
                                            'message' => 'SMS Gateway not active'
                                        ]);
                                    }
                                } else {
                                    $cg_info = CustomSMSGateways::where('gateway_id', $gateway->id)->first();
                                }

                            } else {
                                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                                if ($gateway_credential == null) {
                                    return response()->json([
                                        'code' => '108',
                                        'message' => 'SMS Gateway not active'
                                    ]);
                                }
                            }


                            $invalid = [];

                            foreach ($results as $number) {
                                $number = str_replace(['(', ')', '+', '-', ' '], '', $number);

                                if (is_numeric($number)) {
                                    $phoneUtil         = PhoneNumberUtil::getInstance();
                                    $phoneNumberObject = $phoneUtil->parse('+' . $number, null);
                                    $isValid           = $phoneUtil->isValidNumber($phoneNumberObject);
                                } else {
                                    $isValid = false;
                                }

                                if (!$isValid) {
                                    array_push($invalid, $number);
                                }
                            }

                            if (count($invalid) > 0) {
                                return response()->json([
                                    'code' => '103',
                                    'message' => count($invalid) . ' invalid Phone Number on your list'
                                ]);
                            }

                            $filtered_data = [];
                            $blacklist     = BlackListContact::select('numbers')->where('user_id', $user_id)->get()->toArray();

                            if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
                                $blacklist = array_column($blacklist, 'numbers');
                                array_filter($results, function ($element) use ($blacklist, &$filtered_data, $request) {
                                    $element = trim($element);
                                    if (!in_array($element, $blacklist)) {
                                        array_push($filtered_data, $element);
                                    }
                                });
                                $results = array_values($filtered_data);
                            }

                            if (count($results) <= 0) {
                                return response()->json([
                                    'code' => '112',
                                    'message' => 'Destination number contain in blacklist number'
                                ]);
                            }


                            $get_final_data = [];
                            $get_cost       = 0;

                            foreach ($results as $r) {

                                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

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

                                        $sms_charge = $get_operator->plain_price;

                                        if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                                            $sms_charge = $get_operator->plain_price;
                                        }

                                        if ($msg_type == 'voice') {
                                            $sms_charge = $get_operator->voice_price;
                                        }

                                        if ($msg_type == 'mms') {
                                            $sms_charge = $get_operator->mms_price;
                                        }

                                        $get_cost += $sms_charge;
                                    } else {
                                        $sms_charge = $sms_cost->plain_tariff;

                                        if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                                            $sms_charge = $sms_cost->plain_tariff;
                                        }

                                        if ($msg_type == 'voice') {
                                            $sms_charge = $sms_cost->voice_tariff;
                                        }

                                        if ($msg_type == 'mms') {
                                            $sms_charge = $sms_cost->mms_tariff;
                                        }

                                        $get_cost += $sms_charge;
                                    }
                                } else {
                                    array_push($get_inactive_coverage, $phone);
                                    continue;
                                }

                                array_push($get_final_data, $phone);
                            }

                            $total_cost = $get_cost * $msgcount;

                            if ($total_cost == 0) {
                                return response()->json([
                                    'code' => '105',
                                    'message' => 'Insufficient balance or invalid coverage'
                                ]);
                            }

                            if ($total_cost > $client->sms_limit) {
                                return response()->json([
                                    'code' => '105',
                                    'message' => 'Insufficient balance'
                                ]);
                            }

                            $remain_sms        = $client->sms_limit - $total_cost;
                            $client->sms_limit = $remain_sms;
                            $client->save();

                            $balance = $client->sms_limit;

                            if ($schedule_time != '') {
                                $campaign_id = uniqid('C');

                                $campaign = Campaigns::create([
                                    'campaign_id' => $campaign_id,
                                    'user_id' => $user_id,
                                    'sender' => $from,
                                    'sms_type' => $msg_type,
                                    'camp_type' => 'scheduled',
                                    'status' => 'Scheduled',
                                    'use_gateway' => $gateway->id,
                                    'total_recipient' => count($get_final_data),
                                    'run_at' => $schedule_time,
                                    'media_url' => $media_url,
                                ]);

                                if ($campaign) {
                                    $final_insert_data = [];
                                    foreach ($get_final_data as $r) {
                                        $number    = str_replace(['(', ')', '+', '-', ' '], '', $r);
                                        $push_data = [
                                            'campaign_id' => $campaign_id,
                                            'number' => $number,
                                            'message' => $sms,
                                            'amount' => $msgcount,
                                            'status' => 'scheduled',
                                            'submitted_time' => $schedule_time
                                        ];

                                        array_push($final_insert_data, $push_data);
                                    }

                                    $campaign_list = CampaignSubscriptionList::insert($final_insert_data);

                                    if ($campaign_list) {
                                        return response()->json([
                                            'code' => 'ok',
                                            'message' => 'SMS Scheduled successfully.',
                                            'balance' => $balance,
                                            'user' => $client->fname . ' ' . $client->lname
                                        ]);
                                    }
                                }

                                $campaign->delete();

                                return response()->json([
                                    'code' => '100',
                                    'message' => 'Bad gateway requested'
                                ]);
                            }

                            foreach ($get_final_data as $r) {

                                $number = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                                if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                                    $this->dispatch(new SendBulkSMS($user_id, $number, $gateway, $gateway_credential, $from, $sms, $msgcount, $cg_info, $api_key, $msg_type));
                                }

                                if ($msg_type == 'voice') {
                                    $this->dispatch(new SendBulkVoice($user_id, $number, $gateway, $gateway_credential, $from, $sms, $msgcount, $api_key));
                                }
                                if ($msg_type == 'mms') {
                                    $this->dispatch(new SendBulkMMS($user_id, $number, $gateway, $gateway_credential, $from, $sms, $media_url, $api_key));
                                }
                            }

                            return response()->json([
                                'code' => 'ok',
                                'message' => 'Successfully Send',
                                'balance' => $balance,
                                'user' => $client->fname . ' ' . $client->lname
                            ]);

                        } else {
                            return response()->json([
                                'code' => '102',
                                'message' => 'Authentication Failed'
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'code' => '113',
                        'message' => 'You can not send more than 100 sms using api option'
                    ]);
                }

                break;

            case 'get-inbox':
                $all_messages = SMSHistory::where('api_key', $api_key)->select('id', 'sender', 'receiver', 'message', 'amount', 'status', 'sms_type')->get();
                $return_data  = [];
                $all_message  = [];
                foreach ($all_messages as $msg) {
                    $return_data['id']       = $msg->id;
                    $return_data['from']     = $msg->sender;
                    $return_data['phone']    = $msg->receiver;
                    $return_data['sms']      = $msg->message;
                    $return_data['segments'] = $msg->amount;
                    $return_data['status']   = $msg->status;
                    $return_data['type']     = $msg->sms_type;

                    $get_inbox            = SMSInbox::where('msg_id', $msg->id)->select('amount', 'message', 'status', 'send_by')->get()->toArray();
                    $return_data['inbox'] = $get_inbox;

                    array_push($all_message, $return_data);
                }

                return response()->json($all_message);

                break;

            case 'check-balance':
                if (app_config('api_key') == $api_key) {

                    return response()->json([
                        'balance' => 'Unlimited',
                        'user' => 'Admin',
                        'country' => app_config('Country')
                    ]);

                } else {
                    $client = Client::where('api_key', $api_key)->where('api_access', 'Yes')->first();
                    if ($client) {
                        $balance = round($client->sms_limit);

                        return response()->json([
                            'balance' => $balance,
                            'user' => $client->fname . ' ' . $client->lname,
                            'country' => $client->country
                        ]);
                    } else {
                        return response()->json([
                            'code' => '102',
                            'message' => 'Authentication Failed'
                        ]);
                    }
                }
                break;

            default:
                return response()->json([
                    'code' => '101',
                    'message' => 'Wrong action'
                ]);
                break;
        }
    }

//======================================================================
// insertSMS Function Start Here
//======================================================================
    public function insertSMS($number, $msg_count, $body, $to = '', $gateway = '')
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'code' => 'error',
                'message' => 'Two way feature no work in demo version'
            ]);
        }

        $client_id = 0;
        $number = str_replace(['(', ')', '+', '-', ' '], '', trim($number));
        $get_history = SMSHistory::where('receiver', 'like', '%'.$number.'%');
        if ($to != '') {
            $to = str_replace(['(', ')', '+', '-', ' '], '', trim($to));

            $sender_id = SenderIdManage::where('sender_id', 'like', '%'.$to.'%')->where('status','unblock')->first();

            if ($sender_id){
                $user_id = json_decode($sender_id->cl_id);
                if (isset($user_id) && is_array($user_id) && count($user_id) > 0){
                    $client_id = $user_id['0'];
                    $get_history->where('userid', $client_id);
                }
            }

            $get_history->where('sender', 'like', '%'.$to.'%');
        }

        $get_info = $get_history->first();

        if ($gateway == 'smpp') {
            $sms_gateway = SMSGateways::where('type', $gateway)->first();
        } else {
            $sms_gateway = SMSGateways::where('settings', $gateway)->first();
        }

        $blacklist_word = strtolower(app_config('opt_out_sms_keyword'));
        $blacklist_word = explode(',', $blacklist_word);
        $blacklist_word = array_map('trim', $blacklist_word);

        $opt_in_word = strtolower(app_config('opt_in_sms_keyword'));
        $opt_in_word = explode(',', $opt_in_word);
        $opt_in_word = array_map('trim', $opt_in_word);

        $reply_word = strtolower($body);

        $campaign = Campaigns::where('keyword', $body)->where('status', 'Delivered')->first();

        if ($campaign) {
            $campaign_list = CampaignSubscriptionList::where('number', $number)->where('campaign_id', $campaign->campaign_id)->where('status', 'like', '%Success%')->first();

            if ($campaign_list && $sms_gateway->status == 'Active') {
                if ($campaign) {
                    $campaign_keyword = $campaign->keyword;
                    if ($campaign_keyword) {
                        $campaign_keyword = explode('|', $campaign_keyword);
                        if (in_array($body, $campaign_keyword)) {
                            $keyword = Keywords::where('user_id', $campaign->user_id)->where('status', '!=', 'expired')->where('keyword_name', $body)->first();

                            if ($keyword) {

                                $gateway_credential = null;
                                $cg_info            = null;
                                if ($sms_gateway->custom == 'Yes') {
                                    if ($sms_gateway->type == 'smpp') {
                                        $gateway_credential = SMSGatewayCredential::where('gateway_id', $sms_gateway->id)->where('status', 'Active')->first();
                                    } else {
                                        $cg_info = CustomSMSGateways::where('gateway_id', $sms_gateway->id)->first();
                                    }
                                } else {
                                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $sms_gateway->id)->where('status', 'Active')->first();
                                }

                                if ($keyword->reply_text) {

                                    $msgcount = strlen(preg_replace('/\s+/', ' ', trim($keyword->reply_text)));
                                    if ($msgcount <= 160) {
                                        $msgcount = 1;
                                    } else {
                                        $msgcount = $msgcount / 157;
                                    }

                                    $msgcount = ceil($msgcount);

                                    $this->dispatch(new SendBulkSMS($campaign->user_id, $number, $sms_gateway, $gateway_credential, $campaign->sender, $keyword->reply_text, $msgcount, $cg_info));

                                }

                                if ($sms_gateway->voice == 'Yes' && $keyword->reply_voice) {

                                    $msgcount = strlen(preg_replace('/\s+/', ' ', trim($keyword->reply_voice)));
                                    if ($msgcount <= 160) {
                                        $msgcount = 1;
                                    } else {
                                        $msgcount = $msgcount / 157;
                                    }

                                    $msgcount = ceil($msgcount);

                                    $this->dispatch(new SendBulkVoice($campaign->user_id, $number, $sms_gateway, $gateway_credential, $campaign->sender, $keyword->reply_voice, $msgcount, $cg_info));

                                }

                                if ($sms_gateway->mms == 'Yes' && $keyword->reply_mms) {
                                    $this->dispatch(new SendBulkMMS($campaign->user_id, $number, $sms_gateway, $gateway_credential, $campaign->sender, $keyword->reply_mms, $keyword->reply_mms));

                                }
                            }
                        }
                    }
                }
            }
        }


        if (in_array($reply_word, $opt_in_word)) {
            $contact = BlackListContact::where('numbers', $number)->first();
            if ($contact) {
                $contact->delete();
            }
        }

        if ($get_info) {
            if (in_array($reply_word, $blacklist_word)) {
                BlackListContact::create([
                    'user_id' => $client_id,
                    'numbers' => $number
                ]);
            }

            $status = SMSInbox::create([
                'msg_id' => $get_info->id,
                'amount' => $msg_count,
                'message' => $body,
                'status' => 'Success',
                'send_by' => 'receiver',
                'mark_read' => 'no',
            ]);

            if ($status) {
                $get_info->send_by = 'receiver';
                $get_info->save();
                $get_info->touch();
                return true;
            }

            return false;

        } else {

            if (in_array($reply_word, $blacklist_word)) {
                BlackListContact::create([
                    'user_id' => $client_id,
                    'numbers' => $number
                ]);
            }

            $status = SMSHistory::create([
                'userid' => $client_id,
                'sender' => $number,
                'receiver' => $to,
                'message' => $body,
                'amount' => $msg_count,
                'status' => 'Success',
                'api_key' => null,
                'use_gateway' => $sms_gateway->id,
                'send_by' => 'receiver',
                'sms_type' => 'plain'
            ]);

            if ($status) {
                SMSInbox::create([
                    'msg_id' => $status->id,
                    'amount' => $msg_count,
                    'message' => $body,
                    'status' => 'Success',
                    'send_by' => 'receiver',
                    'mark_read' => 'no',
                ]);
                return true;
            }
            return false;
        }
    }


//======================================================================
// replyTwilio Function Start Here
//======================================================================
    public function replyTwilio(Request $request)
    {
        $number = $request->input('From');
        $to     = $request->input('To');
        $body   = $request->input('Body');

        $response = new Twiml();

        if ($number == '' || $body == '' || $to == '') {
            $response->message("From, To and Body value required");
            return $response;
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $to, 'Twilio');

        if ($get_status) {
            return $response;
        } else {
            $response->message("failed");
            return $response;
        }

    }

//======================================================================
// replyZang Function Start Here
//======================================================================
    public function replyZang(Request $request)
    {
        $number = $request->input('From');
        $to     = $request->input('To');
        $body   = $request->input('Body');

        if ($number == '' || $body == '' || $to == '') {
            return 'From, To and Body value required';
        }

        $clphone  = str_replace(['(', ')', '+', '-', ' '], '', trim($number));
        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $to, 'Zang');

        if ($get_status) {
            return 'Success';
        } else {
            return 'failed';
        }

    }

//======================================================================
// replyFlowRoute Function Start Here
//======================================================================
    public function replyFlowRoute(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data) && is_array($data) && array_key_exists('data', $data)) {

            $number = $data['data']['attributes']['from'];
            $to     = $data['data']['attributes']['to'];
            $body   = $data['data']['attributes']['body'];

            if ($number == '' || $body == '' || $to == '') {
                return 'From, To and Body value required';
            }

            $clphone = str_replace(" ", "", $number); #Remove any whitespace
            $clphone = str_replace('+', '', $clphone);

            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
            $msgcount = $msgcount / 160;
            $msgcount = ceil($msgcount);

            $get_status = $this->insertSMS($clphone, $msgcount, $body, $to, 'FlowRoute');

            if ($get_status) {
                return 'Success';
            } else {
                return 'failed';
            }
        } else {
            return 'Invalid request';
        }
    }

//======================================================================
// replyFlowRouteMMS Function Start Here
//======================================================================
    public function replyFlowRouteMMS(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data) && is_array($data) && array_key_exists('data', $data) && array_key_exists('included', $data) && $data['data']['attributes']['is_mms'] == true) {

            $number    = $data['data']['attributes']['from'];
            $to        = $data['data']['attributes']['to'];
            $body      = $data['data']['attributes']['body'];
            $media_url = $data['included']['0']['links']['self'];

            if ($number == '' || $media_url == '' || $to == '') {
                return 'From, To and media url value required';
            }

            $body = $body . ' ' . $media_url;
            $body = trim($body);

            $clphone = str_replace(" ", "", $number); #Remove any whitespace
            $clphone = str_replace('+', '', $clphone);

            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
            $msgcount = $msgcount / 160;
            $msgcount = ceil($msgcount);

            $get_status = $this->insertSMS($clphone, $msgcount, $body, $to, 'FlowRoute');

            if ($get_status) {
                return 'Success';
            } else {
                return 'failed';
            }
        } else {
            return 'Invalid request';
        }
    }


//======================================================================
// replySignalWire Function Start Here
//======================================================================
    public function replySignalWire(Request $request)
    {
        $number = $request->input('From');
        $to     = $request->input('To');
        $body   = $request->input('Body');

        if ($number == '' || $body == '' || $to == '') {
            return 'From, To and Body value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $to, 'SignalWire');

        if ($get_status) {
            return 'Success';
        } else {
            return 'Failed';
        }

    }

//======================================================================
// replyCustomGatewayMessage Function Start Here
//======================================================================
    public function replyCustomGatewayMessage($id, Request $request)
    {
        $gateway = TwoWayCommunication::where('gateway_id', $id)->first();

        if ($gateway) {
            $gateway_name = get_sms_gateway_info($id)->settings;
            $source       = $gateway->source_param;
            $destination  = $gateway->destination_param;
            $message      = $gateway->message_param;

            $number = $request->input($source);
            $to     = $request->input($destination);
            $body   = $request->input($message);

            if ($number == '' || $body == '' || $to == '') {
                return 'Source number, destination number and message value required';
            }

            $clphone = str_replace(" ", "", $number); #Remove any whitespace
            $clphone = str_replace('+', '', $clphone);

            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
            $msgcount = $msgcount / 160;
            $msgcount = ceil($msgcount);

            $get_status = $this->insertSMS($clphone, $msgcount, $body, $to, $gateway_name);

            if ($get_status) {
                return $body;
            } else {
                return 'Failed';
            }
        } else {
            return 'Invalid request';
        }
    }

//======================================================================
// replyTxtLocal Function Start Here
//======================================================================
    public function replyTxtLocal(Request $request)
    {
        $number = $request->input('inNumber');
        $sender = $request->input('sender');
        $body   = $request->input('content');


        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $body, $sender, 'Text Local');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// replySmsGlobal Function Start Here
//======================================================================
    public function replySmsGlobal(Request $request)
    {
        $number = $request->input('to');
        $sender = $request->input('from');
        $body   = $request->input('msg');


        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $body, $sender, 'SMSGlobal');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// replyBulkSMS Function Start Here
//======================================================================
    public function replyBulkSMS(Request $request)
    {
        $number = $request->input('msisdn');
        $sender = $request->input('sender');
        $body   = $request->input('message');


        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $body, $sender, 'Bulk SMS');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// replyNexmo Function Start Here
//======================================================================
    public function replyNexmo(Request $request)
    {
        $inbound = InboundMessage::createFromGlobals();

        if ($inbound->isValid()) {
            $sender  = $inbound->getTo();
            $number  = $inbound->getFrom();
            $message = $inbound->getBody();

            if ($number == '' || $message == '') {
                return 'Destination number and message value required';
            }

            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));;
            $msgcount = $msgcount / 160;
            $msgcount = ceil($msgcount);

            $get_status = $this->insertSMS($number, $msgcount, $message, $sender, 'Nexmo');

            if ($get_status) {
                return 'success';
            } else {
                return 'failed';
            }

        } else {
            return 'invalid message';
        }
    }

//======================================================================
// replyPlivo Function Start Here
//======================================================================
    public function replyPlivo(Request $request)
    {
        $number  = $request->input('From');
        $sender  = $request->input('To');
        $message = $request->input('Text');


        if ($number == '' || $message == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $message, $sender, 'Plivo');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }

//======================================================================
// replyAPIWHA Function Start Here
//======================================================================
    public function replyAPIWHA(Request $request)
    {

        $data     = $request->data;
        $get_data = json_decode($data);

        $number  = $get_data->from;
        $sender  = $get_data->to;
        $message = $get_data->text;

        if ($number == '' || $message == '' || $sender == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $message, $sender, 'APIWHA');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// replyInfoBip Function Start Here
//======================================================================
    public function replyInfoBip(Request $request)
    {
        $number  = $request->input('from');
        $sender  = $request->input('to');
        $message = $request->input('body');

        if ($number == '' || $message == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $message, $sender, 'InfoBip');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }

//======================================================================
// replyTelnyx Function Start Here
//======================================================================
    public function replyTelnyx(Request $request)
    {
        $number  = $request->input('from');
        $sender  = $request->input('to');
        $message = $request->input('body');

        if ($number == '' || $message == '') {
            return 'Destination number and message value required';
        }

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($number, $msgcount, $message, $sender, 'Telnyx');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// deliveryReportBulkSMS Function Start Here
//======================================================================
    public function deliveryReportBulkSMS(Request $request)
    {

        $batch  = $request->input('batch_id');
        $status = $request->input('status');

        switch ($status) {
            case '11':
                $status = 'Success';
                break;

            case '22':
                $status = 'Internal fatal error';
                break;

            case '23':
                $status = 'Authentication failure';
                break;

            case '24':
                $status = 'Data validation failed';
                break;

            case '25':
                $status = 'You do not have sufficient credits';
                break;

            case '26':
                $status = 'Upstream credits not available';
                break;

            case '27':
                $status = 'You have exceeded your daily quota';
                break;

            case '28':
                $status = 'Upstream quota exceeded';
                break;

            case '29':
                $status = 'Message sending cancelled';
                break;

            case '31':
                $status = 'Unroutable';
                break;

            case '32':
                $status = 'Blocked';
                break;

            case '33':
                $status = 'Failed: censored';
                break;

            case '50':
                $status = 'Delivery failed - generic failure';
                break;

            case '51':
                $status = 'Delivery to phone failed';
                break;

            case '52':
                $status = 'Delivery to network failed';
                break;

            case '53':
                $status = 'Message expired';
                break;

            case '54':
                $status = 'Failed on remote network';
                break;

            case '55':
                $status = 'Failed: remotely blocked';
                break;

            case '56':
                $status = 'Failed: remotely censored';
                break;

            case '57':
                $status = 'Failed due to fault on handset';
                break;

            case '64':
                $status = 'Queued for retry after temporary failure delivering, due to fault on handset';
                break;

            case '70':
                $status = 'Unknown upstream status';
                break;

            case 'default':
                $status = 'Failed';
                break;

        }

        $existing_status = 'Success|' . $batch;

        $get_data = SMSHistory::where('status', 'like', '%' . $existing_status . '%')->first();

        if ($get_data) {
            $get_data->status = $status;
            $get_data->save();

            return 'success';
        } else {
            return 'failed';
        }


    }


    //======================================================================
    // deliveryReportInfobip Function Start Here
    //======================================================================
    public function deliveryReportInfobip(Request $request)
    {

        $get_data = $request->getContent();

        $get_data = json_decode($get_data, true);
        if (isset($get_data) && is_array($get_data) && array_key_exists('results', $get_data)) {
            $message_id = $get_data['results']['0']['messageId'];
            if (isset($get_data['results']['0']['messageId'])) {
                $sms = SMSHistory::where('status', 'like', '%' . $message_id . '%')->first();
                if ($sms) {
                    foreach ($get_data['results'] as $msg) {
                        if ($msg['status']['groupName'] == 'DELIVERED') {
                            $get_sms_status = 'Success|' . $msg['messageId'];
                        } else {
                            if ($sms->userid != 0) {
                                $client = Client::find($sms->userid);
                                if ($client) {

                                    $phone    = $sms->receiver;
                                    $msgcount = $sms->amount;
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
                            $get_sms_status = $msg['status']['groupName'];
                        }
                    }
                    $sms->status = $get_sms_status;
                    $sms->save();
                }
            }
        }
    }


    //======================================================================
    // deliveryReportAfricasTalking Function Start Here
    //======================================================================
    public function deliveryReportAfricasTalking(Request $request)
    {

        $get_data = $request->all();

        if (isset($get_data) && is_array($get_data) && array_key_exists('phoneNumber', $get_data) && array_key_exists('id', $get_data) && array_key_exists('status', $get_data)) {
            $message_id = $get_data['id'];
            if (isset($message_id)) {
                $sms = SMSHistory::where('status', 'like', '%' . $message_id . '%')->first();
                if ($sms) {
                    if ($get_data['status'] == 'Success') {
                        $get_sms_status = 'Success';
                    } else {
                        if ($sms->userid != 0) {
                            $client = Client::find($sms->userid);
                            if ($client) {

                                $phone    = $sms->receiver;
                                $msgcount = $sms->amount;
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
                        $get_sms_status = $get_data['status'];
                    }

                    $sms->status = $get_sms_status;
                    $sms->save();
                }
            }
        }
    }



//======================================================================
// deliveryReport46ELKS Function Start Here
//======================================================================
    public function deliveryReport46ELKS(Request $request)
    {

        $id     = $request->input('id');
        $status = $request->input('status');

        $existing_status = 'Success|' . $id;
        $get_data        = SMSHistory::where('status', 'like', '%' . $existing_status . '%')->first();

        if ($get_data) {
            if ($status != 'delivered') {

                $client = Client::find($get_data->userid);

                $phone    = $get_data->receiver;
                $msgcount = $get_data->amount;
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
            } else {
                $status = 'Success';
            }

            $get_data->status = $status;
            $get_data->save();

            return 'success';
        } else {
            return 'failed';
        }
    }




//======================================================================
// deliveryReportSMPP Function Start Here
//======================================================================
    public function deliveryReportSMPP(Request $request)
    {

        $number = $request->input('NUMBER');
        $status = $request->input('STATUS');

        if ($number == null || $status == null) {
            return 'Number or status parameter not found';
        }


        $number = str_replace(['(', ')', '+', '-', ' '], '', $number);


        $get_data = SMSHistory::where('receiver', $number)->first();

        if ($get_data) {
            if ($status != '4') {

                if ($status == '2') {
                    $status = 'Sent';
                } else if ($status == '3') {
                    $status = 'Invalid number';
                } else if ($status == '7') {
                    $status = 'Answers from client';
                } else {
                    $status = 'Invalid status code ' . $status;
                }

                if ($get_data->userid != '0') {

                    $client = Client::find($get_data->userid);

                    $phone    = $get_data->receiver;
                    $msgcount = $get_data->amount;
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

            } else {
                $status = 'Success';
            }

            $get_data->status = $status;
            $get_data->save();

            return 'success';
        } else {
            return 'failed';
        }
    }

//======================================================================
// replyMessageBird Function Start Here
//======================================================================
    public function replyMessageBird(Request $request)
    {
        $number = $request->input('originator');
        $sender = $request->input('recipient');
        $body   = $request->input('body');

        if ($number == '' || $body == '' || $sender == '') {
            return 'Destination number, Source number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, 'MessageBird');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }

    }


//======================================================================
// replyDiafaan Function Start Here
//======================================================================
    public function replyDiafaan(Request $request)
    {

        $number = $request->input('from');
        $sender = $request->input('to');
        $body   = $request->input('message');

        if ($number == '' || $body == '' || $sender == '') {
            return 'Destination number, Source number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, 'Diafaan');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }



//======================================================================
// replyThinq Function Start Here
//======================================================================
    public function replyThinq(Request $request)
    {

        $number = $request->input('from');
        $sender = $request->input('to');
        $body   = $request->input('message');

        if ($number == '' || $body == '' || $sender == '') {
            return 'Destination number, Source number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, 'Thinq');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// replyEasySendSMS Function Start Here
//======================================================================
    public function replyEasySendSMS(Request $request)
    {

        $number = $request->input('From');
        $body   = $request->input('message');

        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, 'Unknown', 'EasySendSMS');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }

//======================================================================
// replySMPP Function Start Here
//======================================================================
    public function replySMPP(Request $request)
    {

        $number = $request->input('from');
        $body   = $request->input('text');

        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $clphone, 'smpp');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }

//======================================================================
// replyGatewayAPI Function Start Here
//======================================================================
    public function replyGatewayAPI(Request $request)
    {

        $number = $request->input('msisdn');
        $body   = $request->input('message');
        $sender = $request->input('receiver');

        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, 'Gatewayapi');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// reply019SMS Function Start Here
//======================================================================
    public function reply019SMS(Request $request)
    {

        $number = $request->input('phone');
        $body   = $request->input('message');
        $sender = $request->input('dest');

        if ($number == '' || $body == '' || $sender == '') {
            return 'Destination, From number and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, '019SMS');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// reply46ELKS Function Start Here
//======================================================================
    public function reply46ELKS(Request $request)
    {

        $number = $request->input('from');
        $body   = $request->input('message');
        $sender = $request->input('to');
        $image  = $request->input('image');

        if ($number == '' || $body == '') {
            return 'Destination number and message value required';
        }

        if ($image != '') {
            $body = $body . ' ' . $image;
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, '46ELKS');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


//======================================================================
// replyWhatsApp Function Start Here
//======================================================================
    public function replyWhatsApp()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        foreach ($data['messages'] as $message) {
            $status_message = 'Success|' . $message['messageNumber'];
            $history        = SMSHistory::where('status', $status_message)->first();
            if ($history) {
                $number = $history->receiver;
                $body   = $message['body'];
                $sender = (int)$message['author'];

                if ($number == '' || $body == '') {
                    return 'Destination number and message value required';
                }
                $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
                $msgcount = $msgcount / 160;
                $msgcount = ceil($msgcount);

                $get_status = $this->insertSMS($number, $msgcount, $body, $sender, 'WhatsAppChatApi');


            } else {
                $number = (int)$message['author'];
                $body   = $message['body'];
                $sender = (int)$message['author'];

                if ($number == '' && $body == '') {
                    return 'Invalid Request';
                }
                $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
                $msgcount = $msgcount / 160;
                $msgcount = ceil($msgcount);

                $get_status = $this->insertSMS($number, $msgcount, $body, $sender, 'WhatsAppChatApi');
            }

            if ($get_status) {
                return 'success';
            } else {
                return 'failed';
            }

        }
    }


//======================================================================
// ultimateSMSContactApi Function Start Here
//======================================================================
    public function ultimateSMSContactApi(Request $request)
    {
        $action       = $request->input('action');
        $api_key      = $request->input('api_key');
        $phone_book   = $request->input('phone_book');
        $phone_number = $request->input('phone_number');
        $first_name   = $request->input('first_name');
        $last_name    = $request->input('last_name');
        $email        = $request->input('email');
        $company      = $request->input('company');
        $user_name    = $request->input('user_name');

        if ($action == '' && $api_key == '') {
            return response()->json([
                'code' => '100',
                'message' => 'Bad gateway requested'
            ]);
        }

        switch ($action) {
            case 'subscribe-us':

                if ($phone_book == '' && $phone_number == '') {
                    return response()->json([
                        'code' => '100',
                        'message' => 'Bad gateway requested'
                    ]);
                }

                $isValid = PhoneNumberUtil::isViablePhoneNumber($phone_number);
                $phone   = str_replace(['(', ')', '+', '-', ' '], '', $phone_number);

                if (!$isValid || !preg_match('/^\(?\+?([0-9]{1,4})\)?[-\. ]?(\d{3})[-\. ]?([0-9]{7})$/', trim($phone))) {
                    return response()->json([
                        'code' => '103',
                        'message' => 'Invalid Phone Number'
                    ]);
                }

                if (app_config('api_key') == $api_key) {
                    $contact_list = ImportPhoneNumber::where('user_id', 0)->where('group_name', $phone_book)->first();

                    if ($contact_list) {

                        $exist_check = ContactList::where('pid', $contact_list->id)->where('phone_number', $phone)->first();
                        if ($exist_check) {
                            return response()->json([
                                'code' => '105',
                                'message' => 'You already subscribed'
                            ]);
                        }

                        $status = ContactList::create([
                            'pid' => $contact_list->id,
                            'phone_number' => $phone,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email_address' => $email,
                            'user_name' => $user_name,
                            'company' => $company
                        ]);

                        if ($status) {
                            return response()->json([
                                'code' => 'ok',
                                'message' => 'Subscription successfully done'
                            ]);
                        } else {
                            return response()->json([
                                'code' => '100',
                                'message' => 'Something went wrong. Please try again'
                            ]);
                        }


                    } else {
                        return response()->json([
                            'code' => '104',
                            'message' => 'Subscription list not found'
                        ]);
                    }

                } else {
                    $client = Client::where('api_key', $api_key)->where('api_access', 'Yes')->first();
                    if ($client) {
                        $contact_list = ImportPhoneNumber::where('user_id', $client->id)->where('group_name', $phone_book)->first();

                        if ($contact_list) {

                            $exist_check = ContactList::where('pid', $contact_list->id)->where('phone_number', $phone)->first();
                            if ($exist_check) {
                                return response()->json([
                                    'code' => '105',
                                    'message' => 'You already subscribed'
                                ]);
                            }

                            $status = ContactList::create([
                                'pid' => $contact_list->id,
                                'phone_number' => $phone,
                                'first_name' => $first_name,
                                'last_name' => $last_name,
                                'email_address' => $email,
                                'user_name' => $user_name,
                                'company' => $company
                            ]);

                            if ($status) {
                                return response()->json([
                                    'code' => 'ok',
                                    'message' => 'Subscription successfully done'
                                ]);
                            } else {
                                return response()->json([
                                    'code' => '100',
                                    'message' => 'Something went wrong. Please try again'
                                ]);
                            }


                        } else {
                            return response()->json([
                                'code' => '104',
                                'message' => 'Subscription list not found'
                            ]);
                        }


                    } else {
                        return response()->json([
                            'code' => '102',
                            'message' => 'Authentication Failed'
                        ]);
                    }
                }
                break;

            default:
                return response()->json([
                    'code' => '101',
                    'message' => 'Wrong action'
                ]);
                break;
        }

    }

//======================================================================
// ultimateSMSCoverageApi Function Start Here
//======================================================================
    public function ultimateSMSCoverageApi()
    {
        $coverages = IntCountryCodes::where('active', 1)->select('id', 'country_name', 'iso_code')->get();

        $return_data = [];
        $coverage    = [];
        foreach ($coverages as $country) {
            $return_data['id']   = trim(explode('/', $country->iso_code)[0]);
            $return_data['text'] = $country->country_name;

            array_push($coverage, $return_data);
        }

        $system_country = app_config('Country');

        $default_country = IntCountryCodes::where('country_name', $system_country)->first();

        return response()->json([
            'country_data' => $coverage,
            'country' => $system_country,
            'iso_code' => strtolower(trim(explode('/', $default_country->iso_code)[0])),

        ]);

    }


//======================================================================
// UltimateSMSOperatorPrice Function Start Here
//======================================================================
    public function UltimateSMSOperatorPrice($country)
    {

        if ($country == 'get_default' || $country == '') {
            $country = app_config('Country');
        }

        $coverage = IntCountryCodes::where('country_name', $country)->first();
        if ($coverage) {

            $return_data = [];

            $operator = Operator::where('coverage_id', $coverage->id)->select('operator_name', 'plain_price', 'voice_price', 'mms_price')->where('status', 'active')->get();
            if ($operator->count() > 0) {
                $return_data['operator']       = 'yes';
                $return_data['operator_value'] = $operator->toArray();
            } else {
                $return_data['operator']       = 'no';
                $return_data['operator_value'] = [
                    'plain_price' => $coverage->plain_tariff,
                    'voice_price' => $coverage->voice_tariff,
                    'mms_price' => $coverage->mms_tariff,
                ];
            }
            return response()->json([
                'status' => 'success',
                'currency' => app_config('CurrencyCode'),
                'country' => $coverage->country_name,
                'country_iso_code' => strtolower(trim(explode('/', $coverage->iso_code)[0])),
                'data' => $return_data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Coverage not found'
            ]);
        }


    }


    //======================================================================
    // replyVoyant Function Start Here
    //======================================================================
    public function replyVoyant(Request $request)
    {
        $number = $request->input('from');
        $body   = $request->input('text');
        $sender = $request->input('to');

        if (isset($sender) && is_array($sender) && count($sender) > 0) {
            $sender = $sender['0'];
        } else {
            return 'From number required';
        }

        $image = $request->input('mediaUrls');

        if (isset($image) && is_array($image) && count($image) > 0) {
            $media_url = $image['0'];
        } else {
            $media_url = null;
        }

        if ($number == '' || $body == '' || $sender == '') {
            return 'Destination number, From and message value required';
        }

        $clphone = str_replace(" ", "", $number); #Remove any whitespace
        $clphone = str_replace('+', '', $clphone);

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, 'Voyant');

        if ($get_status) {
            return 'success';
        } else {
            return 'failed';
        }
    }


    //======================================================================
    // replyBandwidth Function Start Here
    //======================================================================
    public function replyBandwidth(Request $request)
    {

        $data = $request->all();

        if (isset($data) && is_array($data) && count($data) > 0) {
            if ($data['0']['type'] == 'message-received'){
                if (isset($data[0]['message']) && is_array($data[0]['message'])){
                    $number = $data[0]['message']['from'];
                    $body   = $data[0]['message']['text'];
                    $sender = $data[0]['to'];

                    if ($number == '' || $body == '' || $sender == '') {
                        return 'Destination number, source Number and message value required';
                    }

                    $clphone = str_replace([' ','+'], "", $number);
                    $sender = str_replace([' ','+'], "", $sender);

                    $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
                    $msgcount = $msgcount / 160;
                    $msgcount = ceil($msgcount);

                    $get_status = $this->insertSMS($clphone, $msgcount, $body, $sender, 'Bandwidth');

                    if ($get_status) {
                        return 'success';
                    } else {
                        return 'failed';
                    }
                }else {
                    return $request->getContent();
                }
            }else {
                return $request->getContent();
            }
        } else {
            return $request->getContent();
        }
    }

}
