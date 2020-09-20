<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientGroups extends Model
{
    protected $table='sys_client_groups';
    protected $fillable = ['group_name','created_by','status'];
}
