<?php

use Illuminate\Database\Seeder;
use App\Language;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       Language::truncate();
       Language::create(['language'=>'English','status'=>'Active','icon'=>'us.png']);
    }
}
