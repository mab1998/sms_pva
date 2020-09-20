<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockMessage extends Model
{
    protected $table = 'sys_block_message';
    protected $fillable = ['user_id','sender','message','use_gateway','status','receiver','scheduled_time','type'];
}
