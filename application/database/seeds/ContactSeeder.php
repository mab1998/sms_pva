<?php

use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\ContactList::truncate();
        $phone_book = \App\ImportPhoneNumber::first();

        if ($phone_book) {
            $limit   = 50000;

            for ($i = 0; $i < $limit; $i++) {
                $number = '88017'. $i . time();
                $number = substr($number, 0, 13);

                \App\ContactList::create([
                    'pid' => $phone_book->id,
                    'phone_number' => $number
                ]);
            }
        }

    }
}
