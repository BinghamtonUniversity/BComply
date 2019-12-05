<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupMembership extends Model
{
    protected $fillable = ['group_id','user_id','type'];
    //
    public function group(){
        return $this->belongsTo('App\Group');
    }
    public function user(){
        return $this->belongsTo('App\User');
    }
}
