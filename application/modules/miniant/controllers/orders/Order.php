<?php
class Order extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('miniant' => null, 'order' => array('browse|Jobs')));
        $this->config->set_item('exclude', array('browse', 'index'));
        $this->load->model('miniant/order_model');
        $this->load->model('miniant/miniant_account_model', 'account_model');
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    /**
     * @param $parent_order_id If given, only the orders that use the same site address as the parent order will be returned
     * @param $maintenance_contract_id If given, only the orders associated with that contract will be returned
     * DOC: When multiple URL params are used in a MY_Controller::index() call, they Must be concatenated into a single forward-slash-padded string and saved as the Datagrid's "url_param" variable.
     *  Otherwise, the params will not be passed to the Action icons and the Datagrid JS that loads all the data
     */
    public function index($outputtype='html', $parent_order_id=null, $maintenance_contract_id=null) {

        $sql_conditions = array();
        $status_selector_filter = null;

        $user_role = null;

        require_capability('orders:vieworders');

        if (has_capability('orders:editappointmentdate', null, false)) { // Ops manager
            $status_selector_filter = "name NOT IN ('SENT TO ACCOUNTS', 'NEEDS JOB NUMBER', 'READY FOR INVOICING', 'ARCHIVED', 'CANCELLED', 'COMPLETE')";
            $sql_conditions[] = "document_statuses.status_id IN (SELECT id FROM statuses WHERE $status_selector_filter) AND document_statuses.document_type = 'order'";
        } else if (has_capability('orders:writeorders', null, false)) { // Accounts
            $status_selector_filter = "name IN ('NEEDS JOB NUMBER', 'READY FOR INVOICING', 'DRAFT')";
            $sql_conditions[] = "document_statuses.status_id IN (SELECT id FROM statuses WHERE $status_selector_filter) AND document_statuses.document_type = 'order'";
        }

        if (has_capability('site:doanything')) {
            $sql_conditions = array();
            $status_selector_filter = null;
        }

        $custom_title = 'List of Jobs';

        if (!empty($parent_order_id)) {
            $order_data = $this->order_model->get_values($parent_order_id);
            if (!empty($order_data['site_address_id'])) {
                $custom_title = 'List of Jobs for '.$this->address_model->get_formatted_address($order_data['site_address_id']);
                $this->config->set_item('replacer', array('order' => array('/miniant/orders/order/index|Jobs'), 'history' => array('/miniant/orders/order/index|Job history')));
                $this->config->set_item('exclude', array('miniant'));
                $sql_conditions[] = 'miniant_orders.site_address_id = '.$order_data['site_address_id'];
            }
        } else {
            $this->config->set_item('exclude', array('miniant', 'html', 'index', 'history'));
        }


        if (!empty($maintenance_contract_id)) {
            $sql_conditions[] = 'miniant_orders.maintenance_contract_id = '.$maintenance_contract_id;
            $custom_title = 'List of Jobs related to MC'.$maintenance_contract_id;
        }

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'row_actions' => array('edit', 'delete'),
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'sql_conditions' => $sql_conditions,
            'uri_segment_1' => 'orders',
            'uri_segment_2' => 'order',
            'wide_layout' => true,
            'feature_type' => 'Custom Feature',
            'row_actions' => array('edit', 'delete', 'documents', 'history', 'assignments'),
            'module' => 'miniant',
            'row_action_capabilities' => array(
                'delete' => 'orders:deleteorders',
                'edit' => 'orders:editassignedorders',
                'documents' => 'orders:vieworders',
                'history' => 'orders:vieworders'
            ),
            'row_action_conditions' => array(
                'documents' =>  function($row) {
                    $ci = get_instance();
                    return $ci->order_model->has_statuses($row[0], array('AWAITING REVIEW', 'REVIEWED', 'INVOICED'));
                }
            ),
            'show_add_button' => has_capability('orders:writeorders'),
            'custom_columns_callback' => $this->order_model->get_custom_columns_callback(),
            'group_by' => 'miniant_orders.id',
            'custom_title' => $custom_title,
            'url_param' => $parent_order_id.'/'.$maintenance_contract_id
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_orders',
            'field' => 'id',
            'label' => 'Job Number',
            'field_alias' => 'order_id',
            ));

        /*
        $this->datagrid->add_column(array(
            'table' => 'orders',
            'field' => 'call_date',
            'label' => 'Call date',
            'field_alias' => 'order_call_datetime',
            'width' => 100
            ));
        */
        $this->datagrid->add_column(array(
            'table' => 'miniant_orders',
            'field' => 'parent_sq_id',
            'field_alias' => 'parent_sq_id',
            'label' => 'SQ ID',
            ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_orders',
            'field' => 'order_type_id',
            'field_alias' => 'order_type',
            'label' => 'Request type',
            'sql_select' => '(SELECT name FROM types WHERE id = order_type_id)',
            ));

        /*
        $this->datagrid->add_column(array(
            'field' => 'number_of_units',
            'field_alias' => 'number_of_units',
            'sql_select' => '(SELECT COUNT(*) FROM assignments WHERE order_id = orders.id)',
            'label' => '# Units',
            ));
        */
        $this->datagrid->add_column(array(
            'field' => 'site_suburb',
            'field_alias' => 'site_suburb',
            'sql_select' => '(SELECT city FROM addresses WHERE addresses.id = miniant_orders.site_address_id)',
            'label' => 'Site suburb',
            ));
        /* We can't display a single booking date for a order, as it could have many (one for each unit/diagnostic/repair_job)
         * Maybe we could enter the date of the earliest appointment,
         * and add the date of the estimated finish time of the latest appointment to represent the estimated finish time of the order
        $this->datagrid->add_column(array(
            'field' => 'appointment_date',
            'field_alias' => 'appointment_date',
            'label' => 'Booking date',
            'in_combo_filter' => false));
         */

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
            'requires_capability' => 'orders:viewbillingcontact'
        ));

        $this->datagrid->add_column(array(
            'table' => 'contacts',
            'table_alias' => 'site_contact',
            'sql_select' => 'CONCAT(site_contact.first_name, " ", site_contact.surname)',
            'field' => 'id',
            'label' => 'Site Contact',
            'field_alias' => 'site_contact_name',
            'requires_capability' => 'orders:viewsitecontact'
        ));

        $this->datagrid->add_column(array(
            'label' => 'Statuses',
            'field_alias' => 'statuses',
            'sortable' => false
        ));

        $this->datagrid->set_joins(array(
            array('table' => 'contacts billing_contact', 'on' => 'billing_contact.id = miniant_orders.billing_contact_id', 'type' => 'LEFT OUTER'),
            array('table' => 'contacts site_contact', 'on' => 'site_contact.id = miniant_orders.site_contact_id', 'type' => 'LEFT OUTER'),
            array('table' => 'accounts', 'on' => 'accounts.id = miniant_orders.account_id'),
            array('table' => 'document_statuses', 'on' => 'document_statuses.document_id = miniant_orders.id AND document_statuses.document_type = "order"'),
            array('table' => 'addresses', 'on' => 'addresses.id = miniant_orders.site_address_id', 'type' => 'LEFT OUTER'),
        ));

        $this->datagrid->render();
    }

    public function history($parent_order_id) {
        return $this->index('html', $parent_order_id);
    }

    public function add() {
        return $this->edit();
    }

    public function edit($order_id=null) {
        $this->load->model('miniant/installation_template_model');
        $this->load->model('miniant/tenancy_model');

        if (empty($order_id)) {
            require_capability('orders:writeorders');
        } else {
            require_capability('orders:editorders');
        }

        $order_data = $this->order_model->get_values($order_id);
        $locked = false;

        if (!empty($order_id)) {

            require_capability('orders:editorders');

            $order_data['call_date'] = unix_to_human($order_data['call_date'], '%d/%m/%Y %h:%i');
            $order_data['maintenance_preferred_start_date'] = unix_to_human($order_data['preferred_start_date'], '%d/%m/%Y');
            $order_data['preferred_start_date'] = unix_to_human($order_data['preferred_start_date'], '%d/%m/%Y %h:%i');

            form_element::$default_data = (array) $order_data;

            if (!empty(form_element::$default_data['attachment'])) {
                form_element::$default_data['attachment'] = $order_data['attachment']->filename_original;
            }

            if ($this->order_model->has_statuses($order_id, array('AWAITING REVIEW'))) {
                trigger_event('lock_for_review', 'orders', $order_id, false, 'miniant');
            }

            if (has_capability('orders:allocateorders') && $this->order_model->has_statuses($order_id, array('LOCKED FOR AMENDMENT'))) {
                add_message('This job is currently being amended by the technician, and is not editable', 'warning');
                $locked = true;
            }

            // Set up title bar
            $title = "Edit Job J$order_id"; // TODO add invoice number
            $help = "Use this form to edit the job";
        } else { // adding a new job
            $title = "Create a new job";
            $help = 'Use this form to create a new job';
        }

        $post_vars = $this->input->post();
        if (!empty($post_vars)) {
            foreach ($post_vars as $var => $val) {
                form_element::$default_data[$var] = $val;
            }
        }

        $this->config->set_item('replacer', array('miniant' => null, 'order' => array('/miniant/orders/order/index|Jobs'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());
        $jstoload = array(
             'jquery.signaturepad',
             'signaturepad/flashcanvas',
             'signaturepad/json2',
             'orders/order_edit',
             'application/messages',
         );

        $is_service = $order_data['order_type_id'] == $this->order_model->get_type_id('Service');
        $is_repair = $order_data['order_type_id'] == $this->order_model->get_type_id('Repair');
        $is_maintenance = $order_data['order_type_id'] == $this->order_model->get_type_id('Maintenance');
        $is_installation = $order_data['order_type_id'] == $this->order_model->get_type_id('Installation');

        if (!empty($order_id)) {
            if ($is_maintenance || $is_service) {
                $jstoload[] = 'orders/order_units_maintenance';
            } else if ($is_installation) {
                $jstoload[] = 'orders/order_units_installation';
            } else if ($is_repair) {
                $jstoload[] = 'orders/order_units_repair';
            } else {
                $jstoload[] = 'orders/order_units';
            }
        }

        $dropdowns = $this->get_dropdowns();

        // Remove any unit type that doesn't have a task template in the DB
        if ($is_installation) {
            foreach ($dropdowns['unit_types'] as $unit_type_id => $unit_type_name) {
                if ($unit_type_id < 1) {
                    continue;
                }

                if (!$this->installation_template_model->get(array('unit_type_id' => $unit_type_id))) {
                    unset($dropdowns['unit_types'][$unit_type_id]);
                }
            }
        }

        if (!empty($order_id)) {
            $this->db->where('account_id', $order_data['account_id']);
            $dropdowns['tenancies'] = $this->tenancy_model->get_dropdown('name', '-- Select a Tenancy/Owner --');
        } else {
            unset($dropdowns['order_types'][$this->order_model->get_type_id('Repair')]);
            $dropdowns['tenancies'] = array();
        }

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/order/edit',
                             'order_id' => $order_id,
                             'order_data' => $order_data,
                             'locked' => $locked,
                             'submit_buttons' => $this->get_submit_buttons($order_id),
                             'dropdowns' => $dropdowns,
                             'jstoloadinfooter' => $jstoload,
                             'csstoload' => array('jquery.signaturepad'),
                             'is_maintenance' => $is_maintenance,
                             'is_service' => $is_service,
                             'is_repair' => $is_repair,
                             'feature_type' => 'Custom Feature',
                             'is_installation' => $is_installation,
                             'module' => 'miniant',
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {
        require_capability('orders:editorders');
        if ($this->input->post('return')) {
            redirect(base_url().'miniant/orders/order/browse');
        }

        $required_fields = array(
            'account_id' => "Billing account",
            'site_address_id' => "Job site address"
        );

        $is_maintenance = $this->input->post('order_type_id') == $this->order_model->get_type_id('Maintenance');
        $is_service = $this->input->post('order_type_id') == $this->order_model->get_type_id('Service');
        $is_installation = $this->input->post('order_type_id') == $this->order_model->get_type_id('Installation');

        if ($order_id = (int) $this->input->post('order_id')) {
            $order = $this->order_model->get($order_id);
            $redirect_url = base_url().'miniant/orders/order/browse';
        } else {
            $required_fields['order_type_id'] = "Request Type";
        }

        if ($is_installation) {
            $required_fields['installation_quotation_number'] = 'Quotation number';
        }

        if ($is_maintenance) {
            $required_fields['maintenance_preferred_start_date'] = 'Start date';
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        if ($this->input->post('deposit_required')) {
            $this->form_validation->set_rules('cc_number', 'Credit Card number', 'trim|exact_length[16]|integer');
            $this->form_validation->set_rules('cc_security', 'Credit Card security', 'trim|exact_length[3]|integer');
            $this->form_validation->set_rules('cc_expiry', 'Credit Card expiry', array(
                'trim',
                array($this->order_model, 'expiry_check')
                )
            );
            $this->form_validation->set_message('expiry_check', 'Please enter an expiry data in the format mm/yy (e.g. 05/13).');
        }

        $success = $this->form_validation->run();

        $action_word = ($order_id) ? 'updated' : 'created';

        if (!$success) {
            echo validation_errors();
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            return $this->edit($order_id);
        }
        $billing_contact_id = $this->contact_model->get(array(
            'contact_type_id' => $this->contact_model->get_type_id('Billing'),
            'account_id' => $this->input->post('account_id')), true
        )->id;

        $order_type_id = $this->input->post('order_type_id');

        $order_data = array(
            'call_date' => human_to_unix($this->input->post('call_date')),
            'account_id' => $this->input->post('account_id'),
            'site_contact_id' => $this->input->post('site_contact_id'),
            'site_address_id' => $this->input->post('site_address_id'),
            'customer_po_number' => $this->input->post('customer_po_number'),
            'deposit_required' => $this->input->post('deposit_required'),
            'deposit_amount' => ($this->input->post('deposit_amount')) ? $this->input->post('deposit_amount') : 0,
            'billing_contact_id' => $billing_contact_id,
            'maintenance_contract_id' => $this->input->post('maintenance_contract_id'),
            'installation_quotation_number' => $this->input->post('installation_quotation_number'),
            'cc_type' => $this->input->post('cc_type'),
            'cc_number' => $this->input->post('cc_number'),
            'cc_expiry' => $this->input->post('cc_expiry'),
            'cc_security' => $this->input->post('cc_security'),
        );

        if (empty($order_data['site_contact_id']) || $order_data['site_contact_id'] == 'null' || $order_data['site_contact_id'] == 'undefined') {
            $order_data['site_contact_id'] = null;
        }

        if (empty($order_data['maintenance_contract_id']) || $order_data['maintenance_contract_id'] == 'null') {
            $order_data['maintenance_contract_id'] = null;
        }

        if ($is_maintenance) {
            $order_data['preferred_start_date'] = human_to_unix($this->input->post('maintenance_preferred_start_date'));
        } else {
            $order_data['preferred_start_date'] = human_to_unix($this->input->post('preferred_start_date'));
        }

        if (empty($order_id)) {
            $order_data['order_type_id'] = $this->input->post('order_type_id');
        }

        // Don't allow removal of call date
        if (empty($order_data['call_date'])) {
            unset($order_data['call_date']);
        }

        if (!empty($order_id)) {
            if (!$this->order_model->edit($order_id, $order_data)) {
                add_message('Could not update this job!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!($order_id = $this->order_model->add($order_data))) {
                add_message('Could not create this job!', 'error');
                redirect($redirect_url);
            }
        }

        if (!empty($_FILES['attachment']['name'])) {
            $result = $this->_process_attachment($order_id);

            if (!$result) {
                add_message("The attachment was not uploaded, please try again", 'warning');
            }
        }

        if ($this->input->post('saveandrecordunits')) {
            trigger_event('create_order', 'orders', $order_id, false, 'miniant');
            $redirect_url = base_url().'miniant/orders/order/edit/'.$order_id.'#order_units';
        }

        if ($this->input->post('submitforallocation')) {
            trigger_event('admin_prep_finished', 'orders', $order_id, false, 'miniant');
            $redirect_url = base_url().'miniant/orders/order/browse/';
        }

        add_message("Job $order_id has been successfully $action_word!", 'success');
        redirect($redirect_url);
    }

    /**
     * Depending on the current statuses and the capabilities of the current user, a different label will be returned
     */
    public function get_submit_buttons($order_id) {
        $submit_buttons = array();

        if (empty($order_id)) {
            return array('saveandrecordunits' => 'Save and record units');
        }

        $order = $this->order_model->get($order_id);

        $statuses = $this->order_model->get_statuses($order_id);

        if (in_array('DRAFT', $statuses)) {
            add_message('Please record at least one unit in the Equipment section, then submit for allocation', 'warning');
            return array('submitforallocation' => 'Submit for allocation');
        }

        if (in_array('READY FOR ALLOCATION', $statuses) && has_capability('orders:allocateorders')) {
            $submit_buttons['tobescheduled'] = 'Submit';
        }

        if ($this->order_model->check_statuses($order_id, array('SCHEDULED', 'PENDING AMENDMENT'), 'OR', array('AWAITING REVIEW', 'REVIEWED'))
            && has_capability('orders:editassignedorders', null, false)) {

            $submit_buttons['submitforreview'] = 'Submit for review';

        }

        if ($this->order_model->check_statuses($order_id, array('REVIEWED'), 'OR', array('SIGNED BY CLIENT')) && has_capability('orders:editassignedorders', null, false)) {
            $submit_buttons['savesignature'] = 'Save client signature';

        }

        if (in_array('LOCKED FOR REVIEW', $statuses) && has_capability('orders:editassignedorders', null, false)) {
            // No buttons
            $submit_buttons = array();
        }

        if (in_array('AWAITING REVIEW', $statuses) && !in_array('LOCKED FOR REVIEW', $statuses) && has_capability('orders:editassignedorders', null, false)) {
            $submit_buttons['lockforamendment'] = 'Lock for amendment';
        }

        if (in_array('AWAITING REVIEW', $statuses) && has_capability('orders:allocateorders', null, false)) {
            $submit_buttons['finishreview'] = 'Finish review';
        }

        if ($this->order_model->has_statuses($order_id, array('DRAFT', 'SCHEDULED', 'ALLOCATED'))&& has_capability('orders:allocateorders', null, false)) {
            $submit_buttons['submit'] = 'Submit';
        }

        return $submit_buttons;
    }

    public function cancel($order_id=null) {
        if (!empty($order_id)) {
            $this->order_model->delete($order_id);
            trigger_event('cancel', 'orders', $order_id, false, 'miniant');
        }

        return $this->index();
    }

    public function get_dropdowns() {
        $dropdowns = array(
            'maintenance_contracts' => $this->maintenance_contract_model->get_labelled_dropdown(),
            'accounts' => $this->account_model->get_dropdown('name'),
            'street_types' => $this->street_type_model->get_dropdown('name', '-- Select a street type --'),
            'unit_types' => $this->unit_model->get_types_dropdown('--Select an equipment type--'),
            'brands_refrigerated' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Refrigerated A/C')),
            'brands_evaporative' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Evaporative A/C')),
            'brands_other' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Other Refrigeration')),
            'order_types' => $this->order_model->get_types_dropdown(true, false, 'order'),
            'servicequotes' => $this->order_model->get_linkable_servicequotes(),
            'unitry_types_refrigerated' => $this->unitry_type_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Refrigerated A/C')),

            );
        return $dropdowns;
    }

    public function delete_attachment($order_id) {
        $this->load->model('miniant/order_attachment_model');
        $this->order_attachment_model->delete(array('order_id' => $order_id));
        add_message('The attachment was successfully deleted');
        return $this->edit($order_id);
    }

    /**
     * @param string $order The job whose attachment is being uploaded
     */
    private function _process_attachment($order_id) {
        $this->load->model('miniant/order_attachment_model');
        $config = array();
        $config['upload_path'] = $this->config->item('files_path').'orders/'.$order_id;

        if (!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        // $config['allowed_types'] = ENQUIRIES_UPLOAD_ALLOWED_TYPES;
        $config['allowed_types'] = $this->get_allowed_file_types();
        $config['encrypt_name'] = true;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('attachment')) {
            if ($this->upload->display_errors('','') == 'You did not select a attachment to upload.') {
                return false;
            }

            $readable_filetypes = strtoupper(str_replace('|', ', ', $config['allowed_types']));
            add_message("The type of attachment you uploaded is not allowed. Allowed types are $readable_filetypes.", 'error');
            redirect(base_url().'miniant/orders/order/edit/'.$order_id);
        }

        $attachment_data = $this->upload->data();

        $attachment = array('filename_original' => $attachment_data['orig_name'],
                      'hash' => $attachment_data['file_name'],
                      'order_id' => $order_id,
                      'directory' => 'orders',
                      'file_type' => $attachment_data['file_type'],
                      'file_extension' => $attachment_data['file_ext'],
                      'file_size' => $attachment_data['file_size']);

        if ($old_attachment = $this->order_attachment_model->get(array('order_id' => $order_id), true)) {
            unlink($config['upload_path'].'/'.$old_attachment->hash);
            $this->order_attachment_model->edit($old_attachment->id, $attachment);
            return $old_attachment->id;
        }

        if (!$attachment_id = $this->order_attachment_model->add($attachment)) {
            add_message('The attachment ' . $new_attachment['filename_original'] . ' was uploaded, but the attachment info could not be recorded in the database...', 'warning');
            return false;
        } else {
            return $attachment_id;
        }
    }

    private function get_allowed_file_types() {
        return 'doc|pdf|xls|xlsx|png|jpg|jpeg|gif';
    }

    public function invoices($order_id) {
        $this->load->model('miniant/assignment_model');
        $order = $this->order_model->get_values($order_id);

        require_capability('orders:vieworders');

        // Set up title bar
        $title = "Invoices for job {$order['reference_id']}"; // TODO add invoice number
        $help = "This screen shows the invoices for this job";

        // Only show assignments that are complete
        foreach ($order['assignments'] as $key => $assignment) {
            if (!$this->assignment_model->has_statuses($assignment->id, array('COMPLETE'))) {
                unset($order['assignments'][$key]);
            }

            if (empty($order['assignments'])) {
                break;
            }

            // Set up the action buttons for each assignment/invoice
            $action_buttons = array();

            if (empty($assignment->invoice_id)) {
                $action_buttons[base_url().'miniant/orders/invoice/generate_invoice/'.$assignment->id] = 'Generate';
            } else {
                $action_buttons[base_url().'miniant/orders/invoice/view_invoice/'.$assignment->invoice_id] = 'View';
                $action_buttons[base_url().'miniant/orders/invoice/send_invoice/'.$assignment->invoice_id] = 'Send';
                $action_buttons[base_url().'miniant/orders/invoice/download_invoice/'.$assignment->invoice_id] = 'Download';
            }

            $order['assignments'][$key]->action_buttons = $action_buttons;
        }

        $this->config->set_item('replacer', array('orders' => array('/orders/order/index|Jobs'), 'edit' => $title, 'add' => $title));

        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/order/invoices',
                             'order_id' => $order_id,
                             'order' => $order,
                             'feature_type' => 'Custom Feature',
                             'jstoloadinfooter' => array(
                             ),
                             'csstoload' => array()
                         );

        $this->load->view('template/default', $pageDetails);
    }

    public function edit_statuses($order_id) {
        $this->load->model('miniant/status_model');
        require_capability('orders:writeorders');

        $all_statuses = $this->status_model->get();
        $order_statuses = $this->order_model->get_statuses($order_id, false);

        $title = "Edit Job #$order_id statuses";
        $this->config->set_item('replacer', array('orders' => array('/orders/order/index|Jobs'), 'edit' => $title, 'add' => $title));

        $title_options = array('title' => $title, 'help' => '', 'expand' => 'page', 'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/order/edit_statuses',
                             'all_statuses' => $all_statuses,
                             'order_statuses' => $order_statuses,
                             'jstoloadinfooter' => array(),
                             'feature_type' => 'Custom Feature',
                             'csstoload' => array('jquery.signaturepad'),
                             );

        $this->load->view('template/default', $pageDetails);
    }
}
