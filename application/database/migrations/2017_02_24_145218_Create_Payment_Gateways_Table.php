<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_payment_gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',20);
            $table->text('value');
            $table->string('settings',20);
            $table->text('extra_value')->nullable();
            $table->text('password')->nullable();
            $table->text('custom_one')->nullable();
            $table->text('custom_two')->nullable();
            $table->text('custom_three')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Active');
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
        Schema::dropIfExists('sys_payment_gateways');
    }
}
