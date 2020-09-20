<?php

namespace App\Console\Commands;

use App\BlackListContact;
use App\CampaignSubscriptionList;
use App\SMSGatewayCredential;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use Illuminate\Console\Command;

class CheckPowerSMSInbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'powersms:checkinbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inbox for Powersms';

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

        $sms_gateway = SMSGateways::where('settings', 'PowerSMS')->where('status', 'Active')->first();


        if ($sms_gateway) {

            $gateway_credential = SMSGatewayCredential::where('gateway_id', $sms_gateway->id)->where('status', 'Active')->first();



            if ($gateway_credential) {

                $url = 'https://powersms.banglaphone.net.bd/httpapi/getsms?' . "userId=" . $gateway_credential->username . "&password=" . $gateway_credential->password . "&lastReadSmsId=" . $sms_gateway->port;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch);

                if ($result === false) {
                    return false;
                }

                $json_result = json_decode($result);

                if (isset($json_result) && count($json_result) > 0) {

                    foreach ($json_result as $result) {
                        if ($result->Sender == '' && $result->Text == '') {
                            continue;
                        }

                        $number = str_replace(['(', ')', '+', '-', ' '], '', trim($result->Sender));
                        $body = $result->Text;



                        $msgcount = strlen(preg_replace('/\s+/', ' ', trim($body)));;
                        $msgcount = $msgcount / 160;
                        $msgcount = ceil($msgcount);

                        $get_history = SMSHistory::where('receiver', $number)->orderBy('id', 'desc');
                        $get_info = $get_history->first();

                        $blacklist_word = strtolower(app_config('opt_out_sms_keyword'));
                        $blacklist_word = explode(',', $blacklist_word);
                        $blacklist_word = array_map('trim', $blacklist_word);

                        $opt_in_word = strtolower(app_config('opt_in_sms_keyword'));
                        $opt_in_word = explode(',', $opt_in_word);
                        $opt_in_word = array_map('trim', $opt_in_word);

                        $reply_word = strtolower($body);

                        if (in_array($reply_word, $opt_in_word)) {
                            $contact = BlackListContact::where('numbers', $number)->first();
                            if ($contact) {
                                $contact->delete();
                            }
                        }

                        if ($get_info) {
                            if (in_array($reply_word, $blacklist_word)) {
                                BlackListContact::create([
                                    'user_id' => $get_info->userid,
                                    'numbers' => $number
                                ]);
                            }


                            $status = SMSInbox::create([
                                'msg_id' => $get_info->id,
                                'amount' => $msgcount,
                                'message' => $body,
                                'status' => 'Received from ' . $number,
                                'send_by' => 'receiver',
                                'mark_read' => 'no',
                            ]);

                            if ($status) {
                                $get_info->send_by = 'receiver';
                                $get_info->save();
                                $get_info->touch();

                                return true;
                            }

                            return false;

                        } else {

                            if (in_array($reply_word, $blacklist_word)) {
                                BlackListContact::create([
                                    'user_id' => 0,
                                    'numbers' => $number
                                ]);
                            }

                            $status = SMSHistory::create([
                                'userid' => 0,
                                'sender' => $number,
                                'receiver' => 'Not found',
                                'message' => $body,
                                'amount' => $msgcount,
                                'status' => 'Success',
                                'api_key' => null,
                                'use_gateway' => $sms_gateway->id,
                                'send_by' => 'receiver',
                                'sms_type' => 'plain'
                            ]);

                            if ($status) {
                                SMSInbox::create([
                                    'msg_id' => $status->id,
                                    'amount' => $msgcount,
                                    'message' => $body,
                                    'status' => 'Received from ' . $number,
                                    'send_by' => 'receiver',
                                    'mark_read' => 'no',
                                ]);
                                return true;
                            }
                            return false;
                        }

                    }

                    $get_last_id = end($json_result)->Id;
                    $sms_gateway->port = $get_last_id;
                    $sms_gateway->save();
                } else {
                    return false;
                }


                curl_close($ch);
            }

            return false;
        }

        return false;
    }
}
