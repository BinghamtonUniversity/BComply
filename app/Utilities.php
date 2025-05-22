<?php 

namespace App\Libraries;

class Utilities {

    /**
     * Takes a date formatted like YYYY-MM-DD and returns a 
     * date object
     * @param String string formatted like 2025-03-25
     * @throws ValueError
     */
    public static function string_to_date($s_date) {
        $format = 'Y-m-d';
        return DateTime::createFromFormat($format, $s_date);
    }
}

