<?php

use Illuminate\Database\Seeder;
use App\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::truncate();

        Admin::create([
            'fname' => 'Abul Kashem',
            'lname' => 'Shamim',
            'username' => 'admin',
            'password' => bcrypt('admin.password'),
            'status' => 'Active',
            'email' => 'akasham67@gmail.com',
            'image' => 'profile.jpg',
            'roleid' => '0'
        ]);
    }
}
