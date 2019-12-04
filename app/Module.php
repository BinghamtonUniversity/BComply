<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Module extends Model
{
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
    public function assign_to(Array $assignment_arr, $testonly=false) {
        if (is_null($this->module_version_id)) {
            // Not Current Version Exists!
            return false;
        }
        $assignments = ModuleAssignment::where('user_id',$assignment_arr['user_id'])->where('module_id',$this->id)->get();
        foreach($assignments as $assignment) {
            if ($assignment->module_version_id === $this->module_version_id) {
                return null;
            }
            if ($assignment->date_due > Carbon::now() && is_null($assignment->date_completed)) {
                return null;
            }
        }
        $new_assignment = new ModuleAssignment($assignment_arr);
        $new_assignment->module_version_id = $this->module_version_id;
        $new_assignment->module_id = $this->id;
        $new_assignment->date_assigned = Carbon::now();
        if (!$testonly) {
            $new_assignment->save();
        }
        return $new_assignment;
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
