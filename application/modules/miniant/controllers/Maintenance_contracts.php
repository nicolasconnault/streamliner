<?php
class Maintenance_contracts extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('miniant' => array('index|Maintenance contracts')));

        // Being a global controller, maintenance_contracts doesn't need its second-level segment to be hidden
        $this->load->model('miniant/maintenance_contract_model');
        $this->load->model('miniant/maintenance_contract_unit_model');
        $this->load->model('miniant/miniant_account_model', 'account_model');
        $this->load->model('miniant/brand_model');
        $this->load->model('miniant/tenancy_model');
        $this->load->model('miniant/unitry_type_model');
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('maintenance_contracts:viewcontracts');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'maintenance_contracts',
            'row_actions' => array('view', 'edit', 'contractjobs', 'delete'),
            'row_action_capabilities' => array('view' => 'maintenance_contracts:viewcontracts', 'edit' => 'maintenance_contracts:editcontracts', 'delete' => 'maintenance_contracts:deletecontracts'),
            'available_export_types' => array('xml', 'csv'),
            'show_add_button' => has_capability('maintenance_contracts:writecontracts'),
            'feature_type' => 'Custom Feature',
            'model' => $this->maintenance_contract_model,
            'module' => 'miniant',
            'custom_title' => "List of maintenance contracts",
            'custom_columns_callback' => $this->maintenance_contract_model->get_custom_columns_callback(),
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_maintenance_contracts',
            'field' => 'id',
            'label' => 'MC ID',
            'field_alias' => 'maintenance_contract_id'));
        $this->datagrid->add_column(array(
            'table' => 'accounts',
            'table_alias' => 'accounts',
            'field' => 'name',
            'label' => 'Account',
            ));

        $this->datagrid->add_column(array(
            'table' => 'contacts',
            'table_alias' => 'billing_contact',
            'sql_select' => 'CONCAT(billing_contact.first_name, " ", billing_contact.surname, " (", (IF(billing_contact.mobile, billing_contact.mobile, billing_contact.phone)),")")',
            'field' => 'id',
            'label' => 'Billing Contact',
            'field_alias' => 'billing_contact_name',
            'requires_capability' => 'maintenance_contracts:viewbillingcontact'
        ));
        $this->datagrid->add_column(array(
            'field' => 'number_of_units',
            'field_alias' => 'number_of_units',
            'sql_select' => '(SELECT COUNT(*) FROM miniant_maintenance_contract_units WHERE maintenance_contract_id = miniant_maintenance_contracts.id)',
            'label' => '# Units',
            ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_maintenance_contracts',
            'field' => 'site_address_id',
            'label' => 'Location',
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_maintenance_contracts',
            'field' => 'schedule_interval',
            'label' => 'Schedule',
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_maintenance_contracts',
            'field' => 'next_maintenance_date',
            'label' => 'Next maintenance date',
            'field_alias' => 'maintenance_contract_next_maintenance_date',
            'width' => 100
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_maintenance_contracts',
            'field' => 'creation_date',
            'label' => 'Creation date',
            'field_alias' => 'maintenance_contract_creation_date',
            'width' => 100
            ));
        $this->datagrid->add_column(array(
            'label' => 'Statuses',
            'field_alias' => 'statuses',
            'sortable' => false
        ));

        $this->datagrid->set_joins(array(
            array('table' => 'contacts billing_contact', 'on' => 'billing_contact.id = miniant_maintenance_contracts.billing_contact_id', 'type' => 'LEFT OUTER'),
            array('table' => 'accounts', 'on' => 'accounts.id = miniant_maintenance_contracts.account_id'),
            array('table' => 'addresses', 'on' => 'addresses.id = miniant_maintenance_contracts.site_address_id', 'type' => 'LEFT OUTER'),
            array('table' => 'document_statuses', 'on' => 'document_statuses.document_id = miniant_maintenance_contracts.id AND document_statuses.document_type = "maintenance_contract"'),
        ));
        $this->datagrid->render();
    }


    public function add() {
        require_capability('maintenance_contracts:writecontracts');
        return $this->edit();
    }

    public function edit($maintenance_contract_id=null) {

        $maintenance_contract_data = $this->maintenance_contract_model->get_values($maintenance_contract_id);
        $locked = false;

        if (!empty($maintenance_contract_id)) {
            require_capability('maintenance_contracts:editcontracts');

            $maintenance_contract_data['creation_date'] = unix_to_human($maintenance_contract_data['creation_date'], '%d/%m/%Y %h:%i');
            $maintenance_contract_data['next_maintenance_date'] = unix_to_human($maintenance_contract_data['next_maintenance_date'], '%d/%m/%Y %h:%i');

            form_element::$default_data = (array) $maintenance_contract_data;

            // Set up title bar
            $title = "Edit maintenance contract #$maintenance_contract_id";
            $help = "Use this form to edit the maintenance contract";
        } else { // adding a new maintenance_contract
            $title = "Create a new maintenance contract";
            $help = 'Use this form to create a new maintenance contract';
        }

        $post_vars = $this->input->post();
        if (!empty($post_vars)) {
            foreach ($post_vars as $var => $val) {
                form_element::$default_data[$var] = $val;
            }
        }

        $this->config->set_item('replacer', array('miniant' => array('/miniant/maintenance_contracts/index|Maintenance contracts')));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());
        $jstoload = array(
             'signaturepad/json2',
             'maintenance_contracts/edit',
             'application/messages',
         );

        if (!empty($maintenance_contract_id)) {
            $jstoload[] = 'maintenance_contracts/units';
        }

        $dropdowns = $this->get_dropdowns();

        if (!empty($maintenance_contract_id)) {
            $this->db->where('account_id', $maintenance_contract_data['account_id']);
            $dropdowns['tenancies'] = $this->tenancy_model->get_dropdown('name', '-- Select a Tenancy/Owner --');
        } else {
            $dropdowns['tenancies'] = array();
        }

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'maintenance_contract/edit',
                             'maintenance_contract_id' => $maintenance_contract_id,
                             'maintenance_contract_data' => $maintenance_contract_data,
                             'dropdowns' => $dropdowns,
                             'jstoloadinfooter' => $jstoload,
                             'module' => 'miniant',
                             'csstoload' => array('jquery.signaturepad'),
                             'feature_type' => 'Custom Feature',
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {
        require_capability('maintenance_contracts:editcontracts');

        if ($this->input->post('return')) {
            redirect(base_url().'miniant/maintenance_contracts/browse');
        }

        $required_fields = array(
            'account_id' => "Billing account",
            'site_address_id' => "Job site address"
        );

        if ($maintenance_contract_id = (int) $this->input->post('maintenance_contract_id')) {
            $maintenance_contract = $this->maintenance_contract_model->get($maintenance_contract_id);
            $redirect_url = base_url().'miniant/maintenance_contracts/browse';
        } else {
            $required_fields['creation_date'] = "Creation date";
            $required_fields['next_maintenance_date'] = "Next maintenance date";
            $required_fields['schedule_interval'] = "Schedule type";
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($maintenance_contract_id) ? 'updated' : 'created';

        if (!$success) {
            echo validation_errors();
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            return $this->edit($maintenance_contract_id);
        }

        // The billing and property manager contacts are created dynamically through AJAX before we get to this point
        $billing_contact_id = $this->contact_model->get(array(
            'contact_type_id' => $this->contact_model->get_type_id('Billing'),
            'account_id' => $this->input->post('account_id')), true
        )->id;

        $maintenance_contract_type_id = $this->input->post('maintenance_contract_type_id');

        $maintenance_contract_data = array(
            'creation_date' => human_to_unix($this->input->post('creation_date')),
            'account_id' => $this->input->post('account_id'),
            'site_address_id' => $this->input->post('site_address_id'),
            'billing_contact_id' => $billing_contact_id,
        );

        $maintenance_contract_data['next_maintenance_date'] = human_to_unix($this->input->post('next_maintenance_date'));

        $property_manager_contact = $this->contact_model->get(array(
            'contact_type_id' => $this->contact_model->get_type_id('Property manager'),
            'account_id' => $this->input->post('account_id')), true
        );

        if (!empty($property_manager_contact)) {
            $maintenance_contract_data['property_manager_contact_id'] = $property_manager_contact->id;
        }

        // Don't allow removal of creation date
        if (empty($maintenance_contract_data['creation_date'])) {
            unset($maintenance_contract_data['creation_date']);
        }

        if (!empty($maintenance_contract_id)) {
            if (!$this->maintenance_contract_model->edit($maintenance_contract_id, $maintenance_contract_data)) {
                add_message('Could not update this Maintenance contract!', 'error');
                redirect($redirect_url);
            } else {
                trigger_event('admin_prep_finished', 'maintenance_contracts', $maintenance_contract_id, false, 'miniant');
                $redirect_url = base_url().'miniant/maintenance_contracts/browse/';
            }
        } else {
            if (!($maintenance_contract_id = $this->maintenance_contract_model->add($maintenance_contract_data))) {
                add_message('Could not create this Maintenance contract!', 'error');
                redirect($redirect_url);
            } else {
                trigger_event('create_maintenance_contract', 'maintenance_contracts', $maintenance_contract_id, false, 'miniant');
                $redirect_url = base_url().'miniant/maintenance_contracts/edit/'.$maintenance_contract_id.'#maintenance_contract_units';
            }
        }

        add_message("Maintenance contract $maintenance_contract_id has been successfully $action_word!", 'success');
        redirect($redirect_url);
    }

    public function get_dropdowns() {
        $this->db->where(array('order_type_id' => $this->order_model->get_type_id('Service')));
        $service_order_label_function = function($order) {
            return $order->id . ' (' . $this->account_model->get($order->account_id)->name . ')';
        };

        $service_order_dropdown = $this->order_model->get_dropdown('id', true, $service_order_label_function);

        $dropdowns = array(
            'accounts' => $this->account_model->get_dropdown('name'),
            'street_types' => $this->street_type_model->get_dropdown('name', '-- Select a street type --'),
            'unit_types' => $this->unit_model->get_types_dropdown('--Select an equipment type--'),
            'brands_refrigerated' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Refrigerated A/C')),
            'brands_evaporative' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Evaporative A/C')),
            'brands_other' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Other Refrigeration')),
            'order_types' => $this->order_model->get_types_dropdown(true, false, 'order'),
            'unitry_types_refrigerated' => $this->unitry_type_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Refrigerated A/C')),
            'service_orders' => $service_order_dropdown
            );
        return $dropdowns;
    }

}
