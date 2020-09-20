<?php

namespace App\Http\Controllers;

use App\Admin;
use App\CampaignSubscriptionList;
use App\Classes\Permission;
use App\Client;
use App\ClientGroups;
use App\Invoices;
use App\SMSHistory;
use App\SupportTickets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // dashboard Function Start Here
    //======================================================================
    public function dashboard()
    {
        $self = 'dashboard';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        //For Invoice chart

        $inv_unpaid         = Invoices::where('status', 'Unpaid')->count();
        $inv_paid           = Invoices::where('status', 'Paid')->count();
        $inv_cancelled      = Invoices::where('status', 'Cancelled')->count();
        $inv_partially_paid = Invoices::where('status', 'Partially Paid')->count();
        $total_invoice      = $inv_unpaid + $inv_paid + $inv_partially_paid + $inv_cancelled;

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

        $st_pending  = SupportTickets::where('status', 'Pending')->count();
        $st_answered = SupportTickets::where('status', 'Answered')->count();
        $st_replied  = SupportTickets::where('status', 'Customer Reply')->count();
        $st_closed   = SupportTickets::where('status', 'Closed')->count();

        $total_tickets = $st_pending + $st_answered + $st_replied + $st_closed;

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

        $sms_count   = SMSHistory::count();
        $sms_pending_count = CampaignSubscriptionList::where('status','scheduled')->orWhere('status', 'queued')->count();
        $sms_success = SMSHistory::where('status', 'like', '%Success%')->count();
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
        $day_10 = get_date_format(Carbon::now(app_config('Timezone'))->subDays(9));
        $day_9  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(8));
        $day_8  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(7));
        $day_7  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(6));
        $day_6  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(5));
        $day_5  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(4));
        $day_4  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(3));
        $day_3  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(2));
        $day_2  = get_date_format(Carbon::now(app_config('Timezone'))->subDays(1));
        $day_1  = get_date_format(Carbon::now(app_config('Timezone')));

        $day_10_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_10))->count();
        $day_10_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_10))->count();

        $day_9_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_9))->count();
        $day_9_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_9))->count();

        $day_8_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_8))->count();
        $day_8_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_8))->count();

        $day_7_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_7))->count();
        $day_7_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_7))->count();

        $day_6_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_6))->count();
        $day_6_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_6))->count();

        $day_5_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_5))->count();
        $day_5_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_5))->count();

        $day_4_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_4))->count();
        $day_4_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_4))->count();

        $day_3_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_3))->count();
        $day_3_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_3))->count();

        $day_2_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_2))->count();
        $day_2_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_2))->count();

        $day_1_count_inbound  = SMSHistory::where('send_by', 'receiver')->whereDay('created_at', get_mysql_date($day_1))->count();
        $day_1_count_outbound = SMSHistory::where('send_by', 'sender')->whereDay('created_at', get_mysql_date($day_1))->count();


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

        $recent_five_invoices = Invoices::orderBy('id', 'desc')->take(5)->get();
        $recent_five_tickets  = SupportTickets::orderBy('id', 'desc')->take(5)->get();
        $total_clients        = Client::count();
        $total_groups         = ClientGroups::count();

        return view('admin.dashboard', compact('invoices_json', 'sms_history', 'tickets_json', 'sms_status_json', 'recent_five_invoices', 'recent_five_tickets', 'total_clients', 'total_groups', 'total_invoice', 'total_tickets'));
    }

    //======================================================================
    // menuOpenStatus Function Start Here
    //======================================================================
    public function menuOpenStatus()
    {
        $admin = Admin::find(Auth::user()->id);
        if ($admin->menu_open == 0) {
            $admin->menu_open = '1';
        } else {
            $admin->menu_open = '0';
        }
        $admin->save();
    }


    //======================================================================
    // logout Function Start Here
    //======================================================================
    public function logout()
    {
        Auth::logout();
        return redirect('admin')->with([
            'message' => language_data('Logout Successfully')
        ]);

    }

    //======================================================================
    // editProfile Function Start Here
    //======================================================================
    public function editProfile()
    {
        $admin = admin_info(Auth::user()->id);
        return view('admin.edit-profile', compact('admin'));
    }


    /* postPersonalInfo  Function Start Here */
    public function postPersonalInfo(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin/edit-profile')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'fname' => 'required', 'email' => 'required|email'
        ]);

        if ($v->fails()) {
            return redirect('admin/edit-profile')->withErrors($v->errors());
        }

        $admin = Admin::find(Auth::user()->id);

        $email       = $request->email;
        $exist_email = $admin->email;

        if ($email != '' AND $email != $exist_email) {
            $exist = Admin::where('email', '=', $email)->first();
            if ($exist) {
                return redirect('admin/edit-profile')->with([
                    'message' => language_data('Email already exist'),
                    'message_important' => true
                ]);
            }
        }
        $admin->fname = $request->fname;
        $admin->lname = $request->lname;
        $admin->email = $email;
        $admin->save();

        return redirect('admin/edit-profile')->with([
            'message' => language_data('Profile Updated Successfully')
        ]);

    }

    //======================================================================
    // updateAvatar Function Start Here
    //======================================================================
    public function updateAvatar(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin/edit-profile')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif'
        ]);

        if ($v->fails()) {
            return redirect('admin/edit-profile')->withErrors($v->errors());
        }

        $image = Input::file('image');
        $admin = Admin::find(Auth::user()->id);


        if ($admin) {
            if ($image != '') {

                if (isset($image) && in_array($image->getClientOriginalExtension(), array("png", "jpeg", "gif", 'jpg'))) {
                    $destinationPath = public_path() . '/assets/admin_pic/';
                    $image_name      = $image->getClientOriginalName();
                    Input::file('image')->move($destinationPath, $image_name);

                    $admin->image = $image_name;
                    $admin->save();

                    return redirect('admin/edit-profile')->with([
                        'message' => language_data('Image updated successfully')
                    ]);
                } else {
                    return redirect('admin/edit-profile')->with([
                        'message' => language_data('Upload .png or .jpeg or .jpg or .gif file'),
                        'message_important' => true
                    ]);
                }

            } else {
                return redirect('admin/edit-profile')->with([
                    'message' => language_data('Upload an Image'),
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
        return view('admin.change-password');
    }

    //======================================================================
    // updatePassword Function Start Here
    //======================================================================
    public function updatePassword(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin/change-password')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'current_password' => 'required', 'new_password' => 'required', 'confirm_password' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('admin/change-password')->withErrors($v->errors());
        }

        $user = Admin::find(Auth::user()->id);

        $current_password = Input::get('current_password');
        $new_password     = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');

        if (Hash::check($current_password, $user->password)) {

            if ($new_password == $confirm_password) {
                $user->password = bcrypt($new_password);
                $user->save();

                return redirect('admin/change-password')->with([
                    'message' => language_data('Password Change Successfully')
                ]);

            } else {
                return redirect('admin/change-password')->with([
                    'message' => language_data('Both password does not match'),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('admin/change-password')->with([
                'message' => language_data('Current Password Does Not Match'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // updateApplication Function Start Here
    //======================================================================
    public function updateApplication()
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('dashboard')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        return view('admin.update-application');
    }

    //======================================================================
    // backupDatabase Function Start Here
    //======================================================================
    public function backupDatabase()
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('dashboard')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        try {
            $status = \Artisan::call('backup:run');

            if ($status) {
                return redirect('admin/update-application')->with([
                    'message' => 'Database backup successfully done'
                ]);
            } else {
                return redirect('admin/update-application')->with([
                    'message' => language_data('Something wrong, Please contact with your provider'),
                    'message_important' => true
                ]);
            }

        } catch (\Exception $exception) {
            return redirect('admin/update-application')->with([
                'message' => $exception->getMessage(),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postUpdateApplication Function Start Here
    //======================================================================
    public function postUpdateApplication(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('dashboard')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'update_file' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('admin/update-application')->withErrors($v->errors());
        }

        $update_file   = Input::file('update_file');
        $get_extension = $update_file->getClientOriginalExtension();

        if ($get_extension !== 'zip') {
            return redirect('admin/update-application')->with([
                'message' => 'Invalid file type',
                'message_important' => true
            ]);
        }


        if (!class_exists('ZipArchive')) {
            return redirect('admin/update-application')->with([
                'message' => 'Your PHP version does not support unzip functionality',
                'message_important' => true
            ]);
        }

        $zip = new \ZipArchive();

        // Check if archive is readable.
        if ($zip->open($update_file) === TRUE) {
            // Check if destination is writable
            if (is_writeable(base_path() . '/')) {
                $zip->extractTo(base_path());
                $zip->close();

                Auth::logout();

                return redirect('admin')->with([
                    'message' => 'Application updated successfully. Please login with your admin access'
                ]);

            } else {
                return redirect('admin/update-application')->with([
                    'message' => 'Directory not writeable by webserver. Please set write permission of you installation folder',
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('admin/update-application')->with([
                'message' => 'Cannot read .zip archive. Please try again',
                'message_important' => true
            ]);

        }

    }


}
