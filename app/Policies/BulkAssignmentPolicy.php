<?php

namespace App\Policies;

use App\BulkAssignment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BulkAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bulk assignments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function manage_bulk_assignments(User $user)
    {
        if(in_array('manage_bulk_assignments',$user->user_permissions)){
            return true;
        }
    }
}
