<?php
class Accounts extends MY_Controller {
    public $uri_level = 1;
    public $autoload = array('model' => array('account_model'));

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('accounts' => array('index|Accounts')));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, accounts doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('site:viewaccounts', true, 'Your permissions do not allow you to view account details');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'accounts',
            'row_actions' => array('view', 'edit', 'delete'),
            'row_action_capabilities' => array('view' => 'site:viewaccounts', 'edit' => 'site:editaccounts', 'delete' => 'site:deleteaccounts'),
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'custom_columns_callback' => $this->account_model->get_custom_columns_callback(),
            'feature_type' => 'Streamliner Core',
            'show_add_button' => has_capability('site:writeaccounts'),
            'custom_title' => 'Accounts list',
            'model' => $this->account_model
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

        $values = $this->account_model->get_values($account_id);
        $json_params = array(
            'billing_contacts' => $values['billing_contacts'],
            'site_contacts' => $values['site_contacts'],
            'site_addresses' => $values['site_addresses'],
        );

        $json_params['values'] = $values;
        send_json_data($json_params);
    }


    public function add() {
        require_capability('site:writeaccounts');
        return $this->edit();
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

        $this->config->set_item('replacer', array('accounts' => array('/accounts/index|accounts'), 'edit' => $title, 'add' => $title));
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
            $account = $this->account_model->get($account_id);
            $redirect_url = base_url().'accounts/edit/'.$account_id;
        } else {
            $redirect_url = base_url().'accounts/add';
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
            if (!($account_id = $this->account_model->add($account_data))) {
                add_message('Could not create this account!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->account_model->edit($account_id, $account_data)) {
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
        redirect(base_url().'accounts');
    }
}
