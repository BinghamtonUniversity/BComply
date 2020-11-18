<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','description','icon','owner_user_id','message_configuration','assignment_configuration','module_version_id','reminders','public','past_due','past_due_reminders','templates'];
    protected $casts = ['message_configuration' => 'object','assignment_configuration'=>'object','reminders'=>'object','past_due_reminders'=>'object','templates'=>'object'];
    protected $hidden = ['permissions'];
    protected $appends = ['module_permissions'];
    protected $with = ['permissions'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function current_version(){
        return $this->belongsTo(ModuleVersion::class,'module_version_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions(){
        return $this->hasMany(ModulePermission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(){
        return $this->belongsTo(User::class,'owner_user_id');
    }

    /**
     * @param array $assignment_arr
     * @param bool $testonly
     * @return ModuleAssignment|bool|null
     */
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
            if (!is_null($assignment->date_due)
                    && $assignment->date_due > Carbon::now()
                    && is_null($assignment->date_completed)) {
                return null;
            }
            //Code below was commented out after meeting with Aaron on 11/18/2020
//            if(is_null($assignment->date_completed) && $assignment->date_due < Carbon::now()){
//                $assignment->status = 'incomplete';
//                $assignment->date_started = now();
//                $assignment->date_completed = now();
//                $assignment->save();
//            }
        }
        $new_assignment = new ModuleAssignment($assignment_arr);
        $new_assignment->module_version_id = $this->module_version_id;
        $new_assignment->module_id = $this->id;
        $new_assignment->date_assigned = Carbon::now();
        $new_assignment->status = 'assigned';
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
