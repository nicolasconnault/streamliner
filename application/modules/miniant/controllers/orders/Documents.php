<?php

class Documents extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('order' => array('browse|Job Documents')));
        $this->config->set_item('exclude', array('browse', 'order/index'));
        $this->config->set_item('exclude_segment', array(1,3));
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html', $order_id) {
        require_capability('orders:viewdocuments');
        $this->config->set_item('replacer', array('miniant' => null, 'orders' => array('/miniant/orders/order/index|Jobs'), 'documents' => 'Tenancy invoices'));
        $this->config->set_item('exclude', array('index', 'html'));
        $this->config->set_item('exclude_segment', array(6,5));

        $order_id = (int) $order_id;

        $sql_conditions[] = "miniant_invoices.order_id = '$order_id'";

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'row_actions' => array('edit', 'pdf'),
            'row_action_capabilities' => array('edit' => 'orders:viewdocuments', 'pdf' => 'orders:viewdocuments'),
            'row_action_conditions' => array(
                'pdf' =>  function($row) {
                    $ci = get_instance();
                    return $ci->invoice_tenancy_model->has_statuses($row[0], array('REVIEWED', 'INVOICED'));
                }
            ),
            'available_export_types' => array('pdf', 'csv'),
            'custom_columns_callback' => $this->invoice_tenancy_model->get_custom_columns_callback(),
            'show_add_button' => false,
            'feature_type' => 'Custom Feature',
            'custom_title' => 'Tenancy invoices',
            'module' => 'miniant',
            'url_param' => $order_id,
            'sql_conditions' => $sql_conditions,
            'model' => $this->invoice_tenancy_model,
            'uri_segment_1' => 'orders',
            'uri_segment_2' => 'documents',
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_invoice_tenancies',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'invoice_tenancy_id',
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_invoice_tenancies',
            'field' => 'tenancy_id',
            'label' => 'Tenancy',
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_invoice_tenancies',
            'field' => 'system_time',
            'label' => 'System time'
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_invoice_tenancies',
            'field' => 'technician_time',
            'label' => 'Corrected time'
            ));
        $this->datagrid->add_column(array(
            'label' => 'Statuses',
            'field_alias' => 'statuses',
            'sortable' => false
        ));


        $this->datagrid->setup_filters();

        $this->datagrid->set_joins(array(
            array('table' => 'miniant_invoices', 'on' => 'miniant_invoices.id = miniant_invoice_tenancies.invoice_id', 'type' => 'LEFT OUTER'),
            array('table' => 'document_statuses', 'on' => 'document_statuses.document_id = miniant_invoice_tenancies.id AND document_statuses.document_type = "invoice_tenancy"'),
        ));

        $this->datagrid->render();
    }

    public function edit($invoice_tenancy_id, $order_id) {

        require_capability('orders:viewdocuments');
        $this->load->model('miniant/abbreviation_model');
        $this->load->model('miniant/invoice_tenancy_abbreviation_model');

        $invoice_tenancy = $this->invoice_tenancy_model->get($invoice_tenancy_id);
        $invoice = $this->invoice_model->get(array('order_id' => $order_id), true);
        $tenancy = $this->tenancy_model->get($invoice_tenancy->tenancy_id);
        $abbreviations = $this->abbreviation_model->get();
        $invoice_tenancy_abbreviations = $this->invoice_tenancy_abbreviation_model->get(array('invoice_tenancy_id' => $invoice_tenancy_id));
        $invoice_tenancy->system_time = $this->order_model->get_total_time($invoice->order_id);

        form_element::$default_data['technician_time_hours'] = floor($invoice_tenancy->technician_time / 60 / 60);
        form_element::$default_data['technician_time_minutes'] = ($invoice_tenancy->technician_time - (form_element::$default_data['technician_time_hours'] * 60 * 60)) / 60;

        foreach ($abbreviations as $key => $abbreviation) {
            $abbreviations[$key]->selected = false;
            foreach ($invoice_tenancy_abbreviations as $invoice_tenancy_abbreviation) {
                if ($invoice_tenancy_abbreviation->abbreviation_id == $abbreviation->id) {
                    $abbreviations[$key]->selected = true;
                }
            }
        }

        $title = "Edit invoice for $tenancy->name, job J$order_id";

        $help = "Use this form to edit the time spent by technicians on this job.";

        $this->config->set_item('replacer', array(
            'miniant' => null,
            'orders' => array('/miniant/orders/order/index|Jobs'),
            'documents' => array('/miniant/orders/documents/index/html/'.$order_id.'/0|Tenancy invoices',
            $order_id => 'J'.$order_id,
            $invoice_tenancy_id => 'Edit invoice'
        )));
        $this->config->set_item('exclude', array('edit'));
        $this->config->set_item('exclude_segment', array(7));

        $title_options = array('title' => $title, 'help' => $help, 'expand' => 'page', 'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/documents/edit',
                             'tenancy' => $tenancy,
                             'invoice' => $invoice,
                             'order_id' => $order_id,
                             'abbreviations' => $abbreviations,
                             'invoice_tenancy_abbreviations' => $invoice_tenancy_abbreviations,
                             'invoice_tenancy' => $invoice_tenancy,
                             'jstoload' => array('bootstrap-slider'),
                             'feature_type' => 'Custom Feature',
                             'csstoload' => array('slider')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        $technician_time_hours = $this->input->post('technician_time_hours') * 60 * 60;
        $technician_time_minutes = $this->input->post('technician_time_minutes') * 60;
        $technician_time = $technician_time_hours + $technician_time_minutes;

        $invoice_tenancy_id = $this->input->post('invoice_tenancy_id');
        $order_id = $this->input->post('order_id');
        $abbreviations = $this->input->post('abbreviations');

        $this->invoice_tenancy_model->update_abbreviations($invoice_tenancy_id, $abbreviations);
        $this->invoice_tenancy_model->edit($invoice_tenancy_id, compact('technician_time'));
        trigger_event('reviewed', 'invoice_tenancies', $invoice_tenancy_id, false, 'miniant');
        add_message('Invoice updated');
        redirect(base_url().'miniant/orders/documents/index/html/'.$order_id);
    }

    public function update_statuses($invoice_tenancy_id) {
        $status_ids = $this->input->post('values');
        $this->invoice_tenancy_model->set_statuses($invoice_tenancy_id, $status_ids);
        send_json_message('Statuses were updated');
    }
}
