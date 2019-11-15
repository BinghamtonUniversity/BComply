<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleVersion extends Model
{
    public function modules(){
        return $this->hasMany(Module::class);
    }
    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
}
