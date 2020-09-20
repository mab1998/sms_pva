<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminRolePermission extends Model
{
    protected $table='sys_admin_role_perm';
    protected $fillable = ['role_id','perm_id'];
}
