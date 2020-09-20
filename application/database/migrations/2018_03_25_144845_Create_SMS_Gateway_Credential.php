<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMSGatewayCredential extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sms_gateway_credential', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gateway_id');
            $table->longText('username');
            $table->longText('password')->nullable();
            $table->longText('extra')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Inactive');
            $table->longText('c1')->nullable();
            $table->longText('c2')->nullable();
            $table->longText('c3')->nullable();
            $table->longText('c4')->nullable();
            $table->longText('c5')->nullable();
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
        Schema::dropIfExists('sys_sms_gateway_credential');
    }
}
