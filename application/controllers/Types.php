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
class Types extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('types' => array('index|Types')));
        $this->config->set_item('exclude', array('index', 'browse'));

        // Being a global controller, types doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('site:viewtypes');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'types',
            'row_actions' => array('view', 'edit', 'delete'),
            'row_action_capabilities' => array('edit' => 'site:edittypes', 'delete' => 'site:deletetypes'),
            'available_export_types' => array('csv'),
            'feature_type' => 'Streamliner Core',
            'show_add_button' => has_capability('site:writetypes'),
            'custom_title' => 'Types list',
            'title_icon' => 'cubes',
            'model' => $this->type_model
        ));

        $this->datagrid->add_column(array(
            'table' => 'types',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'type_id'));
        $this->datagrid->add_column(array(
            'table' => 'types',
            'field' => 'name',
            'label' => 'Name'));
        $this->datagrid->add_column(array(
            'table' => 'types',
            'field' => 'description',
            'label' => 'Description'));
        $this->datagrid->add_column(array(
            'table' => 'types',
            'field' => 'entity',
            'label' => 'Entity'));

        $this->datagrid->render();
    }

    public function add() {
        return $this->edit();
    }

    public function edit($id=null) {

        require_capability('site:writetypes');

        if (!empty($id)) {
            require_capability('site:edittypes');
            $type_data = $this->type_model->get($id);

            form_element::$default_data = (array) $type_data;

            // Set up title bar
            $title = "Edit type";
            $help = "Use this form to edit the type";
        } else { // adding a new type
            $title = "Create a new type";
            $help = 'Use this form to create a new type';
        }

        $this->config->set_item('replacer', array('types' => array('/types/index|Types'), 'edit' => $title, 'add' => $title));

        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'types/edit',
                             'id' => $id,
                             'feature_type' => 'Streamliner Core',
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('site:edittypes');

        $required_fields = array('name' => 'Name', 'entity' => 'Entity');

        if ($id = (int) $this->input->post('id')) {
            $type = $this->type_model->get($id);
            $redirect_url = base_url().'types/edit/'.$id;
        } else {
            $redirect_url = base_url().'types/add';
            $id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($id) ? 'updated' : 'created';

        if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($id);
        }

        $type_data = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'entity' => $this->input->post('entity'),
                'id' => $this->input->post('id'),
                );

        if (empty($type_data['id'])) {
            $type_data['id'] = null;
        }

        if (empty($id)) {
            if (!($id = $this->type_model->add($type_data))) {
                add_message('Could not create this type!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->type_model->edit($id, $type_data)) {
                add_message('Could not update this type!', 'error');
                redirect($redirect_url);
            }
        }

        add_message("type $id has been successfully $action_word!", 'success');
        redirect(base_url().'types/browse');
    }

}
