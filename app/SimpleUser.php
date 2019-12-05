<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SimpleUser extends Model
{
    protected $fillable = ['unique_id','first_name', 'last_name','email','code','supervisor','department','division','title','active'];
    protected $casts = ['active'=>'boolean'];
    protected $table = 'users';

    public function group_memberships(){
        return $this->hasMany(GroupMembership::class,'user_id');
    }
    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
}
