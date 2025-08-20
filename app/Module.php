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

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
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

    public function assignments(){
        return $this->hasMany(ModuleAssignment::class);
    }

    /**
     * @param array $assignment_arr
     * @param bool $testonly
     * @return ModuleAssignment|bool|null
     */

    // Returns false if the module does not have a current version
    // Returns null if the user is already assigned to this module
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
            if (is_null($assignment->date_completed) && !$testonly){
                $assignment->status = 'incomplete';
                if (is_null($assignment->date_started)) {
                    $assignment->date_started = now();
                }
                $assignment->date_completed = now();
                $assignment->save();
            }
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

    /**
     * returns which versions are allow for a module if only the latest version is 
     * accepted with a grace period if the latest version was recently 
     * created
     */
    public function get_allowed_versions($grace_period) {
        $allowed_versions = [];
        $days_ago_timestamp = strtotime("-$grace_period days");
        $days_ago_date = date("Y-m-d", $days_ago_timestamp);
        $versions = ModuleVersion::select('id', 'created_at')
            ->where('deleted_at', null)
            ->where('module_id', $this->id)
            ->orderBy('id', 'desc')
            ->limit(2)->get();
        if (count($versions) > 1) {
            $allowed_versions[] = $versions[0]->id;
            $last_create_date = $versions[0]->created_at;
            if ($days_ago_date < $this->string_to_date($last_create_date)) {
                if (count($versions) > 1) {
                   $allowed_versions[] = $versions[1]->id;
                }
            }
        }
        return $allowed_versions;
    }

}
