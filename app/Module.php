<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;



class Module extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','description','owner_user_id','message_configuration','assignment_configuration','module_version_id'];
    protected $casts = ['message_configuration' => 'object','assignment_configuration'=>'object'];
    protected $hidden = ['permissions'];
    protected $appends = ['module_permissions'];
    protected $with = ['permissions'];

    public function current_version(){
        return $this->belongsTo(ModuleVersion::class,'module_version_id');
    }
    public function permissions(){
        return $this->hasMany(ModulePermission::class);
    }
    public function owner(){
        return $this->belongsTo(User::class,'owner_user_id');
    }
    public function getModulePermissionsAttribute() {
        $permissions = $this->permissions()->get();
        $permissions_arr = [];
        foreach($permissions as $permission) {
            $permissions_arr[] = $permission->permission;
        }
        return $permissions_arr;
    }

}
