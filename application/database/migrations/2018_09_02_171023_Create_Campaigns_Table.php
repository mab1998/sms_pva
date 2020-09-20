<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('campaign_id');
            $table->integer('user_id');
            $table->string('sender',100)->nullable();
            $table->enum('sms_type',['plain','unicode','voice','mms','arabic'])->default('plain');
            $table->enum('camp_type',['regular','scheduled'])->default('regular');
            $table->string('status',50);
            $table->integer('use_gateway');
            $table->integer('total_recipient')->default(0);
            $table->integer('total_delivered')->default(0);
            $table->integer('total_failed')->default(0);
            $table->text('media_url')->nullable();
            $table->text('keyword')->nullable();
            $table->timestamp('run_at')->nullable();
            $table->timestamp('delivery_at')->nullable();
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
        Schema::dropIfExists('sys_campaigns');
    }
}
