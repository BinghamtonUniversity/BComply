<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\SimpleUser;
use App\Group;

class PublicAPIController extends Controller
{

    private function sync_users(&$remote_users, &$local_users) {
        $status=['warnings'=>[],'updated'=>[],'added'=>[],'ignored'=>[]];
        // return $remote_users;
        foreach($remote_users as $remote_user) {
            if (isset($remote_user['unique_id'])) {
                $local_user = $local_users->firstWhere('unique_id', $remote_user['unique_id']);
                if (is_null($local_user)) {
                    // User Does Not Exist... Creating
                    $local_user = new SimpleUser($remote_user);
                    $local_user->save();
                    $local_users->push($local_user);
                    $status['added'][] = $local_user->unique_id;
                } else {
                    // User Exists... Perform diff to see if anything changed
                    // Get any user changes (Only for valid user attributes)
                    $diff = array_diff_assoc(array_intersect_key($remote_user,$local_user->getAttributes()),$local_user->getAttributes());
                    if (count($diff) > 0) {
                        // User Changed ... Update User
                        $local_user->update($remote_user);
                        $status['updated'][] = $local_user->unique_id;
                    } else {
                        // User Unchanged... Ignoring
                        $status['ignored'][] = $local_user->unique_id;
                    }
                }
            } else {
                $status['warnings'][] = 'User Missing unique_id';
            }
        }
        return $status;
    }

    private function sync_groups(&$remote_groups, &$local_users, &$local_groups) {
        $status = [];
        foreach($remote_groups as $remote_group => $group_members) {
            $local_group = $local_groups->firstWhere('name', $remote_group);
            if (is_null($local_group)) {
                // Group Does Not Exist... Creating
                $local_group = new Group(['name'=>$remote_group]);
                $local_group->save();
                $local_groups->push($local_group);
            }
            foreach($group_members as $unique_id) {

            }
        }
        return $status;
    }

    public function sync(Request $request) {
        $response = [];
        $local_users = SimpleUser::with('group_memberships')->get();    
        if ($request->has('users')) {
            $remote_users = $request->users;
            $response['users'] = $this->sync_users($remote_users, $local_users);
        }
        if ($request->has('users')) {
            $remote_groups = $request->groups;
            $local_groups = Group::all();
            $response['groups'] = $this->sync_groups($remote_groups, $local_users, $local_groups);
        }
        return $response;
    }
}