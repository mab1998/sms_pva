<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlackListContact extends Model
{
    protected $table='sys_blacklist_contacts';
    protected $fillable = ['user_id','numbers'];
}
