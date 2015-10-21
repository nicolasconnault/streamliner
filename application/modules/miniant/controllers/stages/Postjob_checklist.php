<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Postjob_checklist extends Stage_Controller {

    public function index($assignment_id) {
        require_capability('servicequotes:writesqs');

        if (!($assignment = $this->assignment_model->get($assignment_id))) {
            die("The assignment ID $assignment_id could not be found!");
        }

        $this->assignment = $this->assignment_model->get($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $order = $this->order_model->get($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        parent::update_time($order->id);

        $diagnostics = $this->diagnostic_model->get_for_technician($this->assignment, $this->session->userdata('technician_id'), $order->senior_technician_id);
        $diagnostic = reset($diagnostics);
        $tasks = $this->order_model->get_tasks($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'postjob_checklist', 'param' => $assignment_id, 'module' => 'miniant'));

        $title_options = array('title' => $order_type.' post-job checklist',
                               'help' => 'You must complete these tasks before obtaining the client\'s signature',
                               'icons' => array());

        $complete = $this->order_model->has_statuses($order->id, array('POST-JOB COMPLETE'));

        if (!$complete) {
            add_message('You must complete all the tasks below before moving to the next section', 'warning');
        }

        $this->load_stage_view(array(
             'tasks' => $tasks,
             'completed' => $complete,
        ));
    }

    public function set_order_task_status() {
        $status = $this->input->post('status');
        $order_task_id = $this->input->post('order_task_id');
        $order_id = $this->input->post('order_id');

        $this->orders_task_model->update_order_task($status, $order_task_id, $order_id);
        send_json_message('The tasks was successfully updated');
    }

    public function process($assignment_id) {
        // TODO Make sure all required tasks were completed
        $assignment = $this->assignment_model->get_values($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'postjob_checklist', 'param' => $assignment_id, 'module' => 'miniant'));
        $next_page_url = $this->workflow_manager->get_next_url();

        trigger_event('post-job_complete', 'order', $order->id, false, 'miniant');
        redirect($next_page_url);
    }
}
