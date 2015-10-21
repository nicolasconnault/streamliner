<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Unit_serial extends Stage_Controller {

    public function index($assignment_id) {
        $this->assignment = $this->assignment_model->get_values($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'unit_serial', 'param' => $assignment_id, 'module' => 'miniant'));

        $technician_id = parent::get_technician_id($assignment_id);
        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');

        parent::update_time($order->id);
        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);

        if ($is_technician) {
            trigger_event('start', 'order_technician', $order_technician->id, false, 'miniant');
        }

        $next_page_url = $this->workflow_manager->get_next_url();

        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        $this->load_stage_view(array(
             'units' => $units,
        ));
    }

    public function process() {
        $assignment_id = $this->input->post('assignment_id');
        $assignment = $this->assignment_model->get($assignment_id);
        $unit = $this->unit_model->get($assignment->unit_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);

        $form_data = $this->get_form_data($this->input->post('unit_type_id'), $assignment_id, $this->input->post(), true);

        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'unit_serial', 'param' => $assignment_id, 'module' => 'miniant'));

        foreach ($form_data['required'] as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            return $this->index($assignment_id);
        }

        $unit_id = $this->unit_model->merge_if_serial_exists($form_data['fields'], $assignment_id);

        if ($unit_id != $assignment->unit_id) {
            add_message('The serial number you entered matches an existing unit. Please review the details below and answer the questions at the bottom of the form', 'warning');
        }

        trigger_event('unit_serial_entered', 'assignment', $assignment_id, false, 'miniant');
        redirect($this->workflow_manager->get_next_url());

    }

    public function get_form_data($unit_type_id, $assignment_id, $data) {
        $evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
        $ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
        $trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
        $other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
        $mech_unit_id = $this->unit_model->get_type_id('Mechanical services');

        $form_data = array('required' => array(), 'fields' => array(), 'required_files' => array(), 'files' => array());

        $form_data['required'] = array('assignment_id' => "Assignment id");

        foreach ($data as $field => $value) {
            if (in_array($field, array('unit_id', 'assignment_id', 'button', 'diagnostic_required_'.$assignment_id, 'diagnostic_authorised_'.$assignment_id))) {
                continue;
            }

            if (preg_match('/([a-z_]*)_'.$assignment_id.'/', $field, $matches)) {
                $form_data['fields'][$matches[1]] = $value;
            } else {
                $form_data['fields'][$field] = $value;
            }
        }

        return $form_data;
    }
}
