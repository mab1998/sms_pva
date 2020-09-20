<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $table='sys_admin_role';
    protected $fillable = ['role_name','status'];
}
