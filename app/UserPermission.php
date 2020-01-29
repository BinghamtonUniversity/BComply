<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $fillable = ['user_id','permission'];

    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
}
