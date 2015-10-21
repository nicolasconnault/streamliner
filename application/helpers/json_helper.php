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
function send_json_message($message, $type='success', $extra_params=array()) {
    $json_params = array(
        'message' => $message,
        'type' => $type
    );

    $json_params += $extra_params;

    send_json_data($json_params);
}

function send_json_data($data) {
    $data = (array) $data;
    $json_object = new stdClass();
    foreach ($data as $key => $val) {
        $json_object->$key = $val;
    }

    echo json_encode($json_object);
}
