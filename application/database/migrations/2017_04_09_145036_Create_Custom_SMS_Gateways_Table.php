<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomSMSGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_custom_sms_gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gateway_id');
            $table->string('username_param',100);
            $table->text('username_value');
            $table->string('password_param',100)->nullable();
            $table->text('password_value')->nullable();
            $table->enum('password_status',['yes','no'])->default('yes');
            $table->string('action_param',100)->nullable();
            $table->text('action_value')->nullable();
            $table->enum('action_status',['yes','no'])->default('yes');
            $table->string('source_param',100)->nullable();
            $table->text('source_value')->nullable();
            $table->enum('source_status',['yes','no'])->default('yes');
            $table->string('destination_param',100);
            $table->string('message_param',100);
            $table->string('unicode_param',100)->nullable();
            $table->text('unicode_value')->nullable();
            $table->enum('unicode_status',['yes','no'])->default('yes');
            $table->string('route_param',100)->nullable();
            $table->text('route_value')->nullable();
            $table->enum('route_status',['yes','no'])->default('yes');
            $table->string('language_param',100)->nullable();
            $table->text('language_value')->nullable();
            $table->enum('language_status',['yes','no'])->default('yes');
            $table->string('custom_one_param',100)->nullable();
            $table->text('custom_one_value')->nullable();
            $table->enum('custom_one_status',['yes','no'])->default('yes');
            $table->string('custom_two_param',100)->nullable();
            $table->text('custom_two_value')->nullable();
            $table->enum('custom_two_status',['yes','no'])->default('yes');
            $table->string('custom_three_param',100)->nullable();
            $table->text('custom_three_value')->nullable();
            $table->enum('custom_three_status',['yes','no'])->default('yes');
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
        Schema::dropIfExists('sys_custom_sms_gateways');
    }
}
