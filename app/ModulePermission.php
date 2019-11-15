<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function modules(){
        return$this->belongsTo(Module::class);
    }
}
