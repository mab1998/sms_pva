<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignSubscriptionListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_campaign_subscription_list', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('campaign_id');
            $table->string('number',50);
            $table->longText('message');
            $table->integer('amount')->default(1);;
            $table->text('status')->deafult('queued');
            $table->timestamp('submitted_time')->nullable();
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
        Schema::dropIfExists('sys_campaign_subscription_list');
    }
}
