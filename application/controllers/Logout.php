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
/**
 * @package controllers
 */

class Logout extends MX_Controller {
    function __construct() {
        parent::__construct();
    }

    function index() {
        add_message('You have been successfully logged out.', 'success');
        $this->login_model->logout();
        $user_login = $this->user_login_model->get(array('session_id' => session_id()), true);
        $this->user_login_model->edit($user_login->id, array('status' => 'Suspended'));
        $this->session->sess_destroy();
        redirect(base_url().'login');
    }
}
