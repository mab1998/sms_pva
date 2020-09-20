<?php

use Illuminate\Database\Seeder;

class SMSHistorySeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\SMSHistory::truncate();
        \App\SMSInbox::truncate();

        $factory = \Faker\Factory::create();
        $limit   = 50000;

        for ($i = 0; $i < $limit; $i++) {

            $message = $factory->text(120);
            $number = '88017'.$i.time();
            $number = substr($number, 0, 13);
            $created_at = $factory->dateTimeBetween($startDate = '-10 days', $endDate = 'now', $timezone = null);


            $sms_history = \App\SMSHistory::create([
                'userid' => 0,
                'sender' => $factory->company,
                'receiver' => $number,
                'message' => $message,
                'amount' => 1,
                'status' => 'Success',
                'api_key' => null,
                'use_gateway' => 1,
                'sms_type' => 'plain',
                'send_by' => 'sender',
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ]);

            if ($sms_history) {

                \App\SMSInbox::create([
                    'msg_id' => $sms_history->id,
                    'amount' => 1,
                    'message' => $message,
                    'status' => 'Success',
                    'send_by' => 'sender',
                    'mark_read' => 'yes',
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ]);
            }


        }

    }
}
