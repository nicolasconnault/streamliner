<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
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
class Login_Model extends CI_Model {
    /**
     * Authenticates a user by username/password credentials. Uses the 'users' DB table.
     * @uses User_Model
     * @access public
     * @param string $username
     * @param string $password
     * @return bool
     */
    function auth_user($username, $password) {
        $this->load->helper('secure_hash');
        $params = array('username' => $username, 'status' => 'Active');

        if ($user = $this->user_model->get($params, true)) {
            if (validate_password($password, $user->password)) {
                log_message('info', 'User ' . $this->user_model->get_name($user->id) . ' has just logged in!');
                reload_session_caps($user->id);
                return true;
            } else {
                add_message('Incorrect username or password, please verify your details and try again.', 'danger');
                return false;
            }
        } else {
            add_message('Incorrect username or password, please verify your details and try again.', 'danger');
            return false;
        }
    }

    /**
     * Logs the currently logged-in user out, using DB-based session variables. Also removes capabilities from session data.
     */
    function logout() {
        log_message('info', 'User ' . $this->user_model->get_name($this->session->userdata('user_id')) . ' has just logged out!');
        $this->session->unset_userdata('user_caps');
        $this->session->unset_userdata('user_id');
    }

    /**
     * Checks whether the user is logged in by looking at session variables held in DB.
     * @return bool
     */
    function check_session() {
        $usercaps = $this->session->userdata('user_caps');
        return $this->session->userdata('user_id') && !empty($usercaps);
    }
}
?>
