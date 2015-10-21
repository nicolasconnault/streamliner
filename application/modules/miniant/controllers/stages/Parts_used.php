<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Parts_used extends Stage_Controller {
    /**
     * For repairs, breakdown and maintenance workflows, parts used are associated with a diagnosed issue. For Installations they are simply associated with the assignment
     * @param string $type Repair workflows use this stage twice. The first time is for parts used during the assigned repair tasks (type == null). The second time is following a new diagnostic (type == 'diagnostic')
     */
    public function index($assignment_id, $type=null) {
        require_capability('assignments:recordpartsused');

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
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'parts_used', 'param' => $this->assignment->id, 'extra_param' => $type, 'module' => 'miniant'));

        if ($technician_id == $order->senior_technician_id) {
            $units = $this->assignment_model->get_units($assignment_id);
        } else {
            $units = $this->assignment_model->get_units($assignment_id, $technician_id);
        }

        // TODO This is very messy and confusing. Needs to be refactored!
        // We need to load the following:
        // 1. Parts required to be used, determined by the diagnosed issues and their diagnostic_rules
        // 2. Template parts (Nitrogen, sundries etc.) that we always show the technician
        // 3. Values for the above if they were entered previously
        foreach ($units as $key => $unit) {
            $units[$key]->assignment = (object) $this->assignment_model->get_values($unit->assignment_id);

            // Installations don't have a diagnostic
            if (!empty($units[$key]->assignment->diagnostic_id)) {
                $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($unit->assignment->diagnostic_id);
            }

            // Template parts
            $template_parts_used = $this->part_type_model->get(array('unit_type_id' => $unit->unit_type_id, 'in_template' => true));

            $for_repair_task = $order_type == 'Repair' && $type != 'diagnostic';

            foreach ($template_parts_used as $key2 => $template_part) {
                if ($part_used = $this->part_model->get(array('assignment_id' => $unit->assignment_id, 'part_type_id' => $template_part->id, 'for_repair_task' => $for_repair_task), true)) {
                    $template_parts_used[$key2]->description = ($template_part->field_type == 'text') ? $part_used->description : $part_used->quantity;
                    $this->preload_form_data($template_part, $part_used);
                } else {
                    $template_parts_used[$key2]->description = null;
                }
            }

            $units[$key]->template_parts_used = $template_parts_used;
            $first_repair_task = $this->repair_task_model->get(array('assignment_id' => $unit->assignment_id), true);

            if (!empty($first_repair_task->diagnostic_issue_id)) {
                $diagnostic_issue = $this->diagnostic_issue_model->get($first_repair_task->diagnostic_issue_id);

                if ($type == 'diagnostic') {
                    $units[$key]->diagnostic = $this->diagnostic_model->get($this->assignment->diagnostic_id, true);
                } else {
                    $units[$key]->diagnostic = $this->diagnostic_model->get($diagnostic_issue->diagnostic_id, true);
                }
            } else if (!empty($units[$key]->assignment->diagnostic_id)) {
                $units[$key]->diagnostic = $this->diagnostic_model->get($units[$key]->assignment->diagnostic_id, true);
            }

            // Required parts based on diagnostic
            if (!empty($units[$key]->diagnostic)) {
                $can_be_fixed_now = $order_type != 'Repair';
                $units[$key]->diagnostic_issues =
                    $this->diagnostic_issue_model->get(array('diagnostic_id' =>
                    $units[$key]->diagnostic->id, 'can_be_fixed_now' =>
                    $can_be_fixed_now));

                $required_parts = $this->diagnostic_model->get_required_parts($units[$key]->diagnostic->id, null, $can_be_fixed_now);

                foreach ($required_parts as $key2 => $required_part) {
                    $part_type = $this->part_type_model->get($required_part->part_type_id);

                    if (empty($required_part->part_name)) {
                        $required_parts[$key2]->part_name = $part_type->name;
                    }

                    if ($part_used = $this->part_model->get(array('assignment_id' => $unit->assignment_id, 'part_type_id' => $part_type->id), true)) {
                        $this->preload_form_data($part_type, $part_used);
                    }
                }

                $units[$key]->required_parts = $required_parts;
            } else {
                $units[$key]->required_parts = array();
            }

            $units[$key]->custom_parts = array();
            if ($custom_parts = $this->part_model->get_custom_parts($unit->assignment_id)) {
                $units[$key]->custom_parts = $custom_parts;
                foreach ($custom_parts as $custom_part) {
                    form_element::$default_data['custom_description['.$custom_part->id.']'] = $custom_part->quantity;
                    form_element::$default_data['custom_po_number['.$custom_part->id.']'] = $custom_part->po_number;
                    form_element::$default_data['custom_origin['.$custom_part->id.']'] = $custom_part->origin;
                }
            }
        }


        $title = '';
        switch ($order_type) {
            case 'Installation':
                $title = 'Parts used during installation';
                break;
            case 'Repair':
                $title = 'Repair job: parts used';
                break;
            case 'Breakdown':
            case 'Maintenance':
            case 'Service':
                $title = 'Parts used to repair diagnosed issues';
        }

        $parts_title_options = array('title' => '', 'help' => 'Record the parts used to repair the reported issues', 'icons' => array());
        $info_title_options = array('title' => 'Unit info', 'help' => 'Information', 'icons' => array());

        $this->load_stage_view(array(
             'units' => $units,
             'diagnostic_id' => $this->assignment->diagnostic_id,
             'parts_title_options' => $parts_title_options,
             'info_title_options' => $info_title_options,
             'title' => $title,
             'type' => $type
        ));

    }

    public function process() {

        require_capability('assignments:recordpartsused');
        $diagnostic_id = $this->input->post('diagnostic_id');
        $assignment_id = $this->input->post('assignment_id');
        $part_name  = $this->input->post('part_name');

        $type = $this->input->post('type');
        $assignment = $this->assignment_model->get($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'parts_used', 'param' => $assignment->id, 'extra_param' => $type, 'module' => 'miniant'));

        $post_data = $this->input->post();

        // Setting up validation
        foreach ($post_data['description'] as $part_type_id => $description) {
            $part_type = $this->part_type_model->get($part_type_id);
            $this->form_validation->set_rules('description['.$part_type_id.']', 'Quantity/description for '.$part_type->name, 'trim|required');
            $this->form_validation->set_rules('origin['.$part_type_id.']', 'Origin of '.$part_type->name, 'trim|required');
            if (isset($post_data['po_number['.$part_type_id.']'])) {
                $this->form_validation->set_rules('po_number['.$part_type_id.']', 'PO Number for '.$part_type->name, 'trim|required');
            }
        }

        foreach ($post_data['custom_description'] as $part_id => $description) {
            $part = $this->part_model->get($part_id);
            $this->form_validation->set_rules('custom_description['.$part_id.']', 'Quantity/description for '.$part->part_name, 'trim|required');
            $this->form_validation->set_rules('custom_origin['.$part_id.']', 'Origin of '.$part->part_name, 'trim|required');

            if (isset($post_data['custom_po_number['.$part_id.']'])) {
                $this->form_validation->set_rules('custom_po_number['.$part_id.']', 'PO Number for '.$part->part_name, 'trim|required');
            }
        }

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Make sure you complete all the required fields below (in Yellow)', 'danger');
            return $this->index($assignment->id);
        }

        $for_repair_task = $order_type == 'Repair' && $type != 'diagnostic';

        foreach ($post_data['description'] as $part_type_id => $description) {
            $part_type = $this->part_type_model->get($part_type_id);
            $for_repair_task = $order_type == 'Repair' && $type != 'diagnostic';
            $params = array('assignment_id' => $assignment_id, 'part_type_id' => $part_type_id, 'for_repair_task' => $for_repair_task);

            $po_number = (!empty($post_data['po_number'][$part_type_id])) ? $post_data['po_number'][$part_type_id] : null;
            $origin = $post_data['origin'][$part_type_id];

            // If the part already exists, update its quantity and po_number. Otherwise, create the part
            if ($part = $this->part_model->get($params, true)) {

                if ($part_type->field_type == 'text') {
                    $this->part_model->edit($part->id, array('quantity' => 0, 'description' => $description, 'po_number' => $po_number, 'part_name' => $part_type->name, 'origin' => $origin));
                } else {
                    $this->part_model->edit($part->id, array('quantity' => $description, 'description' => null, 'po_number' => $po_number, 'part_name' => $part_type->name, 'origin' => $origin));
                }

            } else {
                $params['po_number'] = $po_number;
                $params['part_name'] = $part_type->name;
                $params['origin'] = $origin;

                if ($part_type->field_type == 'text') {
                    $params['quantity'] = 0;
                    $params['description'] = $description;
                } else {
                    $params['quantity'] = 0;
                    $params['description'] = $description;
                }

                if ($order_type == 'Repair' && $type != 'diagnostic') {
                    $params['for_repair_task'] = true;
                }

                $part_id = $this->part_model->add($params);
            }
        }

        foreach ($post_data['custom_description'] as $part_id => $description) {
            $part = $this->part_model->get($part_id);
            $po_number = $post_data['custom_po_number'][$part_id];
            $origin = $post_data['custom_origin'][$part_id];
            $this->part_model->edit($part_id, array('quantity' => $description, 'description' => null, 'po_number' => $po_number, 'origin' => $origin));
        }

        if (!empty($part_name)) {
            $this->part_model->add_custom_part($assignment_id, $part_name);
            add_message('The part was successfully added', 'success');
            redirect(base_url().'miniant/stages/parts_used/index/'.$assignment_id);
        }

        trigger_event('parts_used_recorded', 'assignment', $assignment_id, false, 'miniant');
        add_message('The parts used for this unit were successfully recorded');

        redirect($this->workflow_manager->get_next_url());
    }

    public function preload_form_data($part_type, $part_used) {
        if ($part_type->field_type == 'text') {
            $description = $part_used->description;
        } else {
            $description = $part_used->quantity;
        }

        form_element::$default_data['description['.$part_type->id.']'] = $description;
        form_element::$default_data['po_number['.$part_type->id.']'] = $part_used->po_number;
        form_element::$default_data['origin['.$part_type->id.']'] = $part_used->origin;
    }

    public function delete_custom_part($part_id) {
        $part = $this->part_model->get($part_id);
        $this->part_model->delete($part_id);
        add_message('The part was successfully deleted', 'success');
        redirect(base_url().'miniant/stages/parts_used/index/'.$part->assignment_id);
    }
}
