<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    protected $table='sys_invoices';
    protected $fillable = ['cl_id','client_name','created_by','created','duedate','datepaid','subtotal','total','status','pmethod','recurring','bill_created','note'];
}
