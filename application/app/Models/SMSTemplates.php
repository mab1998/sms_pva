<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSTemplates extends Model
{
    protected $table='sys_sms_templates';
    protected $fillable = ['cl_id','template_name','from','message','global','status'];
}
