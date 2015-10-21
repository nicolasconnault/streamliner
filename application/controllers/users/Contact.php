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
class Contact extends MY_Controller {

	function __construct() {
		parent::__construct();
        $this->config->set_item('exclude', array('home', 'browse'));
        $this->config->set_item('replacer', array(
            'users' => array('/users/contact|Contacts'),
            'add' => 'Create new contact',
            'home' => array('contact|Contacts')));
	}

    function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    function index($outputtype='html') {
        require_capability('users:viewcontacts');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'custom_columns_callback' => $this->contact_model->get_custom_columns_callback(),
            'model' => $this->contact_model,
            'show_add_button' => has_capability('users:writecontacts')
        ));

        $this->datagrid->add_column(array(
            'table' => 'contacts',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'contact_id',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'table' => 'contacts',
            'sql_select' => 'CONCAT(contacts.surname," ",contacts.first_name)',
            'field' => 'id',
            'field_alias' => 'contact_name',
            'label' => 'Name',
            'in_combo_filter' => true
            ));
        $this->datagrid->add_column(array(
            'table' => 'contacts',
            'field' => 'email',
            'label' => 'Email',
            'field_alias' => 'user_email',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'table' => 'types',
            'field' => 'name',
            'label' => 'Type',
            'in_combo_filter' => true));
        $this->datagrid->add_column(array(
            'table' => 'accounts',
            'field' => 'name',
            'label' => 'Account',
            'field_alias' => 'contact_account',
            'in_combo_filter' => true));
        $this->datagrid->set_joins(array(
            array('table' => 'accounts', 'on' => 'accounts.id = contacts.account_id'),
            array('table' => 'types', 'on' => 'types.id = contacts.contact_type_id'),
        ));

        $this->datagrid->setup_filters();

        $this->datagrid->render();
    }

    function add() {
        return $this->edit();
    }

    function edit($contact_id=null) {
        if (empty($contact_id)) {
            require_capability('users:writecontacts');
            $title_options = array(
                'title' => "Adding a new contact",
                'help' => 'Use this page to add a new contact',
                'icons' => array()
            );
        } else {
            require_capability('users:editcontacts');
            $contact = $this->contact_model->get($contact_id);

            form_element::$default_data = (array) $contact;
            // Set up title bars
            $title_options = array(
                'title' => "Editing contact " . $this->contact_model->get_name($contact),
                'help' => 'Use this page to edit this contact\'s personal details, contact details, and account information',
                'icons' => array()
            );
        }

        $this->config->set_item('replacer', array('edit' => $title_options['title'], 'users' => array('/users/contact|Contacts')));

        $format_account_callback = function($account) {
            $ci = get_instance();
            $formatted_account = $account->name;
            return $formatted_account;
        };

        $pageDetails = array(
                'title' => $title_options['title'],
                'title_options' => $title_options,
                'accounts' => $this->account_model->get_dropdown('name', true, $format_account_callback),
                'csstoload' => array(),
                'contact_id' => $contact_id,
                'type' => 'contact',
                'types' => $this->contact_model->get_types_dropdown(),
                'form_action' => base_url().'users/contact/process_edit',
                'feature_type' => 'Streamliner Core',
                'content_view' => 'users/contact/edit');
        $this->load->view('template/default', $pageDetails);
    }

    function process_edit() {
        require_capability('users:editcontacts');

        $required_fields = array(
            'first_name' => 'First Name',
            'surname' => 'Surname',
            'phone' => 'Landline phone 1',
            'email' => 'Email address 1',
            'account_id' => 'Account',
            'contact_type_id' => 'Contact type',
        );

        if ($contact_id = (int) $this->input->post('contact_id')) {
            require_capability('users:editcontacts');
            $contact = $this->contact_model->get($contact_id);
            $redirect_url = base_url().'users/contact/edit/'.$contact_id;
        } else {
            require_capability('users:writecontacts');
            $redirect_url = base_url().'users/contact/add';
            $contact_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $rule = (strstr($field, 'email')) ? 'trim|required|valid_email' : 'trim|required';
            $this->form_validation->set_rules($field, $description, $rule);
        }

        $this->form_validation->set_rules('email2', 'Email address 2', 'trim|valid_email');

        $success = $this->form_validation->run();

        $action_word = ($contact_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "contact $contact_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($contact_id);
        }

        $contact_data = array(
            'first_name' => $this->input->post('first_name'),
            'surname' => $this->input->post('surname'),
            'phone' => $this->input->post('phone'),
            'mobile' => $this->input->post('mobile'),
            'email' => $this->input->post('email'),
            'phone2' => $this->input->post('phone2'),
            'mobile2' => $this->input->post('mobile2'),
            'email2' => $this->input->post('email2'),
            'account_id' => $this->input->post('account_id'),
            'contact_type_id' => $this->input->post('contact_type_id'),
        );

        if (empty($contact_id)) {
            if (!($contact_id = $this->contact_model->add($contact_data))) {
                add_message('Could not create this contact!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->contact_model->edit($contact_id, $contact_data)) {
                add_message('Could not update this contact!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("contact $contact_id has been successfully $action_word!", 'success');
        redirect(base_url().'users/contact');
    }

    public function cancel($contact_id=null) {
        return $this->browse();
    }
}
?>
