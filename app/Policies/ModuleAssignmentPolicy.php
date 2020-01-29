<?php

namespace App\Policies;

use App\Http\Middleware\PublicAPIAuth;
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
        return ($moduleAssignment->user_id == $user->id);
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
    public function certificate_policy(User $user,ModuleAssignment $moduleAssignment){
        if(($moduleAssignment->status==='completed')||($moduleAssignment->status==='passed')){
            return true;
        }
        return false;
    }
}
