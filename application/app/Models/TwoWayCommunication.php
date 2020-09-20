<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwoWayCommunication extends Model
{
    protected $table = 'sys_two_way_communication';
    protected $fillable = ['gateway_id','source_param','destination_param','message_param'];
}
