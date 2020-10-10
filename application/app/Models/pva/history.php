<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PVAHistory extends Model
{
    protected $table='sys_pva_history';
    protected $fillable = ['id','id_pva','country','service','price','Phone_Number','activation_code'];
}
