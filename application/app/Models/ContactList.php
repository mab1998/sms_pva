<?php




namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    protected $table = 'sys_contact_list';

    protected $fillable = ['pid','phone_number','email_address','user_name','company','first_name','last_name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     */
    public function get_user(){
        return $this->hasOne('App\ImportPhoneNumber','id','pid');
    }

}
