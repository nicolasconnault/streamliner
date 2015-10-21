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
class Role extends MY_Controller {

	function __construct() {
		parent::__construct();
        $this->config->set_item('replacer', array('users' => array('/users/role/browse|Roles')));
        $this->config->set_item('exclude', array('browse'));
	}

    function index() {
        return $this->browse();
    }

    function browse($outputtype='html') {
        require_capability('users:viewroles');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'available_export_types' => array(),
            'paginate' => false,
            'row_actions' => array('Edit this role' => 'edit',
                                  'Edit users for this role' => 'user_edit',
                                  'Edit capabilities for this role' => 'capabilities',
                                  'Duplicate this role' => 'duplicate',
                                  'Delete this role' => 'delete'),
            'show_add_button' => false,
            'row_action_capabilities' => array(
                'capabilities' => 'users:editroles'
            ),
            'row_action_conditions' => array(
                'user_edit' => function($row) {
                    return $this->role_model->is_lower_in_hierarchy($row[0]);
                }
            ),
            'custom_columns_callback' => $this->role_model->get_custom_columns_callback()
        ));

        $this->datagrid->add_column(array(
            'table' => 'roles',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'role_id',
            'width' => 20
        ));
        $this->datagrid->add_column(array(
            'table' => 'roles',
            'field' => 'name',
            'label' => 'Name',
            'field_alias' => 'role_name'
        ));
        $this->datagrid->add_column(array(
            'table' => 'roles',
            'field' => 'parent_id',
            'label' => 'Parent',
            'field_alias' => 'role_parent_id'
        ));
        $this->datagrid->add_column(array(
            'table' => 'roles',
            'field' => 'description',
            'label' => 'Description',
            'field_alias' => 'role_description'
        ));

        $this->datagrid->render();
    }

    function view($role_id) {
        $caps = $this->role_model->get_capabilities($role_id);
    }

    function edit($role_id) {

        $role_id = (int) $role_id;
        require_capability('users:editroles');

        $role = $this->role_model->get($role_id);

        form_element::$default_data = (array) $role;

        // Set up title bar
        $title = "Edit $role->name Role";
        $this->config->set_item('replacer', array('users' => array('/users/role/browse|Roles'), 'edit' => $title));
        $title_options = array('title' => $title,
                               'help' => 'Use this page to change the name and description of the '.$role->name .' role',
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'parent_ids' => $this->role_model->get_potential_parent_ids($role_id),
                             'content_view' => 'users/role/edit',
                             'feature_type' => 'Streamliner Core',
                             'role_id' => $role_id
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('users:editroles');

        $role_id = (int) $this->input->post('role_id');
        $role = $this->role_model->get($role_id);
        $redirect_url = base_url().'users/role/edit/'.$role_id;

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');

        $success = $this->form_validation->run();

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "The $role->name Role has been successfully updated!";
            } else {
                $json->result = 'danger';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            add_message('Some of the fields were not filled correctly. Please check the messages below.', 'danger');
            return $this->edit($role_id);
        }

        $role_data = array('name' => $this->input->post('name'), 'description' => $this->input->post('description'), 'parent_id' => $this->input->post('parent_id'));

        if (!$this->role_model->edit($role_id, $role_data)) {
            add_message('Could not update this role!', 'danger');
            redirect($redirect_url);
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("The $role->name Role has been successfully updated!", 'success');
        redirect(base_url().'users/role/browse');
    }

    public function user_edit($role_id) {

        require_capability('users:assignroles');
        $role = $this->role_model->get($role_id);


        // Set up title bars
        $list_title = "List of users with the $role->name Role";
        $list_title_options = array('title' => $list_title, 'help' => 'This list shows all the users currently assigned the '.$role->name.' role.', 'expand' => 'page', 'icons' => array());
        $add_title = "Add a user to the $role->name Role";
        $add_title_options = array('title' => $add_title, 'help' => 'Start typing the user\'s name in the text box to bring up matching user names. Select the one to whom you want to assign the '.$role->name.' role.', 'expand' => 'add_div', 'icons' => array());
        $this->config->set_item('replacer', array('users' => array('/users/role/browse|Roles'), 'user_edit' => "users with the $role->name Role"));

        $pageDetails = array('title' => $list_title,
                             'add_title_options' => $add_title_options,
                             'list_title_options' => $list_title_options,
                             'content_view' => 'users/role/user_edit',
                             'role_id' => $role_id,
                             'feature_type' => 'Streamliner Core',
                             'users' => $this->role_model->get_users($role_id),
                             'jstoloadinfooter' => array('jquery/jquery.json',
                                                         'jquery/jquery.url',
                                                         'jquery/datatables/media/js/jquery.dataTables',
                                                         'datatable_pagination',
                                                         'application/users/role_user_edit')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function capabilities($role_id) {
        require_capability('users:editcapabilities');

        $this->load->helper('recursive_list');

        $role = $this->role_model->get($role_id);
        $allcaps = $this->capability_model->get();

        // For each capability assigned to this role, show a hierarchical tree of dependent capabilities
        $capabilities = $this->role_model->get_capabilities($role_id);
        $dependencies = array();
        foreach ($capabilities as $capability) {
            $cap_array = array();
            $caps_to_check = array();
            $dependents = $this->capability_model->get_dependents($capability->id, $cap_array, $caps_to_check, true, $allcaps);
            $dependencies[$capability->id] = $cap_array;
        }

        // Get a hierarchical array of assignable capabilities
        $nested_caps = $this->capability_model->get_nested_caps(null, $dependencies);

        $add_help = "Below is a hierarchy of available capabilities not yet assigned to the $role->name role. Click on one of these capabilities to add it to the role. Expand the hierarchy by clicking the + icons on the left of the parent capabilities.";
        $list_help = "The table below shows all the capabilities currently associated with the $role->name role. You can remove these by clicking the trashcan icon next to the capabilities.";

        // Set up title bars
        $list_title = "Edit capabilities for the $role->name Role";
        $list_title_options = array('title' => $list_title, 'help' => $list_help, 'expand' => 'page', 'icons' => array());
        $add_title = "Add a capability to the $role->name Role";
        $add_title_options = array('title' => $add_title, 'help' => $add_help, 'expand' => 'add', 'icons' => array());
        $this->config->set_item('replacer', array('users' => array('/users/role/browse|Roles'), 'capabilities' => $list_title));

        $pageDetails = array('title' => $list_title,
                             'add_title_options' => $add_title_options,
                             'list_title_options' => $list_title_options,
                             'content_view' => 'users/role/cap_edit',
                             'role_id' => $role_id,
                             'capabilities' => $capabilities,
                             'dependencies' => $dependencies,
                             'assignable_caps' => $nested_caps,
                             'feature_type' => 'Streamliner Core',
                             'csstoload' => array('jquery.autocomplete', 'jquery.treeview'),
                             'jstoloadinfooter' => array('jquery/jquery.json',
                                                         'jquery/jquery.url',
                                                         'jquery/jquery-treeview/jquery.treeview',
                                                         'jquery/datatables/media/js/jquery.dataTables',
                                                         'datatable_pagination',
                                                         'application/users/role_cap_edit')
                             );
        $this->load->view('template/default', $pageDetails);
    }

    function get_assignable_users($role_id) {

        $term = $this->input->post('term');

        $users = $this->role_model->get_assignable_users($role_id, $term);

        echo json_encode($users);
    }

    function duplicate($role_id) {

        require_capability('users:writeroles');
        $new_role = $this->role_model->duplicate($role_id);
        add_message("This role is a duplicate, please edit its name and description", 'success');
        redirect(base_url().'users/role/edit/'.$new_role->id);
    }

    function delete_role_cap($role_id, $cap_id) {

        require_capability('users:edit_roles');

        $cap = $this->capability_model->get($cap_id);
        $result = $this->role_model->remove_capability($role_id, $cap->name);

        if ($result) {
            add_message('Capability successfully removed from this role!', 'success');
        } else {
            add_message('Capability could not be removed from this role!!', 'danger');
        }

        redirect(base_url().'users/role/capabilities/'.$role_id);

    }

    function delete_role_user($role_id, $user_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->unassign_role($user_id, $role_id);

        if ($result) {
            add_message('User unassignment successful!', 'success');
        } else {
            add_message('User unassignment failed!', 'danger');
        }

        redirect(base_url().'users/role/user_edit/'.$role_id);

    }

    function add_cap_to_role($role_id, $cap_id) {

        require_capability('users:editroles');

        $cap = $this->capability_model->get($cap_id);
        $result = $this->role_model->add_capability($role_id, $cap->name);

        if ($result) {
            add_message('Capability successfully added to this role!', 'success');
        } else {
            add_message('Capability could not be added!', 'danger');
        }

        redirect(base_url().'users/role/capabilities/'.$role_id);
    }

    function add_role_to_user($role_id, $user_id) {

        require_capability('users:assignroles');
        $result = $this->user_model->assign_role($user_id, $role_id);

        if ($result) {
            add_message('User assignment successful!', 'success');
        } else {
            add_message('User assignment failed!', 'danger');
        }

        redirect(base_url().'users/role/user_edit/'.$role_id);
    }
}
