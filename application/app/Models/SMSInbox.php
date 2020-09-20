<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSInbox extends Model
{
    protected $table='sys_sms_inbox';
    protected $fillable=['msg_id','amount','message','status','send_by','mark_read'];
}
