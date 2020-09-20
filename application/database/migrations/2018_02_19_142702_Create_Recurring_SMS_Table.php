<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringSMSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_recurring_sms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userid');
            $table->string('sender',100)->nullable();
            $table->integer('total_recipients');
            $table->integer('use_gateway');
            $table->longText('media_url')->nullable();
            $table->string('recurring',20);
            $table->string('recurring_date',50)->nullable();
            $table->enum('type',['plain','unicode','voice','mms','arabic'])->default('plain');
            $table->enum('status',['running','stop'])->default('running');
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
        Schema::dropIfExists('sys_recurring_sms');
    }
}
