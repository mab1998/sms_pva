<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlockMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_block_message', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('sender')->nullable();
            $table->string('receiver',20);
            $table->text('message');
            $table->integer('use_gateway');
            $table->text('scheduled_time')->nullable();
            $table->enum('type',['plain','unicode','voice','mms','arabic'])->default('plain');
            $table->enum('status',['block','release'])->default('block');
            $table->uuid('campaign_id')->nullable();
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
        Schema::dropIfExists('sys_block_message');
    }
}
