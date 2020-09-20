<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSPricePlan extends Model
{
    protected $table='sys_sms_price_plan';
    protected $fillable = ['plan_name','price','popular','status','category'];
}
