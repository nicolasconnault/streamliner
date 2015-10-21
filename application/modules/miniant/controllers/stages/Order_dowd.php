<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Order_dowd extends Stage_controller {

    public function index($assignment_id) {
        $this->assignment_id = $assignment_id;
        $this->assignment = $this->assignment_model->get($this->assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $diagnostic = $this->diagnostic_model->get($this->assignment->diagnostic_id);
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = $this->session->userdata('user_id');
        $is_senior_technician = $technician_id == $order->senior_technician_id;

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'order_dowd', 'param' => $assignment_id, 'module' => 'miniant'));

        $dowd = null;

        switch ($order_type) {
            case 'Repair':
                if ($this->assignment->no_issues_found || !$this->assignment_model->has_statuses($this->assignment->id, array('SQ APPROVED'))) {
                    $dowd = $this->dowd_model->get(array('name' => 'REPAIR TASKS COMPLETED - OPERATIONAL', 'order_type_id' => $order->order_type_id), true);
                } else if ($this->assignment_model->has_statuses($this->assignment->id, array('SQ APPROVED'))) {
                    $dowd = $this->dowd_model->get(array('name' => 'REPAIR TASKS COMPLETED - SQ', 'order_type_id' => $order->order_type_id), true);
                }
                break;
            case 'Breakdown':
                if ($this->assignment->no_issues_found) {
                    $dowd = $this->dowd_model->get(array('name' => 'NO ISSUES FOUND', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                // No repairs done, SQ created and approved WAITING ON APPROVAL
                } else if (!$this->order_model->has_repairs_approved($order->id) && $this->order_model->has_sq_approved($order->id)) {
                    $dowd = $this->dowd_model->get(array('name' => 'WAITING ON APPROVAL', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                // Some repairs done, SQ approved WAITING ON APPROVAL - SOME ISSUES REPAIRED
                } else if ($this->order_model->has_repairs_approved($order->id) && $this->order_model->has_sq_approved($order->id)) {
                    $dowd = $this->dowd_model->get(array('name' => 'WAITING ON APPROVAL - SOME ISSUES REPAIRED', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                // All repairs done, no SQ ISSUES FOUND AND REPAIRED
                } else if ($this->order_model->has_repairs_approved($order->id) && !$this->order_model->has_sq_approved($order->id)) {
                    $dowd = $this->dowd_model->get(array('name' => 'ISSUES FOUND AND REPAIRED', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                // Some issues diagnosed, but no repairs done or SQ created NO REPAIRS AUTHORISED
                } else if (!$this->order_model->has_repairs_approved($order->id) && !$this->order_model->has_sq_approved($order->id)) {
                    $dowd = $this->dowd_model->get(array('name' => 'NO REPAIRS AUTHORISED', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                }
                break;
            case 'Installation':
                $dowd = $this->dowd_model->get(array('name' => 'UNITS INSTALLED', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);

                break;
            case 'Maintenance':
                if ($this->order_model->has_sq_approved($order->id)) {
                    $dowd = $this->dowd_model->get(array('name' => 'MAINTENANCE COMPLETED ISSUES FOUND', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                } else {
                    $dowd = $this->dowd_model->get(array('name' => 'MAINTENANCE COMPLETED NO ISSUES', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                }

                break;
            case 'Service':
                if ($this->order_model->has_sq_approved($order->id)) {
                    $dowd = $this->dowd_model->get(array('name' => 'SERVICE COMPLETED ISSUES FOUND', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                } else {
                    $dowd = $this->dowd_model->get(array('name' => 'SERVICE COMPLETED NO ISSUES', 'order_type_id' => $order->order_type_id, 'granularity' => 'order'), true);
                }

                break;
        }

        $this->db->where(array('order_type_id' => $order->order_type_id, 'granularity' => 'order'));
        $this->load_stage_view(array(
             'is_senior_technician' => $is_senior_technician,
             'dowds_dropdown' => $this->dowd_model->get_dropdown('name'),
             'dowd' => $dowd
        ));
    }

    public function process() {
        $dowd_text = $this->input->post('dowd_text');

        $order_id = $this->input->post('order_id');
        $assignment_id = $this->input->post('assignment_id');

        $order = (object) $this->order_model->get_values($order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = $this->session->userdata('user_id');
        $is_senior_technician = $technician_id == $order->senior_technician_id;
        $dowd_id = $this->input->post('dowd_id');
        $dowd_text = $this->input->post('dowd_text');

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'order_dowd', 'param' => $assignment_id, 'module' => 'miniant'));

        $this->form_validation->set_rules('dowd_text', 'Description for DOWD' , 'trim|required');
        $this->form_validation->set_rules('dowd_id', 'DOWD template', 'trim|required');

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Make sure you complete all the required fields below (in Yellow)', 'danger');
            return $this->index($assignment->id);
        }

        $this->order_model->edit($order_id, compact('dowd_id', 'dowd_text'));

        trigger_event('dowd_recorded', 'order', $order_id, false, 'miniant');

        redirect($this->workflow_manager->get_next_url());
    }

    public function get_dowd_description() {
        $dowd_id = $this->input->post('dowd_id');
        $order_id = $this->input->post('order_id');
        $description = $this->dowd_model->get_formatted_order_dowd($dowd_id, $order_id);
        send_json_data(array('description' => $description));
    }

    public function all_dowds_recorded($assignment_id, $order_id) {
        $technician_id = $this->session->userdata('user_id');
        $units = $this->get_units($assignment_id, 1, 1);

        foreach ($units as $unit) {
            if (!$this->assignment_model->has_statuses($unit->assignment_id, array("DOWD RECORDED"))) {
                return false;
            }
        }
        return true;
    }
}
