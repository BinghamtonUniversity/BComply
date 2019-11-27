<?php

namespace App\Policies;

use App\Group;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any groups.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view_in_admin(User $user){
        return $this->manage_groups($user);
    }

    public function manage_groups(User $user)
    {
        if(in_array('manage_groups',$user->user_permissions)){
            return true;
        }
    }
    public function manage_group_membership(User $user){
        if(in_array('manage_groups',$user->user_permissions)
        || in_array('manage_users',$user->user_permissions) ){
            return true;
        }
    }

    /**
     * Determine whether the user can view the group.
     *
     * @param  \App\User  $user
     * @param  \App\Group  $group
     * @return mixed
     */
//    public function view(User $user, Group $group)
//    {
//        //
//    }
//
//    /**
//     * Determine whether the user can create groups.
//     *
//     * @param  \App\User  $user
//     * @return mixed
//     */
//    public function create(User $user)
//    {
//        //
//    }
//
//    /**
//     * Determine whether the user can update the group.
//     *
//     * @param  \App\User  $user
//     * @param  \App\Group  $group
//     * @return mixed
//     */
//    public function update(User $user, Group $group)
//    {
//        //
//    }
//
//    /**
//     * Determine whether the user can delete the group.
//     *
//     * @param  \App\User  $user
//     * @param  \App\Group  $group
//     * @return mixed
//     */
//    public function delete(User $user, Group $group)
//    {
//        //
//    }
//
//    /**
//     * Determine whether the user can restore the group.
//     *
//     * @param  \App\User  $user
//     * @param  \App\Group  $group
//     * @return mixed
//     */
//    public function restore(User $user, Group $group)
//    {
//        //
//    }
//
//    /**
//     * Determine whether the user can permanently delete the group.
//     *
//     * @param  \App\User  $user
//     * @param  \App\Group  $group
//     * @return mixed
//     */
//    public function forceDelete(User $user, Group $group)
//    {
//        //
//    }
}
