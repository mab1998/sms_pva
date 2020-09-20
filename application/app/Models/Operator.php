<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $table = 'sys_operator';
    protected $fillable = ['coverage_id','operator_name','operator_code','operator_setting','plain_price','voice_price','mms_price','status'];
}
