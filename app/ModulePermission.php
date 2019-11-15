<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $fillable = ['user_id','permission'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function modules(){
        return$this->belongsTo(Module::class);
    }
}
