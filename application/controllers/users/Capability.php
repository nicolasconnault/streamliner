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
class Capability extends MY_Controller {

	function __construct() {
		parent::__construct();
        $this->config->set_item('replacer', array('users' => array('/users/capability/browse|Capabilities')));
        $this->config->set_item('exclude', array('browse'));
	}

    function index() {
        return $this->browse();
    }

    function browse($outputtype='html') {
        require_capability('users:viewcapabilities');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'available_export_types' => array(),
            'paginate' => true,
            'row_actions' => array('Edit this capability' => 'edit',
                                  'Delete this capability' => 'delete'),
            'show_add_button' => true,
            'custom_columns_callback' => $this->capability_model->get_custom_columns_callback()
        ));

        $this->datagrid->add_column(array(
            'table' => 'capabilities',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'capability_id',
            'width' => 20
        ));
        $this->datagrid->add_column(array(
            'table' => 'capabilities',
            'field' => 'name',
            'label' => 'Name',
            'field_alias' => 'capability_name',
            'in_combo_filter' => true
        ));
        $this->datagrid->add_column(array(
            'table' => 'capabilities',
            'field' => 'description',
            'label' => 'Description',
            'field_alias' => 'capability_description'
        ));
        $this->datagrid->add_column(array(
            'table' => 'capabilities',
            'field' => 'id',
            'label' => 'Roles with this capability',
            'field_alias' => 'roles'
        ));

        $this->datagrid->add_column(array(
            'table' => 'capabilities',
            'sql_select' => 'CONCAT(parent_caps.description," (",parent_caps.name,")")',
            'field_alias' => 'dependson_cap',
            'field' => 'dependson',
            'label' => 'Is covered by',
        ));

        $this->datagrid->set_joins(array(
            array('table' => 'capabilities parent_caps', 'on' => 'parent_caps.id = capabilities.dependson', 'type' => 'LEFT OUTER')
        ));

        $this->datagrid->setup_filters();
        $this->datagrid->render();
            $action_icons = array('Edit this capability' => 'edit',
                                  'Delete this capability' => 'delete');
    }

    function view($capability_id) {
        $caps = $this->capability_model->get_capabilities($capability_id);
    }

    function add() {
        return $this->edit();
    }

    function edit($capability_id=null) {

        if (empty($capability_id)) {
            require_capability('users:writecapabilities');
            $title = "Add a capability";

            $title_options = array('title' => $title,
                                   'help' => 'Use this page to add a new capability',
                                   'expand' => 'page',
                                   'icons' => array());
        } else {
            require_capability('users:editcapabilities');
            $capability = $this->capability_model->get($capability_id);
            form_element::$default_data = (array) $capability;
            $this->db->where_not_in('id', array($capability_id));
            $title = "Edit $capability->name capability";

            $title_options = array('title' => $title,
                                   'help' => 'Use this page to change the name and description of the '.$capability->name .' capability',
                                   'expand' => 'page',
                                   'icons' => array());
        }

        $this->config->set_item('replacer', array('users' => array('/users/capability/browse|capabilities'), 'edit' => $title));


        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'users/capability/edit',
                             'parent_caps' => $this->capability_model->get_dropdown('name', false, false, false, null, null, 'name'),
                             'capability_id' => $capability_id,
                             'feature_type' => 'Streamliner Core',
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('users:editcapabilities');

        $capability_id = (int) $this->input->post('capability_id');

        if (!empty($capability_id)) {
            $redirect_url = base_url().'users/capability/edit/'.$capability_id;
        }

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');
        $this->form_validation->set_rules('dependson', 'Parent capability', 'required');

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('Some of the fields were not filled correctly. Please check the messages below.', 'danger');
            return $this->edit($capability_id);
        }

        $capability_data = array('name' => $this->input->post('name'), 'description' => $this->input->post('description'), 'dependson' => $this->input->post('dependson'));

        $updated_or_added = (empty($capability_id)) ? 'added' : 'updated';
        if (empty($capability_id)) {
            if (!($capability_id = $this->capability_model->add($capability_data))) {
                add_message('Could not create this capability!', 'danger');
                redirect($redirect_url);
            }
        } else if (!$this->capability_model->edit($capability_id, $capability_data)) {
            add_message('Could not update this capability!', 'danger');
            redirect($redirect_url);
        }

        add_message("The {$capability_data['name']} capability has been successfully $updated_or_added!", 'success');
        redirect(base_url().'users/capability/browse');
    }

}
