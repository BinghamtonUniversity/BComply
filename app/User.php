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
    protected $casts = ['params' => 'object'];

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
