<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaigns extends Model
{
    protected $table = 'sys_campaigns';

    protected $fillable = ['campaign_id','user_id','sender','sms_type','camp_type','status','use_gateway','total_recipient','total_delivered','total_failed','run_at','delivery_at','media_url','keyword'];
}
