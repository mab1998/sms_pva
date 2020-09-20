<?php

namespace App\Http\Controllers;


use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Client;
use App\Invoices;
use App\Language;
use App\SMSHistory;
use App\SupportTickets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class ClientDashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('client');
    }


    //======================================================================
    // dashboard Function Start Here
    //======================================================================
    public function dashboard()
    {

        //For Invoice chart

        $inv_unpaid         = Invoices::where('status', 'Unpaid')->where('cl_id', Auth::guard('client')->user()->id)->count();
        $inv_paid           = Invoices::where('status', 'Paid')->where('cl_id', Auth::guard('client')->user()->id)->count();
        $inv_cancelled      = Invoices::where('status', 'Cancelled')->where('cl_id', Auth::guard('client')->user()->id)->count();
        $inv_partially_paid = Invoices::where('status', 'Partially Paid')->where('cl_id', Auth::guard('client')->user()->id)->count();

        $invoices_json = app()->chartjs
            ->name('invoiceChart')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Unpaid', 'Paid', 'Cancelled', 'Partially Paid'])
            ->datasets([
                [
                    'backgroundColor' => ['#F0AD4E', '#30DDBC', '#D9534F', '#5BC0DE'],
                    'hoverBackgroundColor' => ['#F0AD4E', '#30DDBC', '#D9534F', '#5BC0DE'],
                    'data' => [$inv_unpaid, $inv_paid, $inv_cancelled, $inv_partially_paid]
                ]
            ])
            ->options([
                'legend' => ['display' => false]
            ]);


        //For Support Ticket Chart

        $st_pending  = SupportTickets::where('status', 'Pending')->where('cl_id', Auth::guard('client')->user()->id)->count();
        $st_answered = SupportTickets::where('status', 'Answered')->where('cl_id', Auth::guard('client')->user()->id)->count();
        $st_replied  = SupportTickets::where('status', 'Customer Reply')->where('cl_id', Auth::guard('client')->user()->id)->count();
        $st_closed   = SupportTickets::where('status', 'Closed')->where('cl_id', Auth::guard('client')->user()->id)->count();


        $tickets_json = app()->chartjs
            ->name('supportTicketChart')
            ->type('doughnut')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Pending', 'Answered', 'Customer Reply', 'Closed'])
            ->datasets([
                [
                    'backgroundColor' => ['#d9534f', '#30DDBC', '#5bc0de', '#7E57C2'],
                    'hoverBackgroundColor' => ['#d9534f', '#30DDBC', '#5bc0de', '#7E57C2'],
                    'data' => [$st_pending, $st_answered, $st_replied, $st_closed]
                ]
            ])
            ->options([
                'legend' => ['display' => false]
            ]);


        //For SMS Status Chart


        //For SMS Status Chart

        $sms_count   = SMSHistory::where('userid', Auth::guard('client')->user()->id)->count();
        $sms_success = SMSHistory::where('userid', Auth::guard('client')->user()->id)->where('status', 'like', '%Success%')->count();

        $get_campaign = Campaigns::where('user_id', Auth::guard('client')->user()->id)->where('status', '!=', 'Delivered')->select('campaign_id')->get()->toArray();

        $get_campaign_ids = array_column($get_campaign, 'campaign_id');

        $sms_pending_count = CampaignSubscriptionList::whereIn('campaign_id', $get_campaign_ids)->where('status','scheduled')->orWhere('status', 'queued')->count();
        $sms_failed  = $sms_count - $sms_success;

        $sms_status_json = app()->chartjs
            ->name('smsStatusChat')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Success', 'Failed', 'Pending'])
            ->datasets([
                [
                    'backgroundColor' => ['#30DDBC', '#F95F5B', '#F0AD4E'],
                    'hoverBackgroundColor' => ['#30DDBC', '#F95F5B', '#F0AD4E'],
                    'data' => [$sms_success, $sms_failed, $sms_pending_count]
                ]
            ])
            ->options([
                'legend' => ['display' => false]
            ]);

        //For SMS History Chart
        $day_10 = Carbon::now(config('app.timezone'))->subDays(9)->format("Y-m-d");
        $day_9  = Carbon::now(config('app.timezone'))->subDays(8)->format("Y-m-d");
        $day_8  = Carbon::now(config('app.timezone'))->subDays(7)->format("Y-m-d");
        $day_7  = Carbon::now(config('app.timezone'))->subDays(6)->format("Y-m-d");
        $day_6  = Carbon::now(config('app.timezone'))->subDays(5)->format("Y-m-d");
        $day_5  = Carbon::now(config('app.timezone'))->subDays(4)->format("Y-m-d");
        $day_4  = Carbon::now(config('app.timezone'))->subDays(3)->format("Y-m-d");
        $day_3  = Carbon::now(config('app.timezone'))->subDays(2)->format("Y-m-d");
        $day_2  = Carbon::now(config('app.timezone'))->subDays(1)->format("Y-m-d");
        $day_1  = Carbon::now(config('app.timezone'))->format("Y-m-d");

        $day_10_from = $day_10.' 00:00:00';
        $day_10_to = $day_10.' 23:59:59';


        $day_9_from = $day_9.' 00:00:00';
        $day_9_to = $day_9.' 23:59:59';


        $day_8_from = $day_8.' 00:00:00';
        $day_8_to = $day_8.' 23:59:59';


        $day_7_from = $day_7.' 00:00:00';
        $day_7_to = $day_7.' 23:59:59';


        $day_6_from = $day_6.' 00:00:00';
        $day_6_to = $day_6.' 23:59:59';


        $day_5_from = $day_5.' 00:00:00';
        $day_5_to = $day_5.' 23:59:59';


        $day_4_from = $day_4.' 00:00:00';
        $day_4_to = $day_4.' 23:59:59';


        $day_3_from = $day_3.' 00:00:00';
        $day_3_to = $day_3.' 23:59:59';


        $day_2_from = $day_2.' 00:00:00';
        $day_2_to = $day_2.' 23:59:59';


        $day_1_from = $day_1.' 00:00:00';
        $day_1_to = $day_1.' 23:59:59';


        $get_reseller = Client::where('parent', Auth::guard('client')->user()->id)->select('id')->get()->toArray();
        $reseller_ids = array_column($get_reseller, 'id');
        $reseller_ids[]=Auth::guard('client')->user()->id;

        $day_10_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_10_from,$day_10_to])->count();
        $day_10_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_10_from,$day_10_to])->count();

        $day_9_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_9_from,$day_9_to])->count();
        $day_9_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_9_from,$day_9_to])->count();

        $day_8_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_8_from,$day_8_to])->count();
        $day_8_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_8_from,$day_8_to])->count();

        $day_7_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_7_from,$day_7_to])->count();
        $day_7_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_7_from,$day_7_to])->count();

        $day_6_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_6_from,$day_6_to])->count();
        $day_6_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_6_from,$day_6_to])->count();

        $day_5_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_5_from,$day_5_to])->count();
        $day_5_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_5_from,$day_5_to])->count();

        $day_4_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_4_from,$day_4_to])->count();
        $day_4_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_4_from,$day_4_to])->count();

        $day_3_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_3_from,$day_3_to])->count();
        $day_3_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_3_from,$day_3_to])->count();

        $day_2_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_2_from,$day_2_to])->count();
        $day_2_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_2_from,$day_2_to])->count();

        $day_1_count_inbound  = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'receiver')->whereBetween('updated_at', [$day_1_from,$day_1_to])->count();
        $day_1_count_outbound = SMSHistory::whereIn('userid', $reseller_ids)->where('send_by', 'sender')->whereBetween('updated_at', [$day_1_from,$day_1_to])->count();


        $sms_history = app()->chartjs
            ->name('smsHistoryChart')
            ->type('line')
            ->size(['width' => 200, 'height' => 50])
            ->labels([$day_10, $day_9, $day_8, $day_7, $day_6, $day_5, $day_4, $day_3, $day_2, $day_1])
            ->datasets([
                [
                    "label" => "Outbound",
                    'backgroundColor' => "rgba(0, 51, 102, 0.5)",
                    'borderColor' => "rgba(0, 51, 102, 0.8)",
                    "pointBorderColor" => "rgba(0, 51, 102, 0.8)",
                    "pointBackgroundColor" => "rgba(0, 51, 102, 0.8)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    'data' => [$day_10_count_outbound, $day_9_count_outbound, $day_8_count_outbound, $day_7_count_outbound, $day_6_count_outbound, $day_5_count_outbound, $day_4_count_outbound, $day_3_count_outbound, $day_2_count_outbound, $day_1_count_outbound],
                ],
                [
                    "label" => "Inbound",
                    'backgroundColor' => "rgba(233, 114, 76, 0.5)",
                    'borderColor' => "rgba(233, 114, 76, 0.8)",
                    "pointBorderColor" => "rgba(233, 114, 76, 0.8)",
                    "pointBackgroundColor" => "rgba(233, 114, 76, 0.8)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    'data' => [$day_10_count_inbound, $day_9_count_inbound, $day_8_count_inbound, $day_7_count_inbound, $day_6_count_inbound, $day_5_count_inbound, $day_4_count_inbound, $day_3_count_inbound, $day_2_count_inbound, $day_1_count_inbound],
                ]
            ])
            ->options([
                'legend' => ['display' => false]
            ]);


        $recent_five_invoices = Invoices::orderBy('id', 'desc')->where('cl_id', Auth::guard('client')->user()->id)->take(5)->get();
        $recent_five_tickets  = SupportTickets::orderBy('id', 'desc')->where('cl_id', Auth::guard('client')->user()->id)->take(5)->get();

        return view('client.dashboard', compact('invoices_json', 'sms_history', 'tickets_json', 'sms_status_json', 'recent_five_invoices', 'recent_five_tickets'));
    }
    //======================================================================
    // menuOpenStatus Function Start Here
    //======================================================================
    public function menuOpenStatus()
    {
        $client = Client::find(Auth::guard('client')->user()->id);
        if ($client->menu_open == 0) {
            $client->menu_open = '1';
        } else {
            $client->menu_open = '0';
        }
        $client->save();
    }

    //======================================================================
    // logout Function Start Here
    //======================================================================
    public function logout()
    {
        Auth::guard('client')->logout();
        return redirect('/')->with([
            'message' => language_data('Logout Successfully')
        ]);

    }

    //======================================================================
    // editProfile Function Start Here
    //======================================================================
    public function editProfile()
    {
        $client = client_info(Auth::guard('client')->user()->id);
        return view('client.edit-profile', compact('client'));
    }


    /* postPersonalInfo  Function Start Here */
    public function postPersonalInfo(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/edit-profile')->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'phone' => 'required', 'country' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/edit-profile')->withInput($request->all())->withErrors($v->errors());
        }

        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client->email != $request->email) {
            $exist_user_email = Client::where('email', $request->email)->first();
            if ($exist_user_email) {
                return redirect('user/edit-profile')->withInput($request->all())->with([
                    'message' => language_data('Email already exist', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        }

        $client->fname    = $request->first_name;
        $client->lname    = $request->last_name;
        $client->company  = $request->company;
        $client->website  = $request->website;
        $client->email    = $request->email;
        $client->address1 = $request->address;
        $client->address2 = $request->more_address;
        $client->state    = $request->state;
        $client->city     = $request->city;
        $client->postcode = $request->postcode;
        $client->country  = $request->country;
        $client->phone    = $request->phone;
        $client->save();

        return redirect('user/edit-profile')->with([
            'message' => language_data('Profile Updated Successfully', Auth::guard('client')->user()->lan_id)
        ]);

    }

//======================================================================
// updateAvatar Function Start Here
//======================================================================
    public function updateAvatar(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/edit-profile')->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('user/edit-profile')->withErrors($v->errors());
        }

        $image  = Input::file('image');
        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client) {
            if ($image != '') {

                if (isset($image) && in_array($image->getClientOriginalExtension(), array("png", "jpeg", "gif", 'jpg'))) {
                    $destinationPath = public_path() . '/assets/client_pic/';
                    $image_name      = $image->getClientOriginalName();
                    Input::file('image')->move($destinationPath, $image_name);

                    $client->image = $image_name;
                    $client->save();

                    return redirect('user/edit-profile')->with([
                        'message' => language_data('Image updated successfully', Auth::guard('client')->user()->lan_id)
                    ]);
                } else {
                    return redirect('user/edit-profile')->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif file', Auth::guard('client')->user()->lan_id),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('user/edit-profile')->with([
                    'message' => language_data('Upload an Image', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }
        } else {
            return $this->logout();
        }
    }

    /* changePassword  Function Start Here */
    public function changePassword()
    {
        return view('client.change-password');
    }

//======================================================================
// updatePassword Function Start Here
//======================================================================
    public function updatePassword(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/change-password')->with([
                'message' => language_data('This Option is Disable In Demo Mode', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'current_password' => 'required', 'new_password' => 'required', 'confirm_password' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/change-password')->withErrors($v->errors());
        }

        $user = Client::find(Auth::guard('client')->user()->id);

        $current_password = Input::get('current_password');
        $new_password     = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');

        if (Hash::check($current_password, $user->password)) {

            if ($new_password == $confirm_password) {
                $user->password = bcrypt($new_password);
                $user->save();

                return redirect('user/change-password')->with([
                    'message' => language_data('Password Change Successfully', Auth::guard('client')->user()->lan_id)
                ]);

            } else {
                return redirect('user/change-password')->with([
                    'message' => language_data('Both password does not match', Auth::guard('client')->user()->lan_id),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('user/change-password')->with([
                'message' => language_data('Current Password Does Not Match', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // changeLanguage Function Start Here
    //======================================================================
    public function changeLanguage($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('dashboard')->with([
                'message' => 'This Option Is Disable In Demo Mode',
                'message_important' => true
            ]);
        }

        $lang = Language::find($id);

        if ($lang) {
            Client::where('id', Auth::guard('client')->user()->id)->update(['lan_id' => $id]);
            return redirect('dashboard')->with([
                'message' => language_data('Language updated Successfully', Auth::guard('client')->user()->lan_id)
            ]);
        } else {
            return redirect('admin/dashboard')->with([
                'message' => language_data('Language not found', Auth::guard('client')->user()->lan_id),
                'message_important' => true
            ]);
        }
    }


}
