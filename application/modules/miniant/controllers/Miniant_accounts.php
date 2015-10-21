<?php
require_once(APPPATH.'controllers/Accounts.php');

class Miniant_accounts extends Accounts {
    public $uri_level = 1;
    public $autoload = array('model' => array('tenancy_model', 'order_model'));

    public function __construct() {
        parent::__construct();
        $this->load->model('miniant/miniant_account_model');
        $this->load->model('miniant/maintenance_contract_model');
    }

    public function index($outputtype='html') {
        require_capability('site:viewaccounts', true, 'Your permissions do not allow you to view account details');

        $this->config->set_item('replacer', array('miniant' => null, 'miniant_accounts' => array('/miniant/miniant_accounts/index|Accounts')));
        $this->config->set_item('exclude', array('browse', 'miniant', 'index'));

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'miniant_accounts',
            'module' => 'miniant',
            'row_actions' => array('view', 'edit', 'delete'),
            'row_action_capabilities' => array('view' => 'site:viewaccounts', 'edit' => 'site:editaccounts', 'delete' => 'site:deleteaccounts'),
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'custom_columns_callback' => $this->miniant_account_model->get_custom_columns_callback(),
            'feature_type' => 'Custom Feature',
            'show_add_button' => has_capability('site:writeaccounts'),
            'custom_title' => 'Accounts list',
            'model' => $this->miniant_account_model
        ));

        $this->datagrid->add_column(array(
            'table' => 'accounts',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'account_id'));
        $this->datagrid->add_column(array(
            'table' => 'accounts',
            'field' => 'name',
            'label' => 'Name'));
        $this->datagrid->add_column(array(
            'table' => 'accounts',
            'field' => 'abn',
            'label' => 'ABN'));
        $this->datagrid->add_column(array(
            'field_alias' => 'billing_address',
            'label' => 'Billing Address',
            'sortable' => true));

        $this->datagrid->setup_filters();
        $this->datagrid->render();
    }

    public function get_data() {
        require_capability('site:viewaccounts');
        $account_id = $this->input->post('account_id');
        $order_id = $this->input->post('order_id');

        $values = $this->miniant_account_model->get_values($account_id);

        $json_params = array(
            'billing_contacts' => $values['billing_contacts'],
            'site_contacts' => $values['site_contacts'],
            'site_addresses' => $values['site_addresses'],
            'tenancies' => $values['tenancies'],
            'site_address_id' => null,
            'site_contact_id' => null,
            'billing_contact_id' => null
        );

        if (!empty($order_id)) {
            $order = $this->order_model->get($order_id);
            $json_params['site_address_id'] = $order->site_address_id;
            $json_params['site_contact_id'] = $order->site_contact_id;
            $json_params['billing_contact_id'] = $order->billing_contact_id;
        }

        unset($values['billing_contacts']);
        unset($values['site_contacts']);
        unset($values['site_addresses']);

        $json_params['values'] = $values;
        send_json_data($json_params);
    }

    public function get_data_from_maintenance_contract() {
        $maintenance_contract_id = $this->input->post('maintenance_contract_id');
        $maintenance_contract = $this->maintenance_contract_model->get_from_cache($maintenance_contract_id);

        $values = $this->miniant_account_model->get_values($maintenance_contract->account_id);
        $json_params = array(
            'billing_contacts' => $values['billing_contacts'],
            'site_contacts' => $values['site_contacts'],
            'site_addresses' => $values['site_addresses'],
            'tenancies' => $values['tenancies'],
            'site_address_id' => $maintenance_contract->site_address_id,
            'property_manager_contact_id' => $maintenance_contract->property_manager_contact_id,
            'property_manager_contacts' => $values['property_manager_contacts'],
            'billing_contact_id' => $maintenance_contract->billing_contact_id,
            'preferred_start_date' => $maintenance_contract->next_maintenance_date,
            'account_id' => $maintenance_contract->account_id
        );

        unset($values['billing_contacts']);
        unset($values['site_contacts']);
        unset($values['site_addresses']);

        $json_params['values'] = $values;
        send_json_data($json_params);
    }

    public function get_data_from_order() {
        $order_id = $this->input->post('order_id');
        $order = $this->order_model->get_from_cache($order_id);

        $values = $this->miniant_account_model->get_values($order->account_id);
        $json_params = array(
            'billing_contacts' => $values['billing_contacts'],
            'site_contacts' => $values['site_contacts'],
            'site_addresses' => $values['site_addresses'],
            'tenancies' => $values['tenancies'],
            'site_address_id' => $order->site_address_id,
            'billing_contact_id' => $order->billing_contact_id,
            'preferred_start_date' => $order->preferred_start_date,
            'property_manager_contacts' => $values['property_manager_contacts'],
            'account_id' => $order->account_id
        );

        unset($values['billing_contacts']);
        unset($values['site_contacts']);
        unset($values['site_addresses']);

        $json_params['values'] = $values;
        send_json_data($json_params);
    }


    public function process_edit() {

        require_capability('site:editaccounts');

        $required_fields = array(
            'name' => 'Billing Name',
            'billing_address_city' => 'Billing address suburb',
            'billing_address_postcode' => 'Billing address post code',
        );

        if ($this->input->post('billing_address_po_box_on')) {
            $required_fields['billing_address_po_box'] = 'Billing address PO Box';
        } else {
            $required_fields['billing_address_number'] = 'Billing address number';
            $required_fields['billing_address_street'] = 'Billing address street';
            $required_fields['billing_address_street_type'] = 'Billing address street type';
        }

        if ($account_id = (int) $this->input->post('account_id')) {
            $account = $this->miniant_account_model->get($account_id);
            $redirect_url = base_url().'miniant/miniant_accounts/edit/'.$account_id;
        } else {
            $redirect_url = base_url().'miniant/miniant_accounts/add';
            $account_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($account_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "account $account_id has been successfully $action_word!";
            } else {
                send_json_message($this->form_validation->error_string(' ', "\n"), 'danger');
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($account_id);
        }

        $account_data = array(
            'name' => $this->input->post('name'),
            'cc_hold' => $this->input->post('cc_hold'),
            'abn' => $this->input->post('abn'),
        );

        if (empty($account_id)) {
            if (!($account_id = $this->miniant_account_model->add($account_data))) {
                add_message('Could not create this account!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->miniant_account_model->edit($account_id, $account_data)) {
                add_message('Could not update this account!', 'error');
                redirect($redirect_url);
            }
        }

        $billing_address_data = array(
            'unit' => $this->input->post('billing_address_unit'),
            'po_box_on' => $this->input->post('billing_address_po_box_on'),
            'po_box' => $this->input->post('billing_address_po_box'),
            'street' => $this->input->post('billing_address_street'),
            'street_type' => $this->input->post('billing_address_street_type'),
            'number' => $this->input->post('billing_address_number'),
            'city' => $this->input->post('billing_address_city'),
            'postcode' => $this->input->post('billing_address_postcode'),
            'type_id' => $this->address_model->get_type_id('Billing'),
            'account_id' => $account_id
        );

        if ($billing_address_data['po_box_on']) {
            $billing_address_data['unit'] = null;
            $billing_address_data['number'] = null;
            $billing_address_data['street'] = null;
            $billing_address_data['street_type'] = null;
        } else {
            $billing_address_data['po_box_on'] = false;
        }

        $billing_address_id = $this->input->post('billing_address_id');

        if ($billing_address_id) {
            $this->address_model->edit($this->input->post('billing_address_id'), $billing_address_data);
        } else {
            $billing_address_id = $this->address_model->add($billing_address_data);
        }

        add_message("account $account_id has been successfully $action_word!", 'success');
        redirect(base_url().'miniant/miniant_accounts');
    }

    public function get_tenancies($account_id) {
        $this->load->model('miniant/unit_model');
        $tenancies = $this->tenancy_model->get(compact('account_id'));

        foreach ($tenancies as $key => $tenancy) {
            if ($this->unit_model->get(array('tenancy_id' => $tenancy->id))) {
                $tenancies[$key]->locked = true;
            } else {
                $tenancies[$key]->locked = false;
            }
        }

        send_json_data(array('tenancies' => $tenancies));
    }

    public function delete($id, $model_name=null) {

        $result = $this->miniant_account_model->delete($id);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "Account $id was successfully deleted";
                $json->id = $id;
                $json->type = 'success';
            } else {
                $json->message = "Account $id could not be deleted";
                $json->id = $id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }

    public function edit($account_id=null) {

        $account_data = array();

        if (!empty($account_id)) {
            require_capability('site:viewaccounts');
            $account_data = (array) $this->account_model->get_values($account_id);
            form_element::$default_data = $account_data;
            // Set up title bar
            $title = "Edit {$account_data['name']} billing account";
            $help = "Use this form to edit the {$account_data['name']} billing account.";
        } else { // adding a new account
            require_capability('site:writeaccounts');

            $title = "Create a new billing account";
            $help = 'Use this form to create a new billing account.';
        }

        $this->config->set_item('replacer', array('miniant' => null, 'miniant_accounts' => array('/miniant/miniant_accounts/index|Accounts'), 'edit' => $title, 'add' => $title));
        $this->config->set_item('exclude', array('browse', 'miniant'));

        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'account/edit',
                             'account_id' => $account_id,
                             'dropdowns' => array('street_types' => $this->street_type_model->get_dropdown('name')),
                             'account_data' => $account_data,
                             'csstoload' => array('jquery.autocomplete'),
                             'feature_type' => 'Streamliner Core',
                             );

        $this->load->view('template/default', $pageDetails);
    }
}
