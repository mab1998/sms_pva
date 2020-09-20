<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentGateways extends Model
{
    protected $table='sys_payment_gateways';
    protected $fillable = ['name','value','settings','extra_value','status'];
}
