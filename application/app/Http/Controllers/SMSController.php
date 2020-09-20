<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\BlackListContact;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\Permission;
use App\Classes\PhoneNumber;
use App\Client;
use App\ClientGroups;
use App\ContactList;
use App\CustomSMSGateways;
use App\ImportPhoneNumber;
use App\IntCountryCodes;
use App\Jobs\SendBulkMMS;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\Keywords;
use App\Operator;
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
use App\TwoWayCommunication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;

class SMSController extends Controller
{
    /**
     * SMSController constructor.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // coverage Function Start Here
    //======================================================================
    public function coverage()
    {

        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $country_codes = IntCountryCodes::all();
        return view('admin.coverage', compact('country_codes'));
    }

    //======================================================================
    // manageCoverage Function Start Here
    //======================================================================
    public function manageCoverage($id)
    {
        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $coverage = IntCountryCodes::find($id);
        if ($coverage) {
            return view('admin.manage-coverage', compact('coverage'));
        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postManageCoverage Function Start Here
    //======================================================================
    public function postManageCoverage(Request $request)
    {
        $cmd = Input::get('cmd');

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/manage-coverage/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'coverage';
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
            'status' => 'required', 'plain_tariff' => 'required', 'voice_tariff' => 'required', 'mms_tariff' => 'required'
        ]);
        if ($v->fails()) {
            return redirect('sms/manage-coverage/' . $cmd)->withErrors($v->errors());
        }

        $coverage = IntCountryCodes::find($cmd);
        if ($coverage) {
            $coverage->active       = $request->status;
            $coverage->plain_tariff = $request->plain_tariff;
            $coverage->voice_tariff = $request->voice_tariff;
            $coverage->mms_tariff   = $request->mms_tariff;
            $coverage->save();

            return redirect('sms/manage-coverage/' . $cmd)->with([
                'message' => language_data('Coverage updated successfully')
            ]);

        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // senderIdManagement Function Start Here
    //======================================================================
    public function senderIdManagement()
    {

        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $sender_id = SenderIdManage::all();
        return view('admin.sender-id-management', compact('sender_id'));
    }

    //======================================================================
    // addSenderID Function Start Here
    //======================================================================
    public function addSenderID()
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clients = Client::where('status', 'Active')->get();
        return view('admin.add-sender-id', compact('clients'));
    }

    //======================================================================
    // postNewSenderID Function Start Here
    //======================================================================
    public function postNewSenderID(Request $request)
    {
        $self = 'sender-id-management';
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
            'client_id' => 'required', 'status' => 'required', 'sender_id' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/add-sender-id')->withErrors($v->errors());
        }
        $sender_ids = $request->sender_id;
        $clients_id = $request->client_id;

        if (isset($clients_id) && is_array($clients_id) && count($clients_id) <= 0) {
            return redirect('sms/add-sender-id')->with([
                'message' => language_data('Select Client'),
                'message_important' => true
            ]);
        }

        if (isset($sender_ids) && is_array($sender_ids) && count(array_filter($sender_ids)) <= 0) {
            return redirect('sms/add-sender-id')->with([
                'message' => language_data('Insert Sender id'),
                'message_important' => true
            ]);
        }

        $clients_id = json_encode($clients_id, true);

        if (isset($sender_ids) && is_array($sender_ids)) {
            foreach ($sender_ids as $ids) {
                if ($ids) {
                    $sender_id            = new SenderIdManage();
                    $sender_id->sender_id = $ids;
                    $sender_id->cl_id     = $clients_id;
                    $sender_id->status    = $request->status;
                    $sender_id->save();
                }
            }
        }

        return redirect('sms/sender-id-management')->with([
            'message' => language_data('Sender Id added successfully')
        ]);

    }

    //======================================================================
    // viewSenderID Function Start Here
    //======================================================================
    public function viewSenderID($id)
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $senderId = SenderIdManage::find($id);
        if ($senderId) {
            $clients           = Client::where('status', 'Active')->get();
            $sender_id_clients = json_decode($senderId->cl_id);
            if (isset($sender_id_clients) && is_array($sender_id_clients) && in_array('0', $sender_id_clients)) {
                $selected_all = true;
            } else {
                $selected_all = false;
            }

            return view('admin.manage-sender-id', compact('clients', 'senderId', 'sender_id_clients', 'selected_all'));
        } else {
            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender Id not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postUpdateSenderID Function Start Here
    //======================================================================
    public function postUpdateSenderID(Request $request)
    {
        $self = 'sender-id-management';
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
            'client_id' => 'required', 'status' => 'required', 'sender_id' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/view-sender-id/' . $cmd)->withErrors($v->errors());
        }

        $senderId = SenderIdManage::find($cmd);
        if ($senderId) {
            $senderId->sender_id = $request->sender_id;
            $senderId->cl_id     = json_encode($request->client_id);
            $senderId->status    = $request->status;
            $senderId->save();
            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender id updated successfully')
            ]);
        } else {
            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender Id not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // deleteSenderID Function Start Here
    //======================================================================
    public function deleteSenderID($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/sender-id-management')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $senderId = SenderIdManage::find($id);
        if ($senderId) {
            $senderId->delete();

            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender id deleted successfully')
            ]);

        } else {
            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender Id not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // pricePlan Function Start Here
    //======================================================================
    public function pricePlan()
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $price_plan = SMSPricePlan::all();
        return view('admin.sms-price-plan', compact('price_plan'));
    }

    //======================================================================
    // addPricePlan Function Start Here
    //======================================================================
    public function addPricePlan()
    {
        $self = 'add-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        return view('admin.add-price-plan');
    }

    //======================================================================
    // postNewPricePlan Function Start Here
    //======================================================================
    public function postNewPricePlan(Request $request)
    {
        $self = 'add-price-plan';
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
            'category'=> 'required','plan_name' => 'required', 'price' => 'required', 'show_in_client' => 'required'
        ]);

        // return $v;

        if ($v->fails()) {
            return redirect('sms/add-price-plan')->withErrors($v->errors());
        }

        $exist_plan = SMSPricePlan::where('plan_name', $request->plan_name)->where('category', $request->category)->first();
        if ($exist_plan) {
            return redirect('sms/add-price-plan')->with([
                'message' => language_data('Plan already exist'),
                'message_important' => true
            ]);
        }

        $plan            = new SMSPricePlan();
        $plan->plan_name = $request->plan_name;
        $plan->price     = $request->price;
        $plan->popular   = $request->popular;
        $plan->status    = $request->show_in_client;
        $plan->category    = $request->category;
        $plan->save();

        return redirect('sms/price-plan')->with([
            'message' => language_data('Plan added successfully')
        ]);

    }


    //======================================================================
    // managePricePlan Function Start Here
    //======================================================================
    public function managePricePlan($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $price_plan = SMSPricePlan::find($id);
        if ($price_plan) {
            return view('admin.manage-price-plan', compact('price_plan'));
        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postManagePricePlan Function Start Here
    //======================================================================
    public function postManagePricePlan(Request $request)
    {
        $self = 'sms-price-plan';
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
            'category'=> 'required','plan_name' => 'required', 'price' => 'required', 'show_in_client' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-price-plan/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }
        $plan = SMSPricePlan::find($cmd);

        if ($plan) {
            if ($plan->plan_name != $request->plan_name) {
                $exist_plan = SMSPricePlan::where('plan_name', $request->plan_name)->first();
                if ($exist_plan) {
                    return redirect('sms/manage-price-plan/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('Plan already exist'),
                        'message_important' => true
                    ]);
                }
            }

            $plan->plan_name = $request->plan_name;
            $plan->price     = $request->price;
            $plan->popular   = $request->popular;
            $plan->status    = $request->show_in_client;
            $plan->category    = $request->category;


            $plan->save();

            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan updated successfully')
            ]);
        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan not found'),
                'message_important' => true
            ]);
        }


    }



    //======================================================================
    // addPlanFeature Function Start Here
    //======================================================================
    public function addPlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $price_plan = SMSPricePlan::find($id);
        if ($price_plan) {
            return view('admin.add-plan-feature', compact('price_plan'));
        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postNewPlanFeature Function Start Here
    //======================================================================
    public function postNewPlanFeature(Request $request)
    {
        $self = 'sms-price-plan';
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
            'feature_name' => 'required', 'feature_value' => 'required', 'show_in_client' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/add-plan-feature/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }

        $price_plan = SMSPricePlan::find($cmd);
        if ($price_plan) {
            $feature_name  = $request->feature_name;
            $feature_value = $request->feature_value;

            foreach ($feature_name as $key => $value) {
                SMSPlanFeature::create([
                    'pid' => $cmd,
                    'feature_name' => $value,
                    'feature_value' => $feature_value[$key],
                    'status' => $request->show_in_client
                ]);
            }

            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan features added successfully')
            ]);

        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // viewPlanFeature Function Start Here
    //======================================================================
    public function viewPlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $features = SMSPlanFeature::where('pid', $id)->get();
        return view('admin.view-plan-feature', compact('features'));

    }

    //======================================================================
    // managePlanFeature Function Start Here
    //======================================================================
    public function managePlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $plan_feature = SMSPlanFeature::find($id);
        if ($plan_feature) {
            return view('admin.manage-plan-feature', compact('plan_feature'));
        } else {
            return redirect('sms/view-plan-feature/' . $id)->with([
                'message' => language_data('Plan feature not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postManagePlanFeature Function Start Here
    //======================================================================
    public function postManagePlanFeature(Request $request)
    {
        $self = 'sms-price-plan';
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
            'feature_name' => 'required', 'feature_value' => 'required', 'show_in_client' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-plan-feature/' . $cmd)->withErrors($v->errors());
        }


        $plan_feature = SMSPlanFeature::find($cmd);
        if ($plan_feature->feature_name != $request->feature_name) {
            $exist = SMSPlanFeature::where('feature_name', $request->feature_name)->where('pid', $plan_feature->pid)->first();
            if ($exist) {
                return redirect('sms/manage-plan-feature/' . $cmd)->with([
                    'message' => language_data('Feature already exist'),
                    'message_important' => true
                ]);
            }
        }

        if ($plan_feature) {
            $plan_feature->feature_name  = $request->feature_name;
            $plan_feature->feature_value = $request->feature_value;
            $plan_feature->status        = $request->show_in_client;
            $plan_feature->save();

            return redirect('sms/view-plan-feature/' . $plan_feature->pid)->with([
                'message' => language_data('Feature updated successfully')
            ]);

        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan feature not found'),
                'message_important' => true
            ]);
        }
    }



    //======================================================================
    // deletePlanFeature Function Start Here
    //======================================================================
    public function deletePlanFeature($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/price-plan')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $plan_feature = SMSPlanFeature::find($id);
        if ($plan_feature) {
            $pid = $plan_feature->pid;
            $plan_feature->delete();
            return redirect('sms/view-plan-feature/' . $pid)->with([
                'message' => language_data('Plan feature deleted successfully')
            ]);
        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan feature not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // deletePricePlan Function Start Here
    //======================================================================
    public function deletePricePlan($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/price-plan')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $price_plan = SMSPricePlan::find($id);
        if ($price_plan) {
            SMSPlanFeature::where('pid', $id)->delete();
            $price_plan->delete();
            return redirect('sms/price-plan')->with([
                'message' => language_data('Price Plan deleted successfully')
            ]);
        } else {
            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan feature not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // httpSmsGateways Function Start Here
    //======================================================================
    public function httpSmsGateways()
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        return view('admin.sms-gateways');
    }

    //======================================================================
    // smppSmsGateways Function Start Here
    //======================================================================
    public function smppSmsGateways()
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        return view('admin.smpp-sms-gateways');
    }


    //======================================================================
    // getAllGatewaysData Function Start Here
    //======================================================================
    public function getAllGatewaysData(Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];
        } else {
            $get_search_column = 'created_at';
            $short_by          = 'ASC';
        }

        $sms_gateway = SMSGateways::where('type', 'http')->orderBy($get_search_column, $short_by)->getQuery();

        if ($request->has('search') && $request->get('search')['value'] != null) {
            $search_value = $request->get('search')['value'];
            $sms_gateway->where('name', 'like', "%" . $search_value . "%");
        }

        return Datatables::of($sms_gateway)
            ->addColumn('action', function ($gateway) {
                if ($gateway->custom == 'Yes') {
                    $return_url = '
                    <a class="btn btn-success btn-xs" href="' . url("sms/custom-gateway-manage/$gateway->id") . '" ><i class="fa fa-edit"></i>' . language_data('Manage') . '</a>
                <a href="#" id="' . $gateway->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-danger"></i> ' . language_data('Delete') . '</a>
                    ';
                    if ($gateway->two_way == 'Yes') {
                        $return_url .= '
                        <a class="btn btn-info btn-xs" href="' . url("sms/custom-gateway-two-way/$gateway->id") . '" ><i class="fa fa-exchange"></i>' . language_data('Two way') . '</a>
                        ';
                    }

                    return $return_url;

                } else {

                    return '
                <a class="btn btn-success btn-xs" href="' . url("sms/gateway-manage/$gateway->id") . '" ><i class="fa fa-edit"></i>' . language_data('Manage') . '</a>
                ';
                }
            })
            ->addColumn('id', function ($gateway) {
                return $gateway->id;
            })
            ->addColumn('name', function ($gateway) {
                return $gateway->name;
            })
            ->addColumn('settings', function ($gateway) {
                return $gateway->settings;
            })
            ->addColumn('schedule', function ($gateway) {
                if ($gateway->schedule == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('two_way', function ($gateway) {
                if ($gateway->two_way == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('mms', function ($gateway) {
                if ($gateway->mms == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('mms', function ($gateway) {
                if ($gateway->mms == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('voice', function ($gateway) {
                if ($gateway->voice == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('status', function ($gateway) {
                if ($gateway->status == 'Active') {
                    return '<p class="text-success">' . language_data("Active") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("Inactive") . '</p>';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }

    //======================================================================
    // getAllSMPPGatewaysData Function Start Here
    //======================================================================
    public function getAllSMPPGatewaysData(Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];
        } else {
            $get_search_column = 'created_at';
            $short_by          = 'ASC';
        }

        $sms_gateway = SMSGateways::where('type', 'smpp')->orderBy($get_search_column, $short_by)->getQuery();

        if ($request->has('search') && $request->get('search')['value'] != null) {
            $search_value = $request->get('search')['value'];
            $sms_gateway->where('name', 'like', "%" . $search_value . "%");
        }

        return Datatables::of($sms_gateway)
            ->addColumn('action', function ($gateway) {
                $sms_url = '
                <a class="btn btn-success btn-xs" href="' . url("sms/gateway-manage/$gateway->id") . '" ><i class="fa fa-edit"></i>' . language_data('Manage') . '</a>
                ';

                if ($gateway->custom == 'Yes') {
                    $sms_url .= '
                <a href="#" id="' . $gateway->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-danger"></i> ' . language_data('Delete') . '</a>
                    ';
                }

                return $sms_url;

            })
            ->addColumn('id', function ($gateway) {
                return $gateway->id;
            })
            ->addColumn('name', function ($gateway) {
                return $gateway->name;
            })
            ->addColumn('schedule', function ($gateway) {
                if ($gateway->schedule == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('two_way', function ($gateway) {
                if ($gateway->two_way == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('mms', function ($gateway) {
                if ($gateway->mms == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('mms', function ($gateway) {
                if ($gateway->mms == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('voice', function ($gateway) {
                if ($gateway->voice == 'Yes') {
                    return '<p class="text-success">' . language_data("Yes") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("No") . '</p>';
                }
            })
            ->addColumn('status', function ($gateway) {
                if ($gateway->status == 'Active') {
                    return '<p class="text-success">' . language_data("Active") . '</p>';
                } else {
                    return '<p class="text-danger">' . language_data("Inactive") . '</p>';
                }
            })
            ->escapeColumns([])
            ->make(true);
    }



    //======================================================================
    // addSmsGateway Function Start Here
    //======================================================================
    public function addSmsGateway()
    {
        $self = 'add-sms-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.add-sms-gateway');
    }

    //======================================================================
    // addSMPPSmsGateway Function Start Here
    //======================================================================
    public function addSMPPSmsGateway()
    {
        $self = 'add-sms-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.add-smpp-sms-gateway');
    }

    //======================================================================
    // postNewSmsGateway Function Start Here
    //======================================================================
    public function postNewSmsGateway(Request $request)
    {
        $self = 'add-sms-gateway';
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
            'gateway_name' => 'required', 'gateway_link' => 'required', 'status' => 'required', 'destination_param' => 'required', 'message_param' => 'required', 'username_param' => 'required', 'username_value' => 'required', 'schedule' => 'required', 'two_way' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/add-sms-gateways')->withInput($request->all())->withErrors($v->errors());
        }

        $exist_gateway = SMSGateways::where('settings', $request->gateway_name)->first();
        if ($exist_gateway) {
            return redirect('sms/add-sms-gateways')->with([
                'message' => language_data('Gateway already exist'),
                'message_important' => true
            ]);
        }

        $gateway           = new SMSGateways();
        $gateway->name     = $request->gateway_name;
        $gateway->settings = $request->gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->schedule = $request->schedule;
        $gateway->custom   = 'Yes';
        $gateway->status   = $request->status;
        $gateway->type     = 'http';
        $gateway->two_way  = $request->two_way;
        $gateway->save();

        $gateway_id = $gateway->id;

        if (is_int($gateway_id)) {
            $cgateway                 = new CustomSMSGateways();
            $cgateway->gateway_id     = $gateway_id;
            $cgateway->username_param = $request->username_param;
            $cgateway->username_value = $request->username_value;

            $cgateway->password_param  = $request->password_param;
            $cgateway->password_value  = $request->password_value;
            $cgateway->password_status = $request->password_status;

            $cgateway->action_param  = $request->action_param;
            $cgateway->action_value  = $request->action_value;
            $cgateway->action_status = $request->action_status;

            $cgateway->source_param  = $request->source_param;
            $cgateway->source_value  = $request->source_value;
            $cgateway->source_status = $request->source_status;

            $cgateway->destination_param = $request->destination_param;
            $cgateway->message_param     = $request->message_param;

            $cgateway->unicode_param  = $request->unicode_param;
            $cgateway->unicode_value  = $request->unicode_value;
            $cgateway->unicode_status = $request->unicode_status;

            $cgateway->route_param  = $request->route_param;
            $cgateway->route_value  = $request->route_value;
            $cgateway->route_status = $request->route_status;

            $cgateway->language_param  = $request->language_param;
            $cgateway->language_value  = $request->language_value;
            $cgateway->language_status = $request->language_status;

            $cgateway->custom_one_param  = $request->custom_one_param;
            $cgateway->custom_one_value  = $request->custom_one_value;
            $cgateway->custom_one_status = $request->custom_one_status;

            $cgateway->custom_two_param  = $request->custom_two_param;
            $cgateway->custom_two_value  = $request->custom_two_value;
            $cgateway->custom_two_status = $request->custom_two_status;

            $cgateway->custom_three_param  = $request->custom_three_param;
            $cgateway->custom_three_value  = $request->custom_three_value;
            $cgateway->custom_three_status = $request->custom_three_status;

            $cgateway->save();

            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('Custom gateway added successfully')
            ]);
        } else {
            SMSGateways::where('id', $gateway_id)->delete();
            return redirect('sms/add-sms-gateways')->with([
                'message' => language_data('Parameter or Value is empty'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // postNewSMPPGateway Function Start Here
    //======================================================================
    public function postNewSMPPGateway(Request $request)
    {
        $self = 'add-sms-gateway';
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
            'gateway_name' => 'required', 'gateway_link' => 'required', 'status' => 'required', 'gateway_user_name' => 'required', 'gateway_password' => 'required', 'schedule' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/add-smpp-sms-gateways')->withInput($request->all())->withErrors($v->errors());
        }

        $exist_gateway = SMSGateways::where('settings', $request->gateway_name)->first();
        if ($exist_gateway) {
            return redirect('sms/add-smpp-sms-gateways')->with([
                'message' => language_data('Gateway already exist'),
                'message_important' => true
            ]);
        }

        $gateway           = new SMSGateways();
        $gateway->name     = $request->gateway_name;
        $gateway->settings = $request->gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->port     = $request->port;
        $gateway->schedule = $request->schedule;
        $gateway->custom   = 'Yes';
        $gateway->status   = $request->status;
        $gateway->type     = 'smpp';
        $gateway->two_way  = 'No';
        $gateway->voice    = 'No';
        $gateway->mms      = 'No';
        $gateway->save();

        $gateway_id = $gateway->id;

        if (is_int($gateway_id)) {

            $gateway_credential             = new SMSGatewayCredential();
            $gateway_credential->gateway_id = $gateway_id;
            $gateway_credential->username   = $request->gateway_user_name;
            $gateway_credential->password   = $request->gateway_password;
            $gateway_credential->status     = $request->status;

            $gateway_credential->save();

            return redirect('sms/smpp-sms-gateway')->with([
                'message' => language_data('Custom gateway added successfully')
            ]);
        } else {
            return redirect('sms/add-smpp-sms-gateways')->with([
                'message' => language_data('Something went wrong please try again'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // customSmsGatewayManage Function Start Here
    //======================================================================
    public function customSmsGatewayManage($id)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateway = SMSGateways::find($id);
        if ($gateway) {
            $gateway_info = CustomSMSGateways::where('gateway_id', $id)->first();
            return view('admin.manage-custom-sms-gateway', compact('gateway', 'gateway_info'));
        } else {
            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('Gateway information not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // postCustomSmsGateway Function Start Here
    //======================================================================
    public function postCustomSmsGateway(Request $request)
    {
        $self = 'sms-gateways';
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
            'gateway_name' => 'required', 'gateway_link' => 'required', 'status' => 'required', 'destination_param' => 'required', 'message_param' => 'required', 'username_param' => 'required', 'username_value' => 'required', 'schedule' => 'required', 'two_way' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/custom-gateway-manage/' . $cmd)->withInput($request->all())->withErrors($v->errors());
        }

        $gateway      = SMSGateways::find($cmd);
        $gateway_name = $request->gateway_name;

        if ($gateway->custom == 'Yes') {
            if ($gateway_name == '') {
                return redirect('sms/custom-gateway-manage/' . $cmd)->with([
                    'message' => language_data('Gateway name required'),
                    'message_important' => true
                ]);
            }
        } else {
            $gateway_name = $gateway->name;
        }

        if ($gateway->name != $gateway_name) {
            $exist_gateway = SMSGateways::where('name', $gateway_name)->first();
            if ($exist_gateway) {
                return redirect('sms/custom-gateway-manage/' . $cmd)->with([
                    'message' => language_data('Gateway already exist'),
                    'message_important' => true
                ]);
            }
        }

        $gateway->name     = $request->gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->schedule = $request->schedule;
        $gateway->status   = $request->status;
        $gateway->two_way  = $request->two_way;
        $gateway->save();

        if ($cmd) {
            $cgateway = CustomSMSGateways::where('gateway_id', $cmd)->first();

            $cgateway->username_param = $request->username_param;
            $cgateway->username_value = $request->username_value;

            $cgateway->password_param  = $request->password_param;
            $cgateway->password_value  = $request->password_value;
            $cgateway->password_status = $request->password_status;

            $cgateway->action_param  = $request->action_param;
            $cgateway->action_value  = $request->action_value;
            $cgateway->action_status = $request->action_status;

            $cgateway->source_param  = $request->source_param;
            $cgateway->source_value  = $request->source_value;
            $cgateway->source_status = $request->source_status;

            $cgateway->destination_param = $request->destination_param;
            $cgateway->message_param     = $request->message_param;

            $cgateway->route_param  = $request->route_param;
            $cgateway->route_value  = $request->route_value;
            $cgateway->route_status = $request->route_status;

            $cgateway->language_param  = $request->language_param;
            $cgateway->language_value  = $request->language_value;
            $cgateway->language_status = $request->language_status;

            $cgateway->custom_one_param  = $request->custom_one_param;
            $cgateway->custom_one_value  = $request->custom_one_value;
            $cgateway->custom_one_status = $request->custom_one_status;

            $cgateway->custom_two_param  = $request->custom_two_param;
            $cgateway->custom_two_value  = $request->custom_two_value;
            $cgateway->custom_two_status = $request->custom_two_status;

            $cgateway->custom_three_param  = $request->custom_three_param;
            $cgateway->custom_three_value  = $request->custom_three_value;
            $cgateway->custom_three_status = $request->custom_three_status;

            $cgateway->save();

            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('Custom gateway updated successfully')
            ]);
        } else {
            return redirect('sms/add-sms-gateways')->with([
                'message' => language_data('Parameter or Value is empty'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // smsGatewayManage Function Start Here
    //======================================================================
    public function smsGatewayManage($id)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateway = SMSGateways::find($id);
        if ($gateway) {
            $credentials = SMSGatewayCredential::where('gateway_id', $id)->get();
            return view('admin.manage-sms-gateway', compact('gateway', 'credentials'));
        } else {
            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('Gateway information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postManageSmsGateway Function Start Here
    //======================================================================
    public function postManageSmsGateway(Request $request)
    {
        $self = 'sms-gateways';
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

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/gateway-manage/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'gateway_name' => 'required', 'schedule' => 'required', 'global_status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/gateway-manage/' . $cmd)->withErrors($v->errors());
        }

        $gateway      = SMSGateways::find($cmd);
        $gateway_name = $gateway->settings;

        if ($gateway->custom == 'Yes') {
            if ($gateway_name == '') {
                return redirect('sms/gateway-manage/' . $cmd)->with([
                    'message' => language_data('Gateway name required'),
                    'message_important' => true
                ]);
            }
        } else {
            $gateway_name = $gateway->settings;
        }

        if ($gateway->name != $gateway_name) {
            $exist_gateway = SMSGateways::where('name', $gateway_name)->first();
            if ($exist_gateway) {
                return redirect('sms/gateway-manage/' . $cmd)->with([
                    'message' => language_data('Gateway already exist'),
                    'message_important' => true
                ]);
            }
        }

        if ($gateway->type == 'http') {
            $redirect_url = 'sms/http-sms-gateway';
        } else {
            $redirect_url = 'sms/smpp-sms-gateway';
        }

        $gateway_user_name      = $request->gateway_user_name;
        $gateway_password       = $request->gateway_password;
        $extra_value            = $request->extra_value;
        $credential_base_status = $request->credential_base_status;

        if (!is_array($gateway_user_name) || count(array_filter($gateway_user_name)) <= 0) {
            return redirect('sms/gateway-manage/' . $cmd)->withInput($request->all())->with(array(
                'message' => language_data('At least one item is required'),
                'message_important' => true
            ));
        }

        if (!is_array($credential_base_status) || count(array_filter($credential_base_status)) <= 0) {
            return redirect('sms/gateway-manage/' . $cmd)->withInput($request->all())->with(array(
                'message' => language_data('At least one item is required'),
                'message_important' => true
            ));
        }

        $check_credential_status = array_count_values($credential_base_status);

        if (!is_array($check_credential_status) || array_key_exists('Active', $check_credential_status) === false) {
            return redirect('sms/gateway-manage/' . $cmd)->withInput($request->all())->with(array(
                'message' => language_data('Select one credential status as Active'),
                'message_important' => true
            ));
        }


        if ($check_credential_status['Active'] > 1) {
            return redirect('sms/gateway-manage/' . $cmd)->withInput($request->all())->with(array(
                'message' => language_data('Select one credential status as Active'),
                'message_important' => true
            ));
        }

        if ($gateway_name == 'PowerSMS') {
            $port = $gateway->port;
        } else {
            $port = $request->port;
        }

        $gateway->name     = $request->gateway_name;
        $gateway->settings = $gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->port     = $port;
        $gateway->schedule = $request->schedule;
        $gateway->status   = $request->global_status;
        $gateway->save();

        SMSGatewayCredential::where('gateway_id', $cmd)->delete();

        $i = 0;
        foreach ($gateway_user_name as $gat_info) {
            if ($gat_info != '') {
                $credential             = new SMSGatewayCredential();
                $credential->gateway_id = $cmd;
                $credential->username   = $gat_info;
                $credential->password   = $gateway_password[$i];
                $credential->extra      = $extra_value[$i];
                $credential->status     = $credential_base_status[$i];
                $credential->save();
            }
            $i++;
        }

        return redirect($redirect_url)->with([
            'message' => language_data('Gateway updated successfully')
        ]);

    }

    //======================================================================
    // deleteSmsGateway Function Start Here
    //======================================================================
    public function deleteSmsGateway($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateway = SMSGateways::find($id);
        if ($gateway && $gateway->custom == 'Yes') {

            if ($gateway->type == 'smpp') {
                $redirect_url = 'sms/smpp-sms-gateway';
            } else {
                $redirect_url = 'sms/http-sms-gateway';
            }

            $client = Client::where('api_gateway', $id)->first();
            if ($client) {
                return redirect($redirect_url)->with([
                    'message' => language_data('Client are registered with this gateway'),
                    'message_important' => true
                ]);
            }

            $all_gateways = [];
            $all_clients  = Client::where('status', 'Active')->select('sms_gateway')->get();

            foreach ($all_clients as $cl) {
                $data = json_decode($cl->sms_gateway, true);
                array_push($all_gateways, $data);
            }

            $final_gateway = in_array_r($id, $all_gateways);

            if ($final_gateway) {
                return redirect($redirect_url)->with([
                    'message' => language_data('Client are registered with this gateway'),
                    'message_important' => true
                ]);
            }


            $sms_history = SMSHistory::where('use_gateway', $id)->get();
            foreach ($sms_history as $history) {
                SMSInbox::where('msg_id', $history->id)->delete();
                $history->delete();
            }

            $recurring_sms = RecurringSMS::where('use_gateway', $id)->get();
            foreach ($recurring_sms as $sms) {
                RecurringSMSContacts::where('campaign_id', $sms->id)->delete();
                $sms->delete();
            }

            $campaign = Campaigns::where('use_gateway', $id)->get();
            foreach ($campaign as $camp) {
                CampaignSubscriptionList::where('campaign_id', $camp->campaign_id)->delete();
                $camp->delete();
            }


            CustomSMSGateways::where('gateway_id', $id)->delete();
            $gateway->delete();


            return redirect($redirect_url)->with([
                'message' => language_data('Gateway deleted successfully'),
            ]);

        } else {
            return redirect('sms/htpp-sms-gateway')->with([
                'message' => language_data('Delete option disable for this gateway'),
                'message_important' => true
            ]);
        }
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
    // sendBulkSMS Function Start Here
    //======================================================================
    public function sendBulkSMS()
    {
        $self = 'send-bulk-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client_group  = ClientGroups::where('status', 'Yes')->get();
        $phone_book    = ImportPhoneNumber::where('user_id', 0)->get();
        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', '0')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();
        $keyword       = Keywords::where('user_id', 0)->get();

        $schedule_sms = false;

        return view('admin.send-bulk-sms', compact('client_group', 'gateways', 'sms_templates', 'phone_book', 'schedule_sms', 'country_code', 'keyword'));

    }

    //======================================================================
    // postSendBulkSMS Function Start Here
    //======================================================================
    public function postSendBulkSMS(Request $request)
    {
        $self = 'send-bulk-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }

        if ($request->schedule_sms_status) {
            $v = \Validator::make($request->all(), [
                'sms_gateway' => 'required', 'schedule_time' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
            ]);

            $redirect_url = 'sms/send-schedule-sms';
        } else {
            $v = \Validator::make($request->all(), [
                'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
            ]);

            $redirect_url = 'sms/send-sms';
        }


        if ($v->fails()) {
            return redirect($redirect_url)->withInput($request->all())->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }


        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway credential not found'),
                    'message_important' => true
                ]);
            }
        }

        $sender_id = $request->sender_id;
        $message   = $request->message;
        $msg_type  = $request->message_type;

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
                'message' => language_data(language_data('Invalid message type')),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported Voice feature'),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported MMS feature'),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS file required'),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name      = $image->getClientOriginalName();
                    $image_name      = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Message required'),
                    'message_important' => true
                ]);
            }
        }

        $results = [];

        if ($request->contact_type == 'phone_book') {
            if (isset($request->contact_list_id) && count($request->contact_list_id)) {
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
                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));
                if ($request->country_code != 0) {
                    $phone = $request->country_code . $phone;
                }

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


        if (isset($results) && is_array($results)) {

            if (count($results) >= 0) {

                if ($request->remove_duplicate == 'yes') {
                    $results = unique_multidim_array($results, 'phone_number');
                    $results = array_values($results);
                }

                $filtered_data = [];
                $blacklist     = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

                if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                    $blacklist = array_column($blacklist, 'numbers');

                    array_filter($results, function ($element) use ($blacklist, &$filtered_data) {
                        if (!in_array($element['phone_number'], $blacklist)) {
                            array_push($filtered_data, $element);
                        }
                    });

                    $results = array_values($filtered_data);
                    unset($filtered_data);
                }

                if (count($results) <= 0) {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Recipient empty'),
                        'message_important' => true
                    ]);
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

                    $campaign_type       = 'scheduled';
                    $campaign_status     = 'Scheduled';
                    $subscription_status = 'scheduled';

                } else {
                    $schedule_time       = date('Y-m-d H:i:s');
                    $campaign_type       = 'regular';
                    $campaign_status     = 'Running';
                    $subscription_status = 'queued';
                }


                $campaign = Campaigns::create([
                    'campaign_id' => uniqid('C'),
                    'user_id' => 0,
                    'sender' => $sender_id,
                    'sms_type' => $msg_type,
                    'camp_type' => $campaign_type,
                    'status' => $campaign_status,
                    'use_gateway' => $gateway->id,
                    'total_recipient' => count($results),
                    'run_at' => $schedule_time,
                    'media_url' => $media_url,
                    'keyword' => $keywords
                ]);

                if ($campaign) {
                    $final_insert_data = [];
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

                            $clphone = str_replace(['(', ')', '+', '-', ' '], '', $r['phone_number']);

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

                            $push_data = [
                                'campaign_id' => $campaign->campaign_id,
                                'number' => $clphone,
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

                    $campaign_list = CampaignSubscriptionList::insert($final_insert_data);

                    if ($campaign_list) {
                        return redirect($redirect_url)->with([
                            'message' => language_data('SMS added in queue and will deliver one by one')
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

            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Recipient empty'),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Invalid Recipients'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // sendBulkSMSFile Function Start Here
    //======================================================================
    public function sendBulkSMSFile()
    {
        $self = 'send-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', '0')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();
        $keyword       = Keywords::where('user_id', 0)->get();
        $schedule_sms  = false;

        return view('admin.send-sms-file', compact('gateways', 'sms_templates', 'schedule_sms', 'country_code', 'keyword'));

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
            return redirect('sms/send-sms-file')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'send-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }


        if ($request->schedule_sms_status) {
            $v = \Validator::make($request->all(), [
                'import_numbers' => 'required', 'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'schedule_time_type' => 'required', 'country_code' => 'required'
            ]);

            $redirect_url = 'sms/send-schedule-sms-file';

        } else {
            $v = \Validator::make($request->all(), [
                'import_numbers' => 'required', 'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required'
            ]);

            $redirect_url = 'sms/send-sms-file';
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

            $campaign_type   = 'scheduled';
            $campaign_status = 'Scheduled';

        } else {
            $campaign_type   = 'regular';
            $campaign_status = 'Running';
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
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

        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway credential not found'),
                    'message_important' => true
                ]);
            }
        }

        $msg_type = $request->message_type;
        $message  = $request->message;

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms' && $msg_type != 'arabic') {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Invalid message type'),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported Voice feature'),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported MMS feature'),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS file required'),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name      = $image->getClientOriginalName();
                    $image_name      = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect($redirect_url)->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect($redirect_url)->withInput($request->all())->with([
                    'message' => language_data('Message required'),
                    'message_important' => true
                ]);
            }
        }


        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r(strtolower($file_extension), $supportedExt)) {
            return redirect($redirect_url)->withInput($request->all())->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true
            ]);
        }

        $all_data = Excel::load($request->import_numbers)->noHeading()->all()->toArray();

        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect($redirect_url)->withInput($request->all())->with([
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

        $all_data = array_map(function ($row) use ($header) {

            return array_combine($header, $row);

        }, $all_data);

        $valid_phone_numbers = [];
        $get_data            = [];

        $blacklist = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
            $blacklist = array_column($blacklist, 'numbers');
        }


        $number_column = trim($request->number_column);
        $campaign_id   = uniqid('C');

        array_filter($all_data, function ($data) use ($number_column, &$get_data, &$valid_phone_numbers, $blacklist, $request, $message, $msg_type, $campaign_id) {

            $a    = array_map('trim', array_keys($data));
            $b    = array_map('trim', $data);
            $data = array_combine($a, $b);

            if ($data[$number_column]) {
                $clphone = $data[$number_column];
                if ($request->country_code != 0) {
                    $clphone = $request->country_code . $clphone;
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

                    if ($request->send_later == 'on') {

                        if ($request->schedule_time_type == 'from_file') {
                            $schedule_time_column = $request->schedule_time_column;
                            $schedule_time        = date('Y-m-d H:i:s', strtotime($data[$schedule_time_column]));
                        } else {
                            $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
                        }

                        array_push($valid_phone_numbers, $clphone);
                        array_push($get_data, [
                            'campaign_id' => $campaign_id,
                            'number' => $clphone,
                            'message' => $get_message,
                            'amount' => $msgcount,
                            'status' => 'scheduled',
                            'submitted_time' => $schedule_time
                        ]);

                    } else {
                        array_push($valid_phone_numbers, $clphone);
                        array_push($get_data, [
                            'campaign_id' => $campaign_id,
                            'number' => $clphone,
                            'message' => $get_message,
                            'amount' => $msgcount,
                            'status' => 'queued'
                        ]);
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

        $sender_id = $request->sender_id;

        if ($request->remove_duplicate == 'yes') {
            $get_data = unique_multidim_array($get_data, 'number');
        }

        $get_data = array_values($get_data);

        $campaign = Campaigns::create([
            'campaign_id' => $campaign_id,
            'user_id' => 0,
            'sender' => $sender_id,
            'sms_type' => $msg_type,
            'camp_type' => $campaign_type,
            'status' => $campaign_status,
            'use_gateway' => $gateway->id,
            'total_recipient' => count($get_data),
            'run_at' => date('Y-m-d H:i:s'),
            'media_url' => $media_url,
            'keyword' => $keywords
        ]);


        if ($campaign) {

            $campaign_list = CampaignSubscriptionList::insert($get_data);

            if ($campaign_list) {
                return redirect($redirect_url)->with([
                    'message' => language_data('SMS added in queue and will deliver one by one')
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

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client_group  = ClientGroups::where('status', 'Yes')->get();
        $phone_book    = ImportPhoneNumber::where('user_id', 0)->get();
        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', '0')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();
        $keyword       = Keywords::where('user_id', 0)->get();

        $schedule_sms = true;

        return view('admin.send-bulk-sms', compact('client_group', 'gateways', 'sms_templates', 'phone_book', 'schedule_sms', 'country_code', 'keyword'));
    }


    //======================================================================
    // postUpdateScheduleSMS Function Start Here
    //======================================================================
    public function postUpdateScheduleSMS(Request $request)
    {

        $self = 'send-schedule-sms';
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
            'phone_number' => 'required', 'schedule_time' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->withInput($request->all())->withErrors($v->errors());
        }

        $schedule_sms = CampaignSubscriptionList::find($request->cmd);

        if (!$schedule_sms) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->with([
                'message' => 'SMS info not found',
                'message_important' => true
            ]);
        }

        if (\DateTime::createFromFormat('m/d/Y h:i A', $request->schedule_time) !== FALSE) {
            $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        } else {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->with([
                'message' => language_data('Invalid time format'),
                'message_important' => true
            ]);
        }

        $campaign = Campaigns::where('campaign_id', $schedule_sms->campaign_id)->first();

        if (!$campaign) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->with([
                'message' => 'SMS info not found',
                'message_important' => true
            ]);
        }

        $msg_type = $campaign->sms_type;
        $message  = $request->message;

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

        $blacklist = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

        if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {
            $blacklist = array_column($blacklist, 'numbers');
        }

        if (in_array($request->phone_number, $blacklist)) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->withInput($request->all())->with([
                'message' => language_data('Phone number contain in blacklist'),
                'message_important' => true
            ]);
        }

        $clphone = str_replace(['(', ')', '+', '-', ' '], '', $request->phone_number);

        CampaignSubscriptionList::where('id', $request->cmd)->where('campaign_id', $campaign->campaign_id)->update([
            'number' => $clphone,
            'amount' => $msgcount,
            'message' => $message,
            'submitted_time' => $schedule_time
        ]);

        return redirect('sms/manage-campaign/' . $campaign->id)->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time')
        ]);

    }

    //======================================================================
    // sendScheduleSMSFile Function Start Here
    //======================================================================
    public function sendScheduleSMSFile()
    {

        $self = 'schedule-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', '0')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();
        $keyword       = Keywords::where('user_id', 0)->get();
        $schedule_sms  = true;

        return view('admin.send-sms-file', compact('gateways', 'sms_templates', 'schedule_sms', 'country_code', 'keyword'));
    }


    //======================================================================
    // updateScheduleSMS Function Start Here
    //======================================================================
    public function updateScheduleSMS()
    {
        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.update-schedule-sms');
    }

    //======================================================================
    // getAllScheduleSMS Function Start Here
    //======================================================================
    public function getAllScheduleSMS()
    {

        $schedule_sms = ScheduleSMS::select(['id', 'sender', 'receiver', 'submit_time']);

        return Datatables::of($schedule_sms)
            ->addColumn('action', function ($ss) {
                return '
               <a class="btn btn-success btn-xs" href="' . url("sms/manage-update-schedule-sms/$ss->id") . '" ><i class="fa fa-edit"></i>' . language_data('Edit') . '</a>
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

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                ScheduleSMS::destroy($all_ids);
            }
        }
    }



    //======================================================================
    // manageUpdateScheduleSMS Function Start Here
    //======================================================================
    public function manageUpdateScheduleSMS($id)
    {
        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $sh = CampaignSubscriptionList::find($id);

        if ($sh) {
            return view('admin.manage-update-schedule-sms', compact('sh'));
        } else {
            return redirect('sms/campaign-reports')->with([
                'message' => language_data('Please try again'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // deleteScheduleSMS Function Start Here
    //======================================================================
    public function deleteScheduleSMS($id)
    {
        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $sh = ScheduleSMS::find($id);
        if ($sh) {
            $sh->delete();
            return redirect('sms/update-schedule-sms')->with([
                'message' => language_data('SMS info deleted successfully')
            ]);
        } else {
            return redirect('sms/update-schedule-sms')->with([
                'message' => language_data('Please try again'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // smsTemplates Function Start Here
    //======================================================================
    public function smsTemplates()
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $sms_templates = SMSTemplates::where('cl_id', '0')->orWhere('global', 'yes')->get();
        return view('admin.sms-templates', compact('sms_templates'));
    }

    //======================================================================
    // createSmsTemplate Function Start Here
    //======================================================================
    public function createSmsTemplate()
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.create-sms-template');
    }

    //======================================================================
    // postSmsTemplate Function Start Here
    //======================================================================
    public function postSmsTemplate(Request $request)
    {

        $self = 'sms-templates';
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
            'template_name' => 'required', 'message' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/create-sms-template')->withErrors($v->errors());
        }


        if ($request->set_global == 'yes') {
            $exist  = SMSTemplates::where('template_name', $request->template_name)->where('global', 'yes')->first();
            $global = 'yes';
        } else {
            $exist  = SMSTemplates::where('template_name', $request->template_name)->where('cl_id', 0)->where('global', 'no')->first();
            $global = 'no';
        }

        if ($exist) {
            return redirect('sms/create-sms-template')->with([
                'message' => language_data('Template already exist'),
                'message_important' => true
            ]);
        }


        $st                = new SMSTemplates();
        $st->cl_id         = '0';
        $st->template_name = $request->template_name;
        $st->from          = $request->from;
        $st->message       = $request->message;
        $st->global        = $global;
        $st->status        = 'active';
        $st->save();

        return redirect('sms/sms-templates')->with([
            'message' => language_data('Sms template created successfully')
        ]);

    }

    //======================================================================
    // manageSmsTemplate Function Start Here
    //======================================================================
    public function manageSmsTemplate($id)
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $st = SMSTemplates::find($id);

        if ($st) {

            return view('admin.manage-sms-template', compact('st'));

        } else {
            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postManageSmsTemplate Function Start Here
    //======================================================================
    public function postManageSmsTemplate(Request $request)
    {

        $self = 'sms-templates';
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
            'template_name' => 'required', 'message' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-sms-template/' . $cmd)->withErrors($v->errors());
        }

        $st = SMSTemplates::find($cmd);

        if ($st) {
            if ($st->template_name != $request->template_name) {

                if ($request->set_global == 'yes') {
                    $exist = SMSTemplates::where('template_name', $request->template_name)->where('global', 'yes')->first();
                } else {
                    $exist = SMSTemplates::where('template_name', $request->template_name)->where('cl_id', 0)->where('global', 'no')->first();
                }

                if ($exist) {
                    return redirect('sms/manage-sms-template/' . $cmd)->with([
                        'message' => language_data('Template already exist'),
                        'message_important' => true
                    ]);
                }
            }
            if ($request->set_global == 'yes') {
                $global = 'yes';
            } else {
                $global = 'no';
            }

            $st->template_name = $request->template_name;
            $st->from          = $request->from;
            $st->message       = $request->message;
            $st->status        = $request->status;
            $st->global        = $global;
            $st->save();

            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template updated successfully')
            ]);

        } else {
            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template not found'),
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
            return redirect('sms/sms-templates')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $st = SMSTemplates::find($id);
        if ($st) {
            $st->delete();

            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template delete successfully')
            ]);

        } else {
            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // apiInfo Function Start Here
    //======================================================================
    public function apiInfo()
    {

        $self = 'sms-api';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateways = SMSGateways::where('status', 'Active')->get();
        return view('admin.sms-api-info', compact('gateways'));
    }

    //======================================================================
    // updateApiInfo Function Start Here
    //======================================================================
    public function updateApiInfo(Request $request)
    {

        $self = 'sms-api';
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
            'api_url' => 'required', 'api_key' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms-api/info')->withErrors($v->errors());
        }

        if ($request->api_url != '') {
            AppConfig::where('setting', '=', 'api_url')->update(['value' => $request->api_url]);
        }

        if ($request->api_key != '') {
            AppConfig::where('setting', '=', 'api_key')->update(['value' => $request->api_key]);
        }

        if ($request->sms_gateway != '') {
            AppConfig::where('setting', '=', 'sms_api_gateway')->update(['value' => $request->sms_gateway]);
        }

        return redirect('sms-api/info')->with([
            'message' => language_data('API information updated successfully')
        ]);

    }


    /*Version 1.3*/

    //======================================================================
    // priceBundles Function Start Here
    //======================================================================
    public function priceBundles()
    {

        $self = 'price-bundles';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $bundles = SMSBundles::all();
        return view('admin.sms-bundles', compact('bundles'));
    }

    //======================================================================
    // postPriceBundles Function Start Here
    //======================================================================
    public function postPriceBundles(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/price-bundles')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }


        $self = 'price-bundles';
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
            'unit_from' => 'required', 'unit_to' => 'required', 'price' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/price-bundles')->withErrors($v->errors());
        }

        $unit_from = Input::get('unit_from');
        $unit_to   = Input::get('unit_to');
        $price     = Input::get('price');
        $trans_fee = Input::get('trans_fee');

        SMSBundles::truncate();

        if (!isset($unit_from) && !is_array($unit_from)) {
            return redirect('sms/price-bundles')->with([
                'message' => 'From unit required',
                'message_important' => true
            ]);
        }

        $unit_from = array_filter($unit_from, 'strlen');

        $i = 0;
        foreach ($unit_from as $uf) {
            $sb            = new SMSBundles();
            $sb->unit_from = $uf;
            $sb->unit_to   = $unit_to[$i];
            $sb->price     = $price[$i];
            $sb->trans_fee = $trans_fee[$i];
            $sb->save();
            $i++;
        }

        return redirect('sms/price-bundles')->with([
            'message' => language_data('Price Bundles Update Successfully')
        ]);

    }


    /*Version 2.0*/

    //======================================================================
    // blacklistContacts Function Start Here
    //======================================================================
    public function blacklistContacts()
    {

        $self = 'blacklist-contacts';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.blacklist-contacts');
    }

    //======================================================================
    // getBlacklistContacts Function Start Here
    //======================================================================
    public function getBlacklistContacts()
    {
        $blacklist = BlackListContact::where('user_id', 0)->getQuery();
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
    // postBlacklistContact Function Start Here
    //======================================================================
    public function postBlacklistContact(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'blacklist-contacts';
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
            'import_numbers' => 'required', 'delimiter' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/blacklist-contacts')->withErrors($v->errors());
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
                return redirect('sms/blacklist-contacts')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }

            $results = array_filter($results);

            foreach ($results as $r) {

                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));
                $exist = BlackListContact::where('numbers', $phone)->where('user_id', 0)->first();

                if (!$exist) {
                    BlackListContact::create([
                        'user_id' => '0',
                        'numbers' => $phone
                    ]);
                }
            }

            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('Number added on blacklist'),
            ]);

        } catch (\Exception $e) {
            return redirect('sms/blacklist-contacts')->with([
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
            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'blacklist-contacts';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $blacklist = BlackListContact::where('user_id', '0')->find($id);
        if ($blacklist) {
            $blacklist->delete();
            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('Number deleted from blacklist'),
            ]);
        } else {
            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('Number not found on blacklist'),
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
            return redirect('sms/blacklist-contacts')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'blacklist-contacts';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                $status = BlackListContact::where('user_id', 0)->whereIn('id', $all_ids)->delete();

                if ($status) {
                    return response()->json([
                        'status' => 'success',
                        'message' => language_data('Number deleted from blacklist'),
                    ]);
                }
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request',
            ]);
        }

    }



    //======================================================================
    // getRecipientsData Function Start Here
    //======================================================================
    public function getRecipientsData(Request $request)
    {
        if ($request->has('client_group_ids')) {
            $client_group_ids = $request->client_group_ids;
            if (isset($client_group_ids) && is_array($client_group_ids) && count($client_group_ids) > 0) {
                $count = Client::whereIn('groupid', $client_group_ids)->count();
                return response()->json(['status' => 'success', 'data' => $count]);
            } else {
                return response()->json(['status' => 'success', 'data' => 0]);
            }
        } elseif ($request->has('contact_list_ids')) {
            $contact_list_ids = $request->contact_list_ids;
            if (isset($contact_list_ids) && is_array($contact_list_ids) && count($contact_list_ids) > 0) {
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
    // Version 2.2
    //======================================================================

    //======================================================================
    // sdkInfo Function Start Here
    //======================================================================
    public function sdkInfo()
    {
        $self = 'sms-api';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        return view('admin.sms-sdk-info');
    }

    //======================================================================
    // sendQuickSMS Function Start Here
    //======================================================================
    public function sendQuickSMS()
    {

        $self = 'send-quick-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateways     = SMSGateways::where('status', 'Active')->get();
        $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        return view('admin.send-quick-sms', compact('gateways', 'country_code'));
    }


    //======================================================================
    // postQuickSMS Function Start Here
    //======================================================================
    public function postQuickSMS(Request $request)
    {

        $self = 'send-quick-sms';
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
            'sms_gateway' => 'required', 'recipients' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/quick-sms')->withInput($request->all())->withErrors($v->errors());
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
                return redirect('sms/quick-sms')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }

            $results = array_filter($recipients);

            if (isset($results) && is_array($results) && count($results) <= 100) {

                $gateway = SMSGateways::find($request->sms_gateway);
                if ($gateway->status != 'Active') {
                    return redirect('sms/quick-sms')->withInput($request->all())->with([
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
                            return redirect('sms/quick-sms')->withInput($request->all())->with([
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
                        return redirect('sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('SMS Gateway credential not found'),
                            'message_important' => true
                        ]);
                    }
                }

                $msg_type = $request->message_type;
                $message  = $request->message;

                if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms' && $msg_type != 'arabic') {
                    return redirect('sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('Invalid message type'),
                        'message_important' => true
                    ]);
                }

                if ($msg_type == 'voice') {
                    if ($gateway->voice != 'Yes') {
                        return redirect('sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('SMS Gateway not supported Voice feature'),
                            'message_important' => true
                        ]);
                    }
                }

                if ($msg_type == 'mms') {

                    if ($gateway->mms != 'Yes') {
                        return redirect('sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('SMS Gateway not supported MMS feature'),
                            'message_important' => true
                        ]);
                    }

                    $image = $request->image;

                    if ($image == '') {
                        return redirect('sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('MMS file required'),
                            'message_important' => true
                        ]);
                    }

                    if (app_config('AppStage') != 'Demo') {
                        if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                            $destinationPath = public_path() . '/assets/mms_file/';
                            $image_name      = $image->getClientOriginalName();
                            $image_name      = str_replace(" ", "-", $image_name);
                            Input::file('image')->move($destinationPath, $image_name);
                            $media_url = asset('assets/mms_file/' . $image_name);

                        } else {
                            return redirect('sms/quick-sms')->withInput($request->all())->with([
                                'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                                'message_important' => true
                            ]);
                        }

                    } else {
                        return redirect('sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('MMS is disable in demo mode'),
                            'message_important' => true
                        ]);
                    }
                } else {
                    $media_url = null;
                    if ($message == '') {
                        return redirect('sms/quick-sms')->withInput($request->all())->with([
                            'message' => language_data('Message required'),
                            'message_important' => true
                        ]);
                    }
                }

                $sender_id = $request->sender_id;

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

                $filtered_data = [];
                $blacklist     = BlackListContact::select('numbers')->where('user_id', 0)->get()->toArray();

                if ($blacklist && is_array($blacklist) && count($blacklist) > 0) {

                    $blacklist = array_column($blacklist, 'numbers');

                    array_filter($results, function ($element) use ($blacklist, &$filtered_data, $request) {
                        $element = trim($element);
                        if ($request->country_code != 0) {
                            $element = $request->country_code . $element;
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
                    return redirect('sms/quick-sms')->withInput($request->all())->with([
                        'message' => language_data('Recipient empty'),
                        'message_important' => true
                    ]);
                }

                if ($request->remove_duplicate == 'yes') {
                    $results = array_map('trim', $results);
                    $results = array_unique($results, SORT_REGULAR);
                }

                $results = array_values($results);


                foreach ($results as $r) {
                    $number = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                    if ($request->country_code != 0) {
                        $number = $request->country_code . $number;
                    }

                    if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                        $this->dispatch(new SendBulkSMS('0', $number, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info, '', $msg_type));
                    }
                    if ($msg_type == 'voice') {
                        $this->dispatch(new SendBulkVoice('0', $number, $gateway, $gateway_credential, $sender_id, $message, $msgcount));
                    }
                    if ($msg_type == 'mms') {
                        $this->dispatch(new SendBulkMMS('0', $number, $gateway, $gateway_credential, $sender_id, $message, $media_url));
                    }
                }

                return redirect('sms/quick-sms')->with([
                    'message' => language_data('Please check sms history for status')
                ]);
            } else {
                return redirect('sms/quick-sms')->withInput($request->all())->with([
                    'message' => language_data('You can not send more than 100 sms using quick sms option'),
                    'message_important' => true
                ]);
            }

        } catch (\Exception $e) {
            return redirect('sms/quick-sms')->withInput($request->all())->with([
                'message' => $e->getMessage(),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // addOperator Function Start Here
    //======================================================================
    public function addOperator($id)
    {
        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $coverage = IntCountryCodes::find($id);
        if ($coverage) {
            return view('admin.add-operator', compact('coverage'));
        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // postAddOperator Function Start Here
    //======================================================================
    public function postAddOperator(Request $request)
    {

        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $coverage_id = $request->coverage_id;

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/add-operator/' . $coverage_id)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'operator_name' => 'required', 'operator_code' => 'required|numeric', 'plain_price' => 'required|numeric', 'voice_price' => 'required|numeric', 'mms_price' => 'required|numeric', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/add-operator/' . $coverage_id)->withInput($request->all())->withErrors($v->errors());
        }

        $phone             = str_replace(['(', ')', '+', '-', ' '], '', trim($request->operator_code));
        $phoneUtil         = PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneUtil->parse('+' . $phone, null);
        $area_code_exist   = $phoneUtil->getLengthOfGeographicalAreaCode($phoneNumberObject);

        $save_data = $request->all();

        if ($area_code_exist) {
            $format            = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::INTERNATIONAL);
            $get_format_data   = explode(" ", $format);
            $operator_settings = explode('-', $get_format_data[1])[0];

        } else {
            $carrierMapper     = PhoneNumberToCarrierMapper::getInstance();
            $operator_settings = $carrierMapper->getNameForNumber($phoneNumberObject, 'en');
        }

        $exist = Operator::where('operator_setting', $operator_settings)->where('coverage_id', $coverage_id)->first();
        if ($exist) {
            return redirect('sms/add-operator/' . $coverage_id)->withInput($request->all())->with([
                'message' => language_data('Operator already exist'),
                'message_important' => true
            ]);
        }

        $save_data['operator_setting'] = $operator_settings;

        $status = Operator::create($save_data);

        if ($status) {
            return redirect('sms/coverage')->with([
                'message' => language_data('Operator added successfully')
            ]);
        } else {
            return redirect('sms/add-operator/' . $coverage_id)->withInput($request->all())->with([
                'message' => language_data('Something went wrong please try again'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postManageOperator Function Start Here
    //======================================================================
    public function postManageOperator(Request $request)
    {

        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $id = $request->id;

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/manage-operator/' . $id)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'operator_name' => 'required', 'operator_code' => 'required|numeric', 'plain_price' => 'required|numeric', 'voice_price' => 'required|numeric', 'mms_price' => 'required|numeric', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-operator/' . $id)->withInput($request->all())->withErrors($v->errors());
        }

        $operator = Operator::find($id);

        $phone             = str_replace(['(', ')', '+', '-', ' '], '', trim($request->operator_code));
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

        $save_data = $request->all();

        if ($operator) {
            if ($operator->operator_setting != $operator_settings) {
                $exist = Operator::where('operator_setting', $operator_settings)->where('coverage_id', $request->coverage_id)->first();
                if ($exist) {
                    return redirect('sms/manage-operator/' . $id)->withInput($request->all())->with([
                        'message' => language_data('Operator already exist'),
                        'message_important' => true
                    ]);
                }
            }

            $save_data['operator_setting'] = $operator_settings;

            $status = Operator::find($id)->update($save_data);
            if ($status) {
                return redirect('sms/view-operator/' . $request->coverage_id)->with([
                    'message' => language_data('Operator updated successfully')
                ]);
            } else {
                return redirect('sms/manage-operator/' . $id)->withInput($request->all())->with([
                    'message' => language_data('Something went wrong please try again'),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // viewOperator Function Start Here
    //======================================================================
    public function viewOperator($id)
    {
        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $coverage = IntCountryCodes::find($id);
        if ($coverage) {
            $operators = Operator::where('coverage_id', $id)->get();
            return view('admin.view-operator', compact('operators'));
        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // manageOperator Function Start Here
    //======================================================================
    public function manageOperator($id)
    {
        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $op = Operator::find($id);
        if ($op) {
            return view('admin.manage-operator', compact('op'));
        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // deleteOperator Function Start Here
    //======================================================================
    public function deleteOperator($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/coverage')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $operator = Operator::find($id);
        if ($operator) {
            $operator->delete();
            return redirect('sms/coverage')->with([
                'message' => language_data('Operator delete successfully')
            ]);
        } else {
            return redirect('sms/coverage')->with([
                'message' => language_data('Information not found'),
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

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.recurring-sms');
    }


    //======================================================================
    // getRecurringSMSData Function Start Here
    //======================================================================
    public function getRecurringSMSData(Request $request)
    {


        if ($request->has('order') && $request->has('columns')) {
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];
        } else {
            $get_search_column = 'updated_at';
            $short_by          = 'DESC';
        }

        $recurring_sms = RecurringSMS::orderBy($get_search_column, $short_by)->getQuery();
        return Datatables::of($recurring_sms)
            ->addColumn('action', function ($sms) {
                $reply_url = '';
                if ($sms->status == 'running') {
                    $reply_url .= ' <a class="btn btn-warning btn-xs stop-recurring" href="#" id="' . $sms->id . '"><i class="fa fa-stop"></i> ' . language_data('Stop Recurring') . '  </a>';
                } else {
                    $reply_url .= ' <a class="btn btn-success btn-xs start-recurring" href="#" id="' . $sms->id . '"><i class="fa fa-check"></i> ' . language_data('Start Recurring') . ' </a>';

                }
                return $reply_url . '
                <a href="#" id="' . $sms->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                <div class="btn-group btn-mini-group dropdown-default">
                    <a class="btn btn-xs dropdown-toggle btn-complete" data-toggle="dropdown" href="#" aria-expanded="false"><i class="fa fa-bars"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="' . url("sms/update-recurring-sms/$sms->id") . '" data-toggle="tooltip" data-placement="left" title="' . language_data('Update Period') . '"><i class="fa fa-clock-o"></i></a></li>
                        <li><a href="' . url("sms/add-recurring-sms-contact/$sms->id") . '" data-toggle="tooltip" data-placement="left" title="' . language_data('Add Contact') . '"><i class="fa fa-plus"></i></a></li>
                        <li><a href="' . url("sms/update-recurring-sms-contact/$sms->id") . '" data-toggle="tooltip" data-placement="left" title="' . language_data('Update Contact') . '"><i class="fa fa-edit"></i></a></li>
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
    // getRecurringSMSContactData Function Start Here
    //======================================================================
    public function getRecurringSMSContactData($id, Request $request)
    {


        if ($request->has('order') && $request->has('columns')) {
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];
        } else {
            $get_search_column = 'updated_at';
            $short_by          = 'DESC';
        }

        $recurring_sms = RecurringSMSContacts::orderBy($get_search_column, $short_by)->getQuery();
        return Datatables::of($recurring_sms)
            ->addColumn('action', function ($sms) {
                return '
                <a href="' . url("sms/update-recurring-sms-contact-data/$sms->id") . '" class="btn btn-xs btn-complete"><i class="fa fa-edit"></i> ' . language_data('Update') . '</a>
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
// deleteRecurringSMS Function Start Here
//======================================================================
    public function deleteRecurringSMS($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring_sms = RecurringSMS::find($id);

        if ($recurring_sms) {
            RecurringSMSContacts::where('campaign_id', $id)->delete();
            $recurring_sms->delete();
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('SMS info deleted successfully')
            ]);

        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('SMS Not Found'),
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
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring_contact = RecurringSMSContacts::find($id);

        if ($recurring_contact) {
            $recurring_contact->delete();
            return redirect('sms/update-recurring-sms-contact/' . $recurring_contact->campaign_id)->with([
                'message' => language_data('Contact deleted successfully')
            ]);
        } else {
            return redirect('sms/recurring-sms')->with([
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
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (is_array($all_ids) && count($all_ids) > 0) {
                foreach ($all_ids as $id) {
                    RecurringSMSContacts::where('campaign_id', $id)->delete();
                    RecurringSMS::delete($id);
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

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return response()->json([
                    'message' => language_data('You do not have permission to view this page'),
                    'status' => 'error'
                ]);
            }
        }

        if ($request->has('data_ids')) {
            $all_ids    = explode(',', $request->get('data_ids'));
            $recipients = count($all_ids);
            if ($request->has('campaign_id')) {
                if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                    RecurringSMSContacts::destroy($all_ids);
                    $recurring                   = RecurringSMS::find($request->campaign_id);
                    $recurring->total_recipients -= $recipients;
                    $recurring->save();
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => language_data('Recipients required')
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => language_data('Recurring SMS info not found')
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => language_data('Contact deleted successfully')
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => language_data('Invalid request')
            ]);
        }
    }

//======================================================================
// sendRecurringSMS Function Start Here
//======================================================================
    public function sendRecurringSMS()
    {
        $self = 'send-recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client_group  = ClientGroups::where('status', 'Yes')->get();
        $phone_book    = ImportPhoneNumber::where('user_id', 0)->get();
        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', '0')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        return view('admin.send-recurring-sms', compact('client_group', 'gateways', 'sms_templates', 'phone_book', 'country_code'));
    }

    //======================================================================
    // postRecurringSMS Function Start Here
    //======================================================================

    public function postRecurringSMS(Request $request)
    {

        $self = 'send-recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }

        $v = \Validator::make($request->all(), [
            'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'period' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
        ]);


        if ($v->fails()) {
            return redirect('sms/send-recurring-sms')->withErrors($v->errors());
        }


        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-recurring-sms')->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }

        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('sms/send-recurring-sms')->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect('sms/send-recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway credential not found'),
                    'message_important' => true
                ]);
            }
        }

        $sender_id = $request->sender_id;
        $message   = $request->message;
        $msg_type  = $request->message_type;


        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms') {
            return redirect('sms/send-recurring-sms')->with([
                'message' => language_data('Invalid message type'),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('SMS Gateway not supported Voice feature'),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('SMS Gateway not supported MMS feature'),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('MMS file required'),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name      = $image->getClientOriginalName();
                    $image_name      = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('sms/send-recurring-sms')->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('Message required'),
                    'message_important' => true
                ]);
            }
        }


        $period = $request->period;
        $its    = strtotime(date('Y-m-d'));

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
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('Schedule time required'),
                    'message_important' => true
                ]);
            }
            $nd = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        } else {
            return redirect('sms/send-recurring-sms')->with([
                'message' => language_data('Date Parsing Error'),
                'message_important' => true
            ]);
        }

        if ($period != '0') {

            if ($request->recurring_time == '') {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('Schedule time required'),
                    'message_important' => true
                ]);
            }

            $schedule_time = $request->recurring_time;
            $nd            = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
        }


        $results = [];

        if ($request->contact_type == 'phone_book') {
            if (count($request->contact_list_id)) {
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
                return redirect('sms/send-recurring-sms')->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }

            foreach ($recipients as $r) {

                $phone = str_replace(['(', ')', '+', '-', ' '], '', trim($r));
                if ($request->country_code != 0) {
                    $phone = $request->country_code . $phone;
                }

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


        if (isset($results) && is_array($results)) {

            if (count($results) >= 0) {


                if ($request->remove_duplicate == 'yes') {
                    $results = unique_multidim_array($results, 'phone_number');
                }

                $filtered_data = [];
                $blacklist     = BlackListContact::where('user_id', 0)->select('numbers')->get()->toArray();

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
                    return redirect('sms/send-recurring-sms')->with([
                        'message' => language_data('Recipient empty'),
                        'message_important' => true
                    ]);
                }


                $results          = array_values($results);
                $total_recipients = count($results);

                $recurring_id = RecurringSMS::create([
                    'userid' => 0,
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

                            RecurringSMSContacts::create([
                                'campaign_id' => $recurring_id->id,
                                'receiver' => $r['phone_number'],
                                'message' => $get_message,
                                'amount' => $msgcount
                            ]);

                        }
                    }

                    return redirect('sms/send-recurring-sms')->with([
                        'message' => language_data('Message recurred successfully. Delivered in correct time')
                    ]);
                } else {
                    return redirect('sms/send-recurring-sms')->with([
                        'message' => language_data('Something went wrong please try again'),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('sms/send-recurring-sms')->with([
                    'message' => language_data('Recipient empty'),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('sms/send-recurring-sms')->with([
                'message' => language_data('Invalid Recipients'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // stopRecurringSMS Function Start Here
    //======================================================================
    public function stopRecurringSMS($id)
    {
        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring = RecurringSMS::find($id);

        if ($recurring) {
            $recurring->status = 'stop';
            $recurring->save();

            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring SMS stop successfully')
            ]);

        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // startRecurringSMS Function Start Here
    //======================================================================
    public function startRecurringSMS($id)
    {

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring = RecurringSMS::find($id);

        if ($recurring) {
            $period = $recurring->recurring;
            $its    = strtotime(date('Y-m-d'));

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
                return redirect('sms/recurring-sms')->with([
                    'message' => language_data('Date Parsing Error'),
                    'message_important' => true
                ]);
            }

            if ($period != '0') {
                $schedule_time = date("H:i:s", strtotime($recurring->recurring_date));
                $nd            = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
            }

            $recurring->recurring_date = $nd;
            $recurring->status         = 'running';
            $recurring->save();

            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring SMS running successfully')
            ]);

        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // updateRecurringSMS Function Start Here
    //======================================================================
    public function updateRecurringSMS($id)
    {

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring = RecurringSMS::find($id);
        if ($recurring) {
            $gateways = SMSGateways::where('status', 'Active')->get();
            return view('admin.update-recurring-sms', compact('recurring', 'gateways'));
        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // addRecurringSMSContact Function Start Here
    //======================================================================
    public function addRecurringSMSContact($id)
    {

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring = RecurringSMS::find($id);
        if ($recurring) {
            $country_code = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();
            return view('admin.add-recurring-sms-contact', compact('recurring', 'country_code'));
        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // updateRecurringSMSContact Function Start Here
    //======================================================================
    public function updateRecurringSMSContact($id)
    {
        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring = RecurringSMS::find($id);
        if ($recurring) {
            return view('admin.update-recurring-sms-contact', compact('id'));
        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }
    //======================================================================
    // updateRecurringSMSContactData Function Start Here
    //======================================================================
    public function updateRecurringSMSContactData($id)
    {
        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $recurring = RecurringSMSContacts::find($id);
        if ($recurring) {
            return view('admin.update-recurring-sms-contact-data', compact('recurring'));
        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // postRecurringSMSContact Function Start Here
    //======================================================================
    public function postRecurringSMSContact(Request $request)
    {
        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $id        = $request->recurring_id;
        $recurring = RecurringSMS::find($id);
        if ($recurring) {

            $v = \Validator::make($request->all(), [
                'recipients' => 'required', 'remove_duplicate' => 'required', 'country_code' => 'required', 'delimiter' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('sms/add-recurring-sms-contact/' . $id)->withInput($request->all())->withErrors($v->errors());
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
                return redirect('sms/add-recurring-sms-contact/' . $id)->with([
                    'message' => 'Invalid delimiter',
                    'message_important' => true
                ]);
            }


            $results = array_filter($recipients);

            if (isset($results) && is_array($results)) {

                $msg_type = $recurring->type;
                $message  = $request->message;

                if ($msg_type != 'mms') {
                    if ($message == '') {
                        return redirect('sms/add-recurring-sms-contact/' . $id)->withInput($request->all())->with([
                            'message' => language_data('Message required'),
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

                $filtered_data = [];
                $blacklist     = BlackListContact::select('numbers')->get()->toArray();

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
                    return redirect('sms/add-recurring-sms-contact/' . $id)->withInput($request->all())->with([
                        'message' => language_data('Recipient empty'),
                        'message_important' => true
                    ]);
                }

                if ($request->remove_duplicate == 'yes') {
                    $results = array_map('trim', $results);
                    $results = array_unique($results, SORT_REGULAR);
                }

                $results = array_values($results);

                $current_recipients = 0;
                foreach ($results as $r) {
                    $number = str_replace(['(', ')', '+', '-', ' '], '', trim($r));

                    if ($request->country_code != 0) {
                        $number = $request->country_code . $number;
                    }

                    $exist_check = RecurringSMSContacts::where('campaign_id', $id)->where('receiver', $number)->first();
                    if (!$exist_check) {
                        RecurringSMSContacts::create([
                            'campaign_id' => $id,
                            'receiver' => $number,
                            'message' => $message,
                            'amount' => $msgcount
                        ]);
                        $current_recipients++;
                    }
                }
                $recurring->total_recipients += $current_recipients;
                $recurring->save();

                return redirect('sms/add-recurring-sms-contact/' . $id)->with([
                    'message' => language_data('Recurring contact added successfully')
                ]);
            } else {
                return redirect('sms/recurring-sms')->withInput($request->all())->with([
                    'message' => language_data('Invalid request'),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postUpdateRecurringSMSContactData Function Start Here
    //======================================================================
    public function postUpdateRecurringSMSContactData(Request $request)
    {

        $self = 'recurring-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $id        = $request->recurring_id;
        $recurring = RecurringSMS::find($id);
        if ($recurring) {

            $contact_id = $request->contact_id;

            $v = \Validator::make($request->all(), [
                'phone_number' => 'required', 'message' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->withErrors($v->errors());
            }

            $msg_type = $recurring->type;
            $message  = $request->message;

            if ($msg_type != 'mms') {
                if ($message == '') {
                    return redirect('sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->with([
                        'message' => language_data('Message required'),
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
                return redirect('sms/update-recurring-sms-contact-data/' . $contact_id)->withInput($request->all())->with([
                    'message' => language_data('Phone number contain in blacklist'),
                    'message_important' => true
                ]);
            }

            RecurringSMSContacts::find($contact_id)->update([
                'receiver' => $request->phone_number,
                'message' => $message,
                'amount' => $msgcount
            ]);

            return redirect('sms/update-recurring-sms-contact-data/' . $contact_id)->with([
                'message' => language_data('Recurring contact updated successfully')
            ]);

        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postUpdateRecurringSMS Function Start Here
    //======================================================================
    public function postUpdateRecurringSMS(Request $request)
    {

        $self = 'recurring-sms';
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
        $v   = \Validator::make($request->all(), [
            'period' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/update-recurring-sms/' . $cmd)->withErrors($v->errors());
        }

        $recurring = RecurringSMS::find($cmd);

        if ($recurring) {

            $gateway = SMSGateways::find($request->sms_gateway);
            if ($gateway->status != 'Active') {
                return redirect('sms/update-recurring-sms/' . $cmd)->with([
                    'message' => language_data('SMS gateway not active'),
                    'message_important' => true
                ]);
            }

            $period = $request->period;
            $its    = strtotime(date('Y-m-d'));

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
                    return redirect('sms/update-recurring-sms/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('Schedule time required'),
                        'message_important' => true
                    ]);
                }
                $nd = date('Y-m-d H:i:s', strtotime($request->schedule_time));
            } else {
                return redirect('sms/update-recurring-sms/' . $cmd)->withInput($request->all())->with([
                    'message' => language_data('Date Parsing Error'),
                    'message_important' => true
                ]);
            }

            if ($period != '0') {
                if ($request->recurring_time == '') {
                    return redirect('sms/update-recurring-sms/' . $cmd)->withInput($request->all())->with([
                        'message' => language_data('Schedule time required'),
                        'message_important' => true
                    ]);
                }

                $schedule_time = $request->recurring_time;
                $nd            = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
            }

            $recurring->use_gateway    = $gateway->id;
            $recurring->recurring      = $period;
            $recurring->recurring_date = $nd;
            $recurring->sender         = $request->sender_id;
            $recurring->save();

            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring SMS period changed')
            ]);

        } else {
            return redirect('sms/recurring-sms')->with([
                'message' => language_data('Recurring information not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // sendRecurringSMSFile Function Start Here
    //======================================================================
    public function sendRecurringSMSFile()
    {

        $self = 'recurring-sms-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->where('cl_id', '0')->get();
        $country_code  = IntCountryCodes::where('Active', '1')->select('country_code', 'country_name')->get();

        return view('admin.send-recurring-sms-file', compact('gateways', 'sms_templates', 'country_code'));
    }

    //======================================================================
    // postRecurringSMSFile Function Start Here
    //======================================================================
    public function postRecurringSMSFile(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/send-recurring-sms-file')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'recurring-sms-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if (function_exists('ini_set') && ini_get('max_execution_time')) {
            ini_set('max_execution_time', '-1');
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'sms_gateway' => 'required', 'message_type' => 'required', 'remove_duplicate' => 'required', 'period' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }


        $gateway_credential = null;
        if ($gateway->custom == 'Yes') {
            if ($gateway->type == 'smpp') {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            }
        } else {
            $gateway_credential = SMSGatewayCredential::where('gateway_id', $request->sms_gateway)->where('status', 'Active')->first();
            if ($gateway_credential == null) {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway credential not found'),
                    'message_important' => true
                ]);
            }
        }


        $sender_id = $request->sender_id;
        $msg_type  = $request->message_type;
        $message   = $request->message;

        if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'voice' && $msg_type != 'mms') {
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Invalid message type'),
                'message_important' => true
            ]);
        }

        if ($msg_type == 'voice') {
            if ($gateway->voice != 'Yes') {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported Voice feature'),
                    'message_important' => true
                ]);
            }
        }

        if ($msg_type == 'mms') {

            if ($gateway->mms != 'Yes') {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('SMS Gateway not supported MMS feature'),
                    'message_important' => true
                ]);
            }

            $image = $request->image;

            if ($image == '') {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('MMS file required'),
                    'message_important' => true
                ]);
            }

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name      = $image->getClientOriginalName();
                    $image_name      = str_replace(" ", "-", $image_name);
                    Input::file('image')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = null;
            if ($message == '') {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('Message required'),
                    'message_important' => true
                ]);
            }
        }


        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r(strtolower($file_extension), $supportedExt)) {
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true
            ]);
        }


        $period = $request->period;
        $its    = strtotime(date('Y-m-d'));

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
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('Schedule time required'),
                    'message_important' => true
                ]);
            }
            $nd = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        } else {
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Date Parsing Error'),
                'message_important' => true
            ]);
        }

        if ($period != '0') {

            if ($request->recurring_time == '') {
                return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                    'message' => language_data('Schedule time required'),
                    'message_important' => true
                ]);
            }

            $schedule_time = $request->recurring_time;
            $nd            = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
        }

        $all_data = Excel::load($request->import_numbers)->noHeading()->all()->toArray();

        if ($all_data && is_array($all_data) && array_empty($all_data)) {
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
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

        $all_data = array_map(function ($row) use ($header) {

            return array_combine($header, $row);

        }, $all_data);

        $valid_phone_numbers = [];
        $get_data            = [];

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
            return redirect('sms/send-recurring-sms-file')->withInput($request->all())->with([
                'message' => language_data('Invalid phone numbers'),
                'message_important' => true
            ]);
        }

        if ($request->remove_duplicate == 'yes') {
            $get_data = unique_multidim_array($get_data, $number_column);
        }

        $results = array_values($get_data);

        $total_recipients = count($results);

        $recurring_id = RecurringSMS::create([
            'userid' => 0,
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


                    $clphone = str_replace(['(', ')', '+', '-', ' '], '', $msg_data[$number_column]);
                    if ($request->country_code != 0) {
                        $clphone = $request->country_code . $clphone;
                    }

                    RecurringSMSContacts::create([
                        'campaign_id' => $recurring_id->id,
                        'receiver' => $clphone,
                        'message' => $get_message,
                        'amount' => $msgcount
                    ]);

                }
            }

            return redirect('sms/send-recurring-sms-file')->with([
                'message' => language_data('Message recurred successfully. Delivered in correct time')
            ]);
        } else {
            return redirect('sms/send-recurring-sms-file')->with([
                'message' => language_data('Something went wrong please try again'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // Version 2.4 (Two way communication)
    //======================================================================

    //======================================================================
    // customGatewayTwoWay Function Start Here
    //======================================================================
    public function customGatewayTwoWay($id)
    {

        $gateway = SMSGateways::select('name', 'id')->find($id);

        if ($gateway) {
            $two_way = TwoWayCommunication::where('gateway_id', $id)->first();
            if ($two_way) {
                $source_param      = $two_way->source_param;
                $destination_param = $two_way->destination_param;
                $message_param     = $two_way->message_param;

            } else {
                $source_param      = null;
                $destination_param = null;
                $message_param     = null;
            }

            return view('admin.two-way-communication', compact('gateway', 'source_param', 'destination_param', 'message_param'));
        } else {
            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('Something went wrong please try again'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postCustomGatewayTwoWay Function Start Here
    //======================================================================
    public function postCustomGatewayTwoWay(Request $request)
    {

        $gateway_id = $request->gateway_id;

        $request_data = $request->only('gateway_id', 'source_param', 'destination_param', 'message_param');

        $v = \Validator::make($request_data, [
            'source_param' => 'required', 'destination_param' => 'required', 'message_param' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('sms/custom-gateway-two-way/' . $gateway_id)->withInput($request->all())->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($gateway_id);

        if ($gateway) {
            $two_way = TwoWayCommunication::where('gateway_id', $gateway_id)->first();
            if ($two_way) {
                $two_way->source_param      = $request->source_param;
                $two_way->destination_param = $request->destination_param;
                $two_way->message_param     = $request->message_param;
                $two_way->save();
            } else {
                TwoWayCommunication::create($request_data);
            }

            return redirect('sms/custom-gateway-two-way/' . $gateway_id)->with([
                'message' => language_data('Gateway updated successfully')
            ]);
        } else {
            return redirect('sms/http-sms-gateway')->with([
                'message' => language_data('Something went wrong please try again'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // keywordSettings Function Start Here
    //======================================================================
    public function keywordSettings()
    {
        return view('admin.keyword-settings');
    }


    //======================================================================
    // postKeywordSettings Function Start Here
    //======================================================================
    public function postKeywordSettings(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('keywords/settings')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }


        $v = \Validator::make($request->all(), [
            'show_keyword_in_client' => 'required', 'opt_in_sms_keyword' => 'required', 'opt_out_sms_keyword' => 'required', 'custom_gateway_response_status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('keywords/settings')->withErrors($v->errors());
        }

        AppConfig::where('setting', '=', 'show_keyword_in_client')->update(['value' => $request->show_keyword_in_client]);
        AppConfig::where('setting', '=', 'opt_in_sms_keyword')->update(['value' => $request->opt_in_sms_keyword]);
        AppConfig::where('setting', '=', 'opt_out_sms_keyword')->update(['value' => $request->opt_out_sms_keyword]);
        AppConfig::where('setting', '=', 'custom_gateway_response_status')->update(['value' => $request->custom_gateway_response_status]);

        return redirect('keywords/settings')->with([
            'message' => language_data('Setting Update Successfully')
        ]);

    }


    //======================================================================
    // addKeyword Function Start Here
    //======================================================================
    public function addKeyword()
    {
        $clients = Client::where('status', 'Active')->get();
        return view('admin.add-new-keyword', compact('clients'));
    }

    //======================================================================
    // postNewKeyword Function Start Here
    //======================================================================
    public function postNewKeyword(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'title' => 'required', 'keyword_name' => 'required', 'status' => 'required', 'client' => 'required', 'price' => 'required', 'validity' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('keywords/add')->withErrors($v->errors());
        }

        if ($request->reply_text == '' && $request->reply_voice == '' && $request->reply_mms == '') {
            return redirect('keywords/add')->with([
                'message' => 'Reply message required',
                'message_important' => true
            ]);
        }

        $media_url = null;
        $image     = $request->reply_mms;

        if ($image != '') {

            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name      = $image->getClientOriginalName();
                    $image_name      = str_replace(" ", "-", $image_name);
                    Input::file('reply_mms')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('keywords/add')->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('keywords/add')->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        }

        $validity = $request->validity;
        $nd       = null;

        $status = $request->status;

        if ($request->client != 0) {
            $status = 'assigned';
        }

        if ($status == 'assigned') {
            $current_date = strtotime(date('Y-m-d'));
            if ($validity == 'month1') {
                $nd = date('Y-m-d', strtotime('+1 month', $current_date));
            } elseif ($validity == 'months2') {
                $nd = date('Y-m-d', strtotime('+2 months', $current_date));
            } elseif ($validity == 'months3') {
                $nd = date('Y-m-d', strtotime('+3 months', $current_date));
            } elseif ($validity == 'months6') {
                $nd = date('Y-m-d', strtotime('+6 months', $current_date));
            } elseif ($validity == 'year1') {
                $nd = date('Y-m-d', strtotime('+1 year', $current_date));
            } elseif ($validity == 'years2') {
                $nd = date('Y-m-d', strtotime('+2 years', $current_date));
            } elseif ($validity == 'years3') {
                $nd = date('Y-m-d', strtotime('+3 years', $current_date));
            } else {
                $nd = null;
            }
        }

        $check_existing = Keywords::where('user_id', $request->client)->where('keyword_name', $request->keyword_name)->first();

        if ($check_existing) {
            return redirect('keywords/add')->with([
                'message' => 'Keyword already exist',
                'message_important' => true
            ]);
        }

        $keyword = Keywords::create([
            'user_id' => $request->client,
            'title' => $request->title,
            'keyword_name' => $request->keyword_name,
            'reply_text' => $request->reply_text,
            'reply_voice' => $request->reply_voice,
            'reply_mms' => $media_url,
            'status' => $status,
            'price' => $request->price,
            'validity' => $validity,
            'validity_date' => $nd
        ]);

        if ($keyword) {
            return redirect('keywords/all')->with([
                'message' => 'Keyword added successfully'
            ]);
        }

        return redirect('keywords/add')->with([
            'message' => language_data('Something went wrong please try again'),
            'message_important' => true
        ]);
    }

    //======================================================================
    // allKeywords Function Start Here
    //======================================================================
    public function allKeywords()
    {
        return view('admin.all-keywords');
    }

    //======================================================================
    // getKeywordsData Function Start Here
    //======================================================================
    public function getKeywordsData()
    {

        $keywords = Keywords::query();
        return Datatables::of($keywords)
            ->addColumn('action', function ($kw) {

                $reply_url = '';

                if ($kw->reply_mms) {
                    $reply_url .= ' <a href="#" id="id_' . $kw->id . '" class="remove_mms btn btn-xs btn-primary"><i class="fa fa-remove"></i> Remove MMS</a>';
                }

                $reply_url .= '
                <a class="btn btn-success btn-xs" href="' . url("keywords/view/$kw->id") . '" ><i class="fa fa-edit"></i> ' . language_data('Manage') . '</a>
                <a href="#" id="' . $kw->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                ';

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
            ->escapeColumns([])
            ->make(true);
    }

    //======================================================================
    // viewKeyword Function Start Here
    //======================================================================
    public function viewKeyword($id)
    {
        $keyword = Keywords::find($id);
        if ($keyword) {
            $clients = Client::where('status', 'Active')->get();
            return view('admin.view-keyword', compact('keyword', 'clients'));
        }

        return redirect('keywords/all')->with([
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

        $v = \Validator::make($request->all(), [
            'title' => 'required', 'keyword_name' => 'required', 'status' => 'required', 'client' => 'required', 'price' => 'required', 'validity' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('keywords/view/' . $keyword_id)->withErrors($v->errors());
        }

        $keyword = Keywords::find($keyword_id);

        if (!$keyword) {
            return redirect('keywords/all')->with([
                'message' => 'Keyword information not found',
                'message_important' => true
            ]);
        }

        if ($keyword->keyword_name != $request->keyword_name) {
            $check_existing = Keywords::where('user_id', $request->client)->where('keyword_name', $request->keyword_name)->first();

            if ($check_existing) {
                return redirect('keywords/view/' . $keyword_id)->with([
                    'message' => 'Keyword already exist',
                    'message_important' => true
                ]);
            }
        }

        if ($request->reply_text == '' && $request->reply_voice == '' && $request->reply_mms == '') {
            return redirect('keywords/view/' . $keyword_id)->with([
                'message' => 'Reply message required',
                'message_important' => true
            ]);
        }

        $image = $request->reply_mms;

        if ($image != '') {
            if (app_config('AppStage') != 'Demo') {
                if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                    $destinationPath = public_path() . '/assets/mms_file/';
                    $image_name      = $image->getClientOriginalName();
                    $image_name      = str_replace(" ", "-", $image_name);
                    Input::file('reply_mms')->move($destinationPath, $image_name);
                    $media_url = asset('assets/mms_file/' . $image_name);

                } else {
                    return redirect('keywords/view/' . $keyword_id)->withInput($request->all())->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('keywords/view/' . $keyword_id)->withInput($request->all())->with([
                    'message' => language_data('MMS is disable in demo mode'),
                    'message_important' => true
                ]);
            }
        } else {
            $media_url = $keyword->reply_mms;
        }

        $validity = $request->validity;
        $nd       = null;

        $status = $request->status;

        if ($request->client != 0) {
            $status = 'assigned';
        }

        if ($status == 'assigned') {
            if ($validity != $keyword->validity) {
                $current_date = strtotime(date('Y-m-d'));
                if ($validity == 'month1') {
                    $nd = date('Y-m-d', strtotime('+1 month', $current_date));
                } elseif ($validity == 'months2') {
                    $nd = date('Y-m-d', strtotime('+2 months', $current_date));
                } elseif ($validity == 'months3') {
                    $nd = date('Y-m-d', strtotime('+3 months', $current_date));
                } elseif ($validity == 'months6') {
                    $nd = date('Y-m-d', strtotime('+6 months', $current_date));
                } elseif ($validity == 'year1') {
                    $nd = date('Y-m-d', strtotime('+1 year', $current_date));
                } elseif ($validity == 'years2') {
                    $nd = date('Y-m-d', strtotime('+2 years', $current_date));
                } elseif ($validity == 'years3') {
                    $nd = date('Y-m-d', strtotime('+3 years', $current_date));
                } else {
                    $nd = null;
                }
            } else {
                $nd = $keyword->validity_date;
            }
        }


        $keyword = Keywords::where('id', $keyword_id)->update([
            'user_id' => $request->client,
            'title' => $request->title,
            'keyword_name' => $request->keyword_name,
            'reply_text' => $request->reply_text,
            'reply_voice' => $request->reply_voice,
            'reply_mms' => $media_url,
            'status' => $request->status,
            'price' => $request->price,
            'validity' => $validity,
            'validity_date' => $nd
        ]);

        if ($keyword) {
            return redirect('keywords/all')->with([
                'message' => 'Keyword updated successfully'
            ]);
        }

        return redirect('keywords/view/' . $keyword_id)->with([
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
            return redirect('keywords/all')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $keyword_id = explode('_', $id);
        if (isset($keyword_id) && is_array($keyword_id) && array_key_exists('1', $keyword_id)) {
            $keyword = Keywords::find($keyword_id['1']);

            if ($keyword) {
                $keyword->reply_mms = null;
                $keyword->save();

                return redirect('keywords/all')->with([
                    'message' => 'MMS file remove successfully'
                ]);
            }

            return redirect('keywords/all')->with([
                'message' => 'Keyword information not found',
                'message_important' => true
            ]);
        }

        return redirect('keywords/all')->with([
            'message' => 'Invalid request',
            'message_important' => true
        ]);
    }

    //======================================================================
    // deleteKeyword Function Start Here
    //======================================================================
    public function deleteKeyword($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('keywords/all')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }
        $keyword = Keywords::find($id);

        if ($keyword) {
            $keyword->delete();
            return redirect('keywords/all')->with([
                'message' => 'Keyword deleted successfully'
            ]);
        }

        return redirect('keywords/all')->with([
            'message' => 'Keyword information not found',
            'message_important' => true
        ]);
    }

    //======================================================================
    // campaignReports Function Start Here
    //======================================================================
    public function campaignReports()
    {
        return view('admin.campaign-reports');
    }

    //======================================================================
    // getCampaignReports Function Start Here
    //======================================================================
    public function getCampaignReports(Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];

            if ($get_search_column == 'campaign_id') {
                $get_search_column = 'updated_at';
            }

        } else {
            $get_search_column = 'updated_at';
            $short_by          = 'DESC';
        }

        $campaigns = Campaigns::orderBy($get_search_column, $short_by)->getQuery();
        return Datatables::of($campaigns)
            ->addColumn('action', function ($campaign) {
                return '
                <a class="btn btn-success btn-xs" href="' . url("sms/manage-campaign/$campaign->id") . '" ><i class="fa fa-line-chart"></i> ' . language_data('Reports') . '</a>
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
                    $date_to   = date('Y-m-d H:i:s', strtotime($request->get('date_to')));
                    $query->whereBetween('updated_at', [$date_from, $date_to]);
                }
            })
            ->addColumn('user_id', function ($campaign) {
                $user_id = 'Admin';
                if ($campaign->user_id != 0) {
                    $user    = client_info($campaign->user_id);
                    $user_id = $user->fname . ' ' . $user->lname;
                }

                return $user_id;
            })
            ->escapeColumns([])
            ->make(true);

    }


    //======================================================================
    // manageCampaign Function Start Here
    //======================================================================
    public function manageCampaign($id)
    {

        $campaign = Campaigns::find($id);

        if ($campaign) {

            if ($campaign->camp_type == 'regular') {
                $queued = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->where('status', 'queued')->count();
            } else {
                $queued = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->where('status', 'scheduled')->count();
            }
            $keyword           = Keywords::where('user_id', 0)->get();
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


            return view('admin.manage-campaign-reports', compact('campaign', 'campaign_chart', 'queued', 'keyword', 'selected_keywords'));
        }

        return redirect('sms/campaign-reports')->with([
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
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];

            if ($get_search_column == 'number') {
                $get_search_column = 'updated_at';
            }

        } else {
            $get_search_column = 'updated_at';
            $short_by          = 'DESC';
        }

        $campaign_list = CampaignSubscriptionList::where('campaign_id', $id)->orderBy($get_search_column, $short_by)->getQuery();
        return Datatables::of($campaign_list)
            ->addColumn('action', function ($campaign) {
                $url = '';

                if ($campaign->submitted_time != null && $campaign->status == 'scheduled' && new \DateTime() < new \DateTime($campaign->submitted_time)) {
                    $url .= '
                <a class="btn btn-success btn-xs" href="' . url("sms/manage-update-schedule-sms/$campaign->id") . '" ><i class="fa fa-clock-o"></i> ' . language_data('Manage') . '</a>
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
            return redirect('sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $campaign_id = $request->campaign_id;
        $campaign    = Campaigns::find($campaign_id);

        if ($campaign) {
            if ($campaign->status == 'Delivered') {
                return redirect('sms/campaign-reports')->with([
                    'message' => 'Campaign already delivered',
                    'message_important' => true
                ]);
            }

            $v = \Validator::make($request->all(), [
                'status' => 'required'
            ]);

            if ($v->fails()) {
                return redirect('sms/manage-campaign/' . $campaign_id)->withErrors($v->errors());
            }


            $gateway = SMSGateways::find($campaign->use_gateway);
            if ($gateway->status != 'Active') {
                return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                    'message' => language_data('SMS gateway not active'),
                    'message_important' => true
                ]);
            }


            $keywords = $request->keyword;

            if ($keywords) {
                if ($gateway->two_way != 'Yes') {
                    return redirect('sms/manage-campaign/' . $campaign_id)->with([
                        'message' => 'SMS Gateway not supported Two way or Receiving feature',
                        'message_important' => true
                    ]);
                }

                if (isset($keywords) && is_array($keywords)) {
                    $keywords = implode("|", $keywords);
                } else {
                    return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => 'Invalid keyword selection',
                        'message_important' => true
                    ]);
                }
            }

            $media_url = $campaign->media_url;

            if ($campaign->sms_type == 'mms') {

                if ($gateway->mms != 'Yes') {
                    return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => language_data('SMS Gateway not supported MMS feature'),
                        'message_important' => true
                    ]);
                }

                $image = $request->image;

                if ($image) {

                    if ($image == '') {
                        return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                            'message' => language_data('MMS file required'),
                            'message_important' => true
                        ]);
                    }

                    if (app_config('AppStage') != 'Demo') {
                        if (isset($image) && in_array(strtolower($image->getClientOriginalExtension()), array("png", "jpeg", "gif", 'jpg', 'mp3', 'mp4', '3gp', 'mpg', 'mpeg'))) {
                            $destinationPath = public_path() . '/assets/mms_file/';
                            $image_name      = $image->getClientOriginalName();
                            $image_name      = str_replace(" ", "-", $image_name);
                            Input::file('image')->move($destinationPath, $image_name);
                            $media_url = asset('assets/mms_file/' . $image_name);

                        } else {
                            return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                                'message' => language_data('Upload .png or .jpeg or .jpg or .gif or .mp3 or .mp4 or .3gp or .mpg or .mpeg file'),
                                'message_important' => true
                            ]);
                        }

                    } else {
                        return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                            'message' => language_data('MMS is disable in demo mode'),
                            'message_important' => true
                        ]);
                    }
                }
            }


            $schedule_time = $campaign->run_at;

            if ($campaign->camp_type == 'scheduled') {

                if ($request->schedule_time == '') {
                    return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
                        'message' => language_data('Schedule time required'),
                        'message_important' => true
                    ]);
                }

                $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));

                if (new \DateTime() > new \DateTime($schedule_time)) {
                    return redirect('sms/manage-campaign/' . $campaign_id)->withInput($request->all())->with([
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

            $campaign->status    = $status;
            $campaign->media_url = $media_url;
            $campaign->keyword   = $keywords;
            $campaign->run_at    = $schedule_time;

            $campaign->save();

            return redirect('sms/manage-campaign/' . $campaign_id)->with([
                'message' => 'Campaign updated successfully'
            ]);
        }

        return redirect('sms/campaign-reports')->with([
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
            return redirect('sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids    = explode(',', $request->get('data_ids'));
            $recipients = count($all_ids);
            if ($request->has('campaign_id')) {
                if (isset($all_ids) && is_array($all_ids) && count($all_ids) > 0) {
                    CampaignSubscriptionList::destroy($all_ids);
                    $campaign                  = Campaigns::find($request->campaign_id);
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
            return redirect('sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $recipient = CampaignSubscriptionList::find($id);
        if ($recipient) {
            $campaign = Campaigns::where('campaign_id', $recipient->campaign_id)->first();
            if ($campaign) {

                if ($campaign->user_id != 0) {

                    $msg_type = $campaign->sms_type;

                    if ($recipient->status == 'queued') {

                        $phone   = $recipient->number;
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

                            $client            = Client::find($campaign->user_id);
                            $client->sms_limit += $cost;
                            $client->save();
                        }
                    }
                }

                $campaign->total_recipient -= 1;
                $campaign->save();

                $recipient->delete();
                return redirect('sms/manage-campaign/' . $campaign->id)->with([
                    'message' => 'Recipient deleted successfully'
                ]);
            }
            return redirect('sms/campaign-reports')->with([
                'message' => 'Campaign info not found',
                'message_important' => true
            ]);
        }
        return redirect('sms/campaign-reports')->with([
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
            return redirect('sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        if ($request->has('data_ids')) {
            $all_ids = explode(',', $request->get('data_ids'));

            if (is_array($all_ids) && count($all_ids) > 0) {
                foreach ($all_ids as $id) {
                    $campaign = Campaigns::find($id);
                    if ($campaign) {

                        if ($campaign->user_id != 0) {

                            $msg_type = $campaign->sms_type;

                            $recipients = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->get();

                            foreach ($recipients as $recipient) {

                                if ($recipient->status == 'queued') {

                                    $phone   = $recipient->number;
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

                                        $client            = Client::find($campaign->user_id);
                                        $client->sms_limit += $cost;
                                        $client->save();
                                    }
                                }
                                $recipient->delete();
                            }
                        } else {
                            CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->delete();
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
            return redirect('sms/campaign-reports')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $campaign = Campaigns::find($id);
        if ($campaign) {

            if ($campaign->user_id != 0) {

                $msg_type = $campaign->sms_type;

                $recipients = CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->get();

                foreach ($recipients as $recipient) {

                    if ($recipient->status == 'queued') {

                        $phone   = $recipient->number;
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

                            $client            = Client::find($campaign->user_id);
                            $client->sms_limit += $cost;
                            $client->save();
                        }
                    }
                    $recipient->delete();
                }

            } else {
                CampaignSubscriptionList::where('campaign_id', $campaign->campaign_id)->delete();
            }

            $campaign->delete();

            return redirect('sms/campaign-reports')->with([
                'message' => 'Campaign deleted successfully'
            ]);

        }

        return redirect('sms/campaign-reports')->with([
            'message' => 'Campaign info not found',
            'message_important' => true
        ]);
    }

}
