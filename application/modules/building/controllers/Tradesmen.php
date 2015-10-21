<?php
class Tradesmen extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('building' => null, 'tradesmen' => array('/tradesmen|Tradies')));
        $this->config->set_item('exclude', array('index', 'browse'));

        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('building:viewtradesmen');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'tradesmen',
            'module' => 'building',
            'row_actions' => array('edit', 'delete'),
            'row_action_capabilities' => array('edit' => 'building:edittradesmen', 'delete' => 'building:deletetradesmen'),
            'feature_type' => 'Custom Feature',
            'available_export_types' => array('pdf', 'csv'),
            'model' => $this->tradesman_model,
            'custom_title' => 'List of Tradies',
            'custom_columns_callback' => $this->tradesman_model->get_custom_columns_callback(),
            'title_icon' => 'briefcase'
        ));

        $this->datagrid->add_column(array(
            'table' => 'building_tradesmen',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'tradesman_id'));
        $this->datagrid->add_column(array(
            'table' => 'building_tradesmen',
            'field' => 'type_id',
            'field_alias' => 'type',
            'label' => 'Type',
            'sortable' => true));
        $this->datagrid->add_column(array(
            'table' => 'building_tradesmen',
            'field' => 'name',
            'field_alias' => 'name',
            'label' => 'Name',
            'sortable' => true));
        $this->datagrid->add_column(array(
            'table' => 'building_tradesmen',
            'field' => 'mobile',
            'label' => 'Mobile',
            'field_alias' => 'mobile',
            'sortable' => false
        ));
        $this->datagrid->add_column(array(
            'table' => 'building_tradesmen',
            'field' => 'email',
            'label' => 'Email',
            'field_alias' => 'email',
            'sortable' => false
        ));

        $this->datagrid->render();
    }

    public function add() {
        return $this->edit();
    }

    public function edit($tradesman_id=null) {

        require_capability('building:writetradesmen');

        if (!empty($tradesman_id)) {
            require_capability('building:edittradesmen');
            $tradesman_data = (array) $this->tradesman_model->get($tradesman_id);

            form_element::$default_data = $tradesman_data;

            // Set up title bar
            $title = "Edit Tradie";
            $help = "Use this form to edit the tradie.";
        } else { // adding a new tradesman
            $title = "Create a new tradie";
            $help = 'Use this form to create a new tradie.';
        }

        $this->config->set_item('replacer', array('building' => null, 'tradesmen' => array('/building/tradesmen/index|Tradies'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'tradesmen/edit',
                             'tradesman_id' => $tradesman_id,
                             'types' => $this->tradesman_model->get_types_dropdown(),
                             'feature_type' => 'Custom feature',
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('building:edittradesmen');

        $required_fields = array('name' => 'Name', 'type_id' => 'Type');

        if ($tradesman_id = (int) $this->input->post('tradesman_id')) {
            $tradesman = $this->tradesman_model->get($tradesman_id);
            $redirect_url = base_url().'building/tradesmen/edit/'.$tradesman_id;
        } else {
            $redirect_url = base_url().'building/tradesmen/add';
            $tradesman_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($tradesman_id) ? 'updated' : 'created';

        if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($tradesman_id);
        }

        $tradesman_data = array(
            'name' => $this->input->post('name'),
            'type_id' => $this->input->post('type_id'),
            'mobile' => $this->input->post('mobile'),
            'email' => $this->input->post('email'),
        );

        if (empty($tradesman_id)) {
            if ($this->tradesman_model->is_unique($tradesman_data)) {
                if (!($tradesman_id = $this->tradesman_model->add($tradesman_data))) {
                    add_message('Could not create this Tradie!', 'danger');
                    redirect($redirect_url);
                }
            } else {
                add_message('A Tradie with this name and type already exists!', 'danger');
                return $this->edit();
            }
        } else {
            if ($this->tradesman_model->is_unique($tradesman_data, $tradesman_id)) {
                if (!$this->tradesman_model->edit($tradesman_id, $tradesman_data)) {
                    add_message('Could not update this Tradie!', 'danger');
                    redirect($redirect_url);
                }
            } else {
                add_message('A Tradie with this name and type already exists!', 'danger');
                return $this->edit();
            }
        }

        add_message("Tradie $tradesman_id has been successfully $action_word!", 'success');
        redirect(base_url().'building/tradesmen');
    }

    public function delete($id, $model_name=null) {

        $result = $this->tradesman_model->delete($id);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "Tradesman $id was successfully deleted";
                $json->id = $id;
                $json->type = 'success';
            } else {
                $json->message = "Tradie $id could not be deleted";
                $json->id = $id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }

    public function get_bookings() {
        $tradesman_id = $this->input->post('id');
        $bookings = $this->booking_model->get(array('tradesman_id' => $tradesman_id));
        echo json_encode($bookings);
    }

    public function get_tradesmen() {
        $tradesman_type_id = $this->input->post('tradesman_type_id');
        $this->db->where(array('type_id' => $tradesman_type_id));
        $tradesmen = $this->tradesman_model->get_dropdown('name');
        echo json_encode($tradesmen);
    }
}
