<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $fillable = ['user_id','permission', 'module_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function module(){
        return$this->belongsTo(Module::class);
    }
}
