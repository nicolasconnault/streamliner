<?php
class Job_sites extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('exclude', array('index', 'browse'));
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('reports:viewjob_sites');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'reports',
            'uri_segment_2' => 'job_sites',
            'row_actions' => array(
                // 'jobs',
                'units'
            ),
            'row_action_capabilities' => array(
                // 'jobs' => 'reports:viewjobs',
                'units' => 'reports:viewunits'
            ),
            'available_export_types' => array('csv'),
            'sql_conditions' => array('type_id = '.$this->address_model->get_type_id('Site')),
            'custom_columns_callback' => $this->address_model->get_custom_columns_callback(),
            'show_add_button' => false,
            'feature_type' => 'Custom Feature',
            'model' => $this->address_model,
            'module' => 'miniant',
            'custom_title' => 'Job Sites'
        ));

        $this->datagrid->add_column(array(
            'table' => 'addresses',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'address_id'));
        $this->datagrid->add_column(array(
            'table' => 'addresses',
            'field' => 'street',
            'label' => 'Address'));
        $this->datagrid->add_column(array(
            'table' => 'addresses',
            'field' => 'account_id',
            'label' => 'Account'));

        $this->datagrid->setup_filters();
        $this->datagrid->render();

    }

}
