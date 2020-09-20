<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMSInboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sms_inbox', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('msg_id');
            $table->integer('amount');
            $table->text('message');
            $table->text('status')->nullable();
            $table->enum('send_by',['sender','receiver']);
            $table->enum('mark_read',['yes','no'])->default('no');
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
        Schema::dropIfExists('sys_sms_inbox');
    }
}
