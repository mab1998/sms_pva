<?php

namespace App\Http\Controllers;

use App\Classes\PhoneNumber;
use App\Client;
use App\ClientGroups;
use App\CustomSMSGateways;
use App\EmailTemplates;
use App\IntCountryCodes;
use App\Invoices;
use App\Jobs\SendBulkSMS;
use App\Mail\UserRegistration;
use App\Operator;
use App\ScheduleSMS;
use App\SenderIdManage;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSTransaction;
use App\SupportTickets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }


    //======================================================================
    // allClients Function Start Here
    //======================================================================
    public function allUsers()
    {
        return view('client.all-clients');
    }

    //======================================================================
    // addClient Function Start Here
    //======================================================================
    public function addUser()
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $clientGroups = ClientGroups::where('status', 'Yes')->where('created_by', Auth::guard('client')->user()->id)->get();
        $gateways     = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();

        return view('client.add-user', compact('clientGroups', 'sms_gateways'));
    }

    //======================================================================
    // addClientPost Function Start Here
    //======================================================================
    public function addUserPost(Request $request)
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'password' => 'required', 'cpassword' => 'required', 'phone' => 'required', 'country' => 'required', 'client_group' => 'required', 'sms_limit' => 'required|numeric|min:0|max:1000000000', 'email' => 'required', 'sms_gateway' => 'required', 'image' => 'image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('user/add')->withInput($request->all())->withErrors($v->errors());
        }

        $sms_gateways = $request->sms_gateway;

        if (isset($sms_gateways) && is_array($sms_gateways) && count(array_filter($sms_gateways)) <= 0) {
            return redirect('user/add')->with([
                'message' => language_data('At least select one sms gateway', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $sms_gateways_id = json_encode($sms_gateways, true);


        $parent = Client::find(Auth::guard('client')->user()->id);

        $exist_user_name  = Client::where('username', $request->user_name)->first();
        $exist_user_email = Client::where('email', $request->email)->first();

        if ($exist_user_name) {
            return redirect('user/add')->withInput($request->all())->with([
                'message' => language_data('User Name already exist', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($exist_user_email) {
            return redirect('user/add')->withInput($request->all())->with([
                'message' => language_data('Email already exist', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $password  = $request->password;
        $cpassword = $request->cpassword;

        if ($password !== $cpassword) {
            return redirect('user/add')->withInput($request->all())->with([
                'message' => language_data('Both password does not match', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        } else {
            $password = bcrypt($password);
        }


        if ($request->sms_limit != '') {
            if ($parent->sms_limit < $request->sms_limit) {
                return redirect('user/add')->withInput($request->all())->with([
                    'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
            if ($request->sms_limit < 0) {
                return redirect('user/add')->withInput($request->all())->with([
                    'message' => 'Insert valid sms balance',
                    'message_important' => true
                ]);
            }
        }

        $image = $request->image;
        if ($image != '' && app_config('AppStage') != 'Demo') {
            if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                $destinationPath = public_path() . '/assets/client_pic/';
                $image_name      = $image->getClientOriginalName();
                Input::file('image')->move($destinationPath, $image_name);
            } else {
                return redirect('user/add')->withInput($request->all())->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

        } else {
            $image_name = 'profile.jpg';
        }

        $email_notify = $request->email_notify;
        if ($email_notify == 'yes') {
            $email_notify = 'Yes';
        } else {
            $email_notify = 'No';
        }

        $api_access = $request->api_access;
        if ($api_access == '') {
            $api_access = 'No';
        }

        $email = $request->email;

        $api_key_generate    = $request->user_name . ':' . $cpassword;
        $client              = new Client();
        $client->groupid     = $request->client_group;
        $client->parent      = $parent->id;
        $client->fname       = $request->first_name;
        $client->lname       = $request->last_name;
        $client->company     = $request->company;
        $client->website     = $request->website;
        $client->email       = $email;
        $client->username    = $request->user_name;
        $client->password    = $password;
        $client->address1    = $request->address;
        $client->address2    = $request->more_address;
        $client->state       = $request->state;
        $client->city        = $request->city;
        $client->postcode    = $request->postcode;
        $client->country     = $request->country;
        $client->phone       = $request->phone;
        $client->image       = $image_name;
        $client->datecreated = date('Y-m-d');
        $client->sms_limit   = $request->sms_limit;
        $client->api_access  = $api_access;
        $client->api_key     = base64_encode($api_key_generate);
        $client->status      = 'Active';
        $client->reseller    = $request->reseller_panel;
        $client->sms_gateway = $sms_gateways_id;
        $client->api_gateway = $sms_gateways[0];
        $client->emailnotify = $email_notify;
        $client->save();
        $client_id = $client->id;

        $parent->sms_limit -= $request->sms_limit;
        $parent->save();

        if (is_int($client_id)) {
            //Add SMS transaction reports
            SMSTransaction::create([
                'cl_id' => $client_id,
                'amount' => $request->sms_limit
            ]);

            /*For Email Confirmation*/
            if ($email_notify == 'Yes' && $email != '') {
                $name = $request->first_name . ' ' . $request->last_name;

                try {
                    \Mail::to($email)->send(new UserRegistration($name, $request->user_name, $cpassword));
                    return redirect('user/all')->with([
                        'message' => language_data('Client Added Successfully', Auth::guard('client')->user()->lan_id)
                    ]);
                } catch (\Exception $ex) {
                    return redirect('user/all')->with([
                        'message' => $ex->getMessage()
                    ]);
                }


            }

        }

        return redirect('user/all')->with([
            'message' => language_data('Client Added Successfully', Auth::guard('client')->user()->lan_id)
        ]);

    }


    //======================================================================
    // viewClient Function Start Here
    //======================================================================
    public function viewUser($id)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $client = Client::where('parent', Auth::guard('client')->user()->id)->find($id);
        if ($client) {
            $invoices        = Invoices::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $tickets         = SupportTickets::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $sms_transaction = SMSTransaction::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $clientGroups    = ClientGroups::where('status', 'Yes')->where('created_by', Auth::guard('client')->user()->id)->get();

            $gateways          = json_decode(Auth::guard('client')->user()->sms_gateway);
            $sms_gateways      = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();
            $selected_gateways = json_decode($client->sms_gateway);


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

            return view('client.user-manage', compact('client', 'invoices', 'tickets', 'sms_gateways', 'sms_transaction', 'clientGroups', 'selected_gateways','sender_ids'));
        } else {
            return redirect('user/all')->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // updateLimit Function Start Here
    //======================================================================
    public function updateLimit(Request $request)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $cmd = Input::get('cmd');
        $v   = \Validator::make($request->all(), [
            'sms_amount' => 'required|numeric|max:1000000000'
        ]);

        if ($v->fails()) {
            return redirect('user/view/' . $cmd)->withErrors($v->errors());
        }

        $parent = Client::find(Auth::guard('client')->user()->id);

        $client = Client::where('parent', Auth::guard('client')->user()->id)->find($cmd);
        if ($client && $parent) {

            $client_balance = $client->sms_limit;

            if ($client_balance == 0 && $request->sms_amount  < 0){
                return redirect('user/view/' . $cmd)->with([
                    'message' => 'Insert positive or valid sms balance',
                    'message_important' => true
                ]);
            }

            $remaining_balance = $client_balance + $request->sms_amount;

            if ($request->sms_amount > $parent->sms_limit || $remaining_balance < 0) {
                return redirect('user/view/' . $cmd)->with([
                    'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            SMSTransaction::create([
                'cl_id' => $cmd,
                'amount' => $request->sms_amount
            ]);

            $client->sms_limit += $request->sms_amount;
            $client->save();

            $parent->sms_limit -= $request->sms_amount;
            $parent->save();

            return redirect('user/view/' . $cmd)->with([
                'message' => language_data('Limit updated successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('user/all')->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // updateImage Function Start Here
    //======================================================================
    public function updateImage(Request $request)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $cmd      = Input::get('cmd');
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/view/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'client_image' => 'required |image| mimes:jpeg,jpg,png,gif',
        ]);

        if ($v->fails()) {
            return redirect('user/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::where('parent', Auth::guard('client')->user()->id)->find($cmd);
        if ($client) {
            $image = $request->client_image;
            if ($image != '') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {

                    $destinationPath = public_path() . '/assets/client_pic/';
                    $image_name      = $image->getClientOriginalName();
                    Input::file('client_image')->move($destinationPath, $image_name);

                    $client->image = $image_name;
                    $client->save();

                    return redirect('user/view/' . $cmd)->with([
                        'message' => language_data('Image updated successfully', Auth::guard('client')->user()->lan_id)
                    ]);
                } else {
                    return redirect('user/view/' . $cmd)->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif file', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('user/view/' . $cmd)->with([
                    'message' => language_data('Please try again', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('user/all')->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // updateClient Function Start Here
    //======================================================================
    public function updateUser(Request $request)
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $cmd      = Input::get('cmd');
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/view/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'phone' => 'required', 'country' => 'required', 'client_group' => 'required', 'status' => 'required', 'email' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/view/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }

        $sms_gateways = $request->sms_gateway;

        if (isset($sms_gateways) && is_array($sms_gateways) && count(array_filter($sms_gateways)) <= 0) {
            return redirect('user/view/' . $cmd)->with([
                'message' => language_data('At least select one sms gateway', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $sms_gateways_id = json_encode($sms_gateways, true);

        $client = Client::where('parent', Auth::guard('client')->user()->id)->find($cmd);
        if ($client) {
            if ($client->username != $request->user_name) {
                $exist_user_name = Client::where('username', $request->user_name)->first();
                if ($exist_user_name) {
                    return redirect('user/view/' . $cmd)->with([
                        'message' => language_data('User Name already exist', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }

            if ($client->email != $request->email) {

                $exist_user_email = Client::where('email', $request->email)->first();
                if ($exist_user_email) {
                    return redirect('user/view/' . $cmd)->with([
                        'message' => language_data('Email already exist', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }
            }

            $password = $request->password;

            if ($password != '') {
                $password = bcrypt($password);
            } else {
                $password = $client->password;
            }

            $api_access = $request->api_access;
            if ($request->api_access == '') {
                $api_access = 'No';
            }

            $client->groupid     = $request->client_group;
            $client->fname       = $request->first_name;
            $client->lname       = $request->last_name;
            $client->company     = $request->company;
            $client->website     = $request->website;
            $client->email       = $request->email;
            $client->username    = $request->user_name;
            $client->password    = $password;
            $client->address1    = $request->address;
            $client->address2    = $request->more_address;
            $client->state       = $request->state;
            $client->city        = $request->city;
            $client->postcode    = $request->postcode;
            $client->country     = $request->country;
            $client->phone       = $request->phone;
            $client->api_access  = $api_access;
            $client->status      = $request->status;
            $client->reseller    = $request->reseller_panel;
            $client->sms_gateway = $sms_gateways_id;
            $client->save();

            return redirect('user/view/' . $cmd)->with([
                'message' => language_data('Client updated successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('user/all')->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // sendSMS Function Start Here
    //======================================================================
    public function sendSMS(Request $request)
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'message' => 'required', 'sms_gateway' => 'required', 'message_type' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/view/' . $cmd)->withErrors($v->errors());
        }

        $account = Client::find(Auth::guard('client')->user()->id);

        $client = Client::where('parent', Auth::guard('client')->user()->id)->find($cmd);
        if ($client == '') {
            return redirect('user/view/' . $cmd)->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/view/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }

        $gateway_credential = null;
        $cg_info = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('user/view/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            } else {
                $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
            }

        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect('user/view/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway credential not found'),
                    'message_important' => true
                ]);
            }
        }


        $message   = $request->message;
        $sender_id = $request->sender_id;

        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($message)));

        $msg_type = $request->message_type;

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'arabic') {
            return redirect('user/view/' . $cmd)->with([
                'message' => 'Invalid message type',
                'message_important' => true
            ]);
        }

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


        $phone   = str_replace(['(', ')', '+', '-', ' '], '', $client->phone);
        $c_phone = PhoneNumber::get_code($phone);

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
            } else {
                $sms_charge = $sms_cost->plain_tariff;
            }

            if ($sms_charge == 0) {
                return redirect('user/view/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            if ($sms_charge > $account->sms_limit) {
                return redirect('user/view/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('user/view/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('Phone Number Coverage are not active', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        if ($sender_id != '') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/view/' . $cmd)->with([
                    'message' => language_data('This Sender ID have Blocked By Administrator', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $this->dispatch(new SendBulkSMS(Auth::guard('client')->user()->id, $client->phone, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info, '', $msg_type));

        $remain_sms        = $account->sms_limit - $sms_charge;
        $account->sms_limit = $remain_sms;
        $account->save();

        return redirect('user/view/' . $cmd)->with([
            'message' => language_data('Please check sms history', Auth::guard('client')->user()->lan_id)
        ]);
    }




    //======================================================================
    // exportImport Function Start Here
    //======================================================================
    public function exportImport()
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $client_groups = ClientGroups::where('status', 'Yes')->where('created_by', Auth::guard('client')->user()->id)->get();

        $gateways     = json_decode(Auth::guard('client')->user()->sms_gateway);
        $sms_gateways = SMSGateways::whereIn('id', $gateways)->where('status', 'Active')->get();

        return view('client.export-n-import', compact('client_groups', 'sms_gateways'));
    }


    //======================================================================
    // exportUsers Function Start Here
    //======================================================================
    public function exportUsers()
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        Excel::create('Clients', function ($excel) {
            $excel->sheet('Sheetname', function ($sheet) {
                $sheet->fromModel(Client::where('parent', Auth::guard('client')->user()->id)->get());
            });

        })->export('csv');
    }

    //======================================================================
    // downloadSampleCSV Function Start Here
    //======================================================================
    public function downloadSampleCSV()
    {
        return response()->download('assets/test_file/test_file.csv');
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    public function checkUsername($array, $key, $client_group = '0', $sms_gateway = '1')
    {
        $client_data = [];
        $i           = 0;
        $key_array   = [];
        $keep_ids    = [];
        $final_data  = [];
        $parent      = Auth::guard('client')->user()->id;
        $api_access  = client_info($parent)->api_access;

        foreach ($array as $k => $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i]   = $val[$key];
                $client_data[$i] = $val;
                if (!array_key_exists('id', $client_data[$k])) {
                    $client_data[$k]['password'] = bcrypt($val['password']);
                    $client_data[$k]['groupid']  = $client_group;
                    $client_data[$k]['parent']   = $parent;
                    if ($api_access == 'No') {
                        $client_data[$k]['api_access'] = $api_access;
                    }
                    $client_data[$k]['sms_gateway'] = $sms_gateway;
                    $client_data[$k]['created_at']  = Carbon::now(app_config('Timezone'));
                    $client_data[$k]['updated_at']  = Carbon::now(app_config('Timezone'));
                    array_push($keep_ids, $k);
                }
            }
            $i++;
        }
        foreach ($keep_ids as $v) {
            if (array_key_exists($v, $client_data)) {
                array_push($final_data, $client_data[$v]);
            }
        }
        return $final_data;
    }

    /**
     * @param $array
     * @return mixed
     */
    public function prepareForInsert($array)
    {
        foreach ($array as $k => $v) {
            if (!array_key_exists('id', $v)) {
                $array[$k] ['fname']    = $array[$k] ['first_name'];
                $array[$k] ['lname']    = $array[$k] ['last_name'];
                $array[$k] ['address1'] = $array[$k] ['address'];
                $array[$k] ['address2'] = $array[$k] ['more_address'];
                $array[$k] ['postcode'] = $array[$k] ['zip_code'];
                unset($array[$k]['first_name']);
                unset($array[$k]['last_name']);
                unset($array[$k]['address']);
                unset($array[$k]['more_address']);
                unset($array[$k]['zip_code']);
            }
        }
        $array = array_map('array_filter', $array);
        return $array;
    }



    //======================================================================
    // addNewUserCSV Function Start Here
    //======================================================================
    public function addNewUserCSV(Request $request)
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/export-n-import')->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_client' => 'required', 'client_group' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/export-n-import')->withErrors($v->errors());
        }

        $sms_gateways = $request->sms_gateway;

        if (isset($sms_gateways) && is_array($sms_gateways) && count(array_filter($sms_gateways)) <= 0) {
            return redirect('user/export-n-import')->with([
                'message' => language_data('At least select one sms gateway', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $file_extension = Input::file('import_client')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('user/export-n-import')->with([
                'message' => language_data('Insert Valid Excel or CSV file', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $sms_gateways_id = json_encode($sms_gateways, true);
        $sms_gateways_id = json_encode($sms_gateways_id);

        $client_group   = $request->client_group;
        $reseller_panel = $request->reseller_panel;
        $api_access     = $request->api_access;

        $all_data = Excel::load($request->import_client)->noHeading()->all()->toArray();

        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect('user/export-n-import')->with([
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


        if (count($header) == count($header, COUNT_RECURSIVE)) {
            $all_data = array_map(function ($row) use ($header) {
                return array_combine($header, $row);
            }, $all_data);
        } else {
            return redirect('user/export-n-import')->with([
                'message' => language_data('Insert Valid Excel or CSV file', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }


        $first_name_column    = $request->first_name_column;
        $last_name_column     = $request->last_name_column;
        $company_column       = $request->company_column;
        $website_column       = $request->website_column;
        $email_address_column = $request->email_address_column;
        $user_name_column     = $request->user_name_column;
        $password_column      = $request->password_column;
        $number_column        = $request->number_column;
        $address_column       = $request->address_column;
        $more_address_column  = $request->more_address_column;
        $state_column         = $request->state_column;
        $city_column          = $request->city_column;
        $postcode_column      = $request->postcode_column;
        $country_column       = $request->country_column;
        $sms_limit_column     = $request->sms_limit_column;

        $valid_clients = [];
        $get_data      = [];

        $exist_clients = Client::select('username')->get()->toArray();

        if ($exist_clients && is_array($exist_clients) && count($exist_clients) > 0) {
            $exist_clients = array_column($exist_clients, 'username');
        }

        $sms_limit = 0;

        array_filter($all_data, function ($data) use ($first_name_column, $last_name_column, $company_column, $website_column, $email_address_column, $user_name_column, $password_column, $number_column, $address_column, $more_address_column, $state_column, $city_column, $postcode_column, $country_column, $sms_limit_column, $exist_clients, $sms_gateways_id, $client_group, $reseller_panel, $api_access, $sms_gateways, &$get_data, &$valid_clients, &$sms_limit) {

            $a = array_map('trim', array_keys($data));
            $b = array_map('trim', $data);
            $data = array_combine($a, $b);

            if ($data[$first_name_column] && $data[$email_address_column] && $data[$user_name_column] && $data[$password_column] && $data[$number_column] && $data[$country_column] && $data[$sms_limit_column] >= 0) {

                if (!in_array($data[$user_name_column], $exist_clients)) {

                    $last_name = null;
                    if ($last_name_column != '0') {
                        $last_name = $data[$last_name_column];
                    }

                    $company = null;
                    if ($company_column != '0') {
                        $company = $data[$company_column];
                    }

                    $website = null;
                    if ($website_column != '0') {
                        $website = $data[$website_column];
                    }

                    $address = null;
                    if ($address_column != '0') {
                        $address = $data[$address_column];
                    }

                    $more_address = null;
                    if ($more_address_column != '0') {
                        $more_address = $data[$more_address_column];
                    }

                    $state = null;
                    if ($state_column != '0') {
                        $state = $data[$state_column];
                    }

                    $city = null;
                    if ($city_column != '0') {
                        $city = $data[$city_column];
                    }

                    $postcode = null;
                    if ($postcode_column != '0') {
                        $postcode = $data[$postcode_column];
                    }

                    $sms_limit += $data[$sms_limit_column];

                    $api_key_generate = $data[$user_name_column] . ':' . $data[$password_column];

                    array_push($valid_clients, $data[$user_name_column]);
                    array_push($get_data, [
                        'groupid' => $client_group,
                        'parent' => Auth::guard('client')->user()->id,
                        'fname' => $data[$first_name_column],
                        'lname' => $last_name,
                        'company' => $company,
                        'website' => $website,
                        'email' => $data[$email_address_column],
                        'username' => $data[$user_name_column],
                        'password' => bcrypt($data[$password_column]),
                        'address1' => $address,
                        'address2' => $more_address,
                        'state' => $state,
                        'city' => $city,
                        'postcode' => $postcode,
                        'country' => $data[$country_column],
                        'phone' => $data[$number_column],
                        'image' => 'profile.jpg',
                        'datecreated' => date('Y-m-d'),
                        'sms_limit' => $data[$sms_limit_column],
                        'api_access' => $api_access,
                        'api_key' => base64_encode($api_key_generate),
                        'api_gateway' => $sms_gateways[0],
                        'status' => 'Active',
                        'reseller' => $reseller_panel,
                        'sms_gateway' => $sms_gateways_id,
                    ]);

                }
            }
        });


        if (isset($valid_clients) && is_array($valid_clients) && count($valid_clients) <= 0) {
            return redirect('user/export-n-import')->with([
                'message' => 'Client already exist or not found',
                'message_important' => true
            ]);
        }

        $parent = Client::find(Auth::guard('client')->user()->id);

        if ($parent->sms_limit < $sms_limit) {
            return redirect('user/export-n-import')->withInput($request->all())->with([
                'message' => language_data('You do not have enough sms balance', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $remove_duplicate_user_name = unique_multidim_array($get_data, 'username');
        $final_data                 = unique_multidim_array($remove_duplicate_user_name, 'email');

        foreach (array_chunk($final_data, 50) as $chunk_result) {
            foreach ($chunk_result as $data) {
                $client = Client::create($data);

                if ($data['sms_limit'] > 0) {
                    //Add SMS transaction reports
                    SMSTransaction::create([
                        'cl_id' => $client->id,
                        'amount' => $data['sms_limit']
                    ]);
                }
            }
        }

        $parent->sms_limit -= $sms_limit;
        $parent->save();
        return redirect('user/all')->with([
            'message' => language_data('Client imported successfully', Auth::guard('client')->user()->lan_id)
        ]);

    }
    //======================================================================
    // userGroups Function Start Here
    //======================================================================
    public function userGroups()
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $clientGroups = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->get();
        return view('client.client-groups', compact('clientGroups'));
    }

    //======================================================================
    // addNewUserGroup Function Start Here
    //======================================================================
    public function addNewUserGroup(Request $request)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'group_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('users/groups')->withErrors($v->errors());
        }

        $clientGroup             = new ClientGroups();
        $clientGroup->group_name = $request->group_name;
        $clientGroup->created_by = Auth::guard('client')->user()->id;
        $clientGroup->status     = $request->status;
        $clientGroup->save();

        return redirect('users/groups')->with([
            'message' => language_data('Client Group added successfully', Auth::guard('client')->user()->lan_id)
        ]);

    }

    //======================================================================
    // updateClientGroup Function Start Here
    //======================================================================
    public function updateUserGroup(Request $request)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $cmd = $request->cmd;

        $v = \Validator::make($request->all(), [
            'group_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('users/groups')->withErrors($v->errors());
        }

        $clientGroup = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->find($cmd);

        if ($clientGroup) {
            $clientGroup->group_name = $request->group_name;
            $clientGroup->status     = $request->status;
            $clientGroup->save();

            return redirect('users/groups')->with([
                'message' => language_data('Client Group updated successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('users/groups')->with([
                'message' => language_data('Client Group not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // exportClientGroup Function Start Here
    //======================================================================
    public function exportUserGroup($id)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        Excel::create('Clients', function ($excel) use ($id) {
            $excel->sheet('Sheetname', function ($sheet) use ($id) {
                $sheet->fromModel(Client::where('groupid', $id)->get());
            });

        })->export('csv');
    }


    //======================================================================
    // deleteUserGroup Function Start Here
    //======================================================================
    public function deleteUserGroup($id)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('users/groups')->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $clientGroup = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->find($id);

        if ($clientGroup) {
            $exist_client = Client::where('groupid', $id)->first();
            if ($exist_client) {
                return redirect('users/groups')->with([
                    'message' => language_data('This Group exist in a client', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            $clientGroup->delete();

            return redirect('users/groups')->with([
                'message' => language_data('Client group deleted successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('users/groups')->with([
                'message' => language_data('Client Group not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // deleteUser Function Start Here
    //======================================================================
    public function deleteUser($id)
    {
        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/all')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }
        //check client info
        $client = Client::find($id);
        if ($client) {

            //Check Client Group
            $client_group = ClientGroups::where('created_by', $id)->first();

            if ($client_group) {
                return redirect('user/all')->with([
                    'message' => language_data('Client contain in', Auth::guard('client')->user()->lan_id) . ' ' . language_data('Client Group', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }


            //Check Invoice
            $client_inv = Invoices::where('cl_id', $id)->first();

            if ($client_inv) {
                return redirect('user/all')->with([
                    'message' => language_data('Client contain in', Auth::guard('client')->user()->lan_id) . ' ' . language_data('Invoice', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }


            //Check SMS History
            $client_sms = SMSHistory::where('userid', $id)->first();

            if ($client_sms) {
                return redirect('user/all')->with([
                    'message' => language_data('Client contain in', Auth::guard('client')->user()->lan_id) . ' ' . language_data('SMS History', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            //Check Sender ID
            $client_sender_id = SenderIdManage::where('cl_id', $id)->first();

            if ($client_sender_id) {
                return redirect('user/all')->with([
                    'message' => language_data('Client contain in', Auth::guard('client')->user()->lan_id) . ' ' . language_data('Sender ID', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            //Check Client SMS Balance
            if ($client->sms_limit > 0) {
                return redirect('user/all')->with([
                    'message' => language_data('Client sms limit not empty', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }


            //Check Parent
            $parents = Client::where('parent', $id)->first();

            if ($parents) {
                return redirect('user/all')->with([
                    'message' => language_data('This client have some customer', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            //Check Support Tickets
            $client_tickets = SupportTickets::where('cl_id', $id)->first();

            if ($client_tickets) {
                return redirect('user/all')->with([
                    'message' => language_data('Client contain in', Auth::guard('client')->user()->lan_id) . ' ' . language_data('Support Tickets', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

            // Delete Schedule SMS N SMS Transaction
            ScheduleSMS::where('userid', $id)->delete();
            SMSTransaction::where('cl_id', $id)->delete();

            //Delete Client
            $client->delete();

            return redirect('user/all')->with([
                'message' => language_data('Client delete successfully', Auth::guard('client')->user()->lan_id)
            ]);

        } else {
            return redirect('user/all')->with([
                'message' => language_data('Client info not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

    }



    //======================================================================
    // getAllClients Function Start Here
    //======================================================================
    public function getAllClients()
    {

        if (Auth::guard('client')->user()->reseller == 'No') {
            return redirect('dashboard')->with([
                'message' => 'Invalid access',
                'message_important' => true
            ]);
        }

        $clients = Client::where('parent', Auth::guard('client')->user()->id)->orderBy('datecreated', 'desc');
        return Datatables::of($clients)
            ->addColumn('action', function ($cl) {
                return '
                <a class="btn btn-success btn-xs" href="' . url("user/view/$cl->id") . '" ><i class="fa fa-edit"></i>' . language_data('Manage', Auth::guard('client')->user()->lan_id) . '</a>
                <a href="#" id="' . $cl->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-danger"></i> ' . language_data('Delete', Auth::guard('client')->user()->lan_id) . '</a>
                ';
            })
            ->addColumn('name', function ($cl) {
                return $cl->fname . ' ' . $cl->lnamge;
            })
            ->addColumn('datecreated', function ($cl) {
                return get_date_format($cl->datecreated);
            })
            ->addColumn('status', function ($cl) {
                if ($cl->status == 'Active') {
                    return '<p class="text-success">' . language_data("Active", Auth::guard('client')->user()->lan_id) . '</p>';
                } elseif ($cl->status == 'Inactive') {
                    return '<p class="text-warning">' . language_data("Inactive", Auth::guard('client')->user()->lan_id) . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("Closed", Auth::guard('client')->user()->lan_id) . '</p>';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }

}
