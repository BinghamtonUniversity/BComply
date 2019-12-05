<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name'];

    public function group_memberships(){
        return $this->hasMany(GroupMembership::class,'group_id');
    }
}
