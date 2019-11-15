<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name','description','owner_user_id','message_configuration','assignment_configuration'];
    protected $casts = ['message_configuration' => 'object','assignment_configuration'=>'object'];

    public function version(){
        return $this->belongsTo(ModuleVersion::class);
    }
    public function permissions(){
        return $this->hasMany(ModulePermission::class);
    }

}
