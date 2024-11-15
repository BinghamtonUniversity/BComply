<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SimpleUser extends Model
{
    protected $fillable = ['unique_id','first_name', 'last_name','email','payroll_code','supervisor','department_id','department_name','division_id','division','negotiation_unit','title','role_type','active'];
    protected $casts = ['active'=>'boolean'];
    protected $table = 'users';

    public function group_memberships(){
        return $this->hasMany(GroupMembership::class,'user_id');
    }

    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
    
    public function send_email_check() {
        /* Send email if MAIL_LIMIT_SEND is false (Not limiting emails) */
        if (config('mail.limit_send') === false) {
            return true;
        }
        /* Send email if MAIL_LIMIT_SEND is true, and MAIL_LIMIT_ALLOW contains user's email address */
        if (config('mail.limit_send') === true && in_array($this->email,config('mail.limit_allow'))) {
            return true;
        }
        /* Otherwise don't send email */
        return false;
    }
}
