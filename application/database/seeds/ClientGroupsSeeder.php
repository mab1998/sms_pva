<?php

use Illuminate\Database\Seeder;
use App\ClientGroups;

class ClientGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClientGroups::create([
            'group_name'=>'Customer'
        ]);
    }
}
