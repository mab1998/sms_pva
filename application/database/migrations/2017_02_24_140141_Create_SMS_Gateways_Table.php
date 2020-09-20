<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMSGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sms_gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            $table->string('settings',50);
            $table->longText('api_link')->nullable();
            $table->string('port',20)->nullable();
            $table->enum('schedule',['No','Yes'])->default('Yes');
            $table->enum('custom',['No','Yes'])->default('No');
            $table->enum('type',['http','smpp'])->default('http');
            $table->enum('status',['Active','Inactive'])->default('Active');
            $table->enum('two_way',['Yes','No'])->default('No');
            $table->enum('mms',['Yes','No'])->default('No');
            $table->enum('voice',['Yes','No'])->default('No');
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
        Schema::dropIfExists('sys_sms_gateways');
    }
}
