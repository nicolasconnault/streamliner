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
 * @package helpers
 */
/**
 * Returns true if the currently logged in user or the given user
 * has the requested capability
 * @param string $capname
 * @param int $userid
 * @param boolean $allowdoanything Whether or not to allow the doanything cap to override everything
 * @return boolean
 */
function has_capability($capname, $user_id=null, $allowdoanything=true) {
    $ci = get_instance();

    if (empty($user_id)) {
        $usercaps = $ci->session->userdata('user_caps');
        if (empty($usercaps)) {
            return false;
        }
    } else {
        $usercaps = get_simple_user_caps($user_id);
    }
    return in_array($capname, $usercaps) || ($allowdoanything && in_array('site:doanything', $usercaps));
}

function get_simple_user_caps($user_id) {
    $ci = get_instance();
    $user_caps = $ci->user_model->get_capabilities($user_id);
    $user = $ci->user_model->get($user_id);

    $simple_user_caps = array();
    foreach ($user_caps as $cap) {
        $simple_user_caps[] = $cap->name;
    }

    return $simple_user_caps;
}

function has_capabilities($caps, $operator = 'AND', $user_id=null, $allowdoanything=true) {
    $result = true;
    foreach ($caps as $capname) {
        if ($operator == 'AND') {
            $result = $result && has_capability($capname, $user_id, $allowdoanything);
        } else if ($operator == 'OR') {
            $result = $result || has_capability($capname, $user_id, $allowdoanything);
        }
    }
    return $result;
}

/**
 * Checks if the currently logged in user has the requested
 * capability, and throws a fatal error if not.
 * @param string $capname
 * @param boolean $allowdoanything Whether or not to allow the doanything cap to override everything
 * @param string $message The message to show to the user on the unauthorised page
 * @return void
 */
function require_capability($capname, $allowdoanything=true, $message=null) {
    $ci = get_instance();
    if (!has_capability($capname, null, $allowdoanything)) {
        if (is_null($message)) {
            $message = 'You are not authorised to view this page.';
        }

        $ci->session->set_flashdata('unauthorised_message', $message);
        redirect(base_url().'access/unauthorised/'.$capname);
    }
}

function reload_session_caps($user_id) {
    $ci = get_instance();
    $user_caps = get_simple_user_caps($user_id);

    if (empty($user_caps)) {
        add_message('You do not have any permissions on this site, '
                . 'please contact the administrator to obtain site permissions.', 'danger');
        return false;
    }

    $user = $ci->user_model->get($user_id);
    $ci->session->set_userdata(array('user_caps' => $user_caps,
                                     'user_id' => $user_id,
                                     'username' => $user->username,
                                     'first_name' => $user->first_name,
                                     'last_name' => $user->last_name,
                                     'roles' => $ci->user_model->get_roles($user->id)));

}

function user_has_role($user_id, $role_name) {
    $ci = get_instance();
    if ($role = $ci->role_model->get(array('name' => $role_name), true)) {
        $role_id = $role->id;
        return $ci->user_model->has_roles($user_id, array($role_id));
    } else {
        add_message("The $role_name role doesn't exist on the system!", 'danger');
        return false;
    }
}
?>
