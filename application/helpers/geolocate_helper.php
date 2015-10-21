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

function get_coordinates_from_address($address) {
    $url_encoded_address = urlencode($address);
    $geocode=file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$url_encoded_address&sensor=false");

    $output= json_decode($geocode);

    if (empty($output->results[0])) {
        return null;
    } else {
        return $output->results[0]->geometry->location->lat.','. $output->results[0]->geometry->location->lng;
    }
}

function get_address_from_coordinates($coordinates) {
    $geocode = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=$coordinates&sensor=false");

    $output= json_decode($geocode);

    if (empty($output->results[0])) {
        return null;
    } else {
        return $output->results[0]->formatted_address;
    }
}
