<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTickets extends Model
{
    protected $table='sys_tickets';
    protected $fillable = ['did','cl_id','admin_id','name','email','date','subject','message','status','admin','replyby','closed_by'];
}
