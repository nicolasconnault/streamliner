<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Maintenance_checklist extends Stage_controller {

    public function index($assignment_id) {
        $this->assignment = (object) $this->assignment_model->get_values($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $technician_id = $this->session->userdata('user_id');

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => 'maintenance', 'stage' => 'maintenance_checklist', 'param' => $assignment_id, 'module' => 'miniant'));

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        foreach ($units as $key => $unit) {
            $maintenance_task_templates = $this->maintenance_task_template_model->get(array('unit_type_id' => $unit->unit_type_id));

            foreach ($maintenance_task_templates as $key2 => $maintenance_task_template) {
                if ($maintenance_task = $this->maintenance_task_model->get(array('maintenance_task_template_id' => $maintenance_task_template->id, 'assignment_id' => $assignment_id), true)) {
                    $maintenance_task_templates[$key2]->completed_date = $maintenance_task->completed_date;
                    $maintenance_task_templates[$key2]->completed_by = $maintenance_task->completed_by;
                } else {
                    $maintenance_task_templates[$key2]->completed_date = null;
                    $maintenance_task_templates[$key2]->completed_by = null;
                }
            }

            $units[$key]->maintenance_tasks = $maintenance_task_templates;

            $this->load->library('Dialog');
            $this->dialog->initialise(array('id' => 'dialog-'.$unit->assignment_id));

            $this->dialog->add_question(array(
                'id' => 'diagnostic_required_'.$unit->id,
                'shown' => is_null($unit->assignment->diagnostic_required) && !$this->assignment_model->has_statuses($unit->assignment_id, array('MAINTENANCE TASKS COMPLETED')),
                'text' => 'Is this unit functioning properly?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => array('continue_'.$unit->id),
                        'ajax_callback' => 'miniant/stages/maintenance_checklist/set_diagnostic_not_required/'.$unit->assignment_id,
                    ),
                    array(
                        'text' => 'No',
                        'ids_to_show' => array('can_be_fixed_immediately_'.$unit->id),
                        'ajax_callback' => 'miniant/stages/maintenance_checklist/set_diagnostic_required/'.$unit->assignment_id,
                    ))
                )
            );

            $this->dialog->add_question(array(
                'id' => 'can_be_fixed_immediately_'.$unit->id,
                'shown' => empty($unit->assignment->estimated_duration) && $this->assignment->diagnostic_required == 1,
                'text' => 'Can you fix the issue within 5 minutes?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => array('diagnostic_authorised_'.$unit->id),
                        'ajax_callback' => 'miniant/stages/maintenance_checklist/set_diagnostic_immediate_fix/'.$unit->assignment_id,
                    ),
                    array(
                        'text' => 'No',
                        'ids_to_show' => array('continue_'.$unit->id),
                        'ajax_callback' => 'miniant/stages/maintenance_checklist/set_diagnostic_needs_sq/'.$unit->assignment_id,
                    ))
                )
            );

            $this->dialog->add_question(array(
                'id' => 'diagnostic_authorised_'.$unit->id,
                'shown' => !$this->assignment_model->has_statuses($unit->assignment_id, array('MAINTENANCE TASKS COMPLETED'))
                        && is_null($unit->assignment->diagnostic_authorised)
                        && $unit->assignment->diagnostic_required == 1
                        && $unit->assignment->estimated_duration == 5,
                'text' => 'Have you been authorised to perform a diagnostic on this unit?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => array('continue_'.$unit->id),
                        'ajax_callback' => 'miniant/stages/maintenance_checklist/set_diagnostic_authorised/'.$unit->assignment_id,
                    ),
                    array(
                        'text' => 'No',
                        'ids_to_show' => array('continue_'.$unit->id),
                        'ajax_callback' => 'miniant/stages/maintenance_checklist/set_diagnostic_not_authorised/'.$unit->assignment_id,
                    ))
                )
            );

            $this->dialog->add_question(array(
                'id' => 'continue_'.$unit->id,
                'shown' => $this->assignment_model->has_statuses($unit->assignment_id, array('MAINTENANCE TASKS COMPLETED'))
                        && isset($unit->assignment->diagnostic_required)
                        && isset($unit->assignment->diagnostic_authorised)
                        && isset($unit->assignment->estimated_duration)
                        ,
                'text' => '&nbsp;',
                'answers' => array(
                    array(
                        'text' => 'Continue',
                        'url' => 'miniant/stages/maintenance_checklist/process/'.$unit->assignment_id
                    ))
                ));
            $units[$key]->dialog = $this->dialog->output();
        }


        $this->load_stage_view(array(
             'units' => $units,
             'is_senior_technician' => $technician_id == $order->senior_technician_id,
        ));
    }

    public function process($assignment_id) {
        $assignment = (object) $this->assignment_model->get_values($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'maintenance_checklist', 'param' => $assignment_id, 'module' => 'miniant'));
        trigger_event('maintenance_tasks_completed', 'assignment', $assignment_id, false, 'miniant');

        if ($this->all_dowds_recorded($assignment->id, $order->id)) {
            trigger_event('unit_work_complete', 'order', $order->id, false, 'miniant');
            $this->document_statuses_model->delete_cache_keys();
        }

        redirect($this->workflow_manager->get_next_url());
    }

    public function set_task_status() {
        $status = $this->input->post('status');
        $maintenance_task_template_id = $this->input->post('maintenance_task_template_id');
        $assignment_id = $this->input->post('assignment_id');

        $this->maintenance_task_model->update_task($status, $maintenance_task_template_id, $assignment_id);
        $all_completed = $this->maintenance_task_model->all_tasks_completed($assignment_id);
        trigger_event('maintenance_tasks_completed', 'assignment', $assignment_id, !$all_completed, 'miniant');

        send_json_message('The task was successfully updated');
    }

    public function set_diagnostic_required($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_required' => true));
    }

    public function set_diagnostic_not_required($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_required' => false));
        trigger_event('dowd_recorded', 'assignment', $assignment_id, false, 'miniant');
    }

    public function set_diagnostic_authorised($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_authorised' => true));
    }

    public function set_diagnostic_not_authorised($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_authorised' => false));
        trigger_event('dowd_recorded', 'assignment', $assignment_id, false, 'miniant');
    }

    public function set_diagnostic_immediate_fix($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('estimated_duration' => 5));
    }

    public function set_diagnostic_needs_sq($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('estimated_duration' => 120));
    }

    public function all_dowds_recorded($assignment_id, $order_id) {
        $technician_id = $this->session->userdata('user_id');
        $units = $this->get_units($assignment_id, 1, 1);

        foreach ($units as $unit) {
            if (!$this->assignment_model->has_statuses($unit->assignment_id, array("DOWD RECORDED")) && $unit->assignment->diagnostic_required) {
                return false;
            }
        }
        return true;
    }
}
