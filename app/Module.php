<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public function version(){
        return $this->belongsTo(ModuleVersion::class);
    }
    public function permissions(){
        return $this->hasMany(ModulePermission::class);
    }

}
