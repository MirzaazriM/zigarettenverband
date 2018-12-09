<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09/12/2018
 * Time: 09:18
 */

namespace App\Service;


class RegexCheckerService
{

    /**
     * Check if $value matches the given regex pattern
     *
     * @param $regexPattern
     * @param $value
     * @return bool
     */
    public function checkRegex($regexPattern, $value) {
        // check pattern
        if (preg_match($regexPattern, $value)) {
            // if matches return true
            return true;
        } else {
            // return false
            return false;
        }
    }
}