<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call(AppConfigSeeder::class);
         $this->call(ClientSeeder::class);
         $this->call(ClientGroupsSeeder::class);
         $this->call(AdminSeeder::class);
         $this->call(SMSGatewaysSeeder::class);
         $this->call(PaymentGatewaysSeeder::class);
         $this->call(EmailTemplateSeeder::class);
         $this->call(IntCountryCodesSeeder::class);
         $this->call(LanguageTableSeeder::class);
         $this->call(LanguageDataTableSeeder::class);
    }
}
