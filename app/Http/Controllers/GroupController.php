<?php

namespace App\Http\Controllers;

use App\Group;
use App\GroupMembership;
use App\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function get_all_groups(){
        return Group::all();
    }
    public function get_group(Group $group){
        return $group;
    }

    public function add_group(Request $request){
        $group = new Group($request->all());
        $group->save();
        return $group;
    }
    public function update_group(Request $request, Group $group){
        $group->update($request->all());
        return $group;
    }
    public function delete_group(Request $request, Group $group){
        $group->delete();
        return true;
    }

    public function get_group_memberships(){
        $group_membership =GroupMembership::all();
        return $group_membership;
    }

    public function add_group_membership(Group $group,User $user){
        $group_membership = new GroupMembership([
           'group_id'=>$group->id,
           'user_id'=>$user->id
        ]);
        $group_membership->save();
        return $group_membership;
    }

    public function delete_group_membership(GroupMembership $groupMembership,Group $group,User $user)
    {
        return (GroupMembership::where('group_id', $group and 'user_id', $user)->delete());
    }
}
