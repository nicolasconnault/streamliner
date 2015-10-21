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
class Addresses extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('addresses' => array('index|Addresses')));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, accounts doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function add() {
        if (!IS_AJAX) {
            return $this->edit();
        }

        $type_id =  ($this->input->post('type_id')) ? $this->input->post('type_id') : $this->address_model->get_type_id($this->input->post('type'));

        $new_address = array(
            'unit' => $this->input->post('unit'),
            'account_id' => $this->input->post('account_id'),
            'number' => $this->input->post('number'),
            'street' => $this->input->post('street'),
            'street_type' => $this->input->post('street_type'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'postcode' => $this->input->post('postcode'),
            'type_id' => $type_id,
        );

        if (empty($new_address['account_id'])) {
            $new_address['account_id'] = null;
        }

        if ($address_id = $this->address_model->add($new_address)) {
            $new_address['id'] = $address_id;
            $new_address['type'] = $this->address_model->get_type_string($type_id);
            $new_address['street_type_short'] = $this->street_type_model->get_abbreviation($new_address['street_type']);

            send_json_message('The address has been successfully recorded', 'success', array('address' => $new_address));
        } else {
            send_json_message('The address could not be recorded', 'danger');
        }
    }

    public function edit($address_id=null) {

        require_capability('site:writeaddresses');
        $this->load->helper('form_template');

        if (!empty($address_id)) {
            require_capability('site:editaddresses');
            $address_data = $this->address_model->get($address_id);

            form_element::$default_data = (array) $address_data;

            // Set up title bar
            $title = "Edit address";
            $help = "Use this form to edit the address";
        } else { // adding a new address
            $title = "Create a new address";
            $help = 'Use this form to create a new address';
        }

        $this->config->set_item('replacer', array('address' => array('/addresses/index|addresses'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'addresses/edit',
                             'address_id' => $address_id,
                             'dropdowns' => $this->get_dropdowns(),
                             'feature_type' => 'Streamliner Core',
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('site:editaddresses');

        $required_fields = array('number' => 'Number',
                                 'street' => 'Street address',
                                 'street_type' => 'Street type',
                                 'city' => 'Suburb',
                                 'postcode' => 'Postcode',
                                 'type_id' => 'Address Type',
                             );

        if ($address_id = (int) $this->input->post('address_id')) {
            $address = $this->address_model->get($address_id);
            $redirect_url = base_url().'addresses/edit/'.$address_id;
        } else {
            $redirect_url = base_url().'addresses/add';
            $address_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($address_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "address $address_id has been successfully $action_word!";
            } else {
                send_json_message($this->form_validation->error_string(' ', "\n"), 'danger');
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($address_id);
        }

        $address_data = array(
                'unit' => $this->input->post('unit'),
                'account_id' => $this->input->post('account_id'),
                'number' => $this->input->post('number'),
                'street' => $this->input->post('street'),
                'street_type' => $this->input->post('street_type'),
                'city' => $this->input->post('city'),
                'state' => 'WA',
                'postcode' => $this->input->post('postcode'),
                'type_id' => $this->input->post('type_id'),
                );

        if (empty($address_data['account_id'])) {
            $address_data['account_id'] = null;
        }

        if (empty($address_id)) {
            if (!($address_id = $this->address_model->add($address_data))) {
                add_message('Could not create this address!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->address_model->edit($address_id, $address_data)) {
                add_message('Could not update this address!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("address $address_id has been successfully $action_word!", 'success');
        redirect($redirect_url);
    }

    public function get_dropdowns() {
        $dropdowns = array(
            'types' => $this->address_model->get_types_dropdown(),
            'street_types' => $this->street_type_model->get_dropdown('name', '-- Select a street type --'),
            );
        return $dropdowns;
    }

    public function get_street_types() {
        $term = $this->input->post('term');

        $this->db->where('name LIKE', '%'.$term.'%');

        $street_types_array = $this->street_type_model->get_dropdown('name', false);
        $street_types = array();
        foreach ($street_types_array as $value => $label) {
            $street_type = new stdClass();
            $street_type->label = $label;
            $street_type->value = $label;
            $street_types[] = $street_type;
        }
        echo json_encode($street_types);
    }
}
