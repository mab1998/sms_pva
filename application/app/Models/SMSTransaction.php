<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSTransaction extends Model
{
    protected $table='sys_sms_transaction';
    protected $fillable=['cl_id','amount'];
}
