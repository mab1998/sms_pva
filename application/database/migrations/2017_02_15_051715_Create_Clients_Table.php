<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('groupid')->default(0);
            $table->integer('parent')->default(0);
            $table->text('fname');
            $table->text('lname')->nullable();
            $table->text('company')->nullable();
            $table->text('website')->nullable();
            $table->text('email')->nullable();
            $table->string('username',200);
            $table->text('password');
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->text('state')->nullable();
            $table->text('city')->nullable();
            $table->text('postcode')->nullable();
            $table->string('country',50);
            $table->string('phone',30);
            $table->text('image')->nullable();
            $table->date('datecreated')->default(date('Y-m-d'));
            $table->string('sms_limit',11)->default(0);
            $table->enum('api_access',['Yes','No'])->default('No');
            $table->text('api_key')->nullable();
            $table->integer('api_gateway')->nullable();
            $table->integer('online')->default(0);
            $table->enum('status',['Active','Inactive','Closed'])->default('Active');
            $table->enum('reseller',['Yes','No'])->default('No');
            $table->text('sms_gateway');
            $table->date('lastlogin')->nullable();
            $table->text('pwresetkey')->nullable();
            $table->integer('pwresetexpiry')->nullable();
            $table->enum('emailnotify',['Yes','No'])->default('No');
            $table->integer('menu_open')->default(0);
            $table->integer('lan_id')->default(1);
            $table->text('remember_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_clients');
    }
}
