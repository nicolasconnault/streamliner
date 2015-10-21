<?php

class Documents extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('miniant' => null, 'servicequotes' => array('browse|Service quotes')));
        $this->config->set_item('exclude', array('browse', 'servicequote/index'));
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html', $servicequote_id) {
        require_capability('servicequotes:viewsqs');

        $this->config->set_item('exclude', array('index'));

        $this->config->set_item('replacer', array('miniant' => null, 'servicequotes' => array('/miniant/servicequotes/servicequote/index|Service Quotes'), 'documents' => 'SQ Documents'));
        $this->config->set_item('exclude', array('index', 'html'));
        $this->config->set_item('exclude_segment', array(6,5));

        $servicequote_id = (int) $servicequote_id;

        $sql_conditions[] = "miniant_servicequote_documents.servicequote_id = '$servicequote_id'";

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'row_actions' => array('pdf'),
            'row_action_capabilities' => array('pdf' => 'servicequotes:viewsqs'),
            'available_export_types' => array(),
            'custom_columns_callback' => $this->servicequote_document_model->get_custom_columns_callback(),
            'show_add_button' => false,
            'feature_type' => 'Custom Feature',
            'custom_title' => 'Service quotation documents',
            'url_param' => $servicequote_id,
            'sql_conditions' => $sql_conditions,
            'module' => 'miniant',
            'model' => $this->servicequote_document_model,
            'uri_segment_1' => 'servicequotes',
            'uri_segment_2' => 'documents',

        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_servicequote_documents',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'servicequote_document_id'
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_servicequote_documents',
            'field' => 'type_id',
            'label' => 'Type',
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_servicequote_documents',
            'field' => 'recipient_contact_id',
            'field_alias' => 'recipient_contact_id',
            'label' => 'Recipient'
            ));
        $this->datagrid->add_column(array(
            'table' => 'miniant_servicequote_documents',
            'field' => 'creation_date',
            'field_alias' => 'creation_datetime',
            'label' => 'Sent date'
            ));

        $this->datagrid->setup_filters();

        $this->datagrid->render();
    }

}
