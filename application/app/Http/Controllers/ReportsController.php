<?php

namespace App\Http\Controllers;

use App\BlackListContact;
use App\BlockMessage;
use App\Campaigns;
use App\CampaignSubscriptionList;
use App\Classes\Permission;
use App\Classes\PhoneNumber;
use App\Client;
use App\ContactList;
use App\CustomSMSGateways;
use App\IntCountryCodes;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\Operator;
use App\ScheduleSMS;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\ApiException;
use SMSGatewayMe\Client\Configuration;
use Yajra\Datatables\Datatables;

class ReportsController extends Controller
{

    /**
     * ReportsController constructor.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // smsHistory Function Start Here
    //======================================================================
    public function smsHistory()
    {

        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        return view('admin.sms-history');
    }

    //======================================================================
    // smsViewInbox Function Start Here
    //======================================================================
    public function smsViewInbox($id)
    {

        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $inbox_info = SMSHistory::find($id);

        if ($inbox_info) {
            return view('admin.sms-inbox', compact('inbox_info'));
        } else {
            return redirect('sms/history')->with([
                'message' => language_data('SMS Not Found'),
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
            return redirect('sms/history')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $inbox_info = SMSHistory::find($id);

        if ($inbox_info) {
            $inbox_info->delete();
            return redirect('sms/history')->with([
                'message' => language_data('SMS info deleted successfully')
            ]);

        } else {
            return redirect('sms/history')->with([
                'message' => language_data('SMS Not Found'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // getSmsHistoryData Function Start Here
    //======================================================================
    public function getSmsHistoryData(Request $request)
    {

        if ($request->has('order') && $request->has('columns')) {
            $order_col_num     = $request->get('order')[0]['column'];
            $get_search_column = $request->get('columns')[$order_col_num]['name'];
            $short_by          = $request->get('order')[0]['dir'];

            if ($get_search_column == 'date') {
                $get_search_column = 'updated_at';
            }

        } else {
            $get_search_column = 'updated_at';
            $short_by          = 'DESC';
        }

        $sms_history = SMSHistory::orderBy($get_search_column, $short_by)->getQuery();
        return Datatables::of($sms_history)
            ->addColumn('action', function ($sms) {
                return '
                <a class="btn btn-success btn-xs" href="' . url("sms/view-inbox/$sms->id") . '" ><i class="fa fa-inbox"></i> ' . language_data('Inbox') . '</a>
                <a href="#" id="' . $sms->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
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
                    $date_to   = date('Y-m-d H:i:s', strtotime($request->get('date_to')));
                    $query->whereBetween('updated_at', [$date_from, $date_to]);
                }
            })
            ->addColumn('send_by', function ($sms) {
                if ($sms->send_by == 'api') {
                    return '<span class="text-info">' . language_data('API SMS') . ' </span>';
                }elseif ($sms->send_by == 'receiver') {
                    return '<span class="text-primary">Incoming</span>';
                } else {
                    return language_data('Outgoing');
                }
            })
            ->addColumn('userid', function ($sms) {
                if ($sms->userid == '0') {
                    return '<span class="text-info">' . language_data('Admin') . ' </span>';
                } else {
                    $client = Client::find($sms->userid);
                    if ($client){
                        $user = '<a href="' . url("clients/view/$sms->userid") . '" >' . $client->fname.' '.$client->lname. '</a>';
                        return $user;
                    }
                    return 'Customer';
                }
            })
            ->escapeColumns([])
            ->make(true);


    }
    //======================================================================
    // bulkDeleteSMS Function Start Here
    //======================================================================
    public function bulkDeleteSMS(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/history')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'sms-history';
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
                SMSHistory::destroy($all_ids);
            }
        }

    }

    //======================================================================
    // Version 2.3
    //======================================================================

    //======================================================================
    // blockMessage Function Start Here
    //======================================================================
    public function blockMessage()
    {
        $self = 'block-message';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return view('admin.block-message');
    }



    //======================================================================
    // getBlockMessageData Function Start Here
    //======================================================================
    public function getBlockMessageData()
    {
        $block_message = BlockMessage::orderBy('updated_at', 'DESC')->getQuery();
        return Datatables::of($block_message)
            ->addColumn('action', function ($sms) {
                return '
                <a href="#" id="' . $sms->id . '" class="crelease btn btn-xs btn-complete"><i class="fa fa-check"></i> ' . language_data('Release') . '</a>
                <a class="btn btn-success btn-xs" href="' . url("sms/view-block-message/$sms->id") . '" ><i class="fa fa-inbox"></i>' . language_data('View') . '</a>
                <a href="#" id="' . $sms->id . '" class="cdelete btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . language_data('Delete') . '</a>
                ';
            })
            ->addColumn('user_id', function ($sms) {
                if ($sms->user_id == 0) {
                    return language_data('Admin');
                } else {
                    return '
                    <a href=' . url('clients/view/' . $sms->user_id) . '>' . client_info($sms->user_id)->username . '</a>
                    ';
                }
            })
            ->addColumn('status', function ($sms) {
                if ($sms->status == 'block') {
                    return '<span class="text-danger"> ' . language_data('Block') . ' </span>';
                } else {
                    return '<span class="text-success"> ' . language_data('Release') . ' </span>';
                }
            })
            ->addColumn('date', function ($sms) {
                return $sms->updated_at;
            })
            ->escapeColumns([])
            ->make(true);
    }



    //======================================================================
    // viewBlockMessage Function Start Here
    //======================================================================
    public function viewBlockMessage($id)
    {
        $self = 'block-message';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inbox_info = BlockMessage::find($id);

        if ($inbox_info) {
            return view('admin.view-block-message', compact('inbox_info'));
        } else {
            return redirect('sms/block-message')->with([
                'message' => language_data('SMS Not Found'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // releaseBlockMessage Function Start Here
    //======================================================================
    public function releaseBlockMessage($id)
    {


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/block-message')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'block-message';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inbox_info = BlockMessage::find($id);

        if ($inbox_info) {

            $message  = $inbox_info->message;
            $msg_type = $inbox_info->type;

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


            $gateway = SMSGateways::find($inbox_info->use_gateway);

            $gateway_credential = null;
            $cg_info            = null;
            if ($gateway->custom == 'Yes') {
                if ($gateway->type == 'smpp') {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                    if ($gateway_credential == null) {
                        return redirect('sms/block-message')->with([
                            'message' => language_data('SMS Gateway credential not found'),
                            'message_important' => true
                        ]);
                    }
                } else {
                    $cg_info = CustomSMSGateways::where('gateway_id', $gateway->id)->first();
                }

            } else {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('sms/block-message')->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            }


            if ($inbox_info->scheduled_time === null) {

                if ($msg_type == 'plain' || $msg_type == 'unicode') {
                    $this->dispatch(new SendBulkSMS($inbox_info->user_id, $inbox_info->receiver, $gateway, $gateway_credential, $inbox_info->sender, $message, $msgcount, $cg_info, '', $msg_type));
                }

                if ($msg_type == 'voice') {
                    $this->dispatch(new SendBulkVoice($inbox_info->user_id, $inbox_info->receiver, $gateway, $gateway_credential, $inbox_info->sender, $message, $msgcount));
                }

                if ($msg_type == 'mms') {
                    return redirect('sms/block-message')->with([
                        'message' => language_data('MMS not supported in block message'),
                        'message_important' => true
                    ]);
                }

                if ($inbox_info->campaign_id != '') {
                    CampaignSubscriptionList::where('campaign_id', $inbox_info->campaign_id)->create([
                        'campaign_id' => $inbox_info->campaign_id,
                        'number' => $inbox_info->receiver,
                        'message' => $message,
                        'amount' => $msgcount,
                        'status' => 'Success'
                    ]);
                }


            } else {

                if ($inbox_info->campaign_id != '') {
                    CampaignSubscriptionList::where('campaign_id', $inbox_info->campaign_id)->create([
                        'campaign_id' => $inbox_info->campaign_id,
                        'number' => $inbox_info->receiver,
                        'message' => $message,
                        'amount' => $msgcount,
                        'status' => 'queued',
                        'submitted_time' => $inbox_info->scheduled_time
                    ]);
                }
            }

            //call jobs

            $inbox_info->delete();

            return redirect('sms/block-message')->with([
                'message' => language_data('SMS release successfully')
            ]);

        } else {
            return redirect('sms/block-message')->with([
                'message' => language_data('SMS Not Found'),
                'message_important' => true
            ]);
        }
    }



    //======================================================================
    // deleteBlockMessage Function Start Here
    //======================================================================
    public function deleteBlockMessage($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/block-message')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'block-message';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inbox_info = BlockMessage::find($id);

        if ($inbox_info) {
            //refund money to client

            $msg_type = $inbox_info->type;
            $message  = $inbox_info->message;

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

            $phone = $inbox_info->receiver;

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
                }
            } else {
                $sms_charge = 1;
            }

            $remain_amount = $msgcount * $sms_charge;

            $client            = Client::find($inbox_info->user_id);
            $client->sms_limit = $client->sms_limit + $remain_amount;
            $client->save();

            if ($inbox_info->campaign_id) {
                $campaign = Campaigns::where('campaign_id', $inbox_info->campaign_id)->first();
                if ($campaign) {
                    $campaign->total_recipient -= 1;
                    $campaign->save();
                }
            }

            $inbox_info->delete();
            return redirect('sms/block-message')->with([
                'message' => language_data('SMS info deleted successfully')
            ]);

        } else {
            return redirect('sms/block-message')->with([
                'message' => language_data('SMS Not Found'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // postReplySMS Function Start Here
    //======================================================================
    public function postReplySMS($cmd, $message)
    {
        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        if ($message == '') {
            return redirect('sms/history')->with([
                'message' => language_data('Insert your message'),
                'message_important' => true
            ]);
        }

        $h = SMSHistory::find($cmd);

        if ($h) {
            $gateway = SMSGateways::find($h->use_gateway);

            if ($gateway->status != 'Active') {
                return redirect('sms/history')->with([
                    'message' => language_data('SMS gateway not active.Contact with Provider'),
                    'message_important' => true
                ]);
            }

            $gateway_credential = null;
            $cg_info            = null;
            if ($gateway->custom == 'Yes') {
                if ($gateway->type == 'smpp') {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                    if ($gateway_credential == null) {
                        return redirect('sms/history')->with([
                            'message' => language_data('SMS Gateway credential not found'),
                            'message_important' => true
                        ]);
                    }
                } else {
                    $cg_info = CustomSMSGateways::where('gateway_id', $gateway->id)->first();
                }

            } else {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                if ($gateway_credential == null) {
                    return redirect('sms/history')->with([
                        'message' => language_data('SMS Gateway credential not found'),
                        'message_important' => true
                    ]);
                }
            }

            $sender_id = $h->receiver;
            $msg_type  = $h->sms_type;
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

            if ($msg_type == 'plain' || $msg_type == 'unicode') {
                $this->dispatch(new SendBulkSMS(0, $h->sender, $gateway, $gateway_credential, $sender_id, $message, $msgcount, $cg_info, '', $msg_type));
            }

            if ($msg_type == 'voice') {
                $this->dispatch(new SendBulkVoice(0, $h->sender, $gateway, $gateway_credential, $sender_id, $message, $msgcount));
            }

            if ($msg_type == 'mms') {
                return redirect('sms/history')->with([
                    'message' => language_data('MMS not supported in two way communication'),
                    'message_important' => true
                ]);
            }

            return redirect('sms/history')->with([
                'message' => language_data('Successfully sent reply')
            ]);

        } else {
            return redirect('sms/history')->with([
                'message' => language_data('SMS Not Found'),
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

        $self = 'chat-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        $get_data    = SMSHistory::where('send_by', 'receiver')->orderBy('updated_at', 'DESC');
        $sms_history = $get_data->paginate(15);
        $sms_count   = $get_data->count();

        if ($request->ajax()) {
            if ($sms_history->count() > 0) {
                if (isset($request->page) && strpos($request->page, 'usms_search_') !== false){
                    $search_string = ltrim($request->page, 'usms_search_');
                    $sms_history = SMSHistory::where('send_by', 'receiver')->where('sender', 'like', '%'.$search_string.'%')->orWhere('receiver', 'like', '%'.$search_string.'%')->orderBy('updated_at', 'DESC')->get();
                }
                $view = view('admin.get-chat-box', compact('sms_history'))->render();
            } else {
                $view = null;
            }
            return response()->json(['html' => $view]);
        }

        return view('admin.chat-box', compact('sms_history', 'sms_count'));
    }

    //======================================================================
    // viewChatReports Function Start Here
    //======================================================================
    public function viewChatReports(Request $request)
    {

        $id = $request->id;

        $inbox_info = SMSHistory::find($id);
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

        $self = 'chat-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('This Option is Disable In Demo Mode'),
            ]);
        }

        $inbox_info = SMSHistory::find($request->sms_id);

        if ($inbox_info) {

            $phone = $inbox_info->receiver;

            $blacklist = BlackListContact::where('numbers', $phone)->first();

            if ($blacklist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Number already in blacklist'
                ]);

            } else {

                $status = BlackListContact::create([
                    'user_id' => 0,
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

        $self = 'chat-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {

                return response()->json([
                    'status' => 'error',
                    'message' => language_data('You do not have permission to view this page'),
                ]);
            }
        }

        $cmd     = $request->sms_id;
        $message = $request->message;

        if ($message == '') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('Insert your message')
            ]);
        }

        $h = SMSHistory::find($cmd);

        if ($h) {
            $gateway = SMSGateways::find($h->use_gateway);

            if ($gateway->status != 'Active') {

                return response()->json([
                    'status' => 'error',
                    'message' => language_data('SMS gateway not active.Contact with Provider')
                ]);
            }

            $gateway_credential = null;
            $cg_info            = null;
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

            $blacklist = BlackListContact::where('numbers', $h->sender)->first();

            if ($blacklist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Number contain in blacklist'
                ]);
            }

            $sender_id = $h->receiver;
            $msg_type  = $h->sms_type;

            if ($msg_type != 'plain' && $msg_type != 'unicode' && $msg_type != 'arabic') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid message type. Only text message will support'
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

            if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                $this->dispatch(new SendBulkSMS(0, $sender_id, $gateway, $gateway_credential, $h->sender, $message, $msgcount, $cg_info, '', $msg_type, $h->id));
            }
            return response()->json([
                'status' => 'success',
                'message' => language_data('Successfully sent reply'),
                'data' => $message
            ]);


        } else {
            return response()->json([
                'status' => 'error',
                'message' => language_data('SMS Not Found')
            ]);
        }

    }


    //======================================================================
    // removeChatHistory Function Start Here
    //======================================================================
    public function removeChatHistory(Request $request)
    {

        $self = 'chat-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return response()->json([
                'status' => 'error',
                'message' => language_data('This Option is Disable In Demo Mode'),
            ]);
        }

        $sms_history = SMSHistory::find($request->sms_id);
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
