<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['unique_id','first_name', 'last_name','email','payroll_code','supervisor','department_id','department_name','division_id','division','negotiation_unit','title','role_type','active'];
    protected $hidden = ['password', 'remember_token','created_at','updated_at','user_perms','module_perms','workshop_perms'];
    protected $casts = ['active'=>'boolean'];
    protected $appends = ['user_permissions','module_permissions','workshop_permissions'];
    protected $with = ['user_perms','module_perms','workshop_perms'];
//    protected $dispatchesEvents=[
//        'saved' =>UserSaved::class,
//        'deleted'=->UserDeleted::class,
//    ]
    public function group_memberships(){
        return $this->hasMany(GroupMembership::class,'user_id');
    }
    public function pivot_groups() {
        return $this->belongsToMany('App\Group','group_memberships')->withPivot('type');
    }
    public function pivot_module_permissions() {
        return $this->belongsToMany('App\Module','module_permissions')->withPivot('permission');
    }
    public function pivot_workshop_permissions() {
        return $this->belongsToMany('App\Module','workshop_permissions')->withPivot('permission');
    }
    public function pivot_module_assignments() {
        return $this->belongsToMany('App\ModuleVersion','module_assignments')->withPivot('status');
    }
    public function owned_modules() {
        return $this->hasMany('App\Module','owner_user_id');
    }
    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
    public function action_logs(){
        return $this->hasMany(UserActionLog::class);
    }
    // These are default Relationships that are restructured
    // using the setters below.
    public function workshop_perms(){
        return $this->hasMany(WorkshopPermission::class);
    }  
    public function module_perms(){
        return $this->hasMany(ModulePermission::class);
    }
    public function user_perms(){
        return $this->hasMany(UserPermission::class);
    }
     // Converts Workshop Permissions to Array
     public function getWorkshopPermissionsAttribute() {
        $permissions = $this->workshop_perms()->get();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[] = $permission->permission;
        }
        return $permissions_arr;
    }
    // Converts User Permissions to Array
    public function getUserPermissionsAttribute() {
        $permissions = $this->user_perms()->get();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[] = $permission->permission;
        }
        return $permissions_arr;
    }
    // Converts Module Permissions to Array
    public function getModulePermissionsAttribute() {
        $permissions = $this->module_perms()->get();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[$permission->module_id][] = $permission->permission;
        }
        return (Object)$permissions_arr;
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
