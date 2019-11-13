<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public function index(){
        return $this->hasMany("user_id");
    }
}
