<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportPhoneNumber extends Model
{
    protected $table='sys_import_phone_number';
    protected $fillable=['user_id','group_name'];
}
