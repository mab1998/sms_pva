<?php

namespace App\Console\Commands;

use App\Campaigns;
use App\CampaignSubscriptionList;
use App\CustomSMSGateways;
use App\Jobs\SendBulkMMS;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\ScheduleSMS;
use App\SMSGatewayCredential;
use App\SMSGateways;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendScheduleSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send schedule sms to user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $camp = Campaigns::where('status', 'Scheduled')->where('camp_type', 'scheduled')->first();

        if ($camp) {
            if (new \DateTime() >= new \DateTime($camp->run_at)) {

                $fromDate = Carbon::now()->subYears(2)->toDateTimeString();
                $toDate = Carbon::now()->toDateTimeString();

                //Check queued in subscription list
                $subscription = CampaignSubscriptionList::where('status', 'scheduled')->whereBetween('submitted_time', [$fromDate, $toDate]);
                $subscription_portion = $subscription->take('100');
                $subscription_list = $subscription_portion->get();
                $list_count = $subscription->count();

                $msg_type = $camp->sms_type;
                $gateway = SMSGateways::find($camp->use_gateway);

                $gateway_credential = null;
                $cg_info = null;
                if ($gateway->custom == 'Yes') {
                    if ($gateway->type == 'smpp') {
                        $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                    } else {
                        $cg_info = CustomSMSGateways::where('gateway_id', $camp->use_gateway)->first();
                    }
                } else {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                }


                //if queued available run send sms query
                if ($list_count > 0) {

                    $subscription_portion->update(['status' => 'In Progress']);

                    foreach ($subscription_list as $list) {

                        $msgcount = $list->amount;

                        if (new \DateTime() >= new \DateTime($list->submitted_time)) {

                            if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                                dispatch(new SendBulkSMS($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message, $msgcount, $cg_info, '', $msg_type, $list->id));
                            }

                            if ($msg_type == 'voice') {
                                dispatch(new SendBulkVoice($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message, $msgcount, '', $msg_type, $list->id));
                            }

                            if ($msg_type == 'mms') {
                                dispatch(new SendBulkMMS($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message, $msgcount, '', $msg_type, $list->id));
                            }
                        }
                    }
                } else {

                    $subscription = CampaignSubscriptionList::where('campaign_id', $camp->campaign_id)->where('status', 'In Progress');
                    $subscription_portion = $subscription->take('100');
                    $subscription_list = $subscription_portion->get();
                    $list_count = $subscription->count();

                    if ($list_count) {

                        $subscription_portion->update(['status' => 'Retry']);

                        foreach ($subscription_list as $list) {

                            $msgcount = $list->amount;

                            if ($msg_type == 'plain' || $msg_type == 'unicode' || $msg_type == 'arabic') {
                                dispatch(new SendBulkSMS($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message, $msgcount, $cg_info, '', $msg_type, $list->id));
                            }

                            if ($msg_type == 'voice') {
                                dispatch(new SendBulkVoice($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message, $msgcount, '', $msg_type, $list->id));
                            }


                            if ($msg_type == 'mms') {
                                $media_url = $camp->media_url;
                                dispatch(new SendBulkMMS($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message, $media_url, '', $msg_type, $list->id));
                            }
                        }
                    } else {
                        $camp->status = 'Delivered';
                        $camp->delivery_at = date('Y-m-d H:i:s');
                        $camp->save();
                    }

                }
            }
        } else {
            Campaigns::where('camp_type', 'scheduled')->update(['status' => 'Delivered']);
        }
    }
}
