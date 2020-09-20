<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable{

    use Notifiable;
    protected $table = 'sys_clients';

    protected $fillable = ['groupid', 'parent', 'fname', 'lname', 'company', 'website', 'email', 'username', 'password', 'address1', 'address2', 'state', 'city', 'postcode', 'country', 'phone', 'image', 'datecreated', 'sms_limit', 'api_access', 'api_key', 'api_gateway', 'online', 'status', 'reseller', 'sms_gateway', 'lastlogin', 'pwresetkey', 'pwresetexpiry', 'emailnotify', 'menu_open', 'lan_id', 'remember_token'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'online', 'lastlogin', 'pwresetkey', 'pwresetexpiry'
    ];

    /**
     * @var array
     */

    protected $casts = [
        'sms_gateway' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     */
    public function get_sms_gateway()
    {
        return $this->hasOne('App\SMSGateways', 'id', 'sms_gateway');
    }


}
