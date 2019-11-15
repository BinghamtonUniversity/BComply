<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleAssignment extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function version(){
        return $this->belongsTo(ModuleVersion::class);
    }
}
