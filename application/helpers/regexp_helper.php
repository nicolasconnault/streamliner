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
function nl2br_revert($string) {
    return str_replace(
        array("<br>\n",   "<br>\r",   "<br />\n", "<br />\r"),
        array("<br />\n", "<br />\r",       "\n",       "\r"),
        $string
    );
}

/**
 * Given a comma-separate list of email addresses (with option name <address> syntax),
 * parses it and returns an array of arrays (array('name' => $name, 'address' => $address))
 */
function process_email_list($string) {
    $addresses = explode(',', $string);
    $return_addresses = array();

    foreach ($addresses as $address) {
        $address_array = array();
        if (preg_match('/(.*)\w?<([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})>/i', $address, $matches)) {
            $address_array['name'] = trim($matches[1]);
            $address_array['address'] = trim($matches[2]);
        } else {
            $address_array['name'] = null;
            $address_array['address'] = trim($address);
        }

        if (!empty($address_array['address'])) {
            $return_addresses[] = $address_array;
        }
    }

    return $return_addresses;
}

function is_currency($string) {
    return preg_match('/(?=.)^\$?(([1-9][0-9]{0,2}(,[0-9]{3})*)|[0-9]+)?(\.[0-9]{1,2})?$/', $string);
}
?>
