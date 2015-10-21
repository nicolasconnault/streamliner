<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Required_parts extends Stage_Controller {

    public function index($assignment_id) {
        $this->load->model('miniant/diagnostic_tree_model');

        require_capability('servicequotes:writesqs');

        $technician_id = parent::get_technician_id($assignment_id);
        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');
        $this->assignment = $this->assignment_model->get($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }

        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'required_parts', 'param' => $this->assignment->id, 'module' => 'miniant'));

        if ($technician_id == $order->senior_technician_id) {
            $units = $this->assignment_model->get_units($assignment_id);
        } else {
            $units = $this->assignment_model->get_units($assignment_id, $technician_id);
        }

        $diagnostic_estimate_tree = $this->diagnostic_tree_model->get_estimate_tree($assignment_id);

        $estimated_time = $this->diagnostic_tree_model->get_estimate_time($diagnostic_estimate_tree);

        // We no longer ask the technician to estimate the labour, it is done entirely by the system based on diagnostic rules
        $this->diagnostic_model->edit($this->assignment->diagnostic_id, array('estimated_time' => $estimated_time));
        // Create SQ if none for this job/unit yet
        $sq = $this->servicequote_model->get(array('order_id' => $order->id, 'diagnostic_id' => $this->assignment->diagnostic_id), true);

        if (empty($sq->id)) {
            $sq_id = $this->servicequote_model->create_from_diagnostic($this->assignment->diagnostic_id);
        } else {
            $sq_id = $sq->id;
        }

        // Replace required_parts (loaded from step_parts table) with actual parts (from parts table)
        foreach ($units as $key => $unit) {
            $units[$key]->assignment = (object) $this->assignment_model->get_values($units[$key]->assignment_id);
            $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($units[$key]->assignment->diagnostic_id);


            // Only provide the SQ ID if this stage has been completed
            if ($this->assignment_model->has_statuses($units[$key]->assignment_id, array('REQUIRED PARTS RECORDED'))) {
                $units[$key]->required_parts = $this->diagnostic_model->get_required_parts($units[$key]->assignment->diagnostic_id, $sq_id);
            } else {
                $units[$key]->required_parts = $this->diagnostic_model->get_required_parts($units[$key]->assignment->diagnostic_id);
            }

            foreach ($units[$key]->required_parts as $part_key => $part) {
                form_element::$default_data["part_model_number[$part->id]"] = $part->part_number;
            }
        }

        $parts_title_options = array('title' => '', 'help' => 'Record the parts required this SQ', 'icons' => array());
        $info_title_options = array('title' => 'Unit info',
                               'help' => 'Information',
                               'icons' => array(),
                               );

        $this->load_stage_view(array(
             'units' => $units,
             'parts_title_options' => $parts_title_options,
             'info_title_options' => $info_title_options,
             'diagnostic_id' => $this->assignment->diagnostic_id,
             'is_technician' => $is_technician,
             'sq_id' => $sq_id,
        ));
    }

    public function process() {

        require_capability('servicequotes:writesqs');
        $diagnostic_id = $this->input->post('id');
        $diagnostic = $this->diagnostic_model->get($diagnostic_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $diagnostic->id), true);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = $this->session->userdata('user_id');

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'required_parts', 'param' => $assignment->id, 'module' => 'miniant'));

        $sq_id = $this->input->post('sq_id');

        // Create parts
        $post_data = $this->input->post();

        $errors = false;

        // Validation: parts with instructions have required Other Info
        foreach ($post_data['qty'] as $part_id => $qty) {
            $part = $this->part_model->get($part_id);
            $part_type = $this->part_type_model->get($part->part_type_id);
            if (!empty($part_type->instructions)) {
                $this->form_validation->set_rules('part_info['.$part_id.']', 'Required info for '.$part_type->name, 'trim|required');
            }

            if (empty($post_data['part_model_number'][$part_id])) {
                $errors = true;
                add_message('Please enter a model number for each required part', 'danger');
                break;
            }
        }

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Make sure you complete all the required fields below (in Yellow)', 'danger');
            return $this->index($assignment->id);
        }

        foreach ($post_data['qty'] as $part_id => $qty) {
            $part = $this->part_model->get($part_id);
            $part->quantity = $qty;

            $part->description = $post_data['part_info'][$part_id];
            $part->part_number = $post_data['part_model_number'][$part_id];
            $part->part_name = $this->part_type_model->get($part->part_type_id)->name;
            $part->origin = 'Supplier';
            $part->servicequote_id = $sq_id;

            $this->part_model->edit($part_id, (array) $part);
        }

        if (!$errors) {
            trigger_event('submit_sq', 'assignment', $assignment->id, false, 'miniant');
            add_message('The required parts for this unit were successfully recorded');

            // Notify ops managers
            $this->load->helper('miniant_email');
            email_op_managers($this->load->view('stages/emails/new_sq', compact('sq_id', 'order', 'assignment', 'diagnostic', 'technician_id', 'order_type'), true));
            redirect($this->workflow_manager->get_next_url());
        } else {
            return $this->index($assignment->id);
        }
    }
}
