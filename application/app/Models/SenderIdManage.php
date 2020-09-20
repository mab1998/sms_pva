<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SenderIdManage extends Model
{
    protected $table='sys_sender_id_management';
    protected $fillable = ['cl_id','sender_id','status'];
    protected $casts = [
        'cl_id' => 'array'
    ];
}
