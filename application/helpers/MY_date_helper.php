<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */

/**
 * Replaces the human_to_unix function of the core Date helper, using the format dd/mm/yyyy instead of yyyy-mm-dd
 * @param string $humandate
 * @return int Unix timestamp
 */
function human_to_unix($humandate, $original_format=false) {
    if (empty($humandate)) {
        return null;
    }

    $humandate = str_replace(' ', '', $humandate);
    $humandate = str_replace('-', '', $humandate);
    $humandate = str_replace(':', '', $humandate);
    $humandate = str_replace('/', '', $humandate);

    if ($original_format) {
		$humandate = preg_replace('/\040+/', ' ', trim($humandate));
        $year  = substr($humandate, '0', '4');
        $month = substr($humandate, '4', '2');
        $day   = substr($humandate, '6', '2');
        $hours  = substr($humandate, '8', '2');
        $minutes  = substr($humandate, '10', '2');

    } else {
        $year  = substr($humandate, '4', '4');
        $month = substr($humandate, '2', '2');
        $day   = substr($humandate, '0', '2');
        $hours  = substr($humandate, '8', '2');
        $minutes  = substr($humandate, '10', '2');
    }

    $timestamp = mktime($hours,$minutes,0, $month, $day, $year);
    // If an invalid format was passed, return the current time
    if ($timestamp == -1) {
        return mktime();
    } else {
        return $timestamp;
    }
}

/**
 * Shortcut to formatting a UNIX timestamp to dd/mm/YYYY format, useful for jquery ui calendar widgets
 */
function unix_to_human($timestamp, $format='%d/%m/%Y') {
    if (empty($timestamp)) {
        return null;
    }
    return mdate($format, $timestamp, false, 'eu');
}

function get_month_name($month) {
    return date('F', mktime(0,0,0,$month, 1, 2000));
}

function get_duration($minutes) {
    if ($minutes < 60) {
        return "$minutes min";
    } else {
        $hours = floor($minutes / 60);
        $remainder = $minutes % 60;
        return "$hours:$remainder hrs";
    }
}
