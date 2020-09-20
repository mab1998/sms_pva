<?php

namespace App\Console\Commands;

use App\Campaigns;
use App\CampaignSubscriptionList;
use App\CustomSMSGateways;
use App\Jobs\SendBulkMMS;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\SMSGatewayCredential;
use App\SMSGateways;
use Illuminate\Console\Command;

class BulkSMSFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:sendbulk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Bulk SMS From File';

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
        $campaigns = Campaigns::where('status', 'Running')->where('camp_type','regular')->get();
        foreach ($campaigns as $camp) {
            if (new \DateTime() >= new \DateTime($camp->run_at)) {

                $camp->status = 'In Progress';
                $camp->save();

                //Check queued in subscription list
                $subscription_list = CampaignSubscriptionList::where('campaign_id', $camp->campaign_id)->where('status','queued')->get();
                $list_count = $subscription_list->count();

                //if queued available run send sms query
                if ($list_count > 0) {

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
                            dispatch(new SendBulkMMS($camp->user_id, $list->number, $gateway, $gateway_credential, $camp->sender, $list->message,$media_url, '', $msg_type, $list->id));
                        }
                    }
                    $camp->status = 'Delivered';
                    $camp->delivery_at = date('Y-m-d H:i:s');
                    $camp->save();
                }
            }
        }



    }
}
