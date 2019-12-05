<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['unique_id','first_name', 'last_name','email','code','supervisor','department','division','title','active'];
    protected $hidden = ['password', 'remember_token','created_at','updated_at','user_perms','module_perms'];
    protected $casts = ['active'=>'boolean'];
    protected $appends = ['user_permissions','module_permissions'];
    protected $with = ['user_perms','module_perms'];
//    protected $dispatchesEvents=[
//        'saved' =>UserSaved::class,
//        'deleted'=->UserDeleted::class,
//    ]
    public function group_memberships(){
        return $this->hasMany(GroupMembership::class,'user_id');
    }
    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
    public function action_logs(){
        return $this->hasMany(UserActionLog::class);
    }
    // These are default Relationships that are restructured
    // using the setters below.  
    public function module_perms(){
        return $this->hasMany(ModulePermission::class);
    }
    public function user_perms(){
        return $this->hasMany(UserPermission::class);
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
}
