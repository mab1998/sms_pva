<?php

namespace App\Console\Commands;

use App\Client;
use App\CustomSMSGateways;
use App\Jobs\SendBulkMMS;
use App\Jobs\SendBulkSMS;
use App\Jobs\SendBulkVoice;
use App\RecurringSMS;
use App\RecurringSMSContacts;
use App\SMSGatewayCredential;
use App\SMSGateways;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRecurringSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:sendrecurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Recurring SMS';


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


        $fromDate = Carbon::now()->subYears(2)->toDateTimeString();
        $toDate = Carbon::now()->toDateTimeString();

        $bulk_sms = RecurringSMS::where('status', 'running')->whereBetween('recurring_date', [$fromDate, $toDate])->get();

        foreach ($bulk_sms as $sms) {
            $msg_type = $sms->type;

            $gateway = SMSGateways::find($sms->use_gateway);

            $gateway_credential = null;
            $cg_info = null;
            if ($gateway->custom == 'Yes') {
                if ($gateway->type == 'smpp') {
                    $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
                } else {
                    $cg_info = CustomSMSGateways::where('gateway_id', $sms->use_gateway)->first();
                }
            } else {
                $gateway_credential = SMSGatewayCredential::where('gateway_id', $gateway->id)->where('status', 'Active')->first();
            }

            if ($sms->userid != 0) {
                $client = Client::find($sms->userid);

                if ($client) {
                    $sms_unit_cost = RecurringSMSContacts::where('campaign_id',$sms->id)->sum('amount');
                    $current_balance = $client->sms_limit;

                    if ($sms_unit_cost == 0 || $current_balance == 0 || $sms_unit_cost > $current_balance) {
                        $sms->status = 'stop';
                        $sms->save();
                        continue;
                    }

                    $remain_sms = $current_balance - $sms_unit_cost;
                    $client->sms_limit = $remain_sms;
                    $client->save();
                }

            }

            $results = RecurringSMSContacts::where('campaign_id',$sms->id)->get();

            foreach ($results as $r) {

                if ($msg_type == 'plain' || $msg_type == 'unicode') {
                    dispatch(new SendBulkSMS($sms->userid, $r->receiver, $gateway, $gateway_credential, $sms->sender, $r->message, $r->amount, $cg_info, '', $msg_type));
                }

                if ($msg_type == 'voice') {
                    dispatch(new SendBulkVoice($sms->userid, $r->receiver, $gateway, $gateway_credential, $sms->sender, $r->message, $r->amount, '', $msg_type));
                }

                if ($msg_type == 'mms') {
                    dispatch(new SendBulkMMS($sms->userid, $r->receiver, $gateway, $gateway_credential, $sms->sender, $r->message, $sms->media_url, '', $msg_type));
                }
            }


            $period = $sms->recurring;
            $its = strtotime(date('Y-m-d'));
            $status = 'running';

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
                $nd = date('Y-m-d H:i:s', strtotime($sms->recurring_date));
            } else {
                $status = 'stop';
            }

            if ($period != '0') {
                $schedule_time = date("H:i:s", strtotime($sms->recurring_date));
                $nd = date("Y-m-d H:i:s", strtotime($nd . ' ' . $schedule_time));
            }

            $sms->recurring_date = $nd;
            $sms->status = $status;
            $sms->save();

        }

    }
}
