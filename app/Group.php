<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name'];

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Handle the user "created" event.
     *
     * @param
     * @return GroupMembership
     */
    public function group_memberships(){
        return $this->hasMany(GroupMembership::class,'group_id');
    }
}
