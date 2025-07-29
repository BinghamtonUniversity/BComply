<?php 

namespace App\Libraries;

use App\User;
use Carbon\Carbon;

class ApiHelper {
    /**
     * lookup user by b_number
     * 
     * returns null if not found
     */
    public function get_user_for_unique_id($unique_id) {
        $user = User::where('unique_id', $unique_id)->first();
        if (empty($user)){
            $user = null;
        }
        return $user;
    }

    /**
     * lookup a group based on the slug
     * 
     * returns null if not found
     */
    public function get_group_for_slug($group_slug) {
        $group = Group::where('slug', $group_slug)->first();
        if (empty($group)) {
            $group = null;
        }
        return $group;
    }

    /**
     * Takes a date formatted like YYYY-MM-DD and returns a 
     * date object
     * @param String string formatted like 2025-03-25
     * @throws ValueError
     */
    public function string_to_date($s_date) {
        $format = 'Y-m-d';
        return Carbon::createFromFormat($format, $s_date);
    }

    /**
     * Create the module assignment for the user_id and add it to the table or update it if it 
     *  does not already exists
     */
    public function add_or_update_module_assignment($user_id, $version, $module_id, $due_date, $not_completed_after, $current_user){
        $now = now()->timestamp;
        $module_assignment = ModuleAssignment::select()
                ->where('module_id', $module_id)
                ->where('module_version_id', $version)
                ->where('user_id', $user_id)
                ->where('status', 'completed');
        if ($not_completed_after != null) {
            $module_assignment = $module_assignment->where('date_completed', '>=', $not_completed_after);
        }
        $module_assignment = $module_assignment->first();
        if ($module_assignment == null) {
            $module_assignment = ModuleAssignment::select()
                ->where('module_id', $module_id)
                ->where('module_version_id', $version)
                ->where('user_id', $user_id);
            if ($module_assignment == null) {
                $module_assignment = new ModuleAssignment([
                    'user_id' => $user_id,
                    'module_version_id' => $version,
                    'module_id' => $module_id,
                    'due_date' => $due_date,
                    'date_assigned' => $now,
                    'assigned_by_user_id' => $current_user,
                    'status' => 'assigned']
                );
                $module_assignment->save();
            } else {
                $module_assignment->updated_at = $now;
            }
            
        } 
    }
}