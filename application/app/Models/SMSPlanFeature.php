<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSPlanFeature extends Model
{
    protected $table='sys_sms_plan_feature';
    protected $fillable=['pid','feature_name','feature_value','status'];
}
