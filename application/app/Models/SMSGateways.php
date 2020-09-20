<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSGateways extends Model
{
    protected $table='sys_sms_gateways';
    protected $fillable= ['name','settings','api_link','port','schedule','custom','type','status','two_way
mms','voice'];

    public function gateway_relation(){
        return $this->hasOne('App\SMSGatewayCredential','id','gateway_id');
    }

    public function gateway_credential(){
        return $this->gateway_relation()->where('status','Active');
    }
}
