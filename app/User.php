<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['first_name', 'last_name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token','created_at','updated_at','invalidate_cache'];
    protected $appends = ['options','groups','content_admin_groups','apps_admin_groups','site_admin','site_developer','tags','tags_array'];
    protected $casts = ['invalidate_cache' => 'boolean', 'params' => 'object'];


    public function groupMemberships(){
        return $this->hasMany(GroupMembership::class);
    }
    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
    public function userPermissions(){
        return $this->hasMany(UserPermission::class);
    }
    public function actionLogs(){
        return $this->hasMany(UserActionLog::class);
    }
    public function modulePermissions(){
        return $this->hasMany(ModulePermission::class);
    }
}
