<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Office_notes extends Stage_controller {

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

        // Don't record time for this stage, it's always shown after client signature

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'office_notes', 'param' => $this->assignment_id, 'module' => 'miniant'));

        $office_messages = $this->message_model->get(array('document_type' => 'order', 'document_id' => $order->id));

        $this->load_stage_view(array(
             'office_messages' => $office_messages
        ));

    }

    public function process() {
        $assignment_id = $this->input->post('assignment_id');
        $order_id = $this->input->post('order_id');
        $assignment = $this->assignment_model->get($assignment_id);

        $order = $this->order_model->get($order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'office_notes', 'param' => $assignment_id, 'module' => 'miniant'));

        if (user_has_role($this->session->userdata('user_id'), 'Technician')) {
            trigger_event('office_notes_sighted', 'order', $order_id, false, 'miniant');
        }

        redirect($this->workflow_manager->get_next_url());
    }

}
