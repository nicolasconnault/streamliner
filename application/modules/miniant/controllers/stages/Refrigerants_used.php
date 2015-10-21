<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Refrigerants_used extends Stage_Controller {

    public function index($assignment_id) {
        require_capability('assignments:recordrefrigerantsused');

        $technician_id = $this->session->userdata('user_id');
        $this->assignment = $this->assignment_model->get($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }

        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'refrigerants_used', 'param' => $assignment_id, 'module' => 'miniant'));

        if ($technician_id == $order->senior_technician_id) {
            $units = $this->assignment_model->get_units($assignment_id);
        } else {
            $units = $this->assignment_model->get_units($assignment_id, $technician_id);
        }

        foreach ($units as $key => $unit) {
            $units[$key]->assignment = (object) $this->assignment_model->get_values($unit->assignment_id);
            if (!empty($unit->assignment->diagnostic_id)) {
                $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($unit->assignment->diagnostic_id);
            }

            $units[$key]->template_parts_used = $this->part_type_model->get(array('unit_type_id' => $unit->unit_type_id, 'in_template' => true));
            // $units[$key]->required_parts = $this->diagnostic_model->get_required_parts($units[$key]->diagnostic->id, null, true);
            $units[$key]->refrigerants = $this->assignment_refrigerant_model->get(array('assignment_id' => $unit->assignment_id));
        }

        $info_title_options = array('title' => 'Unit info', 'help' => 'Information', 'icons' => array());
        $title = null;
        if ($order_type == 'Repair') {
            $title = 'Repair job: refrigerants used';
        }

        $this->load_stage_view(array(
             'units' => $units,
             'info_title_options' => $info_title_options,
             'diagnostic_id' => $this->assignment->diagnostic_id,
             'title' => $title
        ));

    }

    public function process() {

        require_capability('assignments:recordpartsused');
        $diagnostic_id = $this->input->post('diagnostic_id');
        $assignment_id = $this->input->post('assignment_id');

        $assignment = $this->assignment_model->get($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'parts_used', 'param' => $assignment->id, 'module' => 'miniant'));

        $post_data = $this->input->post();

        foreach ($post_data['description'] as $part_type_id => $description) {
            $part_type = $this->part_type_model->get($part_type_id);
            $this->form_validation->set_rules('description['.$part_type_id.']', 'Quantity/description for '.$part_type->name, 'trim|required');
        }

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Make sure you complete all the required fields below (in Yellow)', 'danger');
            return $this->parts_used($assignment->id);
        }

        foreach ($post_data['description'] as $part_type_id => $description) {
            $part_type = $this->part_type_model->get($part_type_id);
            $params = array('servicequote_id' => $sq_id, 'part_type_id' => $part_type_id);

            $po_number = (!empty($post_data[$part_type_id])) ? $post_data[$part_type_id] : null;

            // If the part already exists, update its quantity and po_number. Otherwise, create the part
            if ($part = $this->part_model->get($params, true)) {
                if ($part_type->field_type == 'text') {
                    $this->part_model->edit($part->id, array('quantity' => 0, 'description' => $description, 'po_number' => $po_number));
                } else {
                    $this->part_model->edit($part->id, array('quantity' => $description, 'po_number' => $po_number));
                }
            } else {
                $params['po_number'] = $po_number;

                if ($part_type->field_type == 'text') {
                    $params['quantity'] = 0;
                    $params['description'] = $description;
                } else {
                    $params['quantity'] = 0;
                    $params['description'] = $description;
                }

                $part_id = $this->part_model->add($params);
            }
        }

        add_message('The refrigerant used or reclaimed for this unit were successfully recorded');
        redirect($this->workflow_manager->get_next_url());
    }

    public function get_refrigerants_used() {
        $assignment_id = $this->input->post('assignment_id');
        $refrigerants_used = $this->assignment_refrigerant_model->get(array('assignment_id' => $assignment_id));

        foreach ($refrigerants_used as $key => $refrigerant) {
            $refrigerants_used[$key]->reclaimed_text = ($refrigerant->reclaimed) ? 'Reclaimed' : 'Used';
            $refrigerants_used[$key]->refrigerant_type = $this->refrigerant_type_model->get($refrigerant->refrigerant_type_id)->name;
        }
        send_json_data(array('refrigerants_used' => $refrigerants_used));
    }

    public function add_refrigerant_used() {
        $params = $this->input->post();

        if (empty($params['refrigerant_type_id'])) {
            send_json_message('Please select a refrigerant type', 'danger');
            die();
        }

        if (strlen($params['quantity_g']) == 0) {
            send_json_message('Please enter the quantity used/reclaimed in g', 'danger');
            die();
        }

        if (!is_numeric($params['quantity_g'])) {
            send_json_message('Please enter a valid number as the quantity used/reclaimed in g', 'danger');
            die();
        }

        if (empty($params['bottle_serial_number'])) {
            send_json_message('Please enter the bottle serial number', 'danger');
            die();
        }

        if ($assignment_refrigerant_id = $this->assignment_refrigerant_model->add($params)) {
            send_json_message('The refrigerant used/reclaimed was successfully recorded');
            trigger_event('refrigerant_used_recorded', 'assignment', $params['assignment_id'], false, 'miniant');
            die();
        }
    }

    public function delete_refrigerant_used($assignment_refrigerant_id) {
        $assignment_refrigerant = $this->assignment_refrigerant_model->get($assignment_refrigerant_id);
        $this->assignment_refrigerant_model->delete(array('id' => $assignment_refrigerant_id));

        if (!($this->assignment_refrigerant_model->get(array('assignment_id' => $assignment_refrigerant->assignment_id)))) {
            trigger_event('refrigerant_used_recorded', 'assignment', $assignment_refrigerant->assignment_id, true, 'miniant');
        }

        send_json_message('The refrigerant used/reclaimed was successfully deleted');
    }
}
