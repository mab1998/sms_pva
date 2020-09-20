<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecurringSMSContacts extends Model
{
    protected $table = 'sys_recurring_sms_contacts';
    protected $fillable = ['campaign_id','receiver','message','amount'];
}
