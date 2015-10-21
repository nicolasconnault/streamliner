<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Installation_checklist extends Stage_controller {

    public function index($assignment_id) {
        $this->assignment_id = $assignment_id;
        $this->assignment = (object) $this->assignment_model->get_values($this->assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $technician_id = $this->session->userdata('user_id');

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => 'installation', 'stage' => 'installation_checklist', 'param' => $assignment_id, 'module' => 'miniant'));

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        foreach ($units as $key => $unit) {
            $installation_tasks = $this->installation_task_model->get(array('unit_id' => $unit->id, 'disabled' => 0));

            $units[$key]->installation_tasks = $installation_tasks;
        }

        $this->load_stage_view(array(
             'units' => $units,
             'is_senior_technician' => $technician_id == $order->senior_technician_id,
             'supervisor' => false
        ));
    }

    public function process($assignment_id) {
        $assignment = (object) $this->assignment_model->get_values($assignment_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => 'installation', 'stage' => 'installation_checklist', 'param' => $assignment_id, 'module' => 'miniant'));
        trigger_event('installation_tasks_completed', 'assignment', $assignment_id, false, 'miniant');
        redirect($this->workflow_manager->get_next_url());
    }

    public function set_task_status() {
        $status = $this->input->post('status');
        $installation_task_id = $this->input->post('installation_task_id');
        $type = $this->input->post('type');

        if ($status) {
            if ($type == 'completed') {
                $this->installation_task_model->edit($installation_task_id, array('completed_date' => time(), 'completed_by' => $this->session->userdata('user_id')));
            } else if ($type == 'satisfactory') {
                $this->installation_task_model->edit($installation_task_id, array('satisfactory' => true, 'reviewed_by' => $this->session->userdata('user_id')));
            }
        } else {
            if ($type == 'completed') {
                $this->installation_task_model->edit($installation_task_id, array('completed_date' => null, 'completed_by' => null));
            } else if ($type == 'satisfactory') {
                $this->installation_task_model->edit($installation_task_id, array('satisfactory' => false, 'reviewed_by' => $this->session->userdata('user_id')));
            }
        }

        send_json_message('The task was successfully updated');
    }

    public function save_installation_task_notes() {
        $task_id = $this->input->post('task_id');
        $notes = $this->input->post('notes');
        $this->installation_task_model->edit($task_id, array('notes' => $notes));
        send_json_message('Task successfully updated', 'success');
    }
}

