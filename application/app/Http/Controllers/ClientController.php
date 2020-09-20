<?php

namespace App\Http\Controllers;

use App\BlackListContact;
use App\BlockMessage;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\Permission;
use App\Client;
use App\ClientGroups;
use App\CustomSMSGateways;
use App\ImportPhoneNumber;
use App\Invoices;
use App\Jobs\SendBulkSMS;
use App\Keywords;
use App\Mail\UserRegistration;
use App\RecurringSMS;
use App\ScheduleSMS;
use App\SenderIdManage;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use App\SMSTemplates;
use App\SMSTransaction;
use App\SupportTicketFiles;
use App\SupportTickets;
use App\SupportTicketsReplies;
use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // allClients Function Start Here
    //======================================================================
    public function allClients()
    {
        $self = 'all-clients';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        return view('admin.all-clients');
    }

    //======================================================================
    // addClient Function Start Here
    //======================================================================
    public function addClient()
    {
        $self = 'add-new-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroups = ClientGroups::where('status', 'Yes')->where('created_by', '0')->get();
        $sms_gateways = SMSGateways::where('status', 'Active')->get();

        return view('admin.add-client', compact('clientGroups', 'sms_gateways'));
    }

    //======================================================================
    // addClientPost Function Start Here
    //======================================================================
    public function addClientPost(Request $request)
    {
        $self = 'add-new-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'email' => 'required', 'password' => 'required', 'cpassword' => 'required', 'phone' => 'required', 'country' => 'required', 'api_access' => 'required', 'client_group' => 'required', 'sms_gateway' => 'required', 'sms_limit' => 'required|numeric|max:1000000000', 'reseller_panel' => 'required', 'image' => 'image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('clients/add')->withInput($request->all())->withErrors($v->errors());
        }

        $sms_gateways = $request->sms_gateway;

        if (isset($sms_gateways) && is_array($sms_gateways) && count(array_filter($sms_gateways)) <= 0) {
            return redirect('clients/add')->with([
                'message' => language_data('At least select one sms gateway'),
                'message_important' => true
            ]);
        }

        $sms_gateways_id = json_encode($sms_gateways, true);

        $exist_user_name  = Client::where('username', $request->user_name)->first();
        $exist_user_email = Client::where('email', $request->email)->first();

        if ($exist_user_name) {
            return redirect('clients/add')->withInput($request->all())->with([
                'message' => language_data('User name already exist'),
                'message_important' => true
            ]);
        }

        if ($exist_user_email) {
            return redirect('clients/add')->withInput($request->all())->with([
                'message' => language_data('Email already exist'),
                'message_important' => true
            ]);
        }

        $password  = $request->password;
        $cpassword = $request->cpassword;

        if ($password !== $cpassword) {
            return redirect('clients/add')->withInput($request->all())->with([
                'message' => language_data('Both password does not match'),
                'message_important' => true
            ]);
        } else {
            $password = bcrypt($password);
        }

        $image = $request->image;
        if ($image != '' && app_config('AppStage') != 'Demo') {
            if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                $destinationPath = public_path() . '/assets/client_pic/';
                $image_name      = $image->getClientOriginalName();
                Input::file('image')->move($destinationPath, $image_name);
            } else {
                return redirect('clients/add')->withInput($request->all())->with([
                    'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
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

        $email = $request->email;

        $api_key_generate    = $request->user_name . ':' . $cpassword;
        $client              = new Client();
        $client->groupid     = $request->client_group;
        $client->parent      = '0';
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
        $client->api_access  = $request->api_access;
        $client->api_key     = base64_encode($api_key_generate);
        $client->status      = 'Active';
        $client->reseller    = $request->reseller_panel;
        $client->sms_gateway = $sms_gateways_id;
        $client->api_gateway = $sms_gateways[0];
        $client->emailnotify = $email_notify;
        $client->save();
        $client_id = $client->id;

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

                    return redirect('clients/all')->with([
                        'message' => language_data('Client Added Successfully')
                    ]);

                } catch (\Exception $ex) {
                    return redirect('clients/all')->with([
                        'message' => $ex->getMessage()
                    ]);
                }

            }
        }

        return redirect('clients/all')->with([
            'message' => language_data('Client Added Successfully')
        ]);

    }

    //======================================================================
    // viewClient Function Start Here
    //======================================================================
    public function viewClient($id)
    {

        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client = Client::find($id);
        if ($client) {
            $invoices          = Invoices::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $tickets           = SupportTickets::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $sms_gateways      = SMSGateways::where('status', 'Active')->get();
            $sms_transaction   = SMSTransaction::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $clientGroups      = ClientGroups::where('status', 'Yes')->where('created_by', '0')->get();
            $selected_gateways = json_decode($client->sms_gateway);
            return view('admin.client-manage', compact('client', 'invoices', 'tickets', 'sms_gateways', 'sms_transaction', 'clientGroups', 'selected_gateways'));
        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // updateLimit Function Start Here
    //======================================================================
    public function updateLimit(Request $request)
    {
        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = Input::get('cmd');
        $v   = \Validator::make($request->all(), [
            'sms_amount' => 'required|numeric|max:1000000000'
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client) {

            SMSTransaction::create([
                'cl_id' => $cmd,
                'amount' => $request->sms_amount
            ]);

            $client->sms_limit += $request->sms_amount;
            $client->save();
        }

        return redirect('clients/view/' . $cmd)->with([
            'message' => language_data('Limit updated successfully')
        ]);
    }

    //======================================================================
    // updateImage Function Start Here
    //======================================================================
    public function updateImage(Request $request)
    {
        $cmd      = Input::get('cmd');
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $v = \Validator::make($request->all(), [
            'client_image' => 'required |image| mimes:jpeg,jpg,png,gif',
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client) {
            $image = $request->client_image;
            if ($image != '') {

                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg'))) {
                    $destinationPath = public_path() . '/assets/client_pic/';
                    $image_name      = $image->getClientOriginalName();
                    Input::file('client_image')->move($destinationPath, $image_name);

                    $client->image = $image_name;
                    $client->save();

                    return redirect('clients/view/' . $cmd)->with([
                        'message' => language_data('Image updated successfully')
                    ]);
                } else {
                    return redirect('clients/view/' . $cmd)->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
                        'message_important' => true
                    ]);
                }


            } else {
                return redirect('clients/view/' . $cmd)->with([
                    'message' => language_data('Please try again'),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // updateClient Function Start Here
    //======================================================================
    public function updateClient(Request $request)
    {
        $cmd      = Input::get('cmd');
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'phone' => 'required', 'country' => 'required', 'api_access' => 'required', 'client_group' => 'required', 'status' => 'required', 'reseller_panel' => 'required', 'email' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }

        $sms_gateways = $request->sms_gateway;

        if (isset($sms_gateways) && is_array($sms_gateways) && count(array_filter($sms_gateways)) <= 0) {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('At least select one sms gateway'),
                'message_important' => true
            ]);
        }

        $sms_gateways_id = json_encode($sms_gateways, true);

        $client = Client::find($cmd);
        if ($client) {
            if ($client->username != $request->user_name) {
                $exist_user_name = Client::where('username', $request->user_name)->first();
                if ($exist_user_name) {
                    return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('User Name already exist'),
                        'message_important' => true
                    ]);
                }
            }

            if ($client->email != $request->email) {

                $exist_user_email = Client::where('email', $request->email)->first();
                if ($exist_user_email) {
                    return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('Email already exist'),
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
            $client->api_access  = $request->api_access;
            $client->status      = $request->status;
            $client->reseller    = $request->reseller_panel;
            $client->sms_gateway = $sms_gateways_id;
            $client->save();

            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('Client updated successfully')
            ]);
        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // sendSMS Function Start Here
    //======================================================================
    public function sendSMS(Request $request)
    {
        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'message' => 'required', 'sms_gateway' => 'required', 'message_type' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client == '') {
            return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }

        $gateway_credential = null;
        $cg_info            = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
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
                return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway credential not found'),
                    'message_important' => true
                ]);
            }
        }

        $clphone   = str_replace(['(', ')', '+', '-', ' '], '', $client->phone);
        $message   = $request->message;
        $sender_id = $request->sender_id;
        $msg_type  = $request->message_type;

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'arabic') {
            return redirect('clients/view/' . $cmd)->withInput($request->all())->with([
                'message' => language_data('Invalid message type'),
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
        if ($msg_type == 'unicode' || $msg_type != 'arabic') {
            $msgcount = mb_strlen(preg_replace('/\s+/', ' ', trim($message)), 'UTF-8');

            if ($msgcount <= 70) {
                $msgcount = 1;
            } else {
                $msgcount = $msgcount / 67;
            }
        }
        $msgcount = ceil($msgcount);

        $this->dispatch(new SendBulkSMS('0', $clphone, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info, '', $msg_type));

        return redirect('clients/view/' . $cmd)->with([
            'message' => language_data('Please check sms history')
        ]);
    }

    //======================================================================
    // exportImport Function Start Here
    //======================================================================
    public function exportImport()
    {
        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client_groups = ClientGroups::where('status', 'Yes')->where('created_by', '0')->get();
        $sms_gateways  = SMSGateways::where('status', 'Active')->get();
        return view('admin.export-n-import', compact('client_groups', 'sms_gateways'));
    }


    //======================================================================
    // exportClients Function Start Here
    //======================================================================
    public function exportClients()
    {
        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        Excel::create('Clients', function ($excel) {
            $excel->sheet('Sheetname', function ($sheet) {
                $sheet->fromModel(Client::all());
            });

        })->export('csv');
    }

    //======================================================================
    // downloadSampleCSV Function Start Here
    //======================================================================
    public function downloadSampleCSV()
    {
        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

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

        foreach ($array as $k => $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i]   = $val[$key];
                $client_data[$i] = $val;
                if (!array_key_exists('id', $client_data[$k])) {
                    $client_data[$k]['password']    = bcrypt($val['password']);
                    $client_data[$k]['groupid']     = $client_group;
                    $client_data[$k]['sms_gateway'] = $sms_gateway;
                    $client_data[$k]['created_at']  = DateTime::dateTime();
                    $client_data[$k]['updated_at']  = DateTime::dateTime();
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
    // addNewClientCSV Function Start Here
    //======================================================================
    public function addNewClientCSV(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/export-n-import')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $v = \Validator::make($request->all(), [
            'import_client' => 'required', 'client_group' => 'required', 'sms_gateway' => 'required', 'reseller_panel' => 'required', 'api_access' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/export-n-import')->withErrors($v->errors());
        }

        $sms_gateways = $request->sms_gateway;

        if (isset($sms_gateways) && is_array($sms_gateways) && count(array_filter($sms_gateways)) <= 0) {
            return redirect('clients/export-n-import')->with([
                'message' => language_data('At least select one sms gateway'),
                'message_important' => true
            ]);
        }

        $sms_gateways_id = json_encode($sms_gateways, true);
        $client_group    = $request->client_group;
        $reseller_panel  = $request->reseller_panel;
        $api_access      = $request->api_access;


        $file_extension = Input::file('import_client')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r(strtolower($file_extension), $supportedExt)) {
            return redirect('clients/export-n-import')->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true
            ]);
        }

        $all_data = Excel::load($request->import_client)->noHeading()->all()->toArray();


        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect('clients/export-n-import')->with([
                'message' => language_data('Empty field'),
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
            return redirect('clients/export-n-import')->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
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


        array_filter($all_data, function ($data) use ($first_name_column, $last_name_column, $company_column, $website_column, $email_address_column, $user_name_column, $password_column, $number_column, $address_column, $more_address_column, $state_column, $city_column, $postcode_column, $country_column, $sms_limit_column, $exist_clients, $sms_gateways_id, $client_group, $reseller_panel, $api_access, $sms_gateways, &$get_data, &$valid_clients) {

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

                    $api_key_generate = $data[$user_name_column] . ':' . $data[$password_column];

                    array_push($valid_clients, $data[$user_name_column]);
                    array_push($get_data, [
                        'groupid' => $client_group,
                        'parent' => 0,
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
            return redirect('clients/export-n-import')->with([
                'message' => 'Client already exist or not found',
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
        return redirect('clients/all')->with([
            'message' => language_data('Client imported successfully')
        ]);

    }


    //======================================================================
    // clientGroups Function Start Here
    //======================================================================
    public function clientGroups()
    {
        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroups = ClientGroups::all();
        return view('admin.client-groups', compact('clientGroups'));
    }

    //======================================================================
    // addNewClientGroup Function Start Here
    //======================================================================
    public function addNewClientGroup(Request $request)
    {
        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'group_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/groups')->withErrors($v->errors());
        }

        $clientGroup             = new ClientGroups();
        $clientGroup->group_name = $request->group_name;
        $clientGroup->created_by = '0';
        $clientGroup->status     = $request->status;
        $clientGroup->save();

        return redirect('clients/groups')->with([
            'message' => language_data('Client Group added successfully')
        ]);

    }

    //======================================================================
    // updateClientGroup Function Start Here
    //======================================================================
    public function updateClientGroup(Request $request)
    {

        $self = 'edit-client-group';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = $request->cmd;

        $v = \Validator::make($request->all(), [
            'group_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/groups')->withErrors($v->errors());
        }

        $clientGroup = ClientGroups::find($cmd);

        if ($clientGroup) {
            $clientGroup->group_name = $request->group_name;
            $clientGroup->status     = $request->status;
            $clientGroup->save();

            return redirect('clients/groups')->with([
                'message' => language_data('Client Group updated successfully')
            ]);

        } else {
            return redirect('clients/groups')->with([
                'message' => language_data('Client Group not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // exportClientGroup Function Start Here
    //======================================================================
    public function exportClientGroup($id)
    {
        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        Excel::create('Clients', function ($excel) use ($id) {
            $excel->sheet('Sheetname', function ($sheet) use ($id) {
                $sheet->fromModel(Client::where('groupid', $id)->get());
            });

        })->export('csv');
    }


    //======================================================================
    // deleteClientGroup Function Start Here
    //======================================================================
    public function deleteClientGroup($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/groups')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroup = ClientGroups::find($id);

        if ($clientGroup) {
            $exist_client = Client::where('groupid', $id)->first();
            if ($exist_client) {
                return redirect('clients/groups')->with([
                    'message' => language_data('This Group exist in a client'),
                    'message_important' => true
                ]);
            }

            $clientGroup->delete();

            return redirect('clients/groups')->with([
                'message' => language_data('Client group deleted successfully')
            ]);

        } else {
            return redirect('clients/groups')->with([
                'message' => language_data('Client Group not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // deleteClient Function Start Here
    //======================================================================
    public function deleteClient($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/all')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        //Check Permission
        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        //check client info
        $client = Client::find($id);
        if ($client) {
            //Check Parent
            $parents = Client::where('parent', $id)->first();

            if ($parents) {
                return redirect('clients/view/' . $id)->with([
                    'message' => language_data('This client have some customer'),
                    'message_important' => true
                ]);
            }

            //Delete Client Group
            ClientGroups::where('created_by', $id)->delete();

            //Delete Invoice
            Invoices::where('cl_id', $id)->delete();

            //Delete SMS History
            $client_sms  = SMSHistory::where('userid', $id);
            $get_history = $client_sms->select('id')->get()->toArray();

            if (isset($get_history) && is_array($get_history) && count($get_history)) {
                $get_inbox_id = array_column($get_history, 'id');
                SMSInbox::whereIn('msg_id', $get_inbox_id)->delete();
                $client_sms->delete();
            }


            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (isset($client_array) && is_array($client_array) && in_array($client->id, $client_array)) {
                    array_push($all_ids, $sid->id);
                }
            }
            $sender_ids = array_unique($all_ids);

            if (isset($sender_ids) && is_array($sender_ids) && count($sender_ids) > 0) {
                $get_sender_id = SenderIdManage::whereIn('id', $sender_ids)->get();
                foreach ($get_sender_id as $s_id) {
                    $get_data = remove_element(json_decode($s_id->cl_id), $client->id);
                    if (is_array($get_data) && count($get_data) > 0) {
                        $clients_id = json_encode(array_values($get_data));
                        $s_id->cl_id  = $clients_id;
                        $s_id->save();
                    } else {
                        $s_id->delete();
                    }
                }
            }


            $campaign = Campaigns::where('user_id', $id);
            $get_camp_ids = $campaign->select('campaign_id')->get()->toArray();

            if (isset($get_camp_ids) && is_array($get_camp_ids) && count($get_camp_ids)) {
                $get_inbox_id = array_column($get_camp_ids, 'campaign_id');
                CampaignSubscriptionList::whereIn('campaign_id', $get_inbox_id)->delete();
                $campaign->delete();
            }

            BlackListContact::where('user_id', $id)->delete();
            BlockMessage::where('user_id', $id)->delete();
            ImportPhoneNumber::where('user_id', $id)->delete();
            Keywords::where('user_id', $id)->update([
                'user_id' => 0,
                'status' => 'available'
            ]);

            RecurringSMS::where('userid', $id)->delete();

            //delete Support Tickets
            SupportTickets::where('cl_id', $client->id)->delete();
            SupportTicketsReplies::where('cl_id', $id)->delete();
            SupportTicketFiles::where('cl_id', $id)->delete();


            // Delete Schedule SMS N SMS Transaction
            ScheduleSMS::where('userid', $client->id)->delete();
            SMSTransaction::where('cl_id', $client->id)->delete();

            SMSTemplates::where('cl_id', $id)->delete();

            //Delete Client
            $client->delete();

            return redirect('clients/all')->with([
                'message' => language_data('Client delete successfully')
            ]);

        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }


    }



    //======================================================================
    // getAllClients Function Start Here
    //======================================================================
    public function getAllClients()
    {
        $clients = Client::orderBy('datecreated', 'desc')->getQuery();
        return Datatables::of($clients)
            ->addColumn('action', function ($cl) {
                return '
                <a class="btn btn-success btn-xs" href="' . url("clients/view/$cl->id") . '" ><i class="fa fa-edit"></i>' . language_data('Manage') . '</a>
                <a href="#" id="' . $cl->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-danger"></i> ' . language_data('Delete') . '</a>
                ';
            })
            ->addColumn('name', function ($cl) {
                return $cl->fname . ' ' . $cl->lname;
            })
            ->addColumn('datecreated', function ($cl) {
                return get_date_format($cl->datecreated);
            })
            ->addColumn('parent', function ($cl) {
                if ($cl->parent == 0) {
                    return language_data('Admin');
                } else {
                    return '
                    <a href=' . url('clients/view/' . $cl->parent) . '>' . client_info($cl->parent)->fname . '</a>
                    ';
                }
            })
            ->addColumn('api_access', function ($cl) {
                if ($cl->api_access == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('status', function ($cl) {
                if ($cl->status == 'Active') {
                    return '<p class="text-success">' . language_data("Active") . '</p>';
                } elseif ($cl->status == 'Inactive') {
                    return '<p class="text-warning">' . language_data("Inactive") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("Closed") . '</p>';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }
}
