<?php

namespace App\Policies;

use App\Module;
use App\ModuleAssignment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class ModuleAssignmentPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view the module assignment.
     *
     * @param  \App\User  $user
     * @param  \App\ModuleAssignment  $moduleAssignment
     * @return mixed
     */
    public function view(User $user, ModuleAssignment $moduleAssignment)
    {
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
            ->with('version')->get()->unique('module_id');
        $elected_assignments=[];
        foreach ($assignments as $assignment){
            if(is_null($assignment->date_completed)){
                $elected_assignments[]=$assignment->id;
            }
        }
        if(in_array($moduleAssignment->id, $elected_assignments)){
            return true;
        }
    }
    public function complete_policy(User $user, ModuleAssignment $moduleAssignment){
        $module = Module::where('id','=',$moduleAssignment->module_id)->first();
        if(in_array('assign_modules',$user->user_permissions)){
            return true;
        }
        // Must first check if the property exists!
        if (property_exists($user->module_permissions,$module->id) &&
            in_array('assign',$user->module_permissions->{$module->id})) {
            return true;
        }
        if ($module->owner_user_id === $user->id){
            return true;
        }
        return false;
    }
}
