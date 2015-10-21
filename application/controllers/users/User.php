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
class User extends MY_Controller {

	function __construct() {
		parent::__construct();

        $this->config->set_item('exclude', array('home', 'index', 'browse'));
        $this->config->set_item('replacer', array( 'users' => array('/users/user|Staff'), 'add' => 'Create new user account', 'home' => array('user|Users')));
	}

    function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    function index($outputtype='html') {

        require_capability('users:viewusers');

        $row_action_function = function($row) {
            $roles = $this->user_model->get_roles($row[0]);
            if (empty($roles)) {
                return true;
            }

            if ($row[0] == $this->session->userdata('user_id')) {
                return false;
            }

            $role_id = reset($roles)->id;
            return $this->role_model->is_lower_in_hierarchy($role_id);
        };

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'custom_columns_callback' => $this->user_model->get_custom_columns_callback(),
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'sql_conditions' => array('users.type = "staff"'),
            'row_actions' => array('Edit this user' => 'edit', 'Edit this user\'s permissions' => 'capabilities', 'Delete this user' => 'delete'),
            'row_action_capabilities' => array('edit' => 'users:editusers', 'capabilities' => 'users:editusercaps', 'delete' => 'users:deleteusers'),
            'row_action_conditions' => array(
                'delete' => $row_action_function,
                'edit' => $row_action_function,
                'capabilities' => $row_action_function
            ),
            'show_add_button' => has_capability('users:writeusers')
        ));

        $this->datagrid->add_column(array(
            'table' => 'users',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'user_id',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'table' => 'users',
            'sql_select' => 'CONCAT(users.last_name," ",users.first_name)',
            'field' => 'id',
            'field_alias' => 'user_name',
            'label' => 'Name',
            'in_combo_filter' => true
            ));
        $this->datagrid->add_column(array(
            'table' => 'user_contacts',
            'field' => 'contact',
            'label' => 'Email',
            'field_alias' => 'user_email',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'label' => 'Roles',
            'field_alias' => 'user_roles'));
        $this->datagrid->set_joins(array(
            array('table' => 'user_contacts', 'on' => 'user_contacts.user_id = users.id AND user_contacts.default_choice = 1 AND user_contacts.type = ' . USERS_CONTACT_TYPE_EMAIL, 'type' => 'LEFT OUTER'),
            array('table' => 'users_roles', 'on' => 'users_roles.user_id = users.id', 'type' => 'LEFT OUTER'),

        ));

        $this->datagrid->setup_filters();
        $roles = $this->role_model->get_dropdown('name');
        $this->datagrid->add_dropdown_filter($roles, 'Role', 'role_id', null);

        $this->datagrid->render();
    }

    function capabilities($user_id) {

        require_capability('users:viewusers');

        $user = $this->user_model->get($user_id);
        $user_roles = $this->user_model->get_roles($user_id);
        $all_caps = $this->capability_model->get(null, false, 'name');

        // Add a better label to capabilities
        foreach ($all_caps as $key => $cap) {
            $parts = explode(':', $cap->name);
            $all_caps[$key]->label = ucfirst($parts[0]) . ' system: ' . $cap->description;
        }

        $roles_and_caps = $this->capability_model->get_with_roles();
        $role_caps = $roles_and_caps['roles'];
        $cap_roles = $roles_and_caps['capabilities'];
        $av_roles = $this->user_model->get_available_roles($user_id);

        // Build user_capabilities based on role_caps and user_roles
        $user_capabilities = array();

        if (!empty($user_roles)) {
            foreach ($user_roles as $user_role) {
                $user_role_caps = $role_caps[$user_role->id];
                foreach ($user_role_caps as $cap_id => $cap) {
                    $user_capabilities[] = $cap->cap_name;
                }
            }
        } else {
            $user_roles = array();
        }

        $available_roles = array(null => '-- Select a new role --');

        foreach ($av_roles as $role) {
            $available_roles[$role->id] = $role->name;
        }

        // Set up title bars
        $add_title = "Add a role to " . $this->user_model->get_name($user);
        $add_title_options = array('title' => $add_title, 'help' => $add_title, 'expand' => 'add', 'icons' => array());
        $roles_title = "Roles for " . $this->user_model->get_name($user);
        $roles_title_options = array('title' => $roles_title, 'help' => $roles_title, 'expand' => 'roles', 'icons' => array());
        $capabilities_title = "Capabilities for " . $this->user_model->get_name($user);
        $capabilities_title_options = array('title' => $capabilities_title, 'help' => $capabilities_title, 'expand' => 'capabilities', 'icons' => array());

        $pageDetails = array('title' => 'Staff Permissions for ' . $this->user_model->get_name($user),
                             'add_title_options' => $add_title_options,
                             'roles_title_options' => $roles_title_options,
                             'capabilities_title_options' => $capabilities_title_options,
                             'content_view' => 'users/user/role_edit',
                             'user_roles' => $user_roles,
                             'user_capabilities' => $user_capabilities,
                             'available_roles' => $available_roles,
                             'role_caps' => $role_caps,
                             'all_caps' => $all_caps,
                             'feature_type' => 'Streamliner Core',
                             'cap_roles' => $cap_roles,
                             'user_id' => $user_id,
                             'jstoloadinfooter' => array('jquery/jquery.json',
                                                         'jquery/jquery.url',
                                                         'jquery/datatables/media/js/jquery.dataTables',
                                                         'datatable_pagination',
                                                         'application/users/user_role_edit')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    function add_role($user_id, $role_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->assign_role($user_id, $role_id);

        if ($result) {
            add_message('Role successfully added!', 'success');
        } else {
            add_message('Role could not be added!', 'danger');
        }

        redirect(base_url().'users/user/capabilities/'.$user_id);

    }

    function delete_user_role($user_id, $role_id) {

        require_capability('users:unassignroles');
        $result = $this->user_model->unassign_role($user_id, $role_id);

        if ($result) {
            add_message('Role successfully removed!', 'success');
        } else {
            add_message('Role could not be removed!', 'danger');
        }

        redirect(base_url().'users/user/capabilities/'.$user_id);

    }

    function add() {
        require_capability('users:writeusers');
        $this->load->helper('dropdowns');

        // Set up title bars
        // Set up title bars
        $top_title_options = array(
            'title' => "Adding a Staff",
            'help' => 'Use this page to create a new staff account',
            'icons' => array()
        );

        $details_title_options = array(
            'title' => "Personal Details",
            'help' => null,
            'level' => 2,
            'icons' => array()
        );

        $contacts_title_options = array(
            'title' => "Contacts",
            'help' => 'Use this section to add, edit or delete contact details.',
            'level' => 2,
            'icons' => array()
        );

        $this->config->set_item('replacer', array('add' => $top_title_options['title'], 'users' => array('/users/user|Staff')));

        $pageDetails = array(
                'title' => $top_title_options['title'],
                'top_title_options' => $top_title_options,
                'details_title_options' => $details_title_options,
                'contacts_title_options' => $contacts_title_options,
                'type' => 'staff',
                'csstoload' => array(),
                'feature_type' => 'Streamliner Core',
                'form_action' => base_url().'users/user/process_edit',
                'jstoloadinfooter' => array('jquery/jquery.domec',
                                            'jquery/jquery.form',
                                            'jquery/jquery.json',
                                            'jquery/jquery.loading',
                                            'jquery/pause',
                                            'jquery/jquery.selectboxes',
                                            'application/users/user_edit'),
                'content_view' => 'users/user/edit');
        $this->load->view('template/default', $pageDetails);
    }

    function edit($user_id) {
        $this->config->set_item('replacer', array('users' => array('/users/user|Staff')));
        if ($this->session->userdata('user_id') == $user_id) {
            require_capability('users:editownaccount', true, 'Your current permissions do not allow you to edit your own account details.');
        } else {
            require_capability('users:editusers');
        }

        $this->load->helper('dropdowns');

        $this->load->helper('secure_hash');

        $user = $this->user_model->get($user_id);

        form_element::$default_data = array('user_id' => $user_id,
                                            'action' => 'edit_user',
                                            'first_name' => $user->first_name,
                                            'last_name' => $user->last_name,
                                            'username' => $user->username
                                            );

        // Set up title bars
        $top_title_options = array(
            'title' => "Editing Staff " . $this->user_model->get_name($user),
            'help' => 'Use this page to edit this Staff\'s personal details, and contact details',
            'icons' => array()
        );

        $details_title_options = array(
            'title' => "Personal Details",
            'help' => null,
            'level' => 2,
            'icons' => array()
        );

        $contacts_title_options = array(
            'title' => "Contacts",
            'help' => 'Use this section to add, edit or delete contact details.',
            'level' => 2,
            'icons' => array()
        );

        $this->config->set_item('replacer', array('edit' => $top_title_options['title'], 'users' => array('/users/user|Staff')));

        $pageDetails = array(
                'title' => $top_title_options['title'],
                'top_title_options' => $top_title_options,
                'details_title_options' => $details_title_options,
                'contacts_title_options' => $contacts_title_options,
                'feature_type' => 'Streamliner Core',
                'csstoload' => array(),
                'type' => 'staff',
                'user_id' => $user_id,
                'form_action' => base_url().'users/user/process_edit',
                'jstoload' => array('jquery/jquery.domec',
                                            'jquery/jquery.form',
                                            'jquery/jquery.json',
                                            'jquery/pause',
                                            'jquery/jquery.selectboxes',
                                            'application/users/user_edit'),
                'content_view' => 'users/user/edit');
        $this->load->view('template/default', $pageDetails);
    }

    function process_edit() {
        $user_id = $this->input->post('user_id');
        $editing_own_account = false;

        if ($this->session->userdata('user_id') == $user_id) {
            require_capability('users:editownaccount');
            if (!has_capability('users:editusers')) {
                $editing_own_account = true;
            }
        } else {
            require_capability('users:editusers');
        }

        $this->load->helper('secure_hash');

        if (!IS_AJAX && !$debug) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $json_data = array('errors' => array());

        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $username = $this->input->post('username');
        $user_id = $this->input->post('user_id');
        $password = $this->input->post('password');

        if (empty($first_name)) {
            $json_data['errors']['first_name'] = 'Please enter a first name for this staff.';
        }

        /*
        if (empty($last_name)) {
            $json_data['errors']['last_name'] = 'Please enter a Last name for this staff.';
        }
        */

        // Test username for uniqueness
        if (!$user_id && $this->user_model->get(array('username' => $username), true)) {
            $json_data['errors']['username'] = 'This username is already in use, please choose another one.';
        }

        $user = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'username' => $username,
                'signature' => $this->input->post('signature'),
                'type' => 'staff'
            );

        if (!empty($password)) {
            $user['password'] = create_hash($password);
        }

        if (empty($json_data['errors'])) {
            if (empty($user_id)) {
                if (!($user_id = $this->user_model->add($user))) {
                    $json_data['message'] = 'The staff could not be entered into the database for an unknown reason.';
                    $json_data['type'] = 'danger';
                } else {
                    $json_data['user_id'] = $user_id;
                    $json_data['message'] = 'The new staff was successfully recorded';
                    $json_data['type'] = 'success';
                }
            } else {
                if (!$this->user_model->edit($user_id, $user)) {
                    $json_data['message'] = 'The staff details could not be updated for an unknown reason.';
                    $json_data['type'] = 'danger';
                } else {
                    $json_data['user_id'] = $user_id;
                    $json_data['message'] = 'The staff\'s details were successfully updated';
                    $json_data['type'] = 'success';
                }
            }
        } else {
            $json_data['message'] = 'Some data entry errors prevented the processing of this staff. See error messages in red below.';
            $json_data['type'] = 'danger';
        }

        if (empty($json_data['message']) && empty($json_data['errors'])) {
            $json_data['message'] = 'No changes made to this staff';
            $json_data['type'] = 'warning';
        }
        echo json_encode($json_data);
    }

    function get_data($user_id) {
        $debug = true;
        $this->load->helper('secure_hash');

        if (!IS_AJAX && !$debug) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $user = $this->user_model->get($user_id);
        $addresses = array();

        $user_data = array('user_id' => $user_id,
                           'first_name' => $user->first_name,
                           'last_name' => $user->last_name,
                           'username' => $user->username,
                           'signature' => $user->signature,
                           'addresses' => $addresses
                           );

        $user_data['emails'] = array();
        $user_data['phones'] = array();
        $user_data['mobiles'] = array();
        $user_data['faxes'] = array();

        $emails = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_EMAIL, false);

        foreach ($emails as $email) {
            $user_data['emails'][] = (array) $email;
        }

        $workphones = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_PHONE, false);
        foreach ($workphones as $workphone) {
            $user_data['phones'][] = (array) $workphone;
        }

        $faxes = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_FAX, false);
        foreach ($faxes as $fax) {
            $user_data['faxes'][] = (array) $fax;
        }

        $mobiles = $this->user_contact_model->get_by_user_id($user_id, USERS_CONTACT_TYPE_MOBILE, false);
        foreach ($mobiles as $mobile) {
            $user_data['mobiles'][] = (array) $mobile;
        }

        echo json_encode($user_data);
    }

    function update_default_contact($contact_id) {

        $debug = true;

        if (!IS_AJAX && !$debug) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $this->user_contact_model->set_as_default($contact_id);
        $contact = $this->user_contact_model->get($contact_id);

        $data['message'] = 'This ' . get_lang_for_constant_value('USERS_CONTACT_TYPE_', $contact->type) . ' has been set as the default.';
        $data['type'] = 'success';
        echo json_encode($data);
    }

    function set_notification($contact_id, $value) {

        if (!IS_AJAX) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $value = ($value == 'false') ? 0 : 1;
        $not = ($value) ? '' : 'NOT';

        $this->user_contact_model->edit($contact_id, array('receive_notifications' => $value));

        $data['message'] = "This email address has been set to $not receive notifications.";
        $data['type'] = 'success';

        echo json_encode($data);
    }

    function delete_contact($contact_id) {

        if (!IS_AJAX) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }

        $contact = $this->user_contact_model->get($contact_id);
        $result = $this->user_contact_model->delete($contact_id);
        $data['message'] = 'The ' . get_lang_for_constant_value('USERS_CONTACT_TYPE_', $contact->type) . ' has been successfully deleted';
        $data['type'] = 'success';
        echo json_encode($data);
    }

    function save_contact() {

        if (!IS_AJAX) {
            show_error("This page can only be accessed through an AJAX request!");
            return false;
        }


        $data = array('errors' => array());

        $contact_id = $this->input->post('contact_id');
        $field_name = $this->input->post('field_name');
        preg_match('/(fax|email|phone|mobile)\[([0-9])*\]/', $field_name, $matches);
        $type_label = $matches[1];
        $contact_type = constant('USERS_CONTACT_TYPE_'.strtoupper($type_label));

        $contact_data['user_id'] = $this->input->post('user_id');
        $contact_data['type'] = $contact_type;
        $contact_data['contact'] = $this->input->post('value');

        // Email validation
        if ($contact_type == USERS_CONTACT_TYPE_EMAIL && !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $contact_data['contact'])) {
            $data['message'] = 'Invalid email address!';
            $data['type'] = 'danger';
            $index = 'email_0';
            if ($contact_id) {
                $index = "email_$contact_id";
            }
            $data['errors'][$index] = 'This email address is invalid, please correct it.';
            echo json_encode($data);
            die();
        }

        if ($contact_id) {
            $this->user_contact_model->edit($contact_id, $contact_data);
            $data['message'] = 'The ' . $type_label . ' has been successfully updated';
        } else {
            $this->user_contact_model->add($contact_data);
            $data['message'] = 'The ' . $type_label . ' has been successfully added';
        }

        $data['type'] = 'success';
        echo json_encode($data);
    }
}
?>
