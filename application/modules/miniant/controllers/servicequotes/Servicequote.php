<?php
class Servicequote extends MY_Controller {
    public $stages = array(
            array('url' => 'review_required_parts', 'statuses' => array('SUPPLIER PARTS RECEIVED')),
            array('url' => 'record_received_parts', 'statuses' => array('PURCHASE ORDERS SENT')),
            array('url' => 'prepare_purchase_orders', 'statuses' => array('CLIENT RESPONSE RECORDED')),
            array('url' => 'record_client_response', 'statuses' => array('CLIENT QUOTE SENT')),
            array('url' => 'prepare_client_quote', 'statuses' => array('FINAL SUPPLIERS SELECTED', 'CLIENT QUOTE PREVIEWED')),
            array('url' => 'select_final_suppliers', 'statuses' => array('SUPPLIER QUOTES RECORDED')),
            array('url' => 'record_supplier_quotes', 'statuses' => array('SUPPLIER QUOTE REQUEST PREVIEWED', 'SUPPLIER QUOTE REQUEST SENT')),
            array('url' => 'prepare_quote_requests', 'statuses' => array('POTENTIAL SUPPLIERS SELECTED')),
            array('url' => 'select_suppliers', 'statuses' => array('REQUIRED PARTS APPROVED')),
            array('url' => 'review_required_parts', 'statuses' => array('DRAFT'))
            );

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), 'servicequotes' => 'test'));
        // $this->config->set_item('exclude', array('browse', 'servicequote/index'));
        $this->load->model('miniant/miniant_account_model', 'account_model');
    }

    public function browse($outputtype='html', $hidden_statuses=0, $sq_id=null) {
        return $this->index($outputtype, $hidden_statuses, $sq_id=null);
    }

    public function history($servicequote_id) {
        return $this->edit($servicequote_id, true);
    }

    /**
     * @param bitmask $show_old_servicequotes 1 = archived, 2 = cancelled
     */
    public function index($outputtype='html', $hidden_statuses=null, $sq_id=null) {
        require_capability('servicequotes:viewsqs');

        if (is_null($hidden_statuses)) {
            $hidden_statuses = $this->session->userdata('servicequote_filter');
        }

        if (is_null($hidden_statuses)) {
            $hidden_statuses = 3;
        }

        $this->session->set_userdata('servicequote_filter', $hidden_statuses);

        define('ARCHIVED_STATUS', 0x1);
        define('CANCELLED_STATUS', 0x2);
        $statuses_to_hide = $this->get_statuses_from_bitmask($hidden_statuses);

        $this->config->set_item('exclude', array('index'));

        $sql_conditions = array();

        if (!empty($statuses_to_hide)) {
            $status_selector_filter = "status_id IN (";
            foreach ($statuses_to_hide as $hidden_status) {
                $status_selector_filter .= $hidden_status . ',';
            }
            $status_selector_filter = substr($status_selector_filter, 0, -1);
            $status_selector_filter .= ')';
            $sql_conditions[] = "miniant_servicequotes.id NOT IN (SELECT document_id FROM document_statuses WHERE $status_selector_filter)";
        }

        if (!empty($sq_id)) {
            $sql_conditions[] = "miniant_servicequotes.id = $sq_id";
        }

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'row_actions' => array('edit', 'documents','delete'),
            'row_action_capabilities' => array('edit' => 'servicequotes:editsqs', 'documents' => 'servicequotes:viewsqs', 'delete' => 'servicequotes:deletesqs'),
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'custom_columns_callback' => $this->servicequote_model->get_custom_columns_callback(),
            'show_add_button' => false,
            'feature_type' => 'Custom Feature',
            'model' => $this->servicequote_model,
            'module' => 'miniant',
            'custom_title' => 'Service quotes',
            'uri_segment_1' => 'servicequotes',
            'uri_segment_2' => 'servicequote',
            'sql_conditions' => $sql_conditions,
            'url_param' => $hidden_statuses.'/'.$sq_id,
            'debug' => false,
            'datagrid_callbacks' => 'servicequotes/datagrid_setup'
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_servicequotes',
            'field' => 'id',
            'label' => 'Invoice ID',
            'field_alias' => 'servicequote_id'));
        $this->datagrid->add_column(array(
            'table' => 'miniant_servicequotes',
            'field' => 'order_id',
            'label' => 'Job Number',
            'field_alias' => 'order_id'));
        $this->datagrid->add_column(array(
            'field' => 'site_suburb',
            'field_alias' => 'site_suburb',
            'sql_select' => '(SELECT city FROM addresses WHERE addresses.id = miniant_orders.site_address_id)',
            'label' => 'Suburb',
            ));
        $this->datagrid->add_column(array(
            'table' => 'accounts',
            'field' => 'name',
            'label' => 'Account'));
        $this->datagrid->add_column(array(
            'label' => 'Statuses',
            'field_alias' => 'statuses',
            'sortable' => false
        ));

        $this->datagrid->setup_filters();

        $this->datagrid->set_joins(array(
            array('table' => 'miniant_orders', 'on' => 'miniant_servicequotes.order_id = miniant_orders.id'),
            array('table' => 'accounts', 'on' => 'accounts.id = miniant_orders.account_id'),
            array('table' => 'miniant_assignments', 'on' => 'miniant_assignments.diagnostic_id = miniant_servicequotes.diagnostic_id'),
            array('table' => 'miniant_units', 'on' => 'miniant_units.id = miniant_assignments.unit_id'),
            // array('table' => 'document_statuses', 'on' => 'document_statuses.document_id = miniant_servicequotes.id AND document_statuses.document_type = "servicequote"'),
            array('table' => 'addresses', 'on' => 'addresses.id = miniant_orders.site_address_id', 'type' => 'LEFT OUTER'),
        ));
        $this->datagrid->render();
    }

    /**
     * This acts as a router to other functions, depending on the SQ's current statuses
     */
    public function edit($servicequote_id, $review_only=false) {
        if ($review_only !== true) {
            $review_only = false;
        }

        require_capability('servicequotes:editsqs');
        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('browse|Service Quotes')));

        foreach ($this->stages as $stage) {
            if ($this->servicequote_model->has_statuses($servicequote_id, $stage['statuses'])) {
                redirect(base_url().'miniant/servicequotes/servicequote/'.$stage['url'].'/'.$servicequote_id.'/'.$review_only);
            }
        }

        // catchall if none of the above checks worked
        redirect(base_url().'miniant/servicequotes/servicequote/review_required_parts/'.$servicequote_id.'/'.$review_only);
    }

    public function review_required_parts($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $title = "Review required parts for SQ #$servicequote_id";
        $help = "Use this form to review, edit and approve the parts required for SQ #$servicequote_id.";

        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id Review required parts", __FUNCTION__ => null));

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());

        $this->db->where('part_type_id IS NOT NULL', null, false);
        $this->db->where('part_type_id NOT IN (SELECT id FROM miniant_part_types WHERE name = "Labour")', null, false);

        $parts = $this->part_model->get(array('servicequote_id' => $servicequote_id));

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'review_required_parts',
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'parts' => $parts,
                             'csstoload' => array()
                             );

        if (!$review_only) {
            $pageDetails['jstoload'] = array('servicequotes/review_required_parts');
        }

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);

    }

    public function approve_required_parts($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        trigger_event('required_parts_approved', 'servicequote', $servicequote_id, false, 'miniant');
        add_message('The required parts have been approved. Please select the suppliers now.');
        redirect(base_url().'miniant/servicequotes/servicequote/edit/'.$servicequote_id.'/'.$review_only);
    }

    public function select_suppliers($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $title = "Select suppliers for SQ #$servicequote_id";
        $help = "Use this form to select supplier for SQ #$servicequote_id.";

        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id select suppliers", __FUNCTION__ => null));
        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());

        $all_suppliers = $this->contact_model->get(array('contact_type_id' => $this->contact_model->get_type_id('Supplier')));
        $selected_suppliers = $this->servicequote_supplier_model->get(array('servicequote_id' => $servicequote_id));
        $suppliers = array();

        foreach ($all_suppliers as $supplier_contact) {
            $supplier = $this->get_supplier_data($supplier_contact);
            $supplier->selected = false;

            foreach ($selected_suppliers as $selected_supplier) {
                $supplier->selected = $supplier->selected || $selected_supplier->supplier_id == $supplier_contact->id;
            }

            $suppliers[] = $supplier;
        }

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'select_suppliers',
                             'suppliers' => $suppliers,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/select_suppliers'),
                             'csstoload' => array()
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);

    }

    public function approve_suppliers() {
        require_capability('servicequotes:editsqs');
        $supplier_contact_ids = $this->input->post('supplier_contact_id');
        $servicequote_id = $this->input->post('servicequote_id');
        $suppliers = array_keys($supplier_contact_ids);

        $this->servicequote_model->update_selected_suppliers($servicequote_id, $suppliers);

        trigger_event('selected_suppliers_approved', 'servicequote', $servicequote_id, false, 'miniant');
        add_message('The selected suppliers have been approved. Please prepare the Supplier quote requests now.');
        redirect(base_url().'miniant/servicequotes/servicequote/edit/'.$servicequote_id);
    }

    public function prepare_quote_requests($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $title = "Prepare supplier quote requests for SQ #$servicequote_id";
        $help = "Use this form to prepare supplier quote requests for SQ #$servicequote_id. Select which suppliers will receive a quote for each part, then preview the supplier quote request before sending it to all suppliers. Checkboxes will be disabled for quotes that have already been sent.";

        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id prepare supplier quote requests", __FUNCTION__ => null));
        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());

        $this->db->where('part_type_id IS NOT NULL', null, false);
        $this->db->where('part_type_id NOT IN (SELECT id FROM miniant_part_types WHERE name = "Labour")', null, false);
        $parts = $this->part_model->get(array('servicequote_id' => $servicequote_id));

        foreach ($parts as $key => $part) {
            $parts[$key]->photos = $this->part_model->get_issue_photos($part->id);
        }

        $selected_suppliers = $this->servicequote_supplier_model->get(array('servicequote_id' => $servicequote_id));

        $suppliers = array();

        foreach ($selected_suppliers as $selected_supplier) {
            $supplier = $this->contact_model->get($selected_supplier->supplier_id);
            $supplier->name = $this->account_model->get($supplier->account_id)->name;
            $supplier->contact = $supplier->first_name . ' ' . $supplier->surname;
            $supplier->email = $supplier->email;

            $suppliers[] = $supplier;
        }

        $supplier_quotes_array = $this->supplier_quote_model->get_from_selected_suppliers($servicequote_id);

        $supplier_quotes = array();

        foreach ($supplier_quotes_array as $supplier_quote) {
            if (empty($supplier_quotes[$supplier_quote->part_id])) {
                $supplier_quotes[$supplier_quote->part_id] = array();
            }
            $supplier_quotes[$supplier_quote->part_id][$supplier_quote->supplier_id] = $supplier_quote;
        }

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'prepare_quote_requests',
                             'suppliers' => $suppliers,
                             'parts' => $parts,
                             'review_only' => $review_only,
                             'supplier_quotes' => $supplier_quotes,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/prepare_quote_requests'),
                             'csstoload' => array()
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);

    }

    public function process_quote_requests() {
        require_capability('servicequotes:editsqs');
        $send = $this->input->post('send');
        $preview = $this->input->post('preview');
        $servicequote_id = $this->input->post('servicequote_id');
        $quoting_supplier_ids = $this->input->post('quoting_supplier_ids');

        // First delete all supplier_quotes that haven't yet been sent
        $this->db->where('request_sent_date IS NULL');
        $this->supplier_quote_model->delete(array('servicequote_id' => $servicequote_id));

        if (!empty($quoting_supplier_ids)) {
            foreach ($quoting_supplier_ids as $part_id => $supplier_ids) {

                $supplier_ids = array_keys($supplier_ids);

                foreach ($supplier_ids as $supplier_id) {

                    $params = compact('part_id', 'supplier_id', 'servicequote_id');

                    if (!$this->supplier_quote_model->get($params)) {
                        $this->supplier_quote_model->add($params);
                    }
                }
            }
        }

        if ($send) {
            return $this->send_quote_requests();
        } else if ($preview) {
            return $this->preview_quote_requests();
        }
    }

    public function preview_quote_requests() {
        ob_end_clean();
        require_capability('servicequotes:editsqs');

        $pdf_settings = $this->get_pdf_settings("Supplier quote requests (preview)");

        $this->load->library('miniant_pdf', $pdf_settings);
        $this->miniant_pdf->_config['page_orientation'] = 'portrait';

        $servicequote_id = $post_data['servicequote_id'];
        $supplier_quotes = $this->supplier_quote_model->get_from_selected_suppliers($servicequote_id);
        $selected_photos = $post_data['selected_photos'];

        trigger_event('supplier_quote_requests_previewed', 'servicequote', $servicequote_id, false, 'miniant');
        $suppliers = $this->collate_suppliers_from_supplier_quotes($supplier_quotes);

        foreach ($suppliers as $supplier) {

            $this->miniant_pdf->addpage();
            $this->miniant_pdf->setCellPadding(55);
            $this->miniant_pdf->_config['encoding'] = 'UTF-8';
            $this->miniant_pdf->SetSubject('Supplier quote request: '.$supplier['supplier_data']->name);
            $this->miniant_pdf->writeDocumentTitle('Supplier quote request for '.$supplier['supplier_data']->name);

            $this->format_supplier_quote_request($supplier, $servicequote_id, $selected_photos);
        }

        $this->miniant_pdf->output("supplier_quote_requests_preview.pdf", 'D');
    }

    public function send_quote_requests() {
        ob_end_clean();
        require_capability('servicequotes:editsqs');
        $servicequote_id = $this->input->post('servicequote_id');
        $supplier_quotes = $this->supplier_quote_model->get_from_selected_suppliers($servicequote_id);
        $suppliers = $this->collate_suppliers_from_supplier_quotes($supplier_quotes);
        $selected_photos = $this->input->post('selected_photos');
        $successful_sends = 0;
        require_once(APPPATH.'modules/miniant/libraries/Miniant_pdf.php');

        // Generate then save and email Supplier quote request PDFs
        foreach ($suppliers as $supplier) {

            $pdf_settings = $this->get_pdf_settings("Supplier quote request");

            // I'm not calling the library in the codeigniter way because it doesn't reset itself properly after each instantiation
            $this->miniant_pdf = new miniant_pdf($pdf_settings);
            $this->miniant_pdf->_config['page_orientation'] = 'portrait';
            $this->miniant_pdf->addpage();
            $this->miniant_pdf->setCellPadding(55);
            $this->miniant_pdf->_config['encoding'] = 'UTF-8';
            $this->miniant_pdf->SetSubject('Supplier quote request for '.$supplier['supplier_data']->name);

            $this->format_supplier_quote_request($supplier, $servicequote_id, $selected_photos);
            $upload_path = $this->config->item('files_path').'servicequotes/'.$servicequote_id.'/';
            if (!file_exists($upload_path)) {
                @mkdir($upload_path, 0777, true);
            }
            $filename = $upload_path . "supplier_quote_request_SQ$servicequote_id{$supplier['supplier_data']->name}_".unix_to_human(time(), '%d-%m-%Y').".pdf";
            $this->miniant_pdf->output($filename, 'F');

            $this->save_servicequote_document($filename, 'Supplier quote request', $servicequote_id, $supplier['supplier_data']->id);

            $this->email->clear(true);
            $this->email->from($this->setting_model->get_value('Ops manager email address'), 'Temperature Solutions', $this->setting_model->get_value('Ops manager email address'));
            $this->email->subject('Temperature Solutions: Supplier quote request');
            $this->email->message($this->load->view('servicequotes/emails/supplier_quote_request', compact('servicequote_id', 'supplier'), true));
            $this->email->to($supplier['supplier_data']->email);
            $this->email->bcc(array($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Ops manager email address')));

            $this->email->attach($filename);
            $email_object = clone($this->email);

            if (ENVIRONMENT == 'demo') {
                $result = true;
            } else {
                $result = $this->email->send();
            }

            if ($result) {
                $error_message = null;
                $successful_sends++;
                foreach ($supplier['parts'] as $part) {
                    $sq_params = array('servicequote_id' => $servicequote_id, 'supplier_id' => $supplier['supplier_data']->id, 'part_id' => $part->id);
                    if ($supplier_quote = $this->supplier_quote_model->get($sq_params, true)) {
                        $supplier_quote_id = $supplier_quote->id;
                    } else {
                        $supplier_quote_id = $this->supplier_quote_model->add($sq_params);
                    }

                    $this->supplier_quote_model->edit($supplier_quote_id, array('request_sent_date' => time()));
                }
            } else {
                $error_message = $this->email->print_debugger();
            }

            $this->email_log_model->log_message($email_object, __FILE__ . ' at line ' . __LINE__, $error_message, 'users', $this->session->userdata('user_id'), 'contacts', $supplier['supplier_data']->id);
        }

        if ($successful_sends < count($suppliers)) {
            add_message((count($suppliers) - $successful_sends) . ' supplier quote request(s) could not be emailed due to an error. Please email the supplier directly.', 'warning');
            redirect(base_url().'miniant/servicequotes/servicequote/prepare_quote_requests/'.$servicequote_id);
        } else {
            add_message($successful_sends . ' supplier quote requests were successfully sent out by email.', 'success');
            trigger_event('supplier_quote_requests_sent', 'servicequote', $servicequote_id, false, 'miniant');
            redirect(base_url().'miniant/servicequotes/servicequote/browse');
        }
    }

    public function format_supplier_quote_request($supplier, $servicequote_id, $selected_photos=array()) {
        require_capability('servicequotes:editsqs');
        $view_params = array(
            'parts' => $supplier['parts'],
            'supplier' => $supplier['supplier_data'],
            'servicequote_id' => $servicequote_id,
            'valid_until' => time() + 60 * 60 * 24 * 30 // 30 days
        );

        $this->miniant_pdf->SetFont($this->miniant_pdf->_config['page_font'], 'B', $this->miniant_pdf->_config['page_font_size']);
        $output = $this->load->view('servicequotes/pdf/supplier_quote_request_intro', $view_params, true);
        $this->miniant_pdf->writeHTML($output, false, false, false, false, '');

        $this->miniant_pdf->SetFont($this->miniant_pdf->_config['page_font'], '', $this->miniant_pdf->_config['page_font_size']);
        $output = $this->load->view('servicequotes/pdf/supplier_quote_request', $view_params, true);
        $this->miniant_pdf->writeHTML($output, false, false, false, false, '');

        foreach ($supplier['parts'] as $part) {
            if (!empty($selected_photos[$part->id])) {
                foreach ($selected_photos[$part->id] as $issue_photo) {
                    $this->miniant_pdf->addPage();
                    $this->miniant_pdf->print_heading($part->part_name);
                    $this->miniant_pdf->image(FCPATH.$issue_photo, '', '', '180');
                }
            }
        }
    }

    public function record_supplier_quotes($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id record supplier quotes", __FUNCTION__ => null));
        $selected_suppliers = $this->servicequote_supplier_model->get(array('servicequote_id' => $servicequote_id));

        $parts = $this->part_model->get_non_labour($servicequote_id);

        $supplier_quotes = $this->supplier_quote_model->get_from_selected_suppliers($servicequote_id);

        $supplier_parts = array();

        foreach ($selected_suppliers as $servicequote_supplier) {
            if (empty($supplier_parts[$servicequote_supplier->supplier_id])) {
                $supplier_parts[$servicequote_supplier->supplier_id] = array();
            }

            foreach ($parts as $part) {
                if (empty($supplier_parts[$servicequote_supplier->supplier_id][$part->id])) {
                    $supplier_parts[$servicequote_supplier->supplier_id][$part->id] = (object) array(
                        'unit_cost' => null,
                        'total_cost' => null,
                        'availability' => null,
                        'request_sent_date' => null,
                        'quote_received_date' => null,
                        'supplier_quote_id' => null
                    );

                }

                foreach ($supplier_quotes as $supplier_quote) {
                    if ($supplier_quote->part_id == $part->id && $supplier_quote->supplier_id == $servicequote_supplier->supplier_id) {
                        $supplier_parts[$servicequote_supplier->supplier_id][$part->id]->unit_cost = $supplier_quote->unit_cost;
                        $supplier_parts[$servicequote_supplier->supplier_id][$part->id]->total_cost = $supplier_quote->total_cost;
                        $supplier_parts[$servicequote_supplier->supplier_id][$part->id]->availability = $supplier_quote->availability;
                        $supplier_parts[$servicequote_supplier->supplier_id][$part->id]->request_sent_date = $supplier_quote->request_sent_date;
                        $supplier_parts[$servicequote_supplier->supplier_id][$part->id]->quote_received_date = $supplier_quote->quote_received_date;
                        $supplier_parts[$servicequote_supplier->supplier_id][$part->id]->supplier_quote_id = $supplier_quote->id;
                    }
                }
            }
        }

        $suppliers = $this->collate_suppliers_from_supplier_quotes($supplier_quotes);

        $title = "Record supplier quotes for SQ #$servicequote_id";
        $help = "For each supplier, record the quoted prices and availability of each part.";

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());
        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'record_supplier_quotes',
                             'suppliers' => $suppliers,
                             'supplier_parts' => $supplier_parts,
                             'parts' => $parts,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/record_supplier_quotes', 'bootstrap-editable'),
                             'csstoload' => array()
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function approve_supplier_quotes($servicequote_id) {
        require_capability('servicequotes:editsqs');
        $supplier_quotes = $this->supplier_quote_model->get_from_selected_suppliers($servicequote_id);
        $all_quotes_recorded = true;
        $all_availability_recorded = true;

        foreach ($supplier_quotes as $supplier_quote) {
            if (empty($supplier_quote->unit_cost) || empty($supplier_quote->total_cost)) {
                $all_quotes_recorded = false;
            }

            if (empty($supplier_quote->availability)) {
                $all_availability_recorded = false;
            }
        }

        if (!$all_availability_recorded) {
            add_message('Please enter an availability value for each part, for each supplier', 'danger');
            redirect(base_url().'miniant/servicequotes/servicequote/record_supplier_quotes/'.$servicequote_id);
        }

        add_message('Supplier quotes have been approved. Please select the final suppliers now for each part.');
        trigger_event('supplier_quotes_approved', 'servicequote', $servicequote_id, false, 'miniant');
        redirect(base_url().'miniant/servicequotes/servicequote/edit/'.$servicequote_id);
    }

    public function select_final_suppliers($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id select final suppliers", __FUNCTION__ => null));
        $supplier_quotes = $this->supplier_quote_model->get_from_selected_suppliers($servicequote_id);
        $suppliers = $this->collate_suppliers_from_supplier_quotes($supplier_quotes);

        $cheapest_unit_costs = array();
        $cheapest_total_costs = array();

        foreach ($suppliers as $supplier_id => $supplier) {
            $supplier_data = $supplier['supplier_data'];
            $supplier_parts = $supplier['parts'];

            foreach ($supplier_parts as $part) {

                if (empty($cheapest_unit_costs[$part->id])) {
                    $cheapest_unit_costs[$part->id] = new stdClass;
                    $cheapest_unit_costs[$part->id]->cost = 99999999999;
                }

                if ($cheapest_unit_costs[$part->id]->cost > $part->unit_cost) {
                    $cheapest_unit_costs[$part->id]->cost = $part->unit_cost;
                    $cheapest_unit_costs[$part->id]->supplier = $supplier['supplier_data'];
                }

                if (empty($cheapest_total_costs[$part->id])) {
                    $cheapest_total_costs[$part->id] = new stdClass;
                    $cheapest_total_costs[$part->id]->cost = 99999999999;
                }

                if ($cheapest_total_costs[$part->id]->cost > $part->total_cost) {
                    $cheapest_total_costs[$part->id]->cost = $part->total_cost;
                    $cheapest_total_costs[$part->id]->supplier = $supplier['supplier_data'];
                }
            }
        }

        $parts = array();

        foreach ($suppliers as $supplier_id => $supplier) {
            foreach ($supplier['parts'] as $part_id => $part) {
                $suppliers[$supplier_id]['parts'][$part_id]->cheapest_total_cost = $cheapest_total_costs[$part_id]->supplier->id == $supplier_id;
                $suppliers[$supplier_id]['parts'][$part_id]->cheapest_unit_cost = $cheapest_unit_costs[$part_id]->supplier->id == $supplier_id;

                if (empty($parts[$part->id])) {
                    $parts[$part->id] = array('suppliers' => array(), 'suppliers_dropdown' => array());
                }
                $parts[$part->id]['suppliers'][$supplier_id] = $suppliers[$supplier_id]['parts'][$part_id];
                $parts[$part->id]['suppliers_dropdown'][$supplier_id] = $supplier['supplier_data']->name;
            }
        }
        // Remove parts that only have one supplier's quotes

        foreach ($parts as $part_id => $part_data) {
            if (count($part_data['suppliers_dropdown']) < 2) {
                unset($parts[$part_id]);
            } else {
                $parts[$part_id]['suppliers_dropdown'] = array(null => '-- Select One --') + $parts[$part_id]['suppliers_dropdown'];
            }
        }

        // If there are no parts to compare, skip this step entirely and pre-approve the only supplier for all parts
        if (empty($parts)) {
            if ($review_only) {
                redirect(base_url().'miniant/servicequotes/servicequote/prepare_client_quote/'.$servicequote_id.'/1');
            } else {
                $this->approve_final_supplier_for_all_parts($servicequote_id, reset($suppliers));
                return false;
            }
        }

        $title = "Select final suppliers for SQ #$servicequote_id";
        $help = "Review the quoted prices and select for each part the supplier that will be sent a purchase order for that part.";

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());
        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'select_final_suppliers',
                             'suppliers' => $suppliers,
                             'parts' => $parts,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/select_final_suppliers'),
                             'csstoload' => array()
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function approve_final_supplier_for_all_parts($servicequote_id, $supplier, $review_only) {
        $supplier_id = $supplier['supplier_data']->id;

        foreach ($supplier['parts'] as $part_id => $part) {
            $supplier_quote = $this->supplier_quote_model->get(array('supplier_id' => $supplier_id, 'part_id' => $part_id, 'servicequote_id' => $servicequote_id), true);
            $this->part_model->edit($part_id, array('supplier_contact_id' => $supplier_id, 'supplier_quote_id' => $supplier_quote->id, 'supplier_cost' => $supplier_quote->total_cost));
        }

        trigger_event('final_suppliers_approved', 'servicequote', $servicequote_id, false, 'miniant');
        add_message('Final suppliers have been selected. Please prepare the client\'s service quotation now.');
        redirect(base_url().'miniant/servicequotes/servicequote/edit/'.$servicequote_id.'/'.$review_only);
    }

    public function approve_final_suppliers() {
        require_capability('servicequotes:editsqs');
        $supplier_ids = $this->input->post('supplier_ids');
        $servicequote_id = $this->input->post('servicequote_id');

        $all_completed = true;
        foreach ($supplier_ids as $part_id => $supplier_id) {
            if (empty($supplier_id)) {
                $all_completed = false;
            }
        }

        if (!$all_completed) {
            add_message('Please select a supplier for each part', 'danger');
            redirect(base_url().'miniant/servicequotes/servicequote/select_final_suppliers/'.$servicequote_id);
        }

        foreach ($supplier_ids as $part_id => $supplier_id) {
            $supplier_quote = $this->supplier_quote_model->get(array('supplier_id' => $supplier_id, 'part_id' => $part_id, 'servicequote_id' => $servicequote_id), true);
            $this->part_model->edit($part_id, array('supplier_contact_id' => $supplier_id, 'supplier_quote_id' => $supplier_quote->id, 'supplier_cost' => $supplier_quote->total_cost));
            $this->supplier_quote_model->edit($supplier_quote->id, array('selected' => true));
        }

        trigger_event('final_suppliers_approved', 'servicequote', $servicequote_id, false, 'miniant');
        add_message('Final suppliers have been selected. Please prepare the client\'s service quotation now.');
        redirect(base_url().'miniant/servicequotes/servicequote/edit/'.$servicequote_id);
    }

    public function prepare_client_quote($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $this->load->model('miniant/abbreviation_model');

        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id prepare client's service quotation", __FUNCTION__ => null));
        $parts = $this->part_model->get_quoted_parts($servicequote_id);
        $custom_parts = $this->part_model->get_custom_client_quote_parts($servicequote_id);
        $servicequote = $this->servicequote_model->get($servicequote_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $servicequote->diagnostic_id), true);
        $dowds = $this->diagnostic_issue_model->get(array('diagnostic_id' => $servicequote->diagnostic_id, 'can_be_fixed_now' => false));
        $abbreviations = $this->abbreviation_model->get();

        foreach ($parts as $key => $part) {
            $parts[$key]->photos = $this->part_model->get_issue_photos($part->id);
            $parts[$key]->ready = (!is_null($part->client_cost) && !empty($part->part_name));
        }

        foreach ($custom_parts as $key => $part) {
            $custom_parts[$key]->ready = (!is_null($part->client_cost) && !empty($part->part_name));
        }

        $title = "Prepare client's service quotation (SQ) #$servicequote_id";
        $help = "Add any labour and other parts to invoice the client, and set the client costs for each part/labour. You can also edit the part names and the supplier cost here.";

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());
        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'prepare_client_quote',
                             'parts' => $parts,
                             'dowds' => $dowds,
                             'custom_parts' => $custom_parts,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'abbreviations' => $abbreviations,
                             'jstoload' => array('servicequotes/prepare_client_quote', 'bootstrap-editable')
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_client_quote() {
        require_capability('servicequotes:editsqs');
        $send = $this->input->post('send');
        $preview = $this->input->post('preview');
        $diagnostic_time = $this->input->post('diagnostic_time');
        $diagnostic_cost = $this->input->post('diagnostic_cost');

        $this->servicequote_model->edit($this->input->post('servicequote_id'), compact('diagnostic_time', 'diagnostic_cost'));

        if ($send) {
            return $this->send_client_quote();
        } else if ($preview) {
            return $this->preview_client_quote();
        }
    }

    public function preview_client_quote() {
        ob_end_clean();
        require_capability('servicequotes:editsqs');
        $pdf_settings = $this->get_pdf_settings("Service Quotation (preview)");

        $this->load->library('miniant_pdf', $pdf_settings);

        $servicequote_id = $this->input->post('servicequote_id');
        $parts = $this->part_model->get_quoted_parts($servicequote_id);
        $custom_parts = $this->part_model->get_custom_client_quote_parts($servicequote_id);
        $client_details = $this->servicequote_model->get_client_details($servicequote_id);
        $selected_photos = $this->input->post('selected_photos');

        trigger_event('client_quote_previewed', 'servicequote', $servicequote_id, false, 'miniant');

        $this->miniant_pdf->_config['page_orientation'] = 'portrait';

        $this->miniant_pdf->addpage();
        $this->miniant_pdf->setCellPadding(55);
        $this->miniant_pdf->_config['encoding'] = 'UTF-8';
        $this->miniant_pdf->SetSubject('Service quotation');

        $this->format_client_quote($client_details, $parts, $custom_parts, $servicequote_id, $selected_photos);
        $this->miniant_pdf->output("client_quote_preview.pdf", 'D');
    }

    public function send_client_quote() {
        ob_end_clean();
        require_capability('servicequotes:editsqs');
        $servicequote_id = $this->input->post('servicequote_id');
        $parts = $this->part_model->get_quoted_parts($servicequote_id);
        $custom_parts = $this->part_model->get_custom_client_quote_parts($servicequote_id);
        $client_details = $this->servicequote_model->get_client_details($servicequote_id);
        $selected_photos = $this->input->post('selected_photos');

        $pdf_settings = $this->get_pdf_settings("Service quotation");
        $this->load->library('miniant_pdf', $pdf_settings);
        $this->miniant_pdf->_config['page_orientation'] = 'portrait';
        $this->miniant_pdf->addpage();
        $this->miniant_pdf->setCellPadding(55);
        $this->miniant_pdf->_config['encoding'] = 'UTF-8';
        $this->miniant_pdf->SetSubject('Service quotation');

        $this->format_client_quote($client_details, $parts, $custom_parts, $servicequote_id, $selected_photos);
        $upload_path = $this->config->item('files_path').'servicequotes/'.$servicequote_id.'/';
        if (!file_exists($upload_path)) {
            @mkdir($upload_path, 0777, true);
        }

        $account_name_shortened = str_replace(' ', '', strtolower(substr(str_replace('/', '', $client_details->account_name), 0, 15)));
        $filename = $upload_path . "service_quotation_SQ$servicequote_id-{$account_name_shortened}_".unix_to_human(time(), '%d-%m-%Y').".pdf";
        $this->save_servicequote_document($filename, 'Service Quotation', $servicequote_id, $client_details->contact_id);

        $this->miniant_pdf->output($filename, 'F');

        $this->email->clear(true);
        $this->email->from($this->setting_model->get_value('Ops manager email address'), 'Temperature Solutions', $this->setting_model->get_value('Ops manager email address'));
        $this->email->subject('Temperature Solutions: Service Quotation');
        $this->email->message($this->load->view('servicequotes/emails/client_quote', compact('servicequote_id', 'supplier'), true));
        $this->email->to($client_details->email);
        $this->email->bcc(array($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Ops manager email address')));
        $this->email->attach($filename);
            $email_object = clone($this->email);

        if (ENVIRONMENT == 'demo') {
            $result = true;
        } else {
            $result = $this->email->send();
        }

        if ($result) {
            add_message('The service quotation was successfully sent out by email.', 'success');
            trigger_event('client_quote_sent', 'servicequote', $servicequote_id, false, 'miniant');
            $this->email_log_model->log_message($email_object, __FILE__ . ' at line ' . __LINE__, null, 'users', $this->session->userdata('user_id'), 'contacts', $client_details->contact_id);
            redirect(base_url().'miniant/servicequotes/servicequote/browse');
        } else {
            $error_message = $this->email->print_debugger();
            add_message('The service quotation could not be emailed due to an error. Please email the client directly.', 'warning');
            $this->email_log_model->log_message($email_object, __FILE__ . ' at line ' . __LINE__, $error_message, 'users', $this->session->userdata('user_id'), 'contacts', $client_details->contact_id);
            redirect(base_url().'miniant/servicequotes/servicequote/prepare_client_quote/'.$servicequote_id);
        }
    }

    public function format_client_quote($client_details, $parts, $custom_parts, $servicequote_id, $selected_photos) {
        require_capability('servicequotes:editsqs');
        $servicequote = $this->servicequote_model->get($servicequote_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $servicequote->diagnostic_id), true);
        $unit_type = $this->unit_model->get_type_string($this->unit_model->get($assignment->unit_id)->unit_type_id);

        $total_cost = 0;
        foreach ($parts as $part) {
            $total_cost += $part->client_cost;
        }
        foreach ($custom_parts as $part) {
            $total_cost += $part->client_cost;
        }

        $view_params = array(
            'parts' => $parts,
            'custom_parts' => $custom_parts,
            'client_details' => $client_details,
            'servicequote_id' => $servicequote_id,
            'order_id' => $servicequote->order_id,
            'valid_until' => time() + $this->setting_model->get_value('Quote validity period'),
            'validity_period' => $this->setting_model->get_value('Quote validity period'),
            'total_cost' => $total_cost,
            'servicequote' => $servicequote,
            'unit_type' => $unit_type
        );

        $this->load->view('servicequotes/pdf/client_quote_template', $view_params, true);

        foreach ($parts as $part) {
            if (!empty($selected_photos[$part->id])) {
                foreach ($selected_photos[$part->id] as $issue_photo) {
                    $this->miniant_pdf->addPage();
                    $this->miniant_pdf->print_heading($part->part_name);
                    $this->miniant_pdf->image($issue_photo, '', '', '180');
                }
            }
        }
    }

    public function record_client_response($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id record client response", __FUNCTION__ => null));
        $servicequote = $this->servicequote_model->get($servicequote_id);

        $title = "Record client response for SQ #$servicequote_id";
        $help = "Record the client's response to the service quotation. If accepted, preview then send the purchase order(s) to the supplier(s).";

        $attachment = $this->servicequote_attachment_model->get(array('servicequote_id' => $servicequote_id), true);

        if (!empty($attachment->filename_original)) {
            $attachment->url = base_url()."miniant/files/$attachment->directory/$attachment->servicequote_id/$attachment->hash";
        }

        form_element::$default_data = array('client_response' => $servicequote->client_response, 'client_response_notes' => $servicequote->client_response_notes);

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());
        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'record_client_response',
                             'attachment' => $attachment,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/record_client_response'),
                             'csstoload' => array()
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);

    }

    public function process_client_response() {
        require_capability('servicequotes:editsqs');

        $preview = $this->input->post('Preview_supplier_purchase_orders');
        $send = $this->input->post('Send_supplier_purchase_orders');

        if ($send) {
            return $this->send_purchase_orders();
        } else if ($preview) {
            return $this->preview_purchase_orders();
        }

        $this->form_validation->set_rules('client_response', 'Client response', 'required');
        if ($this->form_validation->run()) {
            $sq_data = $this->input->post();
            $servicequote_id = $sq_data['servicequote_id'];

            $this->servicequote_model->edit($sq_data['servicequote_id'], array(
                'client_response' => $sq_data['client_response'],
                'client_response_notes' => $sq_data['client_response_notes'],
                'client_response_date' => time())
            );

            if (!empty($_FILES['attachment']['name'])) {
                $result = $this->_process_attachment($sq_data['servicequote_id']);

                if (!$result) {
                    add_message("The attachment was not uploaded, please try again", 'warning');
                }
            }
            add_message("The client's response was successfully recorded");
            trigger_event('client_response_recorded', 'servicequote', $servicequote_id, false, 'miniant');

        } else {
            add_message("There was an error saving the client's response", 'warning');
        }

        redirect(base_url().'miniant/servicequotes/servicequote/prepare_purchase_orders/'.$servicequote_id);
    }

    public function prepare_purchase_orders($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');

        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id prepare purchase orders", __FUNCTION__ => null));
        $parts = $this->part_model->get_quoted_parts($servicequote_id);
        $custom_parts = $this->part_model->get_custom_client_quote_parts($servicequote_id);
        $servicequote = $this->servicequote_model->get($servicequote_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $servicequote->diagnostic_id), true);

        foreach ($parts as $key => $part) {
            $parts[$key]->photos = $this->part_model->get_issue_photos($part->id);
            $parts[$key]->ready = (!is_null($part->client_cost) && !empty($part->part_name));
        }

        $selected_suppliers = $this->servicequote_supplier_model->get(array('servicequote_id' => $servicequote_id));

        $suppliers = array();

        foreach ($selected_suppliers as $selected_supplier) {
            $supplier = $this->contact_model->get($selected_supplier->supplier_id);
            $supplier->name = $this->account_model->get($supplier->account_id)->name;
            $supplier->contact = $supplier->first_name . ' ' . $supplier->surname;
            $supplier->email = $supplier->email;

            $suppliers[] = $supplier;
        }

        $supplier_quotes_array = $this->supplier_quote_model->get_from_final_suppliers($servicequote_id);

        $supplier_quotes = array();

        foreach ($supplier_quotes_array as $supplier_quote) {
            if (empty($supplier_quotes[$supplier_quote->part_id])) {
                $supplier_quotes[$supplier_quote->part_id] = array();
            }
            $supplier_quotes[$supplier_quote->part_id][$supplier_quote->supplier_id] = $supplier_quote;
        }

        $title = "Prepare purchase orders for SQ #$servicequote_id";
        $help = "Select which photos to add to each purchase order.";

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());
        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'prepare_purchase_orders',
                             'parts' => $parts,
                             'supplier_quotes' => $supplier_quotes,
                             'suppliers' => $suppliers,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/prepare_purchase_orders', 'bootstrap-editable')
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);
    }

    public function process_purchase_orders() {
        require_capability('servicequotes:editsqs');
        $send = $this->input->post('send');
        $preview = $this->input->post('preview');

        if ($send) {
            return $this->send_purchase_orders();
        } else if ($preview) {
            return $this->preview_purchase_orders();
        }
    }

    public function preview_purchase_orders() {
        ob_end_clean();
        require_capability('servicequotes:editsqs');
        $pdf_settings = $this->get_pdf_settings("Purchase orders (preview)");

        $this->load->library('miniant_pdf', $pdf_settings);

        $servicequote_id = $this->input->post('servicequote_id');
        $supplier_quotes = $this->supplier_quote_model->get_from_final_suppliers($servicequote_id);
        $selected_photos = $this->input->post('selected_photos');

        trigger_event('purchase_orders_previewed', 'servicequote', $servicequote_id, false, 'miniant');

        $this->miniant_pdf->_config['page_orientation'] = 'portrait';

        $parts = $this->part_model->get_quoted_parts($servicequote_id);

        $suppliers = array();
        foreach ($parts as $part) {
            if (empty($suppliers[$part->supplier_contact_id])) {
                $suppliers[$part->supplier_contact_id] = array('supplier_data' => $this->get_supplier_data($this->contact_model->get($part->supplier_contact_id)), 'parts' => array());
            }
            $suppliers[$part->supplier_contact_id]['parts'][$part->id] = $part;
        }
        foreach ($suppliers as $supplier) {
            $total_cost = 0;

            foreach ($supplier['parts'] as $part) {
                $total_cost += $part->supplier_cost;
            }
            $this->miniant_pdf->addpage();
            $this->miniant_pdf->setCellPadding(55);
            $this->miniant_pdf->_config['encoding'] = 'UTF-8';
            $this->miniant_pdf->SetSubject('Purchase order: '.$supplier['supplier_data']->name);
            $this->miniant_pdf->writeDocumentTitle('Purchase order: '.$supplier['supplier_data']->name);
            $this->format_purchase_order($supplier, $servicequote_id, $total_cost);
        }

        foreach ($parts as $part) {
            if (!empty($selected_photos[$part->id])) {
                foreach ($selected_photos[$part->id] as $issue_photo) {
                    $this->miniant_pdf->addPage();
                    $this->miniant_pdf->print_heading($part->part_name);
                    $this->miniant_pdf->image($issue_photo, '', '', '180');
                }
            }
        }

        $this->miniant_pdf->output("purchase_orders_preview.pdf", 'D');
    }

    public function send_purchase_orders() {
        ob_end_clean();
        require_capability('servicequotes:editsqs');
        $servicequote_id = $this->input->post('servicequote_id');
        $parts = $this->part_model->get_quoted_parts($servicequote_id);
        $selected_photos = $this->input->post('selected_photos');

        $purchase_orders = array();
        foreach ($parts as $part) {
            if (empty($purchase_orders[$part->supplier_contact_id])) {
                $purchase_order = new stdClass();
                $purchase_order->servicequote_id = $servicequote_id;
                $purchase_order->supplier_contact_id = $part->supplier_contact_id;

                if ($existing_purchase_order = $this->purchase_order_model->get((array)$purchase_order, true)) {
                    $purchase_order = $existing_purchase_order;
                } else {
                    $purchase_order->id = $this->purchase_order_model->add($purchase_order);
                    $purchase_order->parts = array();
                    $purchase_order->total_cost = 0;
                }
                $purchase_order->supplier_contact = $this->contact_model->get($part->supplier_contact_id);
                $purchase_order->account_name = $this->account_model->get_name($purchase_order->supplier_contact->account_id);

                $purchase_orders[$part->supplier_contact_id] = $purchase_order;
            }

            $purchase_orders[$part->supplier_contact_id]->parts[$part->id] = $part;
            $purchase_orders[$part->supplier_contact_id]->total_cost += $part->total_cost;
            $this->supplier_quote_model->edit($part->supplier_quote_id, array('purchase_order_id' => $purchase_orders[$part->supplier_contact_id]->id));
        }

        $successful_sends = 0;
        require_once(APPPATH.'modules/miniant/libraries/Miniant_pdf.php');

        // Generate then save and email purchase order PDFs
        foreach ($purchase_orders as $purchase_order) {

            // I'm not calling the library in the codeigniter way because it doesn't reset itself properly after each instantiation
            $pdf_settings = $this->get_pdf_settings("Purchase order");
            $this->miniant_pdf = new miniant_pdf($pdf_settings);
            $this->miniant_pdf->_config['page_orientation'] = 'portrait';
            $this->miniant_pdf->addpage();
            $this->miniant_pdf->setCellPadding(55);
            $this->miniant_pdf->_config['encoding'] = 'UTF-8';
            $this->miniant_pdf->SetSubject('Purchase order');

            $this->format_purchase_order(null, $servicequote_id, $purchase_order->total_cost, $purchase_order);

            foreach ($purchase_order->parts as $part) {
                if (!empty($selected_photos[$part->id])) {
                    foreach ($selected_photos[$part->id] as $issue_photo) {
                        $this->miniant_pdf->addPage();
                        $this->miniant_pdf->print_heading($part->part_name);
                        $this->miniant_pdf->image($issue_photo, '', '', '180');
                    }
                }
            }

            $upload_path = $this->config->item('files_path').'servicequotes/'.$servicequote_id.'/';
            if (!file_exists($upload_path)) {
                @mkdir($upload_path, 0777, true);
            }
            $filename = $upload_path . "purchase_order_PO$purchase_order->id{$purchase_order->account_name}_".unix_to_human(time(), '%d-%m-%Y').".pdf";
            $this->save_servicequote_document($filename, 'Purchase Order', $servicequote_id, $purchase_order->supplier_contact->id);

            $this->miniant_pdf->output($filename, 'F');

            $this->email->clear(true);
            $this->email->from($this->setting_model->get_value('Ops manager email address'), 'Temperature Solutions', $this->setting_model->get_value('Ops manager email address'));
            $this->email->subject('Temperature Solutions: Purchase order');
            $this->email->message($this->load->view('servicequotes/emails/purchase_order', compact('servicequote_id', 'purchase_order'), true));
            $this->email->to($purchase_order->supplier_contact->email);
            $this->email->bcc(array($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Ops manager email address')));
            $this->email->attach($filename);
            $email_object = clone($this->email);

            if (ENVIRONMENT == 'demo') {
                $result = true;
            } else {
                $result = $this->email->send();
            }

            if ($result) {
                $successful_sends++;
                $error_message = null;
                $this->purchase_order_model->edit($purchase_order->id, array('sent_date' => time(), 'total_cost' => $purchase_order->total_cost));
            } else {
                $error_message = $this->email->print_debugger();
            }

            $this->email_log_model->log_message($email_object, __FILE__ . ' at line ' . __LINE__, null, 'users', $this->session->userdata('user_id'), 'contacts', $purchase_order->supplier_contact->id);
        }

        if ($successful_sends < count($purchase_orders)) {
            add_message((count($purchase_orders) - $successful_sends) . ' purchase order(s) could not be emailed due to an error. Please email the supplier directly.', 'warning');
            redirect(base_url().'miniant/servicequotes/servicequote/record_client_response/'.$servicequote_id);
        } else {
            add_message($successful_sends . ' purchase orders were successfully sent out by email.', 'success');
            trigger_event('purchase_orders_sent', 'servicequote', $servicequote_id, false, 'miniant');
            redirect(base_url().'miniant/servicequotes/servicequote/browse');
        }
    }

    // Purchase order is only created when the "Send purchase order" button is clicked
    public function format_purchase_order($supplier=null, $servicequote_id, $total_cost, $purchase_order=null) {
        require_capability('servicequotes:editsqs');
        $servicequote = $this->servicequote_model->get($servicequote_id);

        $view_params = array(
            'servicequote_id' => $servicequote_id,
            'purchase_order' => $purchase_order,
            'total_cost' => $total_cost,
            'order_id' => $servicequote->order_id
        );

        if (is_null($supplier)) {
            $view_params += array(
                'parts' => $purchase_order->parts,
                'supplier' => $this->get_supplier_data($this->contact_model->get($purchase_order->supplier_contact_id))
            );
        } else {
            $view_params += array(
                'parts' => $supplier['parts'],
                'supplier' => $supplier['supplier_data']
            );
        }

        $this->miniant_pdf->print_heading('Purchase order details');

        $this->miniant_pdf->SetFont($this->miniant_pdf->_config['page_font'], '', $this->miniant_pdf->_config['page_font_size']);
        $output = $this->load->view('servicequotes/pdf/purchase_order_intro', $view_params, true);
        $this->miniant_pdf->writeHTML($output, false, false, false, false, '');

        $this->miniant_pdf->print_heading('Parts required');

        $this->miniant_pdf->SetFont($this->miniant_pdf->_config['page_font'], '', $this->miniant_pdf->_config['page_font_size']);
        $output = $this->load->view('servicequotes/pdf/purchase_order', $view_params, true);
        $this->miniant_pdf->writeHTML($output, false, false, false, false, '');
    }

    public function record_received_parts($servicequote_id, $review_only=false) {
        require_capability('servicequotes:editsqs');
        $this->config->set_item('replacer', array('miniant' => null, 'servicequote' => array('/miniant/servicequotes/servicequote/browse|Service Quotes'), $servicequote_id => "SQ#$servicequote_id record received parts", __FUNCTION__ => null));
        $purchase_orders = $this->purchase_order_model->get(compact('servicequote_id'));

        $parts = array();

        foreach ($purchase_orders as $purchase_order) {
            $supplier_quotes = $this->supplier_quote_model->get(array('supplier_id' => $purchase_order->supplier_contact_id, 'servicequote_id' => $servicequote_id));
            $supplier_contact = $this->contact_model->get($purchase_order->supplier_contact_id);
            $supplier_account = $this->account_model->get($supplier_contact->account_id);

            foreach ($supplier_quotes as $supplier_quote) {
                $part = $this->part_model->get($supplier_quote->part_id);
                if ($part->supplier_quote_id != $supplier_quote->id) {
                    continue;
                }

                $part->supplier_name = $supplier_account->name;
                $part->purchase_order_id = $purchase_order->id;
                $part->part_received_date = $supplier_quote->part_received_date;
                $part->part_received_note = $supplier_quote->part_received_note;
                $parts[] = $part;
            }
        }

        $title = "Record received parts for SQ #$servicequote_id";
        $help = "When a part arrives at the workshop from a supplier, record it here.";

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());
        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'servicequotes/template',
                             'content_stage' => 'record_received_parts',
                             'parts' => $parts,
                             'review_only' => $review_only,
                             'module' => 'miniant',
                             'jstoload' => array('servicequotes/record_received_parts', 'bootstrap-editable'),
                             'csstoload' => array()
                             );

        foreach ($this->get_common_variables($servicequote_id, $review_only) as $var => $val) {
            $pageDetails[$var] = $val;
        }

        $this->load->view('template/default', $pageDetails);

    }

    public function get_common_variables($servicequote_id, $review_only=false) {
        $servicequote = $this->servicequote_model->get($servicequote_id);
        $order = $this->order_model->get_values($servicequote->order_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $servicequote->diagnostic_id), true);
        $assignment = $this->assignment_model->get_values($assignment->id);
        $technician = $this->user_model->get($assignment->technician_id);
        $technician_name = 'No longer on record';
        if (!empty($technician->id)) {
            $technician_name = $this->user_model->get_name($technician->id);
        }
        $unit = $this->unit_model->get_values($assignment->unit_id);
        $unit['photos'] = get_photos('assignment', null, $unit['id']);
        $diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $servicequote->diagnostic_id, 'can_be_fixed_now' => 0));
        $issues = array();

        foreach ($diagnostic_issues as $di) {
            $issues[] = "$di->issue_type_name $di->part_type_name";
        }

        $statuses = $this->servicequote_model->get_statuses($servicequote_id);
        $dropdowns = $this->get_dropdowns($servicequote_id);
        $feature_type = 'Custom Feature';
        $stages = array();
        $raw_stages = array_reverse($this->stages);
        array_pop($raw_stages);

        foreach ($raw_stages as $stage) {
            $page_name = str_replace('_', ' ', ucfirst($stage['url']));
            if ($this->uri->segment(4) == $stage['url']) {
                $stages[] = $page_name;
            } else {
                $stages[] = anchor(base_url()."miniant/servicequotes/servicequote/{$stage['url']}/$servicequote_id/$review_only", $page_name);
            }
        }

        return compact('servicequote','order','assignment', 'technician', 'technician_name', 'issues', 'statuses', 'unit', 'dropdowns', 'servicequote_id', 'feature_type', 'stages');
    }

    public function get_dropdowns($servicequote_id) {
        $servicequote = $this->servicequote_model->get($servicequote_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $servicequote->diagnostic_id), true);
        $unit = $this->unit_model->get($assignment->unit_id);
        $dropdowns = array(
            'part_types' => $this->part_type_model->get_dropdown('name', true, false, false, null, null, null, array('unit_type_id' => $unit->unit_type_id))
        );
        return $dropdowns;
    }

    public function get_supplier_data($supplier_contact) {
        $supplier = new stdClass();
        $supplier->id = $supplier_contact->id;
        $account = $this->account_model->get($supplier_contact->account_id);
        $supplier->name = $account->name;
        $supplier->abn = @$account->abn;
        $supplier->address = $this->address_model->get_formatted_address($this->account_model->get_billing_address($account->id));
        $supplier->contact = $supplier_contact->first_name . ' ' . $supplier_contact->surname;
        $supplier->email = $supplier_contact->email;
        return $supplier;
    }

    public function collate_suppliers_from_supplier_quotes($supplier_quotes) {
        $suppliers = array();

        foreach ($supplier_quotes as $key => $supplier_quote) {

            if (empty($suppliers[$supplier_quote->supplier_id])) {
                $suppliers[$supplier_quote->supplier_id] = array('supplier_data' => $this->get_supplier_data($this->contact_model->get($supplier_quote->supplier_id)), 'parts' => array());
            }

            $part = $this->part_model->get($supplier_quote->part_id);
            $part->unit_cost = $supplier_quote->unit_cost;
            $part->total_cost = $supplier_quote->total_cost;
            $part->availability = $supplier_quote->availability;
            $part->request_sent_date = $supplier_quote->request_sent_date;
            $part->quote_received_date = $supplier_quote->quote_received_date;
            $part->provisional_supplier_quote_id = $supplier_quote->id;
            $part->issue_photos = $this->part_model->get_issue_photos($part->id);

            $suppliers[$supplier_quote->supplier_id]['parts'][$part->id] = $part;
        }

        return $suppliers;
    }

    public function delete_part($part_id) {
        require_capability('servicequotes:editsqs');
        $part = $this->part_model->get($part_id);
        $servicequote_id = $part->servicequote_id;
        $this->part_model->delete($part_id);
        add_message('The part/labour was successfully deleted');
        redirect(base_url().'miniant/servicequotes/servicequote/prepare_client_quote/'.$servicequote_id);
    }

    private function _process_attachment($servicequote_id) {
        $config = array();
        $config['upload_path'] = $this->config->item('files_path').'servicequotes/'.$servicequote_id;
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
            redirect(base_url().'miniant/servicequotes/servicequote/record_client_response/'.$servicequote_id);
        }

        $attachment_data = $this->upload->data();

        $attachment = array('filename_original' => $attachment_data['orig_name'],
                      'hash' => $attachment_data['file_name'],
                      'servicequote_id' => $servicequote_id,
                      'directory' => 'servicequotes',
                      'file_type' => $attachment_data['file_type'],
                      'file_extension' => $attachment_data['file_ext'],
                      'file_size' => $attachment_data['file_size']);

        if ($old_attachment = $this->servicequote_attachment_model->get(array('servicequote_id' => $servicequote_id), true)) {
            unlink($config['upload_path'].'/'.$old_attachment->hash);
            $this->servicequote_attachment_model->edit($old_attachment->id, $attachment);
            return $old_attachment->id;
        }

        if (!$attachment_id = $this->servicequote_attachment_model->add($attachment)) {
            add_message('The attachment ' . $new_attachment['filename_original'] . ' was uploaded, but the attachment info could not be recorded in the database...', 'warning');
            return false;
        } else {
            return $attachment_id;
        }
    }

    private function get_allowed_file_types() {
        return 'doc|pdf|xls|xlsx|png|jpg|jpeg|gif';
    }

    public function delete_attachment($servicequote_id) {
        require_capability('servicequotes:editsqs');
        $this->servicequote_attachment_model->delete(array('servicequote_id' => $servicequote_id));
        add_message('The attachment was successfully deleted');
        return $this->edit($servicequote_id);
    }

    private function save_servicequote_document($filename, $type, $servicequote_id, $recipient_id) {
        $params = array(
            'type_id' => $this->servicequote_document_model->get_type_id($type),
            'servicequote_id' => $servicequote_id,
            'recipient_contact_id' => $recipient_id
        );

        $this->servicequote_document_model->delete($params);

        $params['filepath'] = $filename;

        $this->servicequote_document_model->add($params);
    }

    public function get_statuses_from_bitmask($bitmask) {
        $document_types = array();

        if ($bitmask & ARCHIVED_STATUS) {
            $document_types[] = $this->status_model->get_id_from_name('ARCHIVED');
        }
        if ($bitmask & CANCELLED_STATUS) {
            $document_types[] = $this->status_model->get_id_from_name('CANCELLED');
        }

        return $document_types;
    }

    public function delete($servicequote_id, $hidden_statuses=null) {
        return parent::delete($servicequote_id, 'servicequote');
    }

    public function set_bulk_availability() {
        $servicequote_id = $this->input->post('servicequote_id');
        $supplier_id = $this->input->post('supplier_id');
        $availability = $this->input->post('availability');

        $this->supplier_quote_model->update_availability($servicequote_id, $supplier_id, $availability);
        add_message('All parts for this supplier were set to '.$availability.'.');
        redirect(base_url().'miniant/servicequotes/servicequote/record_supplier_quotes/'.$servicequote_id);
    }

    public function get_pdf_settings($title) {

        $post_data = $this->input->post();

        $pdf_settings = array('header_title' => $title, 'header_font_size' => 14);

        if (!is_null($post_data['margin_left'])) { // default 15
            $pdf_settings['margin_left'] = $post_data['margin_left'];
        }
        if (!is_null($post_data['margin_right'])) { // default 15
            $pdf_settings['margin_right'] = $post_data['margin_right'];
        }
        if (!is_null($post_data['margin_top'])) { // default 66
            $pdf_settings['margin_top'] = $post_data['margin_top'];
        }
        if (!is_null($post_data['header_margin'])) { // default 5
            $pdf_settings['header_margin'] = $post_data['header_margin'];
        }
        if (!is_null($post_data['footer_margin'])) { // default 10
            $pdf_settings['footer_margin'] = $post_data['footer_margin'];
        }
        return $pdf_settings;
    }
}
