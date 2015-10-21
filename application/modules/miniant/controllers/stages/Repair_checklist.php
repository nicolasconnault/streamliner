<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Repair_checklist extends Stage_controller {

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
        $this->workflow_manager->initialise(array('workflow' => 'repair', 'stage' => 'repair_checklist', 'param' => $assignment_id, 'module' => 'miniant'));

        $unit = (object) $this->unit_model->get_values($this->assignment->unit_id);
        $tasks = $this->repair_task_model->get(array('assignment_id' => $assignment_id));

        $is_required_set = ($this->assignment->diagnostic_required === '0' || $this->assignment->diagnostic_required === '1');
        $is_authorised_set = $this->assignment->diagnostic_required === '0' || ($this->assignment->diagnostic_authorised === '0' || $this->assignment->diagnostic_authorised === '1');

        $this->load->library('Dialog');
        $this->dialog->initialise(array('id' => 'dialog'));

        $this->dialog->add_question(array(
            'id' => 'repairs_completed',
            'shown' => !$this->order_model->has_statuses($order->id, array('REPAIR TASKS COMPLETED')),
            'text' => 'Have you completed all needed repairs on this unit?',
            'answers' => array(
                array(
                    'text' => 'Yes',
                    'ids_to_show' => array('diagnostic_required'),
                    'ajax_callback' => 'miniant/stages/repair_checklist/set_diagnostic_not_required/'.$this->assignment->id,
                    'triggers' => array(
                        array('system' => 'order', 'document_id' => $this->assignment->order_id, 'event_name' => 'repair_tasks_completed', 'module' => 'miniant'),
                    ),
                ))
            )
        );

        $this->dialog->add_question(array(
            'id' => 'diagnostic_required',
            'shown' => !$is_required_set && $this->order_model->has_statuses($order->id, array('REPAIR TASKS COMPLETED')),
            'text' => 'Is this unit now functioning properly?',
            'answers' => array(
                array(
                    'text' => 'Yes',
                    'ids_to_show' => array('continue'),
                    'ajax_callback' => 'miniant/stages/repair_checklist/set_diagnostic_not_required/'.$this->assignment->id,
                ),
                array(
                    'text' => 'No',
                    'ids_to_show' => array('diagnostic_authorised'),
                    'ajax_callback' => 'miniant/stages/repair_checklist/set_diagnostic_required/'.$this->assignment->id,
                ))
            )
        );

        $this->dialog->add_question(array(
            'id' => 'diagnostic_authorised',
            'shown' => $this->order_model->has_statuses($order->id, array('REPAIR TASKS COMPLETED')) && !$is_authorised_set && $this->assignment->diagnostic_required === '1',
            'text' => 'Have you been authorised to perform a diagnostic on this unit?',
            'answers' => array(
                array(
                    'text' => 'Yes',
                    'ids_to_show' => array('continue'),
                    'ajax_callback' => 'miniant/stages/repair_checklist/set_diagnostic_authorised/'.$this->assignment->id,
                ),
                array(
                    'text' => 'No',
                    'ids_to_show' => array('continue'),
                    'ajax_callback' => 'miniant/stages/repair_checklist/set_diagnostic_not_authorised/'.$this->assignment->id,
                ))
            )
        );

        $this->dialog->add_question(array(
            'id' => 'continue',
            'shown' => $this->order_model->has_statuses($order->id, array('REPAIR TASKS COMPLETED')) && $is_required_set && $is_authorised_set,
            'text' => '&nbsp;',
            'answers' => array(
                array(
                    'text' => 'Continue',
                    'url' => 'miniant/stages/repair_checklist/process/'.$this->assignment->id
                ))
            ));
        $dialog = $this->dialog->output();


        $this->load_stage_view(array(
             'tasks' => $tasks,
             'unit' => $unit,
             'dialog' => $dialog,
             'title' => 'Repair checklist: Unit '.$unit->id,
             'is_senior_technician' => $technician_id == $order->senior_technician_id,
        ));
    }

    public function process($assignment_id) {
        $assignment = (object) $this->assignment_model->get_values($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'repair_checklist', 'param' => $assignment_id, 'module' => 'miniant'));
        trigger_event('repair_tasks_completed', 'order', $order->id, false, 'miniant');

        if ($this->assignment_model->has_statuses($this->assignment->id, array("DOWD RECORDED")) && $this->assignment->diagnostic_required) {
            trigger_event('unit_work_complete', 'order', $order->id, false, 'miniant');
            $this->document_statuses_model->delete_cache_keys();
        }

        redirect($this->workflow_manager->get_next_url());
    }

    public function set_task_status() {
        $status = $this->input->post('status');
        $task_id = $this->input->post('repair_task_id');
        $task = $this->repair_task_model->get($task_id);
        $assignment = $this->assignment_model->get_from_cache($task->assignment_id);

        $this->repair_task_model->update_task($status, $task_id);
        if (!$status) {
            trigger_event('repair_tasks_completed', 'order', $assignment->order_id, true, 'miniant');
        }

        send_json_message('The task was successfully updated');
    }

    public function set_diagnostic_required($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_required' => true));
    }

    public function set_diagnostic_not_required($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_required' => false));
    }

    public function set_diagnostic_authorised($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_authorised' => true));
    }

    public function set_diagnostic_not_authorised($assignment_id) {
        $this->assignment_model->edit($assignment_id, array('diagnostic_authorised' => false));
    }
}
