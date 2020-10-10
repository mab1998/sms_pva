<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $table='api_keys';
    protected $fillable = ['id','service','api_key'];
}
