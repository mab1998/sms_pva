<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSBundles extends Model
{
    protected $table='sys_sms_bundles';
    protected $fillable = ['unit_from','unit_to','price','trans_fee'];
}
