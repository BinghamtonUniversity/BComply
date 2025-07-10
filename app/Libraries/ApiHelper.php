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

    public function string_to_date($s_date) {
        $format = 'Y-m-d';
        return Carbon::createFromFormat($format, $s_date);
    }
}