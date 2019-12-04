<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ModuleVersion extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','module_id','type','reference'];
    protected $casts = ['reference' => 'object'];

    public function modules(){
        return $this->hasMany(Module::class);
    }
    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }
}
