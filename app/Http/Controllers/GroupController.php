<?php

namespace App\Http\Controllers;

use App\Group;
use App\GroupMembership;
use App\User;
use App\SimpleUser;
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
        GroupMembership::where('group_id',$group->id)->delete();
        $group->delete();
        return 'Success';
    }

    public function get_members(Request $request, Group $group){
        if ($request->has('simple') && $request->simple === "true") {
            $users = GroupMembership::where('group_id',$group->id)
                ->with('simple_user')
                ->select('type','user_id')
                ->get();
            return $users;
        } else {
            return GroupMembership::where('group_id',$group->id)->with('user')->get();
        }
    }

    public function add_member(Request $request, Group $group){
        $group_membership = new GroupMembership([
           'group_id'=>$group->id,
           'user_id'=>$request->user_id,
           'type'=>'internal',
        ]);
        $group_membership->save();
        return GroupMembership::where('id',$group_membership->id)->with('user')->first();
    }
    public function bulk_add_members(Request $request, Group $group){
        $unique_ids = array_unique(preg_split('/(,|\n| )/',$request->unique_ids,-1, PREG_SPLIT_NO_EMPTY));
        $group_memberships = GroupMembership::where('group_id',$group->id)->select('group_id','user_id')->get();
        $users = User::select('id','unique_id')->get();
        $skipped = [];
        $ignored = [];
        $added = [];

        foreach ($unique_ids as $unique_id ) {
            $user = $users->where('unique_id',$unique_id)->first();
            if(!is_null($user)) {
                if($group_memberships->where('user_id',$user->id)->isEmpty()){
                    $group_membership = new GroupMembership([
                        'group_id' => $group->id,
                        'user_id' => $user->id,
                        'type' => 'internal',
                    ]);
                    $group_membership->save();
                    $added[] = $unique_id;
                } else {
                    $ignored[] = $unique_id;
                }
            } else {
                $skipped[] = $unique_id;
            }
        }
        return ["skipped"=>$skipped,"ignored"=>$ignored,"added"=>$added];
    }

    public function delete_member(Group $group,User $user)
    {
        return GroupMembership::where('group_id','=',$group->id)->where('user_id','=',$user->id)->delete();
    }
}
