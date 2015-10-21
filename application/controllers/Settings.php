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
class Settings extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('settings' => array('/settings|Settings')));
        $this->config->set_item('exclude', array('index', 'browse'));

        // Being a global controller, settings doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('site:viewsettings');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'settings',
            'row_actions' => array('edit'),
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'custom_title' => 'Settings list',
            'custom_columns_callback' => $this->setting_model->get_custom_columns_callback(),
            'model' => $this->setting_model
        ));

        $this->datagrid->add_column(array(
            'table' => 'settings',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'setting_id',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'table' => 'settings',
            'field' => 'name',
            'label' => 'Name',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'table' => 'settings',
            'field' => 'value',
            'label' => 'value',
            'in_combo_filter' => true));

        $this->datagrid->setup_filters();
        $this->datagrid->render();
    }

    public function get_data($setting_id) {
        echo json_encode($this->setting_model->get($setting_id));
    }

    public function add() {
        return $this->edit();
    }

    public function edit($setting_id=null) {

        require_capability('site:writesettings');
        $setting = null;

        if (!empty($setting_id)) {
            require_capability('site:editsettings');
            $setting_data = (array) $this->setting_model->get($setting_id);

            form_element::$default_data = $setting_data;

            // Set up title bar
            $title = "Edit {$setting_data['name']} setting";
            $help = "Use this form to edit the {$setting_data['name']} setting.";
            $setting = $this->setting_model->get($setting_id);
        } else { // adding a new setting
            $title = "Create a new setting";
            $help = 'Use this form to create a new setting.';
        }

        $this->config->set_item('replacer', array('settings' => array('/settings/index|settings'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'top_title_options' => $title_options,
                             'content_view' => 'setting/edit',
                             'setting_id' => $setting_id,
                             'setting' => $setting,
                             'feature_type' => 'Streamliner Core',
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('site:editsettings');

        $required_fields = array('name' => 'Name');

        if ($setting_id = (int) $this->input->post('setting_id')) {
            $setting = $this->setting_model->get($setting_id);
            $redirect_url = base_url().'settings/edit/'.$setting_id;
        } else {
            $redirect_url = base_url().'settings/add';
            $setting_id = null;
        }

        foreach ($required_fields as $field => $value) {
            $this->form_validation->set_rules($field, $value, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($setting_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "setting $setting_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($setting_id);
        }

        $value = $this->input->post('value');
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $setting_data = array( 'name' => $this->input->post('name'), 'value' => $value);

        if (empty($setting_id)) {
            if (!($setting_id = $this->setting_model->add($setting_data))) {
                add_message('Could not create this setting!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->setting_model->edit($setting_id, $setting_data)) {
                add_message('Could not update this setting!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("setting $setting_id has been successfully $action_word!", 'success');
        redirect(base_url().'settings');
    }
}
