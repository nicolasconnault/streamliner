<?php
class Refrigerant_types extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('miniant' => array('/home|Administration'), 'refrigerant_types' => array('/miniant/refrigerant_types|Refrigerant Types')));
        $this->config->set_item('exclude', array('index', 'browse'));

        // Being a global controller, refrigerant_types doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('site:viewrefrigerant_types');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'refrigerant_types',
            'row_actions' => array('view', 'edit', 'delete'),
            'row_action_capabilities' => array('view' => 'site:viewrefrigerant_types', 'edit' => 'site:editrefrigerant_types', 'delete' => 'site:deleterefrigerant_types'),
            'feature_type' => 'Custom Feature',
            'available_export_types' => array('xml', 'csv'),
            'show_add_button' => has_capability('site:writerefrigerant_types'),
            'module' => 'miniant',
            'model' => $this->refrigerant_type_model
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_refrigerant_types',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'refrigerant_type_id'));
        $this->datagrid->add_column(array(
            'table' => 'miniant_refrigerant_types',
            'field' => 'name',
            'label' => 'Name'));

        $this->datagrid->setup_filters();
        $this->datagrid->render();
    }


    public function add() {
        require_capability('site:writerefrigerant_types');
        return $this->edit();
    }

    public function edit($refrigerant_type_id=null) {

        $refrigerant_type_data = array();

        if (!empty($refrigerant_type_id)) {
            require_capability('site:viewrefrigerant_types');
            $refrigerant_type_data = (array) $this->refrigerant_type_model->get($refrigerant_type_id);
            form_element::$default_data = $refrigerant_type_data;
            // Set up title bar
            $title = "Edit {$refrigerant_type_data['name']} refrigerant_type";
            $help = "Use this form to edit the {$refrigerant_type_data['name']} refrigerant_type.";
        } else { // adding a new refrigerant_type
            require_capability('site:writerefrigerant_types');

            $title = "Create a new refrigerant_type";
            $help = 'Use this form to create a new refrigerant_type.';
        }

        $this->config->set_item('replacer', array('miniant' => array('/home|Administration'), 'refrigerant_types' => array('/miniant/refrigerant_types/index|Refrigerant types'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'refrigerant_type/edit',
                             'refrigerant_type_id' => $refrigerant_type_id,
                             'dropdowns' => array('street_types' => $this->street_type_model->get_dropdown('name')),
                             'refrigerant_type_data' => $refrigerant_type_data,
                             'feature_type' => 'Custom feature',
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('site:editrefrigerant_types');

        $required_fields = array(
            'name' => 'Refrigerant type name',
        );

        if ($refrigerant_type_id = (int) $this->input->post('refrigerant_type_id')) {
            $refrigerant_type = $this->refrigerant_type_model->get($refrigerant_type_id);
            $redirect_url = base_url().'miniant/refrigerant_types/edit/'.$refrigerant_type_id;
        } else {
            $redirect_url = base_url().'miniant/refrigerant_types/add';
            $refrigerant_type_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($refrigerant_type_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Refrigerant type $refrigerant_type_id has been successfully $action_word!";
            } else {
                send_json_message($this->form_validation->error_string(' ', "\n"), 'danger');
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($refrigerant_type_id);
        }

        $refrigerant_type_data = array(
            'name' => $this->input->post('name')
        );

        if (empty($refrigerant_type_id)) {
            if (!($refrigerant_type_id = $this->refrigerant_type_model->add($refrigerant_type_data))) {
                add_message('Could not create this refrigerant_type!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->refrigerant_type_model->edit($refrigerant_type_id, $refrigerant_type_data)) {
                add_message('Could not update this refrigerant_type!', 'error');
                redirect($redirect_url);
            }
        }

        add_message("Refrigerant type $refrigerant_type_id has been successfully $action_word!", 'success');
        redirect(base_url().'miniant/refrigerant_types');
    }
}
