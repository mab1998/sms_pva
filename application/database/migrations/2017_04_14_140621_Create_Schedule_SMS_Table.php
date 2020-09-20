<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleSMSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_schedule_sms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userid');
            $table->string('sender',100)->nullable();
            $table->string('receiver',20);
            $table->integer('amount');
            $table->text('message');
            $table->integer('use_gateway');
            $table->enum('type',['plain','unicode','voice','mms','arabic'])->default('plain');
            $table->timestamp('submit_time');
            $table->longText('media_url')->nullable();
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
        Schema::dropIfExists('sys_schedule_sms');
    }
}
