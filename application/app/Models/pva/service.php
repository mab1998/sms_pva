<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table='service';
    protected $fillable = ['id','service','service_code'];
}
