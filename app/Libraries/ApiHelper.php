<?php 

namespace App\Libraries;
use App\Group;
use App\ModuleVersion;

use App\User;
use Carbon\Carbon;

class ApiHelper {
    private $DEV_DATA_PROXY_URL = "";
    private $PROD_DATA_PROXY_URL = "";
    /**
     * lookup user by unique_id
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
    

    public function trigger_data_proxy_resync($module_id, $unique_id) {
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
        return file_get_contents($url."completed/syncOne?module_id=".$module_id."&unique_id=".$unique_id);
    }
}