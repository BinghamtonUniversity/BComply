<?php 

namespace App\Libraries;
use App\ModuleVersion;

use App\User;
use Carbon\Carbon;

class ApiHelper {
    private $DEV_DATA_PROXY_URL = "https://hermesdev.binghamton.edu/bcomply/";
    private $PROD_DATA_PROXY_URL = "https://hermesprod.binghamton.edu/bcomply/";
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
        $truncated = substr($s_date, 0, 10);
        $format = 'Y-m-d';
        return Carbon::createFromFormat($format, $truncated);
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

    public function get_allowed_versions($module_id, $grace_period) {
        $allowed_versions = [];
        $days_ago_timestamp = strtotime("-$grace_period days");
        $days_ago_date = date("Y-m-d", $days_ago_timestamp);
        $versions = ModuleVersion::select('id', 'created_at')
            ->where('deleted_at', null)
            ->where('module_id', $module_id)
            ->orderBy('id', 'desc')
            ->limit(2)->get();
        if (count($versions) > 1) {
            $allowed_versions[] = $versions[0]->id;
            $last_create_date = $versions[0]->created_at;
            if ($days_ago_date < $this->string_to_date($last_create_date)) {
                if (count($versions) > 1) {
                   $allowed_versions[] = $versions[1]->id;
                }
            }
        }
        return $allowed_versions;
    }

    public function trigger_data_proxy_resync($module_id, $bnumber) {
        if (is_set($_SERVER['HTTP_HOST'])){
            $this_url = $_SERVER['HTTP_HOST'];
            if (str_contains(strtolower($this_url), "localhost")) {
                $url = $DEV_DATA_PROXY_URL;
            } else if (str_contains(strtolower($this_url), "bcomplydev")) {
                $url = $DEV_DATA_PROXY_URL;
            } else {
                $url = $PROD_DATA_PROXY_URL;
            }
        } else {
            $url = $PROD_DATA_PROXY_URL;
        }
        return file_get_contents($url."ods/completed/syncOne?module_id=".$module_id."&bnumber=".$bnumber);
    }
}