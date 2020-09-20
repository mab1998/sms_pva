<?php

namespace App\Http\Controllers;

use App\Admin;
use App\BlackListContact;
use App\BlockMessage;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\PhoneNumber;
use App\Client;
use App\ClientGroups;
use App\ContactList;
use App\CustomSMSGateways;
use App\EmailTemplates;
use App\ImportPhoneNumber;
use App\IntCountryCodes;
use App\Jobs\SendBulkMMS;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\Keywords;
use App\Mail\RequestSenderID;
use App\Operator;
use App\PaymentGateways;
use App\RecurringSMS;
use App\RecurringSMSContacts;
use App\ScheduleSMS;
use App\SenderIdManage;
use App\SMSBundles;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use App\SMSPlanFeature;
use App\SMSPricePlan;
use App\SMSTemplates;
use App\SpamWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class UserSMSController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }

    //======================================================================
    // senderIdManagement Function Start Here
    //======================================================================
    public function senderIdManagement()
    {

        $all_sender_id = SenderIdManage::where('status', 'unblock')->orWhere('status', 'Pending')->get();
        $all_ids = [];

        foreach ($all_sender_id as $sid) {
            $client_array = json_decode($sid->cl_id);

            if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                array_push($all_ids, $sid->id);
            } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = array_unique($all_ids);

        $sender_id = SenderIdManage::whereIn('id', $sender_ids)->get();

        return view('client.sender-id-management', compact('sender_id'));
    }

    //======================================================================
    // postSenderID Function Start Here
    //======================================================================
    public function postSenderID(Request $request)
    {
        if ($request->sender_id == '') {
            return redirect('user/sms/sender-id-management')->with([
                'message' => language_data('Sender ID required', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $client_id = (string)Auth::guard('client')->user()->id;
        $client_id = (array)$client_id;
        $client_id = json_encode($client_id);

        $sender_id = new  SenderIdManage();
        $sender_id->sender_id = $request->sender_id;
        $sender_id->cl_id = $client_id;
        $sender_id->status = 'pending';
        $sender_id->save();

        $url = url('sms/view-sender-id/' . $sender_id->id);

        try {
            \Mail::to(app_config('Email'))->send(new RequestSenderID(Auth::guard('client')->user()->fname, Auth::guard('client')->user()->email, $request->sender_id, $url));

            return redirect('user/sms/sender-id-management')->with([
                'message' => language_data('Request send successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } catch (\Exception $ex) {
            return redirect('user/sms/sender-id-management')->with([
                'message' => $ex->getMessage()
            ]);
        }

    }


    //======================================================================
    // sendBulkSMS Function Start Here
    //======================================================================
    public function sendBulkSMS()
    {
        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $phone_book = ImportPhoneNumber::where('user_id', Auth::guard('client')->user()->id)->get();
        $client_group = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->where('status', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        $country_code = IntCountryCodes::where('Active', 1)->select('country_code', 'country_name')->get();

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();
        $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->where('status', 'assigned')->get();
        $schedule_sms = false;

        return view('client.send-bulk-sms', compact('client_group', 'sms_templates', 'sender_ids', 'phone_book', 'schedule_sms', 'country_code', 'sms_gateways', 'keyword'));
    }

    //======================================================================
    // postSendBulkSMS Function Start Here
    //======================================================================
    public function postSendBulkSMS(Request $request)
    {

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }

        if ($request->schedule_sms_status) {
            $v = \Validator::make($request->all(), [
                'schedule_time' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'sms_gateway' => 'required', 'delimiter' => 'required'
            ]);

            $redirect_url = 'user/sms/send-schedule-sms';
        } else {
            $v = \Validator::make($request->all(), [
                'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'sms_gateway' => 'required', 'delimiter' => 'required'
            ]);

            $redirect_url = 'user/sms/send-sms';
        }

        if ($v->fails()) {
            return redirect($redirect_url)->withInput($request->all())->withErrors($v->errors());
        }


        $client = Client::find(Auth::guard('client')->user()->id);

        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;
        $message = $request->message;
        $msg_type = $request->message_type;

        if (app_config('sender_id_verification') == '1') {
            if ($sender_id == null) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $gateway = SMSGateways::find($request->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        $keywords = $request->keyword;

        if ($keywords) {
            if ($gateway->two_way != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => 'SMS Gateway not supported Two way or Receiving feature',
                    'message_important' => true
                ]);
            }

            if (isset($keywords) && is_array($keywords)) {
                $keywords = implode("|", $keywords);
            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => 'Invalid keyword selection',
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms' && $msg_type != 'arabic') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Invalid message type', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported Voice feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported MMS feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS file required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name = $image->getClientOriginalName();
                    $image_name = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        $get_cost = 0;
        $total_cost = 0;
        $get_inactive_coverage = [];
        $results = [];

        if ($request->contact_type == 'phone_book') {
            if (isset($request->contact_list_id) && is_array($request->contact_list_id) && count($request->contact_list_id)) {
                $get_data = ContactList::whereIn('pid', $request->contact_list_id)->select('phone_number', 'email_address', 'user_name', 'company', 'first_name', 'last_name')->get()->toArray();
                foreach ($get_data as $data) {
                    array_push($results, $data);
                }
            }
        }

        if ($request->contact_type == 'client_group') {
            $get_group = Client::whereIn('groupid', $request->client_group_id)->select('phone AS phone_number', 'email AS email_address', 'username AS user_name', 'company AS company', 'fname AS first_name', 'lname AS last_name')->get()->toArray();
            foreach ($get_group as $data) {
                array_push($results, $data);
            }
        }

        if ($request->recipients) {

            if ($request->delimiter == 'automatic') {
                $recipients = multi_explode(array(",", "\n", ";", " ", "|"), $request->recipients);
            } elseif ($request->delimiter == ';') {
                $recipients = explode(';', $request->recipients);
            } elseif ($request->delimiter == ',') {
                $recipients = explode(',', $request->recipients);
            } elseif ($request->delimiter == '|') {
                $recipients = explode('|', $request->recipients);
            } elseif ($request->delimiter == 'tab') {
                $recipients = explode(' ', $request->recipients);
            } elseif ($request->delimiter == 'new_line') {
                $recipients = explode("\n", $request->recipients);
            } else {
                return redirect($redirect_url)->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }


            foreach ($recipients as $r) {

                $phone = str_replace(['(', ')', '+', '-', ' '], '', $r);
                if ($request->country_code != 0) {
                    $phone = $request->country_code . ltrim($phone, '0');
                } else {
                    $phone = ltrim($phone, '0');
                }

                if (validate_phone_number($phone)) {
                    $data = [
                        'phone_number' => $phone,
                        'email_address' => null,
                        'user_name' => null,
                        'company' => null,
                        'first_name' => null,
                        'last_name' => null
                    ];
                    array_push($results, $data);
                }
            }
        }

        if (isset($results) && is_array($results)) {

            if (count($results) > 0) {
                $campaign_id = uniqid('C');

                if ($request->remove_duplicate == 'yes') {
                    $results = unique_multidim_array($results, 'phone_number');
                }

                $filtered_data = [];
                $blacklist = BlackListContact::select('numbers')->where('user_id', Auth::guard('client')->user()->id)->get()->toArray();

                if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                    $blacklist = array_column($blacklist, 'numbers');

                    array_filter($results, function ($element) use ($blacklist, &$filtered_data) {
                        if (!in_array($element['phone_number'], $blacklist)) {
                            array_push($filtered_data, $element);
                        }
                    });

                    $results = array_values($filtered_data);
                    unset($filtered_data);
                    unset($blacklist);
                }

                if (count($results) <= 0) {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                $results = array_values($results);


                $spam_word = [];
                $block_message = [];
                $final_insert_data = [];

                if (app_config('fraud_detection') == 1) {
                    $spam_word = SpamWord::all()->toArray();
                    if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                        $spam_word = array_column($spam_word, 'word');
                    }
                }

                if ($request->send_later == 'on') {

                    if ($request->schedule_time == '') {
                        return redirect($redirect_url)->withInput($request->all())->with([
                            'message' => language_data('Schedule time required'),
                            'message_important' => true
                        ]);
                    }

                    $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));

                    if (new \DateTime() > new \DateTime($schedule_time)) {
                        return redirect($redirect_url)->withInput($request->all())->with([
                            'message' => 'Select a valid time',
                            'message_important' => true
                        ]);
                    }

                    $campaign_type = 'scheduled';
                    $campaign_status = 'Scheduled';
                    $subscription_status = 'scheduled';
                    $block_message_schedule = date('Y-m-d H:i:s', strtotime($request->schedule_time));
                } else {
                    $schedule_time = date('Y-m-d H:i:s');
                    $campaign_type = 'regular';
                    $campaign_status = 'Running';
                    $block_message_schedule = null;
                    $subscription_status = 'queued';
                }

                foreach (array_chunk($results, 50) as $chunk_result) {
                    foreach ($chunk_result as $r) {
                        $msg_data = array(
                            'Phone Number' => $r['phone_number'],
                            'Email Address' => $r['email_address'],
                            'User Name' => $r['user_name'],
                            'Company' => $r['company'],
                            'First Name' => $r['first_name'],
                            'Last Name' => $r['last_name'],
                        );


                        $get_message = $this->renderSMS($message, $msg_data);

                        if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($get_message)));
                            if ($msgcount <= 160) {
                                $msgcount = 1;
                            } else {
                                $msgcount = $msgcount / 157;
                            }
                        }
                        if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                            $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($get_message)), 'UTF-8');

                            if ($msgcount <= 70) {
                                $msgcount = 1;
                            } else {
                                $msgcount = $msgcount / 67;
                            }
                        }

                        $msgcount = ceil($msgcount);

                        $phone = str_replace(['(', ')', '+', '-', ' '], '', $r['phone_number']);

                        if ($request->country_code == 0) {
                            if ($gateway->settings == 'FortDigital') {
                                $c_phone = 61;
                            } else {
                                $c_phone = PhoneNumber::get_code($phone);
                            }
                        } else {
                            $c_phone = $request->country_code;
                        }

                        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                        if ($sms_cost) {

                            $phoneUtil = PhoneNumberUtil::getInstance();
                            $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                            $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                            if ($area_code_exist) {
                                $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                                $get_format_data = explode(" ", $format);
                                $operator_settings = explode('-', $get_format_data[1])[0];

                            } else {
                                $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                                    $sms_charge = $sms_cost->voice_tariff;
                                }

                                $get_cost += $sms_charge;
                            }
                        } else {
                            array_push($get_inactive_coverage, $phone);
                            continue;
                        }

                        $total_cost = $get_cost * $msgcount;

                        if ($total_cost == 0) {
                            return redirect($redirect_url)->withInput($request->all())->with([
                                'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                                'message_important' => true
                            ]);
                        }

                        if ($total_cost > $sms_count) {
                            return redirect($redirect_url)->withInput($request->all())->with([
                                'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                                'message_important' => true
                            ]);
                        }


                        if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {

                            $check = array_filter($spam_word, function ($word) use ($get_message) {
                                if (strpos($get_message, $word)) {
                                    return true;
                                }
                                return false;
                            });

                            if (isset($check) && is_array($check) && count($check) > 0) {
                                array_push($block_message, [
                                    'user_id' => Auth::guard('client')->user()->id,
                                    'sender' => $sender_id,
                                    'campaign_id' => $campaign_id,
                                    'receiver' => $phone,
                                    'message' => $get_message,
                                    'scheduled_time' => $block_message_schedule,
                                    'use_gateway' => $gateway->id,
                                    'status' => 'block',
                                    'type' => $msg_type,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);

                                continue;
                            }
                        }

                        $push_data = [
                            'campaign_id' => $campaign_id,
                            'number' => $phone,
                            'message' => $get_message,
                            'amount' => $msgcount,
                            'status' => $subscription_status
                        ];

                        if ($campaign_type == 'scheduled') {
                            $push_data['submitted_time'] = $schedule_time;
                        }

                        array_push($final_insert_data, $push_data);
                    }
                }

                if (isset($final_insert_data) && is_array($final_insert_data) && count($final_insert_data) > 0) {

                    $campaign = Campaigns::create([
                        'campaign_id' => $campaign_id,
                        'user_id' => Auth::guard('client')->user()->id,
                        'sender' => $sender_id,
                        'sms_type' => $msg_type,
                        'camp_type' => $campaign_type,
                        'status' => $campaign_status,
                        'use_gateway' => $gateway->id,
                        'total_recipient' => count($results),
                        'run_at' => date('Y-m-d H:i:s'),
                        'media_url' => $media_url,
                        'keyword' => $keywords
                    ]);


                    if ($campaign) {

                        if (app_config('fraud_detection') == 1) {
                            if (isset($block_message) && is_array($block_message) && count($block_message) > 0) {

                                $store_block_message = BlockMessage::insert($block_message);
                                if ($store_block_message) {

                                    $sysUrl = url('clients/view/' . Auth::guard('client')->user()->id);
                                    $user_name = Auth::guard('client')->user()->username;
                                    $client_email = Auth::guard('client')->user()->email;
                                    $spam_message = 'Message contain spam word';

                                    \Mail::to(app_config('Email'))->send(new \App\Mail\BlockMessage($user_name, $client_email, $spam_message, $sysUrl));

                                }
                            }
                        }

                        $campaign_list = CampaignSubscriptionList::insert($final_insert_data);

                        if ($campaign_list) {

                            $remain_sms = $sms_count - $total_cost;
                            $client->sms_limit = $remain_sms;
                            $client->save();

                            if (isset($get_inactive_coverage) && is_array($get_inactive_coverage) && count($get_inactive_coverage) > 0) {
                                $inactive_phone = implode('; ', $get_inactive_coverage);
                                return redirect($redirect_url)->with([
                                    'message' => 'This phone number(s) ' . $inactive_phone . ' not send for inactive coverage issue'
                                ]);
                            }

                            return redirect($redirect_url)->with([
                                'message' => language_data('SMS added in queue and will deliver one by one', Auth::guard('client')->user()->lan_id)
                            ]);
                        }

                        $campaign->delete();
                        return redirect($redirect_url)->withInput($request->all())->with([
                            'message' => language_data('Something went wrong please try again'),
                            'message_important' => true
                        ]);
                    }
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Something went wrong please try again'),
                        'message_important' => true
                    ]);
                }

                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);

            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Invalid Recipients', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // purchaseSMSPlan Function Start Here
    //======================================================================
    public function purchaseSMSPlan()
    {
        $price_plan = SMSPricePlan::where('status', 'Active')->get();
        return view('client.sms-price-plan', compact('price_plan'));
    }

    public function purchaseVPSPlan()
    {
        $price_plan = SMSPricePlan::where('status', 'Active')->where('category', 'vps')->get();
        return view('client.vps-price-plan', compact('price_plan'));
    }

    public function purchaseVPNPlan()
    {
        $price_plan = SMSPricePlan::where('status', 'Active')->where('category', 'vpn')->get();
        return view('client.vpn-price-plan', compact('price_plan'));
    }

    public function purchaseProxiesPlan()
    {
        $price_plan = SMSPricePlan::where('status', 'Active')->where('category', 'proxies')->get();
        return view('client.proxies-price-plan', compact('price_plan'));
    }

    //======================================================================
    // smsPlanFeature Function Start Here
    //======================================================================
    public function smsPlanFeature($id)
    {
        $sms_plan = SMSPricePlan::where('status', 'Active')->find($id);

        // if ($sms_plan) {
        //     $plan_feature = SMSPlanFeature::where('pid', $id)->get();
        //     $payment_gateways = PaymentGateways::where('status', 'Active')->get();
        //     return view('client.sms-plan-feature', compact('sms_plan', 'plan_feature', 'payment_gateways'));
        // } else {
        //     return redirect('user/sms/purchase-sms-plan')->with([
        //         'message' => language_data('SMS plan not found', Auth::guard('client')->user()->lan_id),
        //         'message_important' => true
        //     ]);
        // }
        $plan_feature = SMSPlanFeature::where('pid', $id)->get();
        $payment_gateways = PaymentGateways::where('status', 'Active')->get();
        $self = 'add-new-invoice';
        $clients = Client::where('status', 'Active')->get();
        // $cl=Auth::guard('client')->user();
        return view('add-new-invoice', compact('sms_plan', 'plan_feature', 'payment_gateways','id'));


    }

    //======================================================================
    // sendSMSFromFile Function Start Here
    //======================================================================
    public function sendSMSFromFile()
    {
        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];


            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();
        $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->where('status', 'assigned')->get();
        $schedule_sms = false;

        return view('client.send-sms-file', compact('sms_templates', 'sender_ids', 'schedule_sms', 'country_code', 'sms_gateways', 'keyword'));
    }

    //======================================================================
    // downloadSampleSMSFile Function Start Here
    //======================================================================
    public function downloadSampleSMSFile()
    {
        return response()->download('assets/test_file/sms.csv');
    }

    //======================================================================
    // postSMSFromFile Function Start Here
    //======================================================================
    public function postSMSFromFile(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-sms-file')->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }


        if ($request->schedule_sms_status) {
            $v = \Validator::make($request->all(), [
                'import_numbers' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'sms_gateway' => 'required'
            ]);

            $redirect_url = 'user/sms/send-schedule-sms-file';
        } else {
            $v = \Validator::make($request->all(), [
                'import_numbers' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'sms_gateway' => 'required'
            ]);

            $redirect_url = 'user/sms/send-sms-file';
        }


        if ($v->fails()) {
            return redirect($redirect_url)->withInput($request->all())->withErrors($v->errors());
        }


        if ($request->send_later == 'on') {

            if ($request->schedule_time == '' && $request->schedule_time_column == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Schedule time required'),
                    'message_important' => true
                ]);
            }

            if ($request->schedule_time_type == 'from_date') {

                $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
                if (\DateTime::createFromFormat('m/d/Y h:i A', $request->schedule_time) === FALSE || new \DateTime() > new \DateTime($schedule_time)) {
                    return redirect($redirect_url)->with([
                        'message' => language_data('Invalid time format'),
                        'message_important' => true
                    ]);
                }
            }

            $campaign_type = 'scheduled';
            $campaign_status = 'Scheduled';

        } else {
            $campaign_type = 'regular';
            $campaign_status = 'Running';
        }


        $client = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;

        $sender_id = $request->sender_id;

        if (app_config('sender_id_verification') == '1') {
            if ($sender_id == null) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $gateway = SMSGateways::find($request->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        $keywords = $request->keyword;

        if ($keywords) {
            if ($gateway->two_way != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => 'SMS Gateway not supported Two way or Receiving feature',
                    'message_important' => true
                ]);
            }

            if (isset($keywords) && is_array($keywords)) {
                $keywords = implode("|", $keywords);
            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => 'Invalid keyword selection',
                    'message_important' => true
                ]);
            }
        }

        $spam_word = [];

        if (app_config('fraud_detection') == 1) {
            $spam_word = SpamWord::all()->toArray();
            if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                $spam_word = array_column($spam_word, 'word');
            }
        }

        $msg_type = $request->message_type;
        $message = $request->message;

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms' && $msg_type != 'arabic') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Invalid message type', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported Voice feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported MMS feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS file required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name = $image->getClientOriginalName();
                    $image_name = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r(strtolower($file_extension), $supportedExt)) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Insert Valid Excel or CSV file', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $get_inactive_coverage = [];
        $valid_phone_numbers = [];
        $get_data = [];
        $block_message = [];

        $all_data = Excel::load($request->import_numbers)->noHeading()->all()->toArray();

        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Empty field', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $counter = "A";

        if ($request->header_exist == 'on') {

            $header = array_shift($all_data);

            foreach ($header as $key => $value) {
                if (!$value) {
                    $header[$key] = "Column " . $counter;
                }

                $counter++;
            }

        } else {

            $header_like = $all_data[0];

            $header = array();

            foreach ($header_like as $h) {
                array_push($header, "Column " . $counter);
                $counter++;
            }

        }

        $all_data = array_map(function ($row) use ($header) {

            return array_combine($header, $row);

        }, $all_data);

        $campaign_id =uniqid('C');

        $blacklist = BlackListContact::select('numbers')->where('user_id', Auth::guard('client')->user()->id)->get()->toArray();

        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
            $blacklist = array_column($blacklist, 'numbers');
        }

        $number_column = trim($request->number_column);
        $get_cost = 0;

        $gateway_id = $gateway->id;

        array_filter($all_data, function ($data) use ($number_column, &$get_data, &$valid_phone_numbers, $blacklist, $request, $message, $msg_type, &$get_cost, $spam_word, $sender_id, $gateway_id, &$block_message, &$get_inactive_coverage, $campaign_id) {

            $a = array_map('trim', array_keys($data));
            $b = array_map('trim', $data);
            $data = array_combine($a, $b);

            if ($data[$number_column]) {
                $clphone = str_replace(['(', ')', '+', '-', ' '], '', $data[$number_column]);

                if (validate_phone_number($clphone)) {

                    if ($request->country_code != 0) {
                        $clphone = $request->country_code . ltrim($clphone, '0');
                    }

                    if (!in_array($clphone, $blacklist)) {
                        $data[$number_column] = $clphone;

                        $get_message = $this->renderSMS($message, $data);

                        if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($get_message)));
                            if ($msgcount <= 160) {
                                $msgcount = 1;
                            } else {
                                $msgcount = $msgcount / 157;
                            }
                        }
                        if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                            $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($get_message)), 'UTF-8');

                            if ($msgcount <= 70) {
                                $msgcount = 1;
                            } else {
                                $msgcount = $msgcount / 67;
                            }
                        }

                        $msgcount = ceil($msgcount);


                        if ($request->country_code == 0) {
                            $c_phone = PhoneNumber::get_code($clphone);
                        } else {
                            $c_phone = $request->country_code;
                        }


                        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

                        if ($sms_cost) {

                            $phoneUtil = PhoneNumberUtil::getInstance();
                            $phoneNumberObject = $phoneUtil->parse('+' . $clphone, null);
                            $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                            if ($area_code_exist) {
                                $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                                $get_format_data = explode(" ", $format);
                                $operator_settings = explode('-', $get_format_data[1])[0];

                            } else {
                                $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                            }


                            $get_cost = $get_cost + ($sms_charge * $msgcount);


                            if ($request->send_later == 'on') {

                                if ($request->schedule_time_type == 'from_file') {
                                    $schedule_time_column = $request->schedule_time_column;
                                    $schedule_time = date('Y-m-d H:i:s', strtotime($data[$schedule_time_column]));
                                } else {
                                    $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
                                }

                                array_push($valid_phone_numbers, $clphone);

                                if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {

                                    $check = array_filter($spam_word, function ($word) use ($get_message) {
                                        if (strpos($get_message, $word)) {
                                            return true;
                                        }
                                        return false;
                                    });

                                    if (isset($check) && is_array($check) && count($check) > 0) {
                                        array_push($block_message, [
                                            'user_id' => Auth::guard('client')->user()->id,
                                            'sender' => $sender_id,
                                            'campaign_id' => $campaign_id,
                                            'receiver' => $clphone,
                                            'message' => $get_message,
                                            'scheduled_time' => $schedule_time,
                                            'use_gateway' => $gateway_id,
                                            'status' => 'block',
                                            'type' => $msg_type,
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'updated_at' => date('Y-m-d H:i:s')
                                        ]);
                                    } else {
                                        array_push($get_data, [
                                            'campaign_id' => $campaign_id,
                                            'number' => $clphone,
                                            'message' => $get_message,
                                            'amount' => $msgcount,
                                            'status' => 'scheduled',
                                            'submitted_time' => $schedule_time
                                        ]);
                                    }
                                } else {
                                    array_push($get_data, [
                                        'campaign_id' => $campaign_id,
                                        'number' => $clphone,
                                        'message' => $get_message,
                                        'amount' => $msgcount,
                                        'status' => 'scheduled',
                                        'submitted_time' => $schedule_time
                                    ]);
                                }

                            } else {
                                array_push($valid_phone_numbers, $clphone);
                                if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {

                                    $check = array_filter($spam_word, function ($word) use ($get_message) {
                                        if (strpos($get_message, $word)) {
                                            return true;
                                        }
                                        return false;
                                    });

                                    if (isset($check) && is_array($check) && count($check) > 0) {
                                        array_push($block_message, [
                                            'user_id' => Auth::guard('client')->user()->id,
                                            'sender' => $sender_id,
                                            'campaign_id' => $campaign_id,
                                            'receiver' => $clphone,
                                            'message' => $get_message,
                                            'scheduled_time' => null,
                                            'use_gateway' => $gateway_id,
                                            'status' => 'block',
                                            'type' => $msg_type,
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'updated_at' => date('Y-m-d H:i:s')
                                        ]);
                                    } else {
                                        array_push($get_data, [
                                            'campaign_id' => $campaign_id,
                                            'number' => $clphone,
                                            'message' => $get_message,
                                            'amount' => $msgcount,
                                            'status' => 'queued'
                                        ]);
                                    }
                                } else {
                                    array_push($get_data, [
                                        'campaign_id' => $campaign_id,
                                        'number' => $clphone,
                                        'message' => $get_message,
                                        'amount' => $msgcount,
                                        'status' => 'queued'
                                    ]);
                                }
                            }
                        } else {
                            array_push($get_inactive_coverage, $clphone);
                        }
                    }
                }
            }
        });

        if (isset($valid_phone_numbers) && is_array($valid_phone_numbers) && count($valid_phone_numbers) <= 0) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Invalid phone numbers'),
                'message_important' => true
            ]);
        }

        unset($valid_phone_numbers);

        if ($request->remove_duplicate == 'yes') {
            $get_data = unique_multidim_array($get_data, 'number');
            if (isset($block_message) && is_array($block_message) && count($block_message) > 0) {
                $block_message = unique_multidim_array($block_message, 'receiver');
            }
        }

        $get_data = array_values($get_data);

        if (isset($get_data) && is_array($get_data) && count($get_data) <= 0) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($get_cost == 0) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($get_cost > $sms_count) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $total_recipient = count($get_data) + count($block_message);

        $campaign = Campaigns::create([
            'campaign_id' => $campaign_id,
            'user_id' => Auth::guard('client')->user()->id,
            'sender' => $sender_id,
            'sms_type' => $msg_type,
            'camp_type' => $campaign_type,
            'status' => $campaign_status,
            'use_gateway' => $gateway->id,
            'total_recipient' => $total_recipient,
            'run_at' => date('Y-m-d H:i:s'),
            'media_url' => $media_url,
            'keyword' => $keywords
        ]);


        if ($campaign) {

            if (app_config('fraud_detection') == 1) {
                if (isset($block_message) && is_array($block_message) && count($block_message) > 0) {

                    $store_block_message = BlockMessage::insert($block_message);
                    if ($store_block_message) {

                        $sysUrl = url('clients/view/' . Auth::guard('client')->user()->id);
                        $user_name = Auth::guard('client')->user()->username;
                        $client_email = Auth::guard('client')->user()->email;
                        $spam_message = 'Message contain spam word';

                        \Mail::to(app_config('Email'))->send(new \App\Mail\BlockMessage($user_name, $client_email, $spam_message, $sysUrl));

                    }
                }
            }

            $campaign_list = CampaignSubscriptionList::insert($get_data);

            if ($campaign_list) {

                $remain_sms = $sms_count - $get_cost;
                $client->sms_limit = $remain_sms;
                $client->save();

                if (isset($get_inactive_coverage) && is_array($get_inactive_coverage) && count($get_inactive_coverage) > 0) {
                    $inactive_phone = implode('; ', $get_inactive_coverage);
                    return redirect($redirect_url)->with([
                        'message' => 'This phone number(s) ' . $inactive_phone . ' not send for inactive coverage issue'
                    ]);
                }

                return redirect($redirect_url)->with([
                    'message' => language_data('SMS added in queue and will deliver one by one', Auth::guard('client')->user()->lan_id)
                ]);
            }

            $campaign->delete();
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Something went wrong please try again'),
                'message_important' => true
            ]);
        }
        return redirect($redirect_url)->withInput($request->all())->with([
            'message' => language_data('Something went wrong please try again'),
            'message_important' => true
        ]);

    }


    //======================================================================
    // sendScheduleSMS Function Start Here
    //======================================================================
    public function sendScheduleSMS()
    {
        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $phone_book = ImportPhoneNumber::where('user_id', Auth::guard('client')->user()->id)->get();
        $client_group = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->where('status', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        $country_code = IntCountryCodes::where('Active', 1)->select('country_code', 'country_name')->get();

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();
        $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->where('status', 'assigned')->get();
        $schedule_sms = true;

        return view('client.send-bulk-sms', compact('client_group', 'sms_templates', 'sender_ids', 'phone_book', 'schedule_sms', 'country_code', 'sms_gateways', 'keyword'));
    }


    //======================================================================
    // sendScheduleSMSFromFile Function Start Here
    //======================================================================
    public function sendScheduleSMSFromFile()
    {
        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];


            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        $country_code = IntCountryCodes::where('Active', 1)->select('country_code', 'country_name')->get();


        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();
        $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->where('status', 'assigned')->get();
        $schedule_sms = true;

        return view('client.send-sms-file', compact('sms_templates', 'sender_ids', 'schedule_sms', 'country_code', 'sms_gateways', 'keyword'));
    }

    //======================================================================
    // smsHistory Function Start Here
    //======================================================================
    public function smsHistory()
    {
        return view('client.sms-history');
    }


    //======================================================================
    // smsViewInbox Function Start Here
    //======================================================================
    public function smsViewInbox($id)
    {

        $inbox_info = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($inbox_info) {
            return view('client.sms-inbox', compact('inbox_info'));
        } else {
            return redirect('user/sms/history')->with([
                'message' => language_data('SMS Not Found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // deleteSMS Function Start Here
    //======================================================================
    public function deleteSMS($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/history')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $inbox_info = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($inbox_info) {
            $inbox_info->delete();

            return redirect('user/sms/history')->with([
                'message' => language_data('SMS info deleted successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('user/sms/history')->with([
                'message' => language_data('SMS Not Found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }



    //======================================================================
    // apiInfo Function Start Here
    //======================================================================
    public function apiInfo()
    {

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();

        return view('client.sms-api-info', compact('sms_gateways'));
    }

    //======================================================================
    // updateApiInfo Function Start Here
    //======================================================================
    public function updateApiInfo(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'api_key' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms-api/info')->withErrors($v->errors());
        }

        if ($request->api_key != '') {
            Client::where('id', Auth::guard('client')->user()->id)->where('api_access', 'Yes')->update([
                'api_key' => $request->api_key,
                'api_gateway' => $request->sms_gateway
            ]);
        }


        return redirect('user/sms-api/info')->with([
            'message' => language_data('API information updated successfully', Auth::guard('client')->user()->lan_id)
        ]);

    }


    /*Version 1.1*/


    //======================================================================
    // updateScheduleSMS Function Start Here
    //======================================================================
    public function updateScheduleSMS()
    {
        return view('client.update-schedule-sms');
    }

    //======================================================================
    // getAllScheduleSMS Function Start Here
    //======================================================================
    public function getAllScheduleSMS()
    {

        $schedule_sms = ScheduleSMS::where('userid', Auth::guard('client')->user()->id)->select(['id', 'sender', 'receiver', 'submit_time']);

        return Datatables::of($schedule_sms)
            ->addColumn('action', function ($ss) {
                return '
               <a class="btn btn-success btn-xs" href="' . url("user/sms/manage-update-schedule-sms/$ss->id") . '" ><i class="fa fa-edit"></i>' . language_data('Edit') . '</a>
               <a href="#" class="btn btn-danger btn-xs cdelete" id="' . $ss->id . '"><i class="fa fa-trash"></i> ' . language_data("Delete") . '</a>';
            })
            ->addColumn('id', function ($ss) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$ss->id'/>
                             <span class='co-check-ui'></span>
                             </div>";

            })
            ->addColumn('submit_time', function ($ss) {
                return date(app_config('DateFormat') . " h:m A", strtotime($ss->submit_time));
            })
            ->escapeColumns([])
            ->make(true);
    }


    //======================================================================
    // deleteBulkScheduleSMS Function Start Here
    //======================================================================
    public function deleteBulkScheduleSMS(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/history')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                foreach ($all_ids as $id) {
                    $sms = ScheduleSMS::where('userid', Auth::guard('client')->user()->id)->find($id);
                    if ($sms) {
                        $client = Client::find($sms->userid);
                        $client->sms_limit += $sms->amount;
                        $client->save();

                        $sms->delete();
                    }
                }
            }
        }
    }



    //======================================================================
    // manageUpdateScheduleSMS Function Start Here
    //======================================================================
    public function manageUpdateScheduleSMS($id)
    {
        $sh = CampaignSubscriptionList::find($id);

        if ($sh) {
            return view('client.manage-update-schedule-sms', compact('sh'));
        } else {
            return redirect('user/sms/campaign-reports')->with([
                'message' => language_data('Please try again', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // postUpdateScheduleSMS Function Start Here
    //======================================================================
    public function postUpdateScheduleSMS(Request $request)
    {

        $cmd = $request->cmd;

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'schedule_time' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }


        $sms_info = CampaignSubscriptionList::find($cmd);

        if (!$sms_info) {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                'message' => language_data('SMS info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        if (\DateTime::createFromFormat('m/d/Y h:i A', $request->schedule_time) !== FALSE) {
            $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        } else {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                'message' => language_data('Invalid time format', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $blacklist = BlackListContact::select('numbers')->where('user_id', Auth::guard('client')->user()->id)->get()->toArray();

        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
            $blacklist = array_column($blacklist, 'numbers');
        }

        if (in_array($request->phone_number, $blacklist)) {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('Phone number contain in blacklist', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client == '') {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $campaign = Campaigns::where('campaign_id', $sms_info->campaign_id)->first();

        if (!$campaign) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->with([
                'message' => 'SMS info not found',
                'message_important' => true
            ]);
        }

        if ($campaign->user_id != $client->id) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $msg_type = $campaign->sms_type;
        $message = $request->message;

        if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {


            if (strlen($message) != strlen(utf8_decode($message))) {
                return redirect('user/sms/manage-update-schedule-sms/' . $request->cmd)->with([
                    'message' => 'SMS contain unicode characters',
                    'message_important' => true
                ]);
            }

            $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));
            if ($msgcount <= 160) {
                $msgcount = 1;
            } else {
                $msgcount = $msgcount / 157;
            }
        }
        if ($msg_type == 'unicode' && $msg_type == 'arabic') {
            $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

            if ($msgcount <= 70) {
                $msgcount = 1;
            } else {
                $msgcount = $msgcount / 67;
            }
        }

        $msgcount = ceil($msgcount);

        $phone = str_replace(['(', ')', '+', '-', ' '], '', $request->phone_number);

        if ($sms_info->amount < $msgcount) {
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {


                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                if ($area_code_exist) {
                    $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                    $get_format_data = explode(" ", $format);
                    $operator_settings = explode('-', $get_format_data[1])[0];

                } else {
                    $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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

                    $existing_price = $sms_charge * $sms_info->amount;
                    $total_cost = ($sms_charge * $msgcount) - $existing_price;

                } else {
                    $sms_charge = $sms_cost->plain_tariff;

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $sms_charge = $sms_cost->plain_tariff;
                    }

                    if ($msg_type == 'voice') {
                        $sms_charge = $sms_cost->voice_tariff;
                    }

                    if ($msg_type == 'mms') {
                        $sms_charge = $sms_cost->voice_tariff;
                    }

                    $existing_price = $sms_charge * $sms_info->amount;
                    $total_cost = ($sms_charge * $msgcount) - $existing_price;
                }

                if ($total_cost == 0) {
                    return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($total_cost > $client->sms_limit) {
                    return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('Phone Number Coverage are not active', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            $total_cost = 0;
        }

        if (app_config('fraud_detection') == 1) {
            $spam_word = SpamWord::all()->toArray();
            if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                $spam_word = array_column($spam_word, 'word');
                $check = array_filter($spam_word, function ($word) use ($message) {
                    if (strpos($message, $word)) {
                        return true;
                    }
                    return false;
                });

                if (isset($check) && is_array($check) && count($check) > 0) {
                    BlockMessage::create([
                        'user_id' => Auth::guard('client')->user()->id,
                        'sender' => $campaign->sender,
                        'receiver' => $phone,
                        'message' => $message,
                        'scheduled_time' => $schedule_time,
                        'use_gateway' => $campaign->use_gateway,
                        'status' => 'block',
                        'type' => $msg_type
                    ]);

                    $sysUrl = url('clients/view/' . Auth::guard('client')->user()->id);
                    $user_name = Auth::guard('client')->user()->username;
                    $client_email = Auth::guard('client')->user()->email;

                    \Mail::to(app_config('Email'))->send(new \App\Mail\BlockMessage($user_name, $client_email, $message, $sysUrl));

                    return redirect('user/sms/update-schedule-sms')->with([
                        'message' => language_data('SMS are scheduled. Deliver in correct time', Auth::guard('client')->user()->lan_id)
                    ]);

                }

            }
        }

        CampaignSubscriptionList::where('id', $request->cmd)->where('campaign_id', $campaign->campaign_id)->update([
            'number' => $phone,
            'amount' => $msgcount,
            'message' => $message,
            'submitted_time' => $schedule_time
        ]);

        $remain_sms = $client->sms_limit - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/manage-campaign/' . $campaign->id)->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time', Auth::guard('client')->user()->lan_id)
        ]);

    }

    //======================================================================
    // deleteScheduleSMS Function Start Here
    //======================================================================
    public function deleteScheduleSMS($id)
    {

        $sh = ScheduleSMS::find($id);
        if ($sh) {
            $client = Client::find($sh->userid);
            $client->sms_limit += $sh->amount;
            $client->save();

            $sh->delete();
            return redirect('user/sms/update-schedule-sms')->with([
                'message' => language_data('SMS info deleted successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('user/sms/update-schedule-sms')->with([
                'message' => language_data('Please try again', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    /*Version 1.2*/

    //======================================================================
    // buyUnit Function Start Here
    //======================================================================
    public function buyUnit()
    {
        $bundles = SMSBundles::orderBy('unit_from')->get();
        $payment_gateways = PaymentGateways::where('status', 'Active')->get();
        return view('client.buy-unit', compact('bundles', 'payment_gateways'));
    }

    //======================================================================
    // getTransaction Function Start Here
    //======================================================================
    public function getTransaction(Request $request)
    {


        if ($request->unit_number != '') {
            $data = SMSBundles::where('unit_from', '<=', $request->unit_number)->where('unit_to', '>=', $request->unit_number)->first();

            if ($data) {
                $unit_price = $data->price;
                $amount_to_pay = $request->unit_number * $unit_price;
                $transaction_fee = ($amount_to_pay * $data->trans_fee) / 100;
                $total = $amount_to_pay + $transaction_fee;
            } else {
                $unit_price = 'Price Bundle empty';
                $amount_to_pay = 'Price Bundle empty';
                $transaction_fee = 'Price Bundle empty';
                $total = 'Price Bundle empty';
            }
        } else {
            $unit_price = 'Price Bundle empty';
            $amount_to_pay = 'Price Bundle empty';
            $transaction_fee = 'Price Bundle empty';
            $total = 'Price Bundle empty';
        }


        return response()->json([
            'unit_price' => $unit_price,
            'amount_to_pay' => $amount_to_pay,
            'transaction_fee' => $transaction_fee,
            'total' => $total
        ]);


    }

    //======================================================================
    // postGetTemplateInfo Function Start Here
    //======================================================================
    public function postGetTemplateInfo(Request $request)
    {
        $template = SMSTemplates::find($request->st_id);
        if ($template) {
            return response()->json([
                'from' => $template->from,
                'message' => $template->message,
            ]);
        }
    }
    //======================================================================
    // renderSMS Start Here
    //======================================================================
    public function renderSMS($msg, $data)
    {
        preg_match_all('~<%(.*?)%>~s', $msg, $datas);
        $Html = $msg;
        foreach ($datas[1] as $value) {
            if (array_key_exists($value, $data)) {
                $Html = preg_replace("/\b$value\b/u", $data[$value], $Html);
            } else {
                $Html = str_ireplace($value, '', $Html);
            }
        }
        return str_ireplace(array("<%", "%>"), '', $Html);
    }


    //======================================================================
    // smsTemplates Function Start Here
    //======================================================================
    public function smsTemplates()
    {
        $sms_templates = SMSTemplates::where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        return view('client.sms-templates', compact('sms_templates'));
    }

    //======================================================================
    // createSmsTemplate Function Start Here
    //======================================================================
    public function createSmsTemplate()
    {
        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];


            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        return view('client.create-sms-template', compact('sender_ids'));
    }

    //======================================================================
    // postSmsTemplate Function Start Here
    //======================================================================
    public function postSmsTemplate(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'template_name' => 'required', 'message' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/create-sms-template')->withErrors($v->errors());
        }
        $exist = SMSTemplates::where('template_name', $request->template_name)->where('cl_id', Auth::guard('client')->user()->id)->first();

        if ($exist) {
            return redirect('user/sms/create-sms-template')->with([
                'message' => language_data('Template already exist', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $st = new SMSTemplates();
        $st->cl_id = Auth::guard('client')->user()->id;
        $st->template_name = $request->template_name;
        $st->from = $request->from;
        $st->message = $request->message;
        $st->global = 'no';
        $st->status = 'active';
        $st->save();

        return redirect('user/sms/sms-templates')->with([
            'message' => language_data('Sms template created successfully', Auth::guard('client')->user()->lan_id)
        ]);

    }

    //======================================================================
    // manageSmsTemplate Function Start Here
    //======================================================================
    public function manageSmsTemplate($id)
    {

        $st = SMSTemplates::find($id);
        if ($st) {

            if (app_config('sender_id_verification') == '1') {
                $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
                $all_ids = [];

                foreach ($all_sender_id as $sid) {
                    $client_array = json_decode($sid->cl_id);

                    if (in_array('0', $client_array)) {
                        array_push($all_ids, $sid->sender_id);
                    } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                        array_push($all_ids, $sid->sender_id);
                    }
                }
                $sender_ids = array_unique($all_ids);

            } else {
                $sender_ids = false;
            }

            return view('client.manage-sms-template', compact('st', 'sender_ids'));
        } else {
            return redirect('user/sms/sms-templates')->with([
                'message' => language_data('Sms template not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postManageSmsTemplate Function Start Here
    //======================================================================
    public function postManageSmsTemplate(Request $request)
    {
        $cmd = Input::get('cmd');
        $v = \Validator::make($request->all(), [
            'template_name' => 'required', 'message' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/manage-sms-template/' . $cmd)->withErrors($v->errors());
        }

        $st = SMSTemplates::find($cmd);

        if ($st) {
            if ($st->template_name != $request->template_name) {

                $exist = SMSTemplates::where('template_name', $request->template_name)->where('cl_id', Auth::guard('client')->user()->id)->first();

                if ($exist) {
                    return redirect('user/sms/manage-sms-template/' . $cmd)->with([
                        'message' => language_data('Template already exist', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }

            $st->template_name = $request->template_name;
            $st->from = $request->from;
            $st->message = $request->message;
            $st->status = $request->status;
            $st->save();

            return redirect('user/sms/sms-templates')->with([
                'message' => language_data('Sms template updated successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/sms-templates')->with([
                'message' => language_data('Sms template not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // deleteSmsTemplate Function Start Here
    //======================================================================
    public function deleteSmsTemplate($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/sms-templates')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $st = SMSTemplates::find($id);
        if ($st) {
            $st->delete();

            return redirect('user/sms/sms-templates')->with([
                'message' => language_data('Sms template delete successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/sms-templates')->with([
                'message' => language_data('Sms template not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }



    /*Version 2.0*/

    //======================================================================
    // blacklistContacts Function Start Here
    //======================================================================
    public function blacklistContacts()
    {
        return view('client.blacklist-contacts');
    }

    //======================================================================
    // postBlacklistContact Function Start Here
    //======================================================================
    public function postBlacklistContact(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'delimiter' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/blacklist-contacts')->withErrors($v->errors());
        }

        try {

            if ($request->delimiter == 'automatic') {
                $results = multi_explode(array(",", "\n", ";", " ", "|"), $request->import_numbers);
            } elseif ($request->delimiter == ';') {
                $results = explode(';', $request->import_numbers);
            } elseif ($request->delimiter == ',') {
                $results = explode(',', $request->import_numbers);
            } elseif ($request->delimiter == '|') {
                $results = explode('|', $request->import_numbers);
            } elseif ($request->delimiter == 'tab') {
                $results = explode(' ', $request->import_numbers);
            } elseif ($request->delimiter == 'new_line') {
                $results = explode("\n", $request->import_numbers);
            } else {
                return redirect('user/sms/blacklist-contacts')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }

            $results = array_filter($results);

            foreach ($results as $r) {

                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));
                $exist = BlackListContact::where('numbers', $phone)->where('user_id', Auth::guard('client')->user()->id)->first();

                if (!$exist) {
                    BlackListContact::create([
                        'user_id' => Auth::guard('client')->user()->id,
                        'numbers' => $phone
                    ]);
                }
            }

            return redirect('user/sms/blacklist-contacts')->with([
                'message' => language_data('Number added on blacklist', Auth::guard('client')->user()->lan_id),
            ]);

        } catch (\Exception $e) {
            return redirect('user/sms/blacklist-contacts')->with([
                'message' => $e->getMessage(),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // deleteBlacklistContact Function Start Here
    //======================================================================
    public function deleteBlacklistContact($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $blacklist = BlackListContact::where('user_id', Auth::guard('client')->user()->id)->find($id);
        if ($blacklist) {
            $blacklist->delete();
            return redirect('user/sms/blacklist-contacts')->with([
                'message' => language_data('Number deleted from blacklist', Auth::guard('client')->user()->lan_id),
            ]);
        } else {
            return redirect('user/sms/blacklist-contacts')->with([
                'message' => language_data('Number not found on blacklist', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }



    //======================================================================
    // deleteBulkBlacklistContact Function Start Here
    //======================================================================
    public function deleteBulkBlacklistContact(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode',Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                $status = BlackListContact::where('user_id',Auth::guard('client')->user()->lan_id)->whereIn('id',$all_ids)->delete();

                if ($status){
                    return response()->json([
                        'status' => 'success',
                        'message' => language_data('Number deleted from blacklist',Auth::guard('client')->user()->lan_id),
                    ]);
                }
            }  return response()->json([
                'status' => 'error',
                'message' => 'Invalid request',
            ]);
        }else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request',
            ]);
        }

    }




    //======================================================================
    // getBlacklistContacts Function Start Here
    //======================================================================
    public function getBlacklistContacts()
    {
        $blacklist = BlackListContact::select(['id', 'numbers'])->where('user_id', Auth::guard('client')->user()->id)->get();
        return Datatables::of($blacklist)
            ->addColumn('action', function ($bl) {
                return '
            <a href="#" class="btn btn-danger btn-xs cdelete" id="' . $bl->id . '"><i class="fa fa-trash"></i> ' . language_data("Delete") . '</a>';
            })
            ->addColumn('id', function ($bl) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$bl->id'/>
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->escapeColumns([])
            ->make(true);
    }


    //======================================================================
    // getSmsHistoryData Function Start Here
    //======================================================================
    public function getSmsHistoryData(Request $request)
    {
        if ($request->has('order') && $request->has('columns')) {
            $order_col_num = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by = $request->get('order')[0]['dir'];

            if ($get_search_column == 'date') {
                $get_search_column = 'updated_at';
            }

        } else {
            $get_search_column = 'updated_at';
            $short_by = 'DESC';
        }

        $get_reseller = Client::where('parent', Auth::guard('client')->user()->id)->select('id')->get()->toArray();
        $reseller_ids = array_column($get_reseller, 'id');
        $reseller_ids[]=Auth::guard('client')->user()->id;

        $sms_history = SMSHistory::whereIn('userid',$reseller_ids)->orderBy($get_search_column, $short_by);

        return Datatables::of($sms_history)
            ->addColumn('action', function ($sms) {
                return '
                <a class="btn btn-success btn-xs" href="' . url("user/sms/view-inbox/$sms->id") . '" ><i class="fa fa-inbox"></i> ' . language_data('Inbox', Auth::guard('client')->user()->lan_id) . '</a>
                <a href="#" id="' . $sms->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-danger"></i> ' . language_data('Delete', Auth::guard('client')->user()->lan_id) . '</a>
                ';
            })
            ->addColumn('date', function ($sms) {
                return $sms->updated_at;
            })
            ->addColumn('id', function ($sms) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$sms->id'  />
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->filter(function ($query) use ($request) {

                if ($request->has('send_by') && $request->get('send_by') != '0') {
                    $query->where('send_by', $request->get('send_by'));
                }


                if ($request->has('client')) {

                    $client = Client::where('fname', 'like', "%{$request->get('client')}%")->orWhere('lname', 'like', "%{$request->get('client')}%")->first();
                    if ($client){
                        $query->where('userid', $client->id);
                    }
                }

                if ($request->has('sender')) {
                    $query->where('sender', 'like', "%{$request->get('sender')}%");
                }

                if ($request->has('receiver')) {
                    $query->where('receiver', 'like', "%{$request->get('receiver')}%");
                }

                if ($request->has('status')) {
                    $query->where('status', 'like', "%{$request->get('status')}%");
                }

                if ($request->has('date_from') && $request->has('date_to')) {
                    $date_from = date('Y-m-d H:i:s', strtotime($request->get('date_from')));
                    $date_to = date('Y-m-d H:i:s', strtotime($request->get('date_to')));
                    $query->whereBetween('updated_at', [$date_from, $date_to]);
                }
            })
            ->addColumn('send_by', function ($sms) {
                if ($sms->send_by == 'api') {
                    return '<span class="text-info">' . language_data('API SMS') . ' </span>';
                } else {
                    return language_data('Outgoing');
                }
            })
            ->escapeColumns([])
            ->make(true);
    }
    //======================================================================
    // getRecipientsData Function Start Here
    //======================================================================
    public function getRecipientsData(Request $request)
    {
        if ($request->has('client_group_ids')) {
            $client_group_ids = $request->client_group_ids;
            if (is_array($client_group_ids) && count($client_group_ids) > 0) {
                $count = Client::whereIn('groupid', $client_group_ids)->count();
                return response()->json(['status' => 'success', 'data' => $count]);
            } else {
                return response()->json(['status' => 'success', 'data' => 0]);
            }
        } elseif ($request->has('contact_list_ids')) {
            $contact_list_ids = $request->contact_list_ids;
            if (is_array($contact_list_ids) && count($contact_list_ids) > 0) {
                $count = ContactList::whereIn('pid', $contact_list_ids)->count();
                return response()->json(['status' => 'success', 'data' => $count]);
            } else {
                return response()->json(['status' => 'success', 'data' => 0]);
            }
        } else {
            return response()->json(['status' => 'success', 'data' => 0]);
        }
    }

    //======================================================================
    // deleteBulkSMS Function Start Here
    //======================================================================
    public function deleteBulkSMS(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/history')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (is_array($all_ids) && count($all_ids) > 0) {
                SMSHistory::where('userid', Auth::guard('client')->user()->id)->whereIn('id', $all_ids)->delete();
            }
        }
    }


    //======================================================================
    // sdkInfo Function Start Here
    //======================================================================
    public function sdkInfo()
    {
        return view('client.sms-sdk-info');
    }


    //======================================================================
    // sendQuickSMS Function Start Here
    //======================================================================
    public function sendQuickSMS()
    {
        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();
        $country_code = IntCountryCodes::where('Active', 1)->select('country_code', 'country_name')->get();

        return view('client.send-quick-sms', compact('sender_ids', 'country_code', 'sms_gateways'));
    }




    //======================================================================
    // postQuickSMS Function Start Here
    //======================================================================
    public function postQuickSMS(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'recipients' => 'required', 'message' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'sms_gateway' => 'required', 'delimiter' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/quick-sms')->withInput($request->all())->withErrors($v->errors());
        }


        $message = $request->message;


        if (app_config('fraud_detection') == 1) {
            $spam_word = SpamWord::all()->toArray();
            if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                $spam_word = array_column($spam_word, 'word');
                $check = array_filter($spam_word, function ($word) use ($message) {
                    if (strpos($message, $word)) {
                        return true;
                    }
                    return false;
                });

                if (isset($check) && is_array($check) && count($check) > 0) {
                    return redirect('user/sms/quick-sms')->with([
                        'message' => language_data('Your are sending fraud message', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            }
        }

        $client = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if (app_config('sender_id_verification') == '1') {
            if ($sender_id == null) {
                return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        try {

            if ($request->delimiter == 'automatic') {
                $recipients = multi_explode(array(",", "\n", ";", " ", "|"), $request->recipients);
            } elseif ($request->delimiter == ';') {
                $recipients = explode(';', $request->recipients);
            } elseif ($request->delimiter == ',') {
                $recipients = explode(',', $request->recipients);
            } elseif ($request->delimiter == '|') {
                $recipients = explode('|', $request->recipients);
            } elseif ($request->delimiter == 'tab') {
                $recipients = explode(' ', $request->recipients);
            } elseif ($request->delimiter == 'new_line') {
                $recipients = explode("\n", $request->recipients);
            } else {
                return redirect('user/sms/quick-sms')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }

            $results = array_filter($recipients);

            if (isset($results) && is_array($results) && count($results) <= 100) {

                $gateway = SMSGateways::find($request->sms_gateway);
                if ($gateway->status != 'Active') {
                    return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('SMS gateway not active', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                $gateway_credential = null;
                $cg_info = null;
                if ($gateway->custom == 'Yes') {
                    if ($gateway->type == 'smpp') {
                        $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                        if ($gateway_credential == null) {
                            return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                                'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                                'message_important' => true
                            ]);
                        }
                    } else {
                        $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
                    }

                } else {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                    if ($gateway_credential == null) {
                        return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                }

                $msg_type = $request->message_type;


                if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms' && $msg_type != 'arabic') {
                    return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('Invalid message type', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($msg_type == 'voice') {
                    if ($gateway->voice != 'Yes') {
                        return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('SMS Gateway not supported Voice feature', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                }

                if ($msg_type == 'mms') {

                    if ($gateway->mms != 'Yes') {
                        return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('SMS Gateway not supported MMS feature', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }

                    $image = $request->image;

                    if ($image == '') {
                        return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('MMS file required', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }

                    if (app_config('AppStage') != 'Demo') {
                        if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                            $destinationPath = public_path() . '/assets/mms_file/';
                            $image_name = $image->getClientOriginalName();
                            $image_name = str_replace(" ", "-", $image_name);
                            Input::file('image')->move($destinationPath, $image_name);
                            $media_url = asset('assets/mms_file/' . $image_name);

                        } else {
                            return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                                'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file', Auth::guard('client')->user()->lan_id),
                                'message_important' => true
                            ]);
                        }

                    } else {
                        return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('MMS is disable in demo mode', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                } else {
                    $media_url = null;
                    if ($message == '') {
                        return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                }


                if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                    $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));
                    if ($msgcount <= 160) {
                        $msgcount = 1;
                    } else {
                        $msgcount = $msgcount / 157;
                    }
                }
                if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                    $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

                    if ($msgcount <= 70) {
                        $msgcount = 1;
                    } else {
                        $msgcount = $msgcount / 67;
                    }
                }

                $msgcount = ceil($msgcount);


                $get_cost = 0;
                $get_inactive_coverage = [];

                $filtered_data = [];
                $blacklist = BlackListContact::select('numbers')->where('user_id', Auth::guard('client')->user()->id)->get()->toArray();

                if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                    $blacklist = array_column($blacklist, 'numbers');

                    array_filter($results, function ($element) use ($blacklist, &$filtered_data, $request) {
                        $element = trim($element);
                        if ($request->country_code != 0) {
                            $element = $request->country_code . ltrim($element, '0');
                        }
                        if (!in_array($element, $blacklist)) {
                            if ($request->country_code != 0) {
                                $element = ltrim($element, $request->country_code);
                            }
                            array_push($filtered_data, $element);
                        }
                    });

                    $results = array_values($filtered_data);
                }

                if (count($results) <= 0) {
                    return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($request->remove_duplicate == 'yes') {
                    $results = array_map('trim', $results);
                    $results = array_unique($results, SORT_REGULAR);
                }

                $results = array_values($results);

                $get_final_data = [];

                foreach ($results as $r) {

                    $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                    if (validate_phone_number($phone)) {

                        if ($request->country_code == 0) {
                            if ($gateway->settings == 'FortDigital') {
                                $c_phone = 61;
                            } else {
                                $c_phone = PhoneNumber::get_code($phone);
                            }
                        } else {
                            $phone = $request->country_code . ltrim($phone, '0');
                            $c_phone = $request->country_code;
                        }

                        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

                        if ($sms_cost) {

                            $phoneUtil = PhoneNumberUtil::getInstance();
                            $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                            $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                            if ($area_code_exist) {
                                $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                                $get_format_data = explode(" ", $format);
                                $operator_settings = explode('-', $get_format_data[1])[0];

                            } else {
                                $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                }

                $total_cost = $get_cost * $msgcount;

                if ($total_cost == 0) {
                    return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($total_cost > $sms_count) {
                    return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                $remain_sms = $sms_count - $total_cost;
                $client->sms_limit = $remain_sms;
                $client->save();

                foreach ($get_final_data as $r) {
                    $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $this->dispatch(new SendBulkSMS($client->id, $phone, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info, '', $msg_type));
                    }
                    if ($msg_type == 'voice') {
                        $this->dispatch(new SendBulkVoice($client->id, $phone, $gateway, $gateway_credential, $sender_id, $message, $msgcount));
                    }
                    if ($msg_type == 'mms') {
                        $this->dispatch(new SendBulkMMS($client->id, $phone, $gateway, $gateway_credential, $sender_id, $message, $media_url));
                    }
                }

                if (isset($get_inactive_coverage) && is_array($get_inactive_coverage) && count($get_inactive_coverage) > 0) {
                    $inactive_phone = implode('; ', $get_inactive_coverage);
                    return redirect('user/sms/quick-sms')->with([
                        'message' => 'This phone number(s) ' . $inactive_phone . ' not send for inactive coverage issue'
                    ]);
                }

                return redirect('user/sms/quick-sms')->with([
                    'message' => language_data('Please check sms history for status', Auth::guard('client')->user()->lan_id)
                ]);
            } else {
                return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                    'message' => language_data('You can not send more than 100 sms using quick sms option', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

        } catch (\Exception $e) {
            return redirect('user/sms/quick-sms')->withInput($request->all())->with([
                'message' => $e->getMessage(),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // postReplySMS Function Start Here
    //======================================================================
    public function postReplySMS($cmd, $message)
    {
        if ($message == '') {
            return redirect('user/sms/history')->with([
                'message' => language_data('Insert your message', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $h = SMSHistory::find($cmd);
        if ($h) {

            $client = Client::find($h->userid);
            $gateway = SMSGateways::find($h->use_gateway);

            if ($gateway->status != 'Active') {
                return redirect('user/sms/history')->with([
                    'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $gateway_credential = null;
            $cg_info = null;
            if ($gateway->custom == 'Yes') {
                if ($gateway->type == 'smpp') {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                    if ($gateway_credential == null) {
                        return redirect('user/sms/history')->with([
                            'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                } else {
                    $cg_info = CustomSMSGateways::where('gateway_id', $gateway->id)->first();
                }
            } else {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('user/sms/history')->with([
                        'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }

            $sender_id = $h->receiver;
            $msg_type = $h->sms_type;
            if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));
                if ($msgcount <= 160) {
                    $msgcount = 1;
                } else {
                    $msgcount = $msgcount / 157;
                }
            }
            if ($msg_type == 'unicode') {
                $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

                if ($msgcount <= 70) {
                    $msgcount = 1;
                } else {
                    $msgcount = $msgcount / 67;
                }
            }

            $msgcount = ceil($msgcount);

            $phone = $h->sender;
            if ($gateway->name == 'FortDigital') {
                $c_phone = 61;
            } elseif ($gateway->name == 'Ibrbd') {
                $c_phone = 880;
            } else {
                $c_phone = PhoneNumber::get_code($phone);
            }

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                if ($area_code_exist) {
                    $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                    $get_format_data = explode(" ", $format);
                    $operator_settings = explode('-', $get_format_data[1])[0];

                } else {
                    $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
                    $operator_settings = $carrierMapper->getNameForNumber($phoneNumberObject, 'en');
                }

                $get_operator = Operator::where('operator_setting', $operator_settings)->where('coverage_id', $sms_cost->id)->first();

                if ($get_operator) {

                    $total_cost = ($get_operator->plain_price * $msgcount);

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $total_cost = ($get_operator->plain_price * $msgcount);
                    }

                    if ($msg_type == 'voice') {
                        $total_cost = ($get_operator->voice_price * $msgcount);
                    }

                    if ($msg_type == 'mms') {
                        $total_cost = ($get_operator->mms_price * $msgcount);
                    }
                } else {
                    $total_cost = ($sms_cost->tariff * $msgcount);

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $total_cost = ($sms_cost->plain_tariff * $msgcount);
                    }

                    if ($msg_type == 'voice') {
                        $total_cost = ($sms_cost->voice_tariff * $msgcount);
                    }

                    if ($msg_type == 'mms') {
                        $total_cost = ($sms_cost->mms_tariff * $msgcount);
                    }

                }

                if ($total_cost == 0) {
                    return redirect('user/sms/history')->with([
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($total_cost > $client->sms_limit) {
                    return redirect('user/sms/history')->with([
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('user/sms/history')->with([
                    'message' => language_data('Phone Number Coverage are not active', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if ($sender_id != '' && app_config('sender_id_verification') == '1') {
                $all_sender_id = SenderIdManage::all();
                $all_ids = [];

                foreach ($all_sender_id as $sid) {
                    $client_array = json_decode($sid->cl_id);

                    if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                        array_push($all_ids, $sender_id);
                    } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                        array_push($all_ids, $sid->sender_id);
                    }
                }
                $all_ids = array_unique($all_ids);

                if (!in_array($sender_id, $all_ids)) {
                    return redirect('user/sms/history')->with([
                        'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }


            if (app_config('fraud_detection') == 1) {
                $spam_word = SpamWord::all()->toArray();
                if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                    $spam_word = array_column($spam_word, 'word');

                    $check = array_filter($spam_word, function ($word) use ($message) {
                        if (strpos($message, $word)) {
                            return true;
                        }
                        return false;
                    });

                    if (isset($check) && is_array($check) && count($check) > 0) {
                        return redirect('user/sms/history')->with([
                            'message' => language_data('Message contain spam word', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                }
            }

            if ($msg_type == 'plain' || $msg_type == 'unicode') {
                $this->dispatch(new SendBulkSMS($h->userid, $h->sender, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info, '', $msg_type));
            }

            if ($msg_type == 'voice') {
                $this->dispatch(new SendBulkVoice($h->userid, $h->sender, $gateway, $gateway_credential, $sender_id, $message, $msgcount));
            }

            if ($msg_type == 'mms') {
                return redirect('user/sms/history')->with([
                    'message' => language_data('MMS not supported in two way communication', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $remain_sms = $client->sms_limit - $total_cost;
            $client->sms_limit = $remain_sms;
            $client->save();

            return redirect('user/sms/history')->with([
                'message' => language_data('Successfully sent reply', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/history')->with([
                'message' => language_data('SMS Not Found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Recurring SMS
    |--------------------------------------------------------------------------
    |
    | All work on Recurring sms
    |
    */

    //======================================================================
    // recurringSMS Function Start Here
    //======================================================================
    public function recurringSMS()
    {
        return view('client.recurring-sms');
    }


    //======================================================================
    // getRecurringSMSData Function Start Here
    //======================================================================
    public function getRecurringSMSData(Request $request)
    {


        if ($request->has('order') && $request->has('columns')) {
            $order_col_num = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by = $request->get('order')[0]['dir'];
        } else {
            $get_search_column = 'updated_at';
            $short_by = 'DESC';
        }

        $recurring_sms = RecurringSMS::select(['id', 'sender', 'status', 'total_recipients', 'recurring_date', 'recurring', 'updated_at'])->where('userid', Auth::guard('client')->user()->id)->orderBy($get_search_column, $short_by);
        return Datatables::of($recurring_sms)
            ->addColumn('action', function ($sms) {
                $reply_url = '';
                if ($sms->status == 'running') {
                    $reply_url .= ' <a class="btn btn-warning btn-xs stop-recurring" href="#" id="' . $sms->id . '"><i class="fa fa-stop"></i> ' . language_data('Stop Recurring', Auth::guard('client')->user()->lan_id) . '  </a>';
                } else {
                    $reply_url .= ' <a class="btn btn-success btn-xs start-recurring" href="#" id="' . $sms->id . '"><i class="fa fa-check"></i> ' . language_data('Start Recurring', Auth::guard('client')->user()->lan_id) . ' </a>';

                }
                return $reply_url . '
                <a href="#" id="' . $sms->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                <div class="btn-group btn-mini-group dropdown-default">
                    <a class="btn btn-xs dropdown-toggle btn-complete" data-toggle="dropdown" href="#" aria-expanded="false"><i class="fa fa-bars"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="' . url("user/sms/update-recurring-sms/$sms->id") . '" data-toggle="tooltip" data-placement="left" title="' . language_data('Update Period', Auth::guard('client')->user()->lan_id) . '"><i class="fa fa-clock-o"></i></a></li>
                        <li><a href="' . url("user/sms/add-recurring-sms-contact/$sms->id") . '" data-toggle="tooltip" data-placement="left" title="' . language_data('Add Contact', Auth::guard('client')->user()->lan_id) . '"><i class="fa fa-plus"></i></a></li>
                        <li><a href="' . url("user/sms/update-recurring-sms-contact/$sms->id") . '" data-toggle="tooltip" data-placement="left" title="' . language_data('Update Contact', Auth::guard('client')->user()->lan_id) . '"><i class="fa fa-edit"></i></a></li>
                    </ul>
                </div>
                ';
            })
            ->addColumn('recurring_date', function ($sms) {
                return $sms->recurring_date;
            })
            ->addColumn('sender', function ($sms) {
                return $sms->sender;
            })
            ->addColumn('total_recipients', function ($sms) {
                return $sms->total_recipients;
            })
            ->addColumn('id', function ($sms) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$sms->id'  />
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->addColumn('status', function ($sms) {
                if ($sms->status == 'running') {
                    return '<span class="text-success"> ' . language_data('Running') . ' </span>';
                } else {
                    return '<span class="text-danger"> ' . language_data('Stop') . ' </span>';
                }
            })
            ->addColumn('recurring', function ($sms) {
                if ($sms->recurring == '0') {
                    $period = language_data('Custom date');
                } elseif ($sms->recurring == 'day') {
                    $period = language_data('Daily');
                } elseif ($sms->recurring == 'week1') {
                    $period = language_data('Weekly');
                } elseif ($sms->recurring == 'weeks2') {
                    $period = language_data('2 Weeks');
                } elseif ($sms->recurring == 'month1') {
                    $period = language_data('Monthly');
                } elseif ($sms->recurring == 'months2') {
                    $period = language_data('2 Months');
                } elseif ($sms->recurring == 'months3') {
                    $period = language_data('3 Months');
                } elseif ($sms->recurring == 'months6') {
                    $period = language_data('6 Months');
                } elseif ($sms->recurring == 'year1') {
                    $period = language_data('Yearly');
                } elseif ($sms->recurring == 'years2') {
                    $period = language_data('2 Years');
                } elseif ($sms->recurring == 'years3') {
                    $period = language_data('3 Years');
                } else {
                    $period = language_data('Invalid');
                }

                return $period;
            })
            ->escapeColumns([])
            ->make(true);


    }


//======================================================================
// deleteRecurringSMS Function Start Here
//======================================================================
    public function deleteRecurringSMS($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $recurring_sms = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($recurring_sms) {
            RecurringSMSContacts::where('campaign_id', $id)->delete();
            $recurring_sms->delete();
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('SMS info deleted successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('SMS Not Found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }



//======================================================================
// deleteRecurringSMSContact Function Start Here
//======================================================================
    public function deleteRecurringSMSContact($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $recurring_contact = RecurringSMSContacts::find($id);

        if ($recurring_contact) {
            $check_exist = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($recurring_contact->campaign_id);
            if ($check_exist) {
                $recurring_contact->delete();

                return redirect('user/sms/update-recurring-sms-contact/' . $recurring_contact->campaign_id)->with([
                    'message' => language_data('Contact deleted successfully', Auth::guard('client')->user()->lan_id)
                ]);
            } else {
                return redirect('user/sms/recurring-sms')->with([
                    'message' => language_data('Invalid access', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('SMS Not Found'),
                'message_important' => true
            ]);
        }

    }


//======================================================================
// bulkDeleteRecurringSMS Function Start Here
//======================================================================
    public function bulkDeleteRecurringSMS(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (is_array($all_ids) && count($all_ids) > 0) {
                foreach ($all_ids as $id) {

                    $check_exist = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);

                    if ($check_exist) {
                        RecurringSMSContacts::where('campaign_id', $id)->delete();
                        $check_exist->delete();
                    }
                }
            }
        }
    }


    //======================================================================
// bulkDeleteRecurringSMSContact Function Start Here
//======================================================================
    public function bulkDeleteRecurringSMSContact(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('This Option is Disable In Demo Mode'),
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));
            $recipients = count($all_ids);
            if ($request->has('campaign_id')) {
                if (is_array($all_ids) && count($all_ids) > 0) {
                    $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($request->campaign_id);
                    if ($recurring) {
                        RecurringSMSContacts::destroy($all_ids);
                        $recurring->total_recipients -= $recipients;
                        $recurring->save();
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => language_data('Recurring SMS info not found', Auth::guard('client')->user()->lan_id)
                        ]);
                    }

                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => language_data('Recipients required', Auth::guard('client')->user()->lan_id)
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => language_data('Recurring SMS info not found', Auth::guard('client')->user()->lan_id)
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => language_data('Contact deleted successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => language_data('Invalid request', Auth::guard('client')->user()->lan_id)
            ]);
        }
    }



//======================================================================
// sendRecurringSMS Function Start Here
//======================================================================
    public function sendRecurringSMS()
    {

        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $phone_book = ImportPhoneNumber::where('user_id', Auth::guard('client')->user()->id)->get();
        $client_group = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->where('status', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        $country_code = IntCountryCodes::where('Active', 1)->select('country_code', 'country_name')->get();

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();

        return view('client.send-recurring-sms', compact('client_group', 'sms_templates', 'sender_ids', 'phone_book', 'country_code', 'sms_gateways'));
    }

    //======================================================================
    // postRecurringSMS Function Start Here
    //======================================================================

    public function postRecurringSMS(Request $request)
    {

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }


        $v = \Validator::make($request->all(), [
            'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'period' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-recurring-sms')->withInput($request->all())->withErrors($v->errors());
        }


        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-recurring-sms')->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }


        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('user/sms/send-recurring-sms')->with([
                        'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $sender_id = $request->sender_id;
        $message = $request->message;
        $msg_type = $request->message_type;


        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms') {
            return redirect('user/sms/send-recurring-sms')->with([
                'message' => language_data('Invalid message type', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => language_data('SMS Gateway not supported Voice feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => language_data('SMS Gateway not supported MMS feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => language_data('MMS file required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name = $image->getClientOriginalName();
                    $image_name = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('user/sms/send-recurring-sms')->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => language_data('MMS is disable in demo mode', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        if (app_config('sender_id_verification') == '1') {
            if ($sender_id == null) {
                return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        $period = $request->period;
        $its = strtotime(date('Y-m-d'));

        if ($period == 'day') {
            $nd = date('Y-m-d', strtotime('+1 day', $its));
        } elseif ($period == 'week1') {
            $nd = date('Y-m-d', strtotime('+1 week', $its));
        } elseif ($period == 'weeks2') {
            $nd = date('Y-m-d', strtotime('+2 weeks', $its));
        } elseif ($period == 'month1') {
            $nd = date('Y-m-d', strtotime('+1 month', $its));
        } elseif ($period == 'months2') {
            $nd = date('Y-m-d', strtotime('+2 months', $its));
        } elseif ($period == 'months3') {
            $nd = date('Y-m-d', strtotime('+3 months', $its));
        } elseif ($period == 'months6') {
            $nd = date('Y-m-d', strtotime('+6 months', $its));
        } elseif ($period == 'year1') {
            $nd = date('Y-m-d', strtotime('+1 year', $its));
        } elseif ($period == 'years2') {
            $nd = date('Y-m-d', strtotime('+2 years', $its));
        } elseif ($period == 'years3') {
            $nd = date('Y-m-d', strtotime('+3 years', $its));
        } elseif ($period == '0') {
            if ($request->schedule_time == '') {
                return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('Schedule time required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
            $nd = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        } else {
            return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                'message' => language_data('Date Parsing Error', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($period != '0') {

            if ($request->recurring_time == '') {
                return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('Schedule time required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $schedule_time = $request->recurring_time;
            $nd = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
        }

        $message = $request->message;
        $get_cost = 0;
        $results = [];

        if ($request->contact_type == 'phone_book') {
            if (isset($request->contact_list_id) && is_array($request->contact_list_id) && count($request->contact_list_id)) {
                $get_data = ContactList::whereIn('pid', $request->contact_list_id)->select('phone_number', 'email_address', 'user_name', 'company', 'first_name', 'last_name')->get()->toArray();
                foreach ($get_data as $data) {
                    array_push($results, $data);
                }
            }
        }

        if ($request->contact_type == 'client_group') {
            $get_group = Client::whereIn('groupid', $request->client_group_id)->select('phone AS phone_number', 'email AS email_address', 'username AS user_name', 'company AS company', 'fname AS first_name', 'lname AS last_name')->get()->toArray();
            foreach ($get_group as $data) {
                array_push($results, $data);
            }
        }

        if ($request->recipients) {

            if ($request->delimiter == 'automatic') {
                $recipients = multi_explode(array(",", "\n", ";", " ", "|"), $request->recipients);
            } elseif ($request->delimiter == ';') {
                $recipients = explode(';', $request->recipients);
            } elseif ($request->delimiter == ',') {
                $recipients = explode(',', $request->recipients);
            } elseif ($request->delimiter == '|') {
                $recipients = explode('|', $request->recipients);
            } elseif ($request->delimiter == 'tab') {
                $recipients = explode(' ', $request->recipients);
            } elseif ($request->delimiter == 'new_line') {
                $recipients = explode("\n", $request->recipients);
            } else {
                return redirect('user/sms/send-recurring-sms')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }


            foreach ($recipients as $r) {

                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));
                if ($request->country_code != 0) {
                    $phone = $request->country_code . ltrim($phone, '0');
                } else {
                    $phone = ltrim($phone, '0');
                }

                if (validate_phone_number($phone)) {
                    $data = [
                        'phone_number' => $phone,
                        'email_address' => null,
                        'user_name' => null,
                        'company' => null,
                        'first_name' => null,
                        'last_name' => null
                    ];
                    array_push($results, $data);
                }
            }
        }


        if (isset($results) && is_array($results)) {

            if (count($results) > 0) {

                $filtered_data = [];
                $blacklist = BlackListContact::select('numbers')->where('user_id', Auth::guard('client')->user()->id)->get()->toArray();

                if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                    $blacklist = array_column($blacklist, 'numbers');

                    array_filter($results, function ($element) use ($blacklist, &$filtered_data) {
                        if (!in_array($element['phone_number'], $blacklist)) {
                            array_push($filtered_data, $element);
                        }
                    });

                    $results = array_values($filtered_data);
                }

                if (count($results) <= 0) {
                    return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                        'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($request->remove_duplicate == 'yes') {
                    $results = unique_multidim_array($results, 'phone_number');
                }

                $results = array_values($results);
                $total_recipients = count($results);

                $recurring_id = RecurringSMS::create([
                    'userid' => Auth::guard('client')->user()->id,
                    'sender' => $sender_id,
                    'total_recipients' => $total_recipients,
                    'status' => 'running',
                    'type' => $msg_type,
                    'media_url' => $media_url,
                    'use_gateway' => $gateway->id,
                    'recurring' => $period,
                    'recurring_date' => $nd
                ]);


                if ($recurring_id) {
                    foreach (array_chunk($results, 50) as $chunk_result) {
                        foreach ($chunk_result as $r) {
                            $msg_data = array(
                                'Phone Number' => $r['phone_number'],
                                'Email Address' => $r['email_address'],
                                'User Name' => $r['user_name'],
                                'Company' => $r['company'],
                                'First Name' => $r['first_name'],
                                'Last Name' => $r['last_name'],
                            );

                            $get_message = $this->renderSMS($message, $msg_data);


                            if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                                $msgcount = strlen(preg_replace('/\s+/', ' ', trim($get_message)));
                                if ($msgcount <= 160) {
                                    $msgcount = 1;
                                } else {
                                    $msgcount = $msgcount / 157;
                                }
                            }
                            if ($msg_type == 'unicode') {
                                $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($get_message)), 'UTF-8');

                                if ($msgcount <= 70) {
                                    $msgcount = 1;
                                } else {
                                    $msgcount = $msgcount / 67;
                                }
                            }
                            $msgcount = ceil($msgcount);

                            $phone = str_replace(['(', ')', '+', '-', ' '], '', $r['phone_number']);

                            if ($gateway->name == 'FortDigital') {
                                $c_phone = 61;
                            } else {
                                $c_phone = PhoneNumber::get_code($phone);
                            }

                            if (app_config('fraud_detection') == 1) {
                                $spam_word = SpamWord::all()->toArray();
                                if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                                    $spam_word = array_column($spam_word, 'word');

                                    $check = array_filter($spam_word, function ($word) use ($get_message) {
                                        if (strpos($get_message, $word)) {
                                            return true;
                                        }
                                        return false;
                                    });

                                    if (isset($check) && is_array($check) && count($check) > 0) {
                                        continue;
                                    }
                                }
                            }

                            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                            if ($sms_cost) {

                                $phoneUtil = PhoneNumberUtil::getInstance();
                                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                                $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                                if ($area_code_exist) {
                                    $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                                    $get_format_data = explode(" ", $format);
                                    $operator_settings = explode('-', $get_format_data[1])[0];

                                } else {
                                    $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                                continue;
                            }


                            $total_cost = $get_cost * $msgcount;

                            RecurringSMSContacts::create([
                                'campaign_id' => $recurring_id->id,
                                'receiver' => $phone,
                                'message' => $get_message,
                                'amount' => $total_cost
                            ]);

                        }
                    }

                    return redirect('user/sms/send-recurring-sms')->with([
                        'message' => language_data('Message recurred successfully. Delivered in correct time', Auth::guard('client')->user()->lan_id)
                    ]);

                } else {
                    return redirect('user/sms/send-recurring-sms')->with([
                        'message' => language_data('Something went wrong please try again', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                'message' => language_data('Invalid Recipients', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // stopRecurringSMS Function Start Here
    //======================================================================
    public function stopRecurringSMS($id)
    {
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($recurring) {
            $recurring->status = 'stop';
            $recurring->save();

            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring SMS stop successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // startRecurringSMS Function Start Here
    //======================================================================
    public function startRecurringSMS($id)
    {
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($recurring) {
            $period = $recurring->recurring;
            $its = strtotime(date('Y-m-d'));

            if ($period == 'day') {
                $nd = date('Y-m-d', strtotime('+1 day', $its));
            } elseif ($period == 'week1') {
                $nd = date('Y-m-d', strtotime('+1 week', $its));
            } elseif ($period == 'weeks2') {
                $nd = date('Y-m-d', strtotime('+2 weeks', $its));
            } elseif ($period == 'month1') {
                $nd = date('Y-m-d', strtotime('+1 month', $its));
            } elseif ($period == 'months2') {
                $nd = date('Y-m-d', strtotime('+2 months', $its));
            } elseif ($period == 'months3') {
                $nd = date('Y-m-d', strtotime('+3 months', $its));
            } elseif ($period == 'months6') {
                $nd = date('Y-m-d', strtotime('+6 months', $its));
            } elseif ($period == 'year1') {
                $nd = date('Y-m-d', strtotime('+1 year', $its));
            } elseif ($period == 'years2') {
                $nd = date('Y-m-d', strtotime('+2 years', $its));
            } elseif ($period == 'years3') {
                $nd = date('Y-m-d', strtotime('+3 years', $its));
            } elseif ($period == '0') {
                $nd = date('Y-m-d H:i:s', strtotime($recurring->recurring_date));
            } else {
                return redirect('user/sms/recurring-sms')->with([
                    'message' => language_data('Date Parsing Error', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if ($period != '0') {
                $schedule_time = date("H:i:s", strtotime($recurring->recurring_date));
                $nd = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
            }

            $recurring->recurring_date = $nd;
            $recurring->status = 'running';
            $recurring->save();

            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring SMS running successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // updateRecurringSMS Function Start Here
    //======================================================================
    public function updateRecurringSMS($id)
    {
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);
        if ($recurring) {

            $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
            $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();

            return view('client.update-recurring-sms', compact('recurring', 'sms_gateways'));
        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postUpdateRecurringSMS Function Start Here
    //======================================================================
    public function postUpdateRecurringSMS(Request $request)
    {
        $cmd = $request->cmd;
        $v = \Validator::make($request->all(), [
            'period' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/update-recurring-sms/' . $cmd)->withErrors($v->errors());
        }

        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($cmd);

        if ($recurring) {

            $gateway = SMSGateways::find($request->sms_gateway);
            if ($gateway->status != 'Active') {
                return redirect('user/sms/update-recurring-sms/' . $cmd)->with([
                    'message' => language_data('SMS gateway not active'),
                    'message_important' => true
                ]);
            }

            $period = $request->period;
            $its = strtotime(date('Y-m-d'));

            if ($period == 'day') {
                $nd = date('Y-m-d', strtotime('+1 day', $its));
            } elseif ($period == 'week1') {
                $nd = date('Y-m-d', strtotime('+1 week', $its));
            } elseif ($period == 'weeks2') {
                $nd = date('Y-m-d', strtotime('+2 weeks', $its));
            } elseif ($period == 'month1') {
                $nd = date('Y-m-d', strtotime('+1 month', $its));
            } elseif ($period == 'months2') {
                $nd = date('Y-m-d', strtotime('+2 months', $its));
            } elseif ($period == 'months3') {
                $nd = date('Y-m-d', strtotime('+3 months', $its));
            } elseif ($period == 'months6') {
                $nd = date('Y-m-d', strtotime('+6 months', $its));
            } elseif ($period == 'year1') {
                $nd = date('Y-m-d', strtotime('+1 year', $its));
            } elseif ($period == 'years2') {
                $nd = date('Y-m-d', strtotime('+2 years', $its));
            } elseif ($period == 'years3') {
                $nd = date('Y-m-d', strtotime('+3 years', $its));
            } elseif ($period == '0') {
                if ($request->schedule_time == '') {
                    return redirect('user/sms/update-recurring-sms/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('Schedule time required', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
                $nd = date('Y-m-d H:i:s', strtotime($request->schedule_time));
            } else {
                return redirect('user/sms/update-recurring-sms/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('Date Parsing Error'),
                    'message_important' => true
                ]);
            }

            if ($period != '0') {
                if ($request->recurring_time == '') {
                    return redirect('user/sms/update-recurring-sms/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('Schedule time required', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                $schedule_time = $request->recurring_time;
                $nd = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
            }

            $recurring->use_gateway = $gateway->id;
            $recurring->recurring = $period;
            $recurring->recurring_date = $nd;
            $recurring->sender = $request->sender_id;
            $recurring->save();

            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring SMS period changed', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // addRecurringSMSContact Function Start Here
    //======================================================================
    public function addRecurringSMSContact($id)
    {
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);
        if ($recurring) {
            $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();
            return view('client.add-recurring-sms-contact', compact('recurring', 'country_code'));
        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // postRecurringSMSContact Function Start Here
    //======================================================================
    public function postRecurringSMSContact(Request $request)
    {
        $id = $request->recurring_id;
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);
        if ($recurring) {

            $v = \Validator::make($request->all(), [
                'recipients' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('user/sms/add-recurring-sms-contact/' . $id)->withInput($request->all())->withErrors($v->errors());
            }


            if ($request->delimiter == 'automatic') {
                $recipients = multi_explode(array(",", "\n", ";", " ", "|"), $request->recipients);
            } elseif ($request->delimiter == ';') {
                $recipients = explode(';', $request->recipients);
            } elseif ($request->delimiter == ',') {
                $recipients = explode(',', $request->recipients);
            } elseif ($request->delimiter == '|') {
                $recipients = explode('|', $request->recipients);
            } elseif ($request->delimiter == 'tab') {
                $recipients = explode(' ', $request->recipients);
            } elseif ($request->delimiter == 'new_line') {
                $recipients = explode("\n", $request->recipients);
            } else {
                return redirect('user/sms/add-recurring-sms-contact/' . $id)->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }
            $results = array_filter($recipients);

            if (isset($results) && is_array($results)) {

                $msg_type = $recurring->type;
                $message = $request->message;


                if ($msg_type != 'mms') {
                    if ($message == '') {
                        return redirect('user/sms/add-recurring-sms-contact/' . $id)->withInput($request->all())->with([
                            'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                }

                $filtered_data = [];
                $blacklist = BlackListContact::select('numbers')->get()->toArray();

                if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                    $blacklist = array_column($blacklist, 'numbers');

                    array_filter($results, function ($element) use ($blacklist, &$filtered_data) {
                        if (!in_array(trim($element), $blacklist)) {
                            array_push($filtered_data, $element);
                        }
                    });

                    $results = array_values($filtered_data);
                }

                if (count($results) <= 0) {
                    return redirect('user/sms/add-recurring-sms-contact/' . $id)->withInput($request->all())->with([
                        'message' => language_data('Recipient empty', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

                if ($request->remove_duplicate == 'yes') {
                    $results = array_map('trim', $results);
                    $results = array_unique($results, SORT_REGULAR);
                }

                $results = array_values($results);

                $current_recipients = 0;

                if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                    $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));
                    if ($msgcount <= 160) {
                        $msgcount = 1;
                    } else {
                        $msgcount = $msgcount / 157;
                    }
                }
                if ($msg_type == 'unicode') {
                    $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

                    if ($msgcount <= 70) {
                        $msgcount = 1;
                    } else {
                        $msgcount = $msgcount / 67;
                    }
                }
                $msgcount = ceil($msgcount);

                $get_inactive_coverage = [];
                $sms_charge = 0;

                foreach ($results as $r) {

                    $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                    if ($request->country_code != 0) {
                        $phone = $request->country_code . ltrim($phone, '0');
                        $c_phone = $request->country_code;
                    } else {
                        $c_phone = PhoneNumber::get_code($phone);
                    }


                    if (app_config('fraud_detection') == 1) {
                        $spam_word = SpamWord::all()->toArray();
                        if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                            $spam_word = array_column($spam_word, 'word');

                            $check = array_filter($spam_word, function ($word) use ($message) {
                                if (strpos($message, $word)) {
                                    return true;
                                }
                                return false;
                            });

                            if (isset($check) && is_array($check) && count($check) > 0) {
                                continue;
                            }
                        }
                    }

                    $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                    if ($sms_cost) {

                        $phoneUtil = PhoneNumberUtil::getInstance();
                        $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                        $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                        if ($area_code_exist) {
                            $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                            $get_format_data = explode(" ", $format);
                            $operator_settings = explode('-', $get_format_data[1])[0];

                        } else {
                            $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                        }
                    } else {
                        array_push($get_inactive_coverage, 'found');
                    }


                    if (in_array('found', $get_inactive_coverage)) {
                        return redirect('user/sms/send-recurring-sms')->withInput($request->all())->with([
                            'message' => language_data('Phone Number Coverage are not active', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }

                    $total_cost = $sms_charge * $msgcount;

                    $exist_check = RecurringSMSContacts::where('campaign_id', $id)->where('receiver', $phone)->first();
                    if (!$exist_check) {
                        RecurringSMSContacts::create([
                            'campaign_id' => $id,
                            'receiver' => $phone,
                            'message' => $message,
                            'amount' => $total_cost
                        ]);
                        $current_recipients++;
                    }
                }
                $recurring->total_recipients += $current_recipients;
                $recurring->save();

                return redirect('user/sms/add-recurring-sms-contact/' . $id)->with([
                    'message' => language_data('Recurring contact added successfully', Auth::guard('client')->user()->lan_id)
                ]);
            } else {
                return redirect('user/sms/recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('Invalid request', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postUpdateRecurringSMSContactData Function Start Here
    //======================================================================
    public function postUpdateRecurringSMSContactData(Request $request)
    {
        $id = $request->recurring_id;
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);
        if ($recurring) {

            $contact_id = $request->contact_id;

            $v = \Validator::make($request->all(), [
                'phone_number' => 'required', 'message' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('user/sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->withErrors($v->errors());
            }

            $msg_type = $recurring->type;
            $message = $request->message;

            if ($msg_type != 'mms') {
                if ($message == '') {
                    return redirect('user/sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->with([
                        'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }

            if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));
                if ($msgcount <= 160) {
                    $msgcount = 1;
                } else {
                    $msgcount = $msgcount / 157;
                }
            }
            if ($msg_type == 'unicode') {
                $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

                if ($msgcount <= 70) {
                    $msgcount = 1;
                } else {
                    $msgcount = $msgcount / 67;
                }
            }

            $msgcount = ceil($msgcount);

            $blacklist = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

            if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
                $blacklist = array_column($blacklist, 'numbers');
            }

            if (in_array($request->phone_number, $blacklist)) {
                return redirect('user/sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->with([
                    'message' => language_data('Phone number contain in blacklist', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }


            $phone = str_replace(['(', ')', '+', '-', ' '], '', $request->phone_number);
            $c_phone = PhoneNumber::get_code($phone);


            if (app_config('fraud_detection') == 1) {
                $spam_word = SpamWord::all()->toArray();
                if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                    $spam_word = array_column($spam_word, 'word');

                    $check = array_filter($spam_word, function ($word) use ($message) {
                        if (strpos($message, $word)) {
                            return true;
                        }
                        return false;
                    });

                    if (isset($check) && is_array($check) && count($check) > 0) {
                        return redirect('user/sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->with([
                            'message' => language_data('Message contain spam word', Auth::guard('client')->user()->lan_id),
                            'message_important' => true
                        ]);
                    }
                }
            }

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
            if ($sms_cost) {

                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                if ($area_code_exist) {
                    $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                    $get_format_data = explode(" ", $format);
                    $operator_settings = explode('-', $get_format_data[1])[0];

                } else {
                    $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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

                }
            } else {
                return redirect('user/sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->with([
                    'message' => language_data('Phone Number Coverage are not active', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }


            $total_cost = $sms_charge * $msgcount;

            RecurringSMSContacts::find($contact_id)->update([
                'receiver' => $phone,
                'message' => $message,
                'amount' => $total_cost
            ]);

            return redirect('user/sms/update-recurring-sms-contact-data/' . $contact_id)->with([
                'message' => language_data('Recurring contact updated successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // updateRecurringSMSContact Function Start Here
    //======================================================================
    public function updateRecurringSMSContact($id)
    {
        $recurring = RecurringSMS::where('userid', Auth::guard('client')->user()->id)->find($id);
        if ($recurring) {
            return view('client.update-recurring-sms-contact', compact('id'));
        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // getRecurringSMSContactData Function Start Here
    //======================================================================
    public function getRecurringSMSContactData($id, Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by = $request->get('order')[0]['dir'];
        } else {
            $get_search_column = 'updated_at';
            $short_by = 'DESC';
        }


        $recurring_sms = RecurringSMSContacts::where('campaign_id', $id)->select(['id', 'receiver', 'message', 'amount'])->orderBy($get_search_column, $short_by);
        return Datatables::of($recurring_sms)
            ->addColumn('action', function ($sms) {
                return '
                <a href="' . url("user/sms/update-recurring-sms-contact-data/$sms->id") . '" class="btn btn-xs btn-complete"><i class="fa fa-edit"></i> ' . language_data('Update') . '</a>
                <a href="#" id="' . $sms->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                
                ';
            })
            ->addColumn('id', function ($sms) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$sms->id'  />
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->escapeColumns([])
            ->make(true);
    }


    //======================================================================
    // updateRecurringSMSContactData Function Start Here
    //======================================================================
    public function updateRecurringSMSContactData($id)
    {
        $recurring = RecurringSMSContacts::find($id);
        if ($recurring) {
            return view('client.update-recurring-sms-contact-data', compact('recurring'));
        } else {
            return redirect('user/sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }



    //======================================================================
    // sendRecurringSMSFile Function Start Here
    //======================================================================
    public function sendRecurringSMSFile()
    {

        if (app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::where('status', 'unblock')->get();
            $all_ids = [];


            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array('0', $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                } elseif (isset($client_array) && is_array($client_array) && in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $sender_ids = array_unique($all_ids);

        } else {
            $sender_ids = false;
        }

        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', Auth::guard('client')->user()->id)->orWhere('global', 'yes')->get();
        $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        $gateways = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();


        return view('client.send-recurring-sms-file', compact('sms_templates', 'sender_ids', 'country_code', 'sms_gateways'));
    }




    //======================================================================
    // postRecurringSMSFile Function Start Here
    //======================================================================
    public function postRecurringSMSFile(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-recurring-sms-file')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'period' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('user/sms/send-recurring-sms-file')->with([
                        'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect('user/sms/send-recurring-sms-file')->with([
                    'message' => language_data('SMS gateway not active.Contact with Provider', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $sender_id = $request->sender_id;
        $msg_type = $request->message_type;
        $message = $request->message;

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms') {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Invalid message type', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported Voice feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported MMS feature', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('MMS file required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name = $image->getClientOriginalName();
                    $image_name = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('Message required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }


        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r(strtolower($file_extension), $supportedExt)) {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Insert Valid Excel or CSV file', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $period = $request->period;
        $its = strtotime(date('Y-m-d'));

        if ($period == 'day') {
            $nd = date('Y-m-d', strtotime('+1 day', $its));
        } elseif ($period == 'week1') {
            $nd = date('Y-m-d', strtotime('+1 week', $its));
        } elseif ($period == 'weeks2') {
            $nd = date('Y-m-d', strtotime('+2 weeks', $its));
        } elseif ($period == 'month1') {
            $nd = date('Y-m-d', strtotime('+1 month', $its));
        } elseif ($period == 'months2') {
            $nd = date('Y-m-d', strtotime('+2 months', $its));
        } elseif ($period == 'months3') {
            $nd = date('Y-m-d', strtotime('+3 months', $its));
        } elseif ($period == 'months6') {
            $nd = date('Y-m-d', strtotime('+6 months', $its));
        } elseif ($period == 'year1') {
            $nd = date('Y-m-d', strtotime('+1 year', $its));
        } elseif ($period == 'years2') {
            $nd = date('Y-m-d', strtotime('+2 years', $its));
        } elseif ($period == 'years3') {
            $nd = date('Y-m-d', strtotime('+3 years', $its));
        } elseif ($period == '0') {
            if ($request->schedule_time == '') {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('Schedule time required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
            $nd = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        } else {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Date Parsing Error', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($period != '0') {

            if ($request->recurring_time == '') {
                return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('Schedule time required', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $schedule_time = $request->recurring_time;
            $nd = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
        }

        $all_data = Excel::load($request->import_numbers)->noHeading()->all()->toArray();

        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Empty field', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $counter = "A";

        if ($request->header_exist == 'on') {

            $header = array_shift($all_data);

            foreach ($header as $key => $value) {
                if (!$value) {
                    $header[$key] = "Column " . $counter;
                }

                $counter++;
            }

        } else {

            $header_like = $all_data[0];

            $header = array();

            foreach ($header_like as $h) {
                array_push($header, "Column " . $counter);
                $counter++;
            }

        }

        $all_data = array_map(function ($row) use ($header) {

            return array_combine($header, $row);

        }, $all_data);

        $valid_phone_numbers = [];
        $get_data = [];

        $blacklist = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
            $blacklist = array_column($blacklist, 'numbers');
        }


        $number_column = $request->number_column;

        array_filter($all_data, function ($data) use ($number_column, &$get_data, &$valid_phone_numbers, $blacklist) {

            if ($data[$number_column]) {
                if (!in_array($data[$number_column], $blacklist)) {
                    array_push($valid_phone_numbers, $data[$number_column]);
                    array_push($get_data, $data);
                }
            }
        });

        if (isset($valid_phone_numbers) && is_array($valid_phone_numbers) && count($valid_phone_numbers) <= 0) {
            return redirect('user/sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Invalid phone numbers', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($request->remove_duplicate == 'yes') {
            $get_data = unique_multidim_array($get_data, $number_column);
        }

        $results = array_values($get_data);
        $total_recipients = count($results);

        $recurring_id = RecurringSMS::create([
            'userid' => Auth::guard('client')->user()->id,
            'sender' => $sender_id,
            'total_recipients' => $total_recipients,
            'status' => 'running',
            'type' => $msg_type,
            'media_url' => $media_url,
            'use_gateway' => $gateway->id,
            'recurring' => $period,
            'recurring_date' => $nd
        ]);

        if ($recurring_id) {
            foreach (array_chunk($results, 50) as $chunk_result) {
                foreach ($chunk_result as $msg_data) {

                    $get_message = $this->renderSMS($message, $msg_data);

                    $phone = str_replace(['(', ')', '+', '-', ' '], '', $msg_data[$number_column]);
                    if ($request->country_code != 0) {
                        $phone = $request->country_code . ltrim($phone, '0');
                        $c_phone = $request->country_code;
                    } else {
                        $c_phone = PhoneNumber::get_code($phone);
                    }


                    if (app_config('fraud_detection') == 1) {
                        $spam_word = SpamWord::all()->toArray();
                        if (isset($spam_word) && is_array($spam_word) && count($spam_word) > 0) {
                            $spam_word = array_column($spam_word, 'word');

                            $check = array_filter($spam_word, function ($word) use ($message) {
                                if (strpos($message, $word)) {
                                    return true;
                                }
                                return false;
                            });

                            if (isset($check) && is_array($check) && count($check) > 0) {
                                continue;
                            }
                        }
                    }

                    $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                    if ($sms_cost) {

                        $phoneUtil = PhoneNumberUtil::getInstance();
                        $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                        $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                        if ($area_code_exist) {
                            $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                            $get_format_data = explode(" ", $format);
                            $operator_settings = explode('-', $get_format_data[1])[0];

                        } else {
                            $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                        }
                    } else {
                        continue;
                    }

                    if ($msg_type == 'plain' || $msg_type == 'voice' || $msg_type == 'mms') {
                        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($get_message)));
                        if ($msgcount <= 160) {
                            $msgcount = 1;
                        } else {
                            $msgcount = $msgcount / 157;
                        }
                    }
                    if ($msg_type == 'unicode') {
                        $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($get_message)), 'UTF-8');

                        if ($msgcount <= 70) {
                            $msgcount = 1;
                        } else {
                            $msgcount = $msgcount / 67;
                        }
                    }
                    $msgcount = ceil($msgcount);

                    $total_cost = $sms_charge * $msgcount;

                    RecurringSMSContacts::create([
                        'campaign_id' => $recurring_id->id,
                        'receiver' => $phone,
                        'message' => $get_message,
                        'amount' => $total_cost
                    ]);

                }
            }

            return redirect('user/sms/send-recurring-sms-file')->with([
                'message' => language_data('Message recurred successfully. Delivered in correct time', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('user/sms/send-recurring-sms-file')->with([
                'message' => language_data('Something went wrong please try again', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // allKeywords Function Start Here
    //======================================================================
    public function allKeywords()
    {
        return view('client.all-keywords');
    }

    //======================================================================
    // getAllKeywords Function Start Here
    //======================================================================
    public function getAllKeywords()
    {


        $keywords = Keywords::select(['id', 'user_id', 'title', 'keyword_name', 'status', 'price', 'reply_mms', 'validity'])->where('status', 'available')->orWhere('user_id', Auth::guard('client')->user()->id);
        return Datatables::of($keywords)
            ->addColumn('action', function ($kw) {

                $reply_url = '';

                if ($kw->status == 'available' && $kw->user_id != Auth::guard('client')->user()->id) {
                    $reply_url .= '
                <a class="btn btn-success btn-xs" href="' . url("user/keywords/purchase/$kw->id") . '" ><i class="fa fa-shopping-cart"></i> ' . language_data('Purchase Now') . '</a>
                ';
                }

                if ($kw->user_id == Auth::guard('client')->user()->id) {
                    if ($kw->reply_mms) {
                        $reply_url .= ' <a href="#" id="id_' . $kw->id . '" class="remove_mms btn btn-xs btn-primary"><i class="fa fa-remove"></i> Remove MMS</a>';
                    }

                    $reply_url .= '
                <a class="btn btn-success btn-xs" href="' . url("user/keywords/view/$kw->id") . '" ><i class="fa fa-edit"></i> ' . language_data('Manage') . '</a>
                ';
                }


                return $reply_url;
            })
            ->addColumn('status', function ($kw) {
                if ($kw->status == 'available') {
                    return '<p class="text-success"> Available </p>';
                } elseif ($kw->status == 'assigned') {
                    return '<p class="text-warning">Assigned</p>';
                } else {
                    return '<p class="text-danger">Applied</p>';
                }
            })
            ->addColumn('validity', function ($kw) {
                if ($kw->validity == '0') {
                    return 'Unlimited';
                } elseif ($kw->validity == 'month1') {
                    return 'Monthly';
                } elseif ($kw->validity == 'months2') {
                    return '2 Months';
                } elseif ($kw->validity == 'months3') {
                    return '3 Months';
                } elseif ($kw->validity == 'months6') {
                    return '6 Months';
                } elseif ($kw->validity == 'year1') {
                    return 'Yearly';
                } elseif ($kw->validity == 'year2') {
                    return '2 Years';
                } else {
                    return '3 Years';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }


    //======================================================================
    // purchaseKeyword Function Start Here
    //======================================================================
    public function purchaseKeyword($id)
    {
        $keyword = Keywords::where('status', 'available')->find($id);
        if ($keyword) {
            $payment_gateways = PaymentGateways::where('status', 'Active')->get();
            return view('client.purchase-keyword', compact('keyword', 'payment_gateways'));
        }

        return redirect('user/keywords')->with([
            'message' => 'Keyword info not found',
            'message_important' => true
        ]);
    }



    //======================================================================
    // viewKeyword Function Start Here
    //======================================================================
    public function viewKeyword($id)
    {
        $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->find($id);
        if ($keyword) {
            return view('client.view-keyword', compact('keyword'));
        }

        return redirect('user/keywords')->with([
            'message' => 'Keyword information not found',
            'message_important' => true
        ]);
    }

    //======================================================================
    // postManageKeyword Function Start Here
    //======================================================================
    public function postManageKeyword(Request $request)
    {
        $keyword_id = $request->keyword_id;

        $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->find($keyword_id);

        if (!$keyword) {
            return redirect('user/keywords')->with([
                'message' => 'Keyword information not found',
                'message_important' => true
            ]);
        }

        if ($request->reply_text == '' && $request->reply_voice == '' && $request->reply_mms == '') {
            return redirect('user/keywords/view/' . $keyword_id)->with([
                'message' => 'Reply message required',
                'message_important' => true
            ]);
        }

        $image = $request->reply_mms;

        if ($image != '') {
            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name = $image->getClientOriginalName();
                    $image_name = str_replace(" ", "-", $image_name);
                    Input::file('reply_mms')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('user/keywords/view/' . $keyword_id)->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('user/keywords/view/' . $keyword_id)->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = $keyword->reply_mms;
        }

        $keyword = Keywords::where('id', $keyword_id)->update([
            'reply_text' => $request->reply_text,
            'reply_voice' => $request->reply_voice,
            'reply_mms' => $media_url
        ]);

        if ($keyword) {
            return redirect('user/keywords')->with([
                'message' => 'Keyword updated successfully'
            ]);
        }

        return redirect('user/keywords/view/' . $keyword_id)->with([
            'message' => language_data('Something went wrong please try again'),
            'message_important' => true
        ]);
    }

    //======================================================================
    // removeKeywordMMSFile Function Start Here
    //======================================================================
    public function removeKeywordMMSFile($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/keywords')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $keyword_id = explode('_', $id);
        if (isset($keyword_id) && is_array($keyword_id) && array_key_exists('1', $keyword_id)) {
            $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->find($keyword_id['1']);

            if ($keyword) {
                $keyword->reply_mms = null;
                $keyword->save();

                return redirect('user/keywords')->with([
                    'message' => 'MMS file remove successfully'
                ]);
            }

            return redirect('user/keywords')->with([
                'message' => 'Keyword information not found',
                'message_important' => true
            ]);
        }

        return redirect('user/keywords')->with([
            'message' => 'Invalid request',
            'message_important' => true
        ]);
    }




    //======================================================================
    // campaignReports Function Start Here
    //======================================================================
    public function campaignReports()
    {
        return view('client.campaign-reports');
    }

    //======================================================================
    // getCampaignReports Function Start Here
    //======================================================================
    public function getCampaignReports(Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by = $request->get('order')[0]['dir'];

            if ($get_search_column == 'campaign_id') {
                $get_search_column = 'updated_at';
            }

        } else {
            $get_search_column = 'updated_at';
            $short_by = 'DESC';
        }

        $campaigns = Campaigns::orderBy($get_search_column, $short_by)->where('user_id', Auth::guard('client')->user()->id);
        return Datatables::of($campaigns)
            ->addColumn('action', function ($campaign) {
                return '
                <a class="btn btn-success btn-xs" href="' . url("user/sms/manage-campaign/$campaign->id") . '" ><i class="fa fa-line-chart"></i> ' . language_data('Reports') . '</a>
                <a href="#" id="' . $campaign->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                ';
            })
            ->addColumn('id', function ($campaign) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$campaign->id'  />
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->filter(function ($query) use ($request) {

                if ($request->has('campaign_id')) {
                    $query->where('campaign_id', 'like', "%{$request->get('campaign_id')}%");
                }

                if ($request->has('sender')) {
                    $query->where('sender', 'like', "%{$request->get('sender')}%");
                }

                if ($request->has('camp_type') && $request->get('camp_type') != '0') {
                    $query->where('camp_type', $request->get('camp_type'));
                }

                if ($request->has('status')) {
                    $query->where('status', 'like', "%{$request->get('status')}%");
                }

                if ($request->has('date_from') && $request->has('date_to')) {
                    $date_from = date('Y-m-d H:i:s', strtotime($request->get('date_from')));
                    $date_to = date('Y-m-d H:i:s', strtotime($request->get('date_to')));
                    $query->whereBetween('updated_at', [$date_from, $date_to]);
                }
            })
            ->escapeColumns([])
            ->make(true);

    }


    //======================================================================
    // manageCampaign Function Start Here
    //======================================================================
    public function manageCampaign($id)
    {

        $campaign = Campaigns::where('user_id', Auth::guard('client')->user()->id)->find($id);

        if ($campaign) {


            if ($campaign->camp_type == 'regular') {
                $queued = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->where('status', 'queued')->count();
            } else {
                $queued = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->where('status', 'scheduled')->count();
            }

            $keyword = Keywords::where('user_id', Auth::guard('client')->user()->id)->get();
            $selected_keywords = explode('|', $campaign->keyword);

            $campaign_chart = app()->chartjs
                ->name('campaignChart')
                ->type('pie')
                ->size(['width' => 400, 'height' => 200])
                ->labels(['Delivered', 'Failed', 'Queued'])
                ->datasets([
                    [
                        'backgroundColor' => ['#5BC0DE', '#D9534F', '#30DDBC'],
                        'hoverBackgroundColor' => ['#5BC0DE', '#D9534F', '#30DDBC'],
                        'data' => [$campaign->total_delivered, $campaign->total_failed, $queued]
                    ]
                ])
                ->options([
                    'legend' => ['display' => true]
                ]);


            return view('client.manage-campaign-reports', compact('campaign', 'campaign_chart', 'queued', 'keyword', 'selected_keywords'));
        }

        return redirect('user/sms/campaign-reports')->with([
            'message' => 'Campaign info not found',
            'message_important' => true
        ]);

    }


    //======================================================================
    // getCampaignRecipients Function Start Here
    //======================================================================
    public function getCampaignRecipients($id, Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by = $request->get('order')[0]['dir'];

            if ($get_search_column == 'number') {
                $get_search_column = 'updated_at';
            }

        } else {
            $get_search_column = 'updated_at';
            $short_by = 'DESC';
        }

        $campaign_list = CampaignSubscriptionList::where('campaign_id', $id)->orderBy($get_search_column, $short_by);
        return Datatables::of($campaign_list)
            ->addColumn('action', function ($campaign) {
                $url = '';

                if ($campaign->submitted_time != null && $campaign->status == 'scheduled' && new \DateTime() < new \DateTime($campaign->submitted_time)) {
                    $url .= '
                <a class="btn btn-success btn-xs" href="' . url("user/sms/manage-update-schedule-sms/$campaign->id") . '" ><i class="fa fa-clock-o"></i> ' . language_data('Manage') . '</a>
                ';
                }

                $url .= '
                <a href="#" id="' . $campaign->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                ';
                return $url;

            })
            ->addColumn('id', function ($campaign) {
                return "<div class='coder-checkbox'>
                             <input type='checkbox'  class='deleteRow' value='$campaign->id'  />
                                            <span class='co-check-ui'></span>
                                        </div>";

            })
            ->escapeColumns([])
            ->make(true);

    }

    //======================================================================
    // postUpdateCampaign Function Start Here
    //======================================================================
    public function postUpdateCampaign(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $campaign_id = $request->campaign_id;
        $campaign = Campaigns::find($campaign_id);

        if ($campaign) {
            if ($campaign->status == 'Delivered') {
                return redirect('user/sms/campaign-reports')->with([
                    'message' => 'Campaign already delivered',
                    'message_important' => true
                ]);
            }

            $v = \Validator::make($request->all(), [
                'status' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('user/sms/manage-campaign/' . $campaign_id)->withErrors($v->errors());
            }


            $gateway = SMSGateways::find($campaign->use_gateway);
            if ($gateway->status != 'Active') {
                return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                    'message' => language_data('SMS gateway not active'),
                    'message_important' => true
                ]);
            }


            $keywords = $request->keyword;

            if ($keywords) {
                if ($gateway->two_way != 'Yes') {
                    return redirect('user/sms/manage-campaign/' . $campaign_id)->with([
                        'message' => 'SMS Gateway not supported Two way or Receiving feature',
                        'message_important' => true
                    ]);
                }

                if (isset($keywords) && is_array($keywords)) {
                    $keywords = implode("|", $keywords);
                } else {
                    return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => 'Invalid keyword selection',
                        'message_important' => true
                    ]);
                }
            }

            $media_url = $campaign->media_url;

            if ($campaign->sms_type == 'mms') {

                if ($gateway->mms != 'Yes') {
                    return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway not supported MMS feature'),
                        'message_important' => true
                    ]);
                }

                $image = $request->image;

                if ($image) {

                    if ($image == '') {
                        return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                            'message' => language_data('MMS file required'),
                            'message_important' => true
                        ]);
                    }

                    if (app_config('AppStage') != 'Demo') {
                        if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                            $destinationPath = public_path() . '/assets/mms_file/';
                            $image_name = $image->getClientOriginalName();
                            $image_name = str_replace(" ", "-", $image_name);
                            Input::file('image')->move($destinationPath, $image_name);
                            $media_url = asset('assets/mms_file/' . $image_name);

                        } else {
                            return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                                'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                                'message_important' => true
                            ]);
                        }

                    } else {
                        return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                            'message' => language_data('MMS is disable in demo mode'),
                            'message_important' => true
                        ]);
                    }
                }
            }


            $schedule_time = $campaign->run_at;

            if ($campaign->camp_type == 'scheduled') {

                if ($request->schedule_time == '') {
                    return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => language_data('Schedule time required'),
                        'message_important' => true
                    ]);
                }

                $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));

                if (new \DateTime() > new \DateTime($schedule_time)) {
                    return redirect('user/sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => 'Select a valid time',
                        'message_important' => true
                    ]);
                }

                CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->update([
                    'submitted_time' => $schedule_time
                ]);

                $status = $request->status;
            } else {
                $status = $campaign->status;
            }

            $campaign->status = $status;
            $campaign->media_url = $media_url;
            $campaign->keyword = $keywords;
            $campaign->run_at = $schedule_time;

            $campaign->save();

            return redirect('user/sms/manage-campaign/' . $campaign_id)->with([
                'message' => 'Campaign updated successfully'
            ]);
        }

        return redirect('user/sms/campaign-reports')->with([
            'message' => 'Campaign info not found',
            'message_important' => true
        ]);

    }

    //======================================================================
    // deleteBulkCampaignRecipients Function Start Here
    //======================================================================
    public function deleteBulkCampaignRecipients(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));
            $recipients = count($all_ids);
            if ($request->has('campaign_id')) {


                $campaign = Campaigns::where('user_id', Auth::guard('client')->user()->id)->find($request->campaign_id);

                if (!$campaign) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Campaign info not found'
                    ]);
                }

                $msg_type = $campaign->sms_type;

                if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {

                    $cost = 0;
                    foreach ($all_ids as $id) {
                        $recipient = CampaignSubscriptionList::find($id);
                        if ($recipient->status == 'queued') {

                            $phone = $recipient->number;
                            $c_phone = PhoneNumber::get_code($phone);

                            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                            if ($sms_cost) {
                                $phoneUtil = PhoneNumberUtil::getInstance();
                                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                                $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                                if ($area_code_exist) {
                                    $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                                    $get_format_data = explode(" ", $format);
                                    $operator_settings = explode('-', $get_format_data[1])[0];

                                } else {
                                    $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                                }

                                $cost += $sms_charge * $recipient->amount;
                            }
                        }

                        $recipient->delete();

                    }


                    $client = Client::find(Auth::guard('client')->user()->id);
                    $client->sms_limit += $cost;
                    $client->save();

                    $campaign->total_recipient -= $recipients;
                    $campaign->save();

                    return response()->json([
                        'status' => 'success',
                        'message' => language_data('Contact deleted successfully')
                    ]);

                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => language_data('Recipients required')
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Campaign info not found'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => language_data('Invalid request')
            ]);
        }
    }

    //======================================================================
    // deleteCampaignRecipient Function Start Here
    //======================================================================
    public function deleteCampaignRecipient($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $recipient = CampaignSubscriptionList::find($id);
        if ($recipient) {
            $campaign = Campaigns::where('campaign_id', $recipient->campaign_id)->where('user_id', Auth::guard('client')->user()->id)->first();
            if ($campaign) {

                $msg_type = $campaign->sms_type;

                if ($recipient->status == 'queued') {

                    $phone = $recipient->number;
                    $c_phone = PhoneNumber::get_code($phone);

                    $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                    if ($sms_cost) {
                        $phoneUtil = PhoneNumberUtil::getInstance();
                        $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                        $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                        if ($area_code_exist) {
                            $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                            $get_format_data = explode(" ", $format);
                            $operator_settings = explode('-', $get_format_data[1])[0];

                        } else {
                            $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                        }

                        $cost = $sms_charge * $recipient->amount;

                        $client = Client::find(Auth::guard('client')->user()->id);
                        $client->sms_limit += $cost;
                        $client->save();
                    }
                }


                $campaign->total_recipient -= 1;
                $campaign->save();

                $recipient->delete();
                return redirect('user/sms/manage-campaign/' . $campaign->id)->with([
                    'message' => 'Recipient deleted successfully'
                ]);
            }
            return redirect('user/sms/campaign-reports')->with([
                'message' => 'Campaign info not found',
                'message_important' => true
            ]);
        }
        return redirect('user/sms/campaign-reports')->with([
            'message' => 'Recipient info not found',
            'message_important' => true
        ]);
    }

    //======================================================================
    // deleteBulkCampaign Function Start Here
    //======================================================================
    public function deleteBulkCampaign(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (is_array($all_ids) && count($all_ids) > 0) {
                foreach ($all_ids as $id) {
                    $campaign = Campaigns::where('user_id', Auth::guard('client')->user()->id)->find($id);
                    if ($campaign) {

                        $msg_type = $campaign->sms_type;

                        $recipients = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->get();

                        foreach ($recipients as $recipient) {

                            if ($recipient->status == 'queued') {

                                $phone = $recipient->number;
                                $c_phone = PhoneNumber::get_code($phone);

                                $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                                if ($sms_cost) {
                                    $phoneUtil = PhoneNumberUtil::getInstance();
                                    $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                                    $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                                    if ($area_code_exist) {
                                        $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                                        $get_format_data = explode(" ", $format);
                                        $operator_settings = explode('-', $get_format_data[1])[0];

                                    } else {
                                        $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                                    }

                                    $cost = $sms_charge * $recipient->amount;

                                    $client = Client::find(Auth::guard('client')->user()->id);
                                    $client->sms_limit += $cost;
                                    $client->save();
                                }
                            }
                            $recipient->delete();
                        }

                        $campaign->delete();
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'msg' => 'Campaign deleted successfully'
                ]);

            }
            return response()->json([
                'status' => 'error',
                'msg' => 'Campaign id not found'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'msg' => 'Invalid request'
        ]);
    }


    //======================================================================
    // deleteCampaign Function Start Here
    //======================================================================
    public function deleteCampaign($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $campaign = Campaigns::where('user_id', Auth::guard('client')->user()->id)->find($id);
        if ($campaign) {

            $msg_type = $campaign->sms_type;

            $recipients = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->get();

            foreach ($recipients as $recipient) {

                if ($recipient->status == 'queued') {

                    $phone = $recipient->number;
                    $c_phone = PhoneNumber::get_code($phone);

                    $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
                    if ($sms_cost) {
                        $phoneUtil = PhoneNumberUtil::getInstance();
                        $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                        $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                        if ($area_code_exist) {
                            $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                            $get_format_data = explode(" ", $format);
                            $operator_settings = explode('-', $get_format_data[1])[0];

                        } else {
                            $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
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
                        }

                        $cost = $sms_charge * $recipient->amount;

                        $client = Client::find(Auth::guard('client')->user()->id);
                        $client->sms_limit += $cost;
                        $client->save();
                    }
                }
                $recipient->delete();
            }

            $campaign->delete();

            return redirect('user/sms/campaign-reports')->with([
                'message' => 'Campaign deleted successfully'
            ]);

        }

        return redirect('user/sms/campaign-reports')->with([
            'message' => 'Campaign info not found',
            'message_important' => true
        ]);
    }

    //======================================================================
    // getCoverage Function Start Here
    //======================================================================
    public function getCoverage()
    {
        $country_codes = IntCountryCodes::where('active', '1')->get();
        return view('client.coverage', compact('country_codes'));
    }

    public function getAccount()
    {
        $country_codes = IntCountryCodes::where('active', '1')->get();
        return view('client.account', compact('country_codes'));
    }

    public function getDomains()
    {
        $country_codes = IntCountryCodes::where('active', '1')->get();
        return view('client.domains', compact('country_codes'));
    }

    public function payments()
    {
        $country_codes = IntCountryCodes::where('active', '1')->get();
        return view('client.payments', compact('country_codes'));
    }

    //======================================================================
    // viewOperator Function Start Here
    //======================================================================
    public function viewOperator($id)
    {

        $coverage = IntCountryCodes::find($id);
        if ($coverage) {
            $operators = Operator::where('coverage_id', $id)->get();
            return view('client.view-operator', compact('operators'));
        } else {
            return redirect('user/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }
    }




    //======================================================================
    // Chat-box
    //======================================================================

    //======================================================================
    // chatBox Function Start Here
    //======================================================================
    public function chatBox(Request $request)
    {
        $get_data = SMSHistory::where('userid', Auth::guard('client')->user()->id)->where('send_by', 'receiver')->orderBy('updated_at', 'DESC');
        $sms_history = $get_data->paginate(15);
        $sms_count = $get_data->count();

        if ($request->ajax()) {
            if ($sms_history->count() > 0) {
                $view = view('client.get-chat-box', compact('sms_history'))->render();
            } else {
                $view = null;
            }
            return response()->json(['html' => $view]);
        }

        return view('client.chat-box', compact('sms_history', 'sms_count'));
    }

    //======================================================================
    // viewChatReports Function Start Here
    //======================================================================
    public function viewChatReports(Request $request)
    {

        $id = $request->id;
        $inbox_info = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($id);
        if ($inbox_info) {

            $sms_inbox = SMSInbox::where('msg_id', $id)->get();

            SMSInbox::where('msg_id', $id)->update([
                'mark_read' => 'yes'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $sms_inbox,
                'sms_id' => $id
            ]);

        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Reports not found'
            ]);
        }


    }
    //======================================================================
    // addToBlacklist Function Start Here
    //======================================================================
    public function addToBlacklist(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('This Option is Disable In Demo Mode'),
            ]);
        }


        $inbox_info = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($request->sms_id);

        if ($inbox_info) {

            $phone = $inbox_info->receiver;

            $blacklist = BlackListContact::where('numbers', $phone)->where('user_id', Auth::guard('client')->user()->id)->first();

            if ($blacklist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Number already in blacklist'
                ]);

            } else {

                $status = BlackListContact::create([
                    'user_id' => Auth::guard('client')->user()->id,
                    'numbers' => $phone
                ]);

                if ($status) {
                    ContactList::where('phone_number', $phone)->delete();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Number added to blacklist'
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'something went wrong. Please try again'
                ]);

            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'SMS info not found'
            ]);
        }

    }

    //======================================================================
    // replySEOSMS Function Start Here
    //======================================================================
    public function replyChatSMS(Request $request)
    {
        $cmd = $request->sms_id;
        $message = $request->message;

        if ($message == '') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('Insert your message')
            ]);
        }

        $h = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($cmd);

        if ($h) {
            $gateway = SMSGateways::find($h->use_gateway);

            if ($gateway->status != 'Active') {

                return response()->json([
                    'status' => 'error',
                    'message' => language_data('SMS gateway not active.Contact with Provider')
                ]);
            }

            $gateway_credential = null;
            $cg_info = null;
            if ($gateway->custom == 'Yes') {
                if ($gateway->type == 'smpp') {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                    if ($gateway_credential == null) {
                        return response()->json([
                            'status' => 'error',
                            'message' => language_data('SMS Gateway credential not found')
                        ]);
                    }
                } else {
                    $cg_info = CustomSMSGateways::where('gateway_id', $gateway->id)->first();
                }

            } else {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return response()->json([
                        'status' => 'error',
                        'message' => language_data('SMS Gateway credential not found')
                    ]);
                }
            }

            $blacklist = BlackListContact::where('numbers', $h->sender)->where('user_id', Auth::guard('client')->user()->id)->first();

            if ($blacklist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Number contain in blacklist'
                ]);
            }

            $phone = $h->receiver;
            $msg_type = $h->sms_type;

            if ($msg_type == 'plain') {
                $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));
                if ($msgcount <= 160) {
                    $msgcount = 1;
                } else {
                    $msgcount = $msgcount / 157;
                }
            }
            if ($msg_type == 'unicode' || $msg_type == 'arabic') {
                $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

                if ($msgcount <= 70) {
                    $msgcount = 1;
                } else {
                    $msgcount = $msgcount / 67;
                }
            }

            $msgcount = ceil($msgcount);

            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
            if ($sms_cost) {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
                $area_code_exist = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

                if ($area_code_exist) {
                    $format = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
                    $get_format_data = explode(" ", $format);
                    $operator_settings = explode('-', $get_format_data[1])[0];

                } else {
                    $carrierMapper = PhoneNumberToCarrierMapper::getInstance();
                    $operator_settings = $carrierMapper->getNameForNumber($phoneNumberObject, 'en');
                }

                $get_operator = Operator::where('operator_setting', $operator_settings)->where('coverage_id', $sms_cost->id)->first();
                if ($get_operator) {

                    $sms_charge = $get_operator->plain_price;

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $sms_charge = $get_operator->plain_price;
                    }

                } else {
                    $sms_charge = $sms_cost->plain_tariff;

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $sms_charge = $sms_cost->plain_tariff;
                    }
                }

                $client = Client::find(Auth::guard('client')->user()->id);

                $cost = $sms_charge * $msgcount;


                if ($cost == 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                    ]);
                }

                if ($cost > $client->sms_limit) {
                    return response()->json([
                        'status' => 'error',
                        'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                    ]);
                }


                $client->sms_limit -= $cost;
                $client->save();

                if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                    $this->dispatch(new SendBulkSMS($client->id, $phone, $gateway, $gateway_credential, $h->sender, $message, $msgcount, $cg_info, '', $msg_type));
                }
                return response()->json([
                    'status' => 'success',
                    'message' => language_data('Successfully sent reply'),
                    'data' => $message
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Coverage not active',
            ]);

        }
        return response()->json([
            'status' => 'error',
            'message' => language_data('SMS Not Found')
        ]);


    }

    //======================================================================
    // removeChatHistory Function Start Here
    //======================================================================
    public function removeChatHistory(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('This Option is Disable In Demo Mode'),
            ]);
        }

        $sms_history = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($request->sms_id);
        if ($sms_history) {
            SMSInbox::where('msg_id', $sms_history->id)->delete();
            $sms_history->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'History remove successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'SMS info not found'
            ]);
        }

    }

}
