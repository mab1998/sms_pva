<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailTemplates extends Model
{
    protected $table='sys_email_templates';
    protected $fillable = ['tplname','subject','message','status'];
}
