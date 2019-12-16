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
    public function simple_user(){
        return $this->belongsTo('App\SimpleUser','user_id')
            ->select('id','unique_id','first_name','last_name','email');
    }
}
