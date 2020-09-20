<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSGatewayCredential extends Model
{
    protected $table = 'sys_sms_gateway_credential';
    protected $fillable = ['gateway_id','username','password','extra','c1','c2','c3','c4','c5'];
}
