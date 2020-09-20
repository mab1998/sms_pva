<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keywords extends Model
{
    protected $table = 'sys_keywords';
    protected $fillable = ['user_id','title','keyword_name','reply_text','reply_voice','reply_mms','status','price','validity','validity_date'];
}
