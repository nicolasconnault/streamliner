<?php
class Brands extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('miniant' => array('/home|Administration'), 'brands' => array('/brands|Brands')));
        $this->config->set_item('exclude', array('index', 'browse'));

        // Being a global controller, brands doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
        $this->load->model('miniant/brand_model');
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('site:viewbrands');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'brands',
            'module' => 'miniant',
            'row_actions' => array('edit'),
            'feature_type' => 'Custom Feature',
            'available_export_types' => array('pdf', 'xml', 'csv'),
            'model' => $this->brand_model
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_brands',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'brand_id'));
        $this->datagrid->add_column(array(
            'table' => 'types',
            'field' => 'name',
            'label' => 'Unit type',
            'field_alias' => 'unit_type_id'));
        $this->datagrid->add_column(array(
            'table' => 'miniant_brands',
            'field' => 'name',
            'label' => 'Name'));
        $this->datagrid->add_column(array(
            'table' => 'miniant_brands',
            'field' => 'description',
            'label' => 'Description'));

        $this->datagrid->set_joins(array(
            array('table' => 'types', 'on' => 'types.id = miniant_brands.unit_type_id', 'type' => 'LEFT OUTER'),
        ));

        $this->datagrid->render();
    }

    public function get_data($brand_id) {
        echo json_encode($this->brand_model->get($brand_id));
    }

    public function get_brands_dropdown() {
        $unit_type_id = $this->input->post('unit_type_id');

        if (!empty($unit_type_id)) {
            $brands = $this->brand_model->get_dropdown_by_unit_type_id($unit_type_id);
        } else {
            $brands = $this->brand_model->get_dropdown('name');
        }

        echo json_encode($brands);
    }

    public function add() {
        return $this->edit();
    }

    public function edit($brand_id=null) {

        require_capability('site:writebrands');

        if (!empty($brand_id)) {
            require_capability('site:editbrands');
            $brand_data = (array) $this->brand_model->get($brand_id);

            form_element::$default_data = $brand_data;

            // Set up title bar
            $title = "Edit {$brand_data['name']} brand";
            $help = "Use this form to edit the {$brand_data['name']} brand.";
        } else { // adding a new brand
            $title = "Create a new brand";
            $help = 'Use this form to create a new brand.';
        }

        $this->config->set_item('replacer', array('miniant' => array('/home|Administration'), 'brands' => array('/miniant/brands/index|Brands'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'brand/edit',
                             'brand_id' => $brand_id,
                             'feature_type' => 'Custom feature',
                             'unit_types' => $this->unit_model->get_types_dropdown('--Select an equipment type--'),
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('site:editbrands');

        $required_fields = array('name' => 'Name', 'description' => 'Description', 'unit_type_id' => 'Equipment type');

        if ($brand_id = (int) $this->input->post('brand_id')) {
            $brand = $this->brand_model->get($brand_id);
            $redirect_url = base_url().'miniant/brands/edit/'.$brand_id;
        } else {
            $redirect_url = base_url().'miniant/brands/add';
            $brand_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($brand_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "brand $brand_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($brand_id);
        }

        $brand_data = array( 'name' => $this->input->post('name'), 'description' => $this->input->post('description'), 'unit_type_id' => $this->input->post('unit_type_id'));

        if (empty($brand_id)) {
            if (!($brand_id = $this->brand_model->add($brand_data))) {
                add_message('Could not create this brand!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->brand_model->edit($brand_id, $brand_data)) {
                add_message('Could not update this brand!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("brand $brand_id has been successfully $action_word!", 'success');
        redirect(base_url().'miniant/brands');
    }

    public function delete($id, $model_name=null) {

        $result = $this->brand_model->delete($id);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "Brand $id was successfully deleted";
                $json->id = $id;
                $json->type = 'success';
            } else {
                $json->message = "Brand $id could not be deleted";
                $json->id = $id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }
}
