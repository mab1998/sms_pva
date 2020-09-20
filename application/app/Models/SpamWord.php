<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpamWord extends Model
{
    protected $table = 'sys_spam_word';
    protected $fillable = ['word'];
}
