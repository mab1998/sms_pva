<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table='sys_app_config';
    protected $fillable = ['setting','value'];
}
