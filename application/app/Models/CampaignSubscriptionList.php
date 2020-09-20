<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignSubscriptionList extends Model
{
    protected $table = 'sys_campaign_subscription_list';
    protected $fillable = ['campaign_id','number','message','amount','status','submitted_time'];
}
