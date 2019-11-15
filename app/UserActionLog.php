<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    public function action(){
        return $this->belongsTo(User::class);
    }
}
