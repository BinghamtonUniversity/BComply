<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function manage_users(User $user)
    {
        if (in_array('manage_users',$user->user_permissions)) {
            return true;
        }
    }

}
