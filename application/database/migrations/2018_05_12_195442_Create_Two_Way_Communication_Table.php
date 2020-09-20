<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwoWayCommunicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_two_way_communication', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gateway_id');
            $table->string('source_param',100);
            $table->string('destination_param',100);
            $table->string('message_param',100);
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
        Schema::dropIfExists('sys_two_way_communication');
    }
}
