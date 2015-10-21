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
 * Use this function to append several messages to the 'message' flashdata or userdata variable
 * @param string $message
 * @param bool $delayed If true, will put variable in flashdata, otherwise in userdata for immediate use. Don't forget to clear the userdata once the message has been shown!
 */
function add_message($message, $type='success', $delayed=false) {
    $ci = get_instance();
    $current_message = ($delayed) ? $ci->session->userdata('flash:new:message') : $ci->session->userdata('message');
    if (!empty($current_message)) {
        if ($delayed) {
            $ci->session->set_flashdata('message', $current_message . "<br />$message");
            $ci->session->set_flashdata('message_type', $type);
        } else {
            $ci->session->set_userdata('message', $current_message . "<br />$message");
            $ci->session->set_userdata('message_type', $type);
        }
    } else {
        if ($delayed) {
            $ci->session->set_flashdata('message', $message);
            $ci->session->set_flashdata('message_type', $type);
        } else {
            $ci->session->set_userdata('message', $message);
            $ci->session->set_userdata('message_type', $type);
        }
    }
}

function clear_messages() {
    $ci = get_instance();
    $ci->session->set_userdata('flash:new:message', null);
    $ci->session->set_userdata('flash:new:message_type', null);
    $ci->session->set_userdata('message', null);
    $ci->session->set_userdata('message_type', null);
}
