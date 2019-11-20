<?php

namespace App\Policies;

use App\Module;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage modules.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function manage_modules(User $user)
    {
        if(in_array('manage_modules',$user->user_permissions)){
            return true;
        }
    }

}
