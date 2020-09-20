<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecurringSMS extends Model
{
    protected $table = 'sys_recurring_sms';
    protected $fillable = ['userid','sender','total_recipients','use_gateway','media_url','recurring','recurring_date','type','status'];
}
