<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleSMS extends Model
{
    protected $table='sys_schedule_sms';
    protected $fillable=['userid','sender','receiver','amount','message','status','use_gateway','submit_time','type','media_url'];
}
