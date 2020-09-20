<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable{

    use Notifiable;
    protected $table = 'sys_admins';


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $fillable = ['fname','lname','username','password','status','email','image','roleid','lastlogin','pwresetkey','pwresetexpiry','emailnotify','online','menu_open','remember_token'];

    public function get_admin_role(){
        return $this->hasOne('App\AdminRole','id','roleid');
    }

}
