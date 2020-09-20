<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMSBundlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sms_bundles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_from')->nullable();
            $table->integer('unit_to')->nullable();
            $table->string('price',20)->nullable();
            $table->string('trans_fee',5)->nullable();
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
        Schema::dropIfExists('sys_sms_bundles');
    }
}
