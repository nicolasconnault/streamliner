<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

/**
 * Also has multiple file upload widget for Before-job photos
 */
class Location_diagram extends Stage_controller {

    public function index($assignment_id) {
        require_capability('orders:editunitdetails');

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
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'location_diagram', 'param' => $assignment_id, 'module' => 'miniant'));

        // Look up if there are existing location diagrams for this job site. If yes, copy the latest one over to this job
        $this->location_diagram_model->copy_if_exists_for_address($order->id, $order->parent_sq_id);

        $this->load_stage_view(array(
             'is_senior_technician' => $technician_id == $order->senior_technician_id,
             'is_maintenance' => $order->order_type_id == $this->order_model->get_type_id('Maintenance'),
             'is_service' => $order->order_type_id == $this->order_model->get_type_id('Service'),
             'module' => 'miniant',
             'jstoload' => array('signature_pad')
        ));

    }

    public function process() {
        require_capability('orders:editunitdetails');

        $postdata = $this->input->post();

        $order = (object) $this->order_model->get_values($postdata['order_id']);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'location_diagram', 'param' => $postdata['assignment_id'], 'module' => 'miniant'));

        if (empty($postdata['location_token'])) {
            add_message('Please enter a location reference # for this unit (like 1 or A)', 'danger');
            return $this->index($postdata['assignment_id']);
        } else if (empty($order->location_diagram_id)) {
            add_message('Please draw a location diagram for this job site', 'danger');
            return $this->index($postdata['assignment_id']);

        } else {
            $this->assignment_model->edit($postdata['assignment_id'], array('location_token' => $postdata['location_token']));
            add_message('The location reference # for this unit was successfully recorded', 'success');
        }

        trigger_event('location_info_recorded', 'assignment', $postdata['assignment_id'], false, 'miniant');
        redirect($this->workflow_manager->get_next_url());
    }

    /**
     * Update all the assignments for the given job, set their location_diagram_id
     */
    public function record_diagnostic_diagram() {
        require_capability('orders:editunitdetails');

        $data_uri = $this->input->post('dataurl');
        $order_id = $this->input->post('order_id');
        $order = (object) $this->order_model->get_values($order_id);

        $encoded_image = explode(',',$data_uri)[1];
        $decoded_image = base64_decode($encoded_image);

        $upload_path = $this->config->item('files_path').'location_diagrams';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        if ($handle = fopen($upload_path.'/'.$order_id.'.png', 'w+')) {
            fwrite($handle, $decoded_image);

            $diagram = new stdClass();
            $diagram->diagram = $data_uri;

            $diagram->id = $this->location_diagram_model->add($diagram);

            $this->order_model->edit($order_id, array('location_diagram_id' => $diagram->id));

            send_json_message('The diagram was successfully recorded', 'success');
        } else {
            send_json_message('The diagram could not be recorded', 'danger');
        }
    }

    public function get_diagnostic_diagram() {
        $order_id = $this->input->post('order_id');
        $order = (object) $this->order_model->get_values($order_id);

        if (empty($order->location_diagram_id)) {
            send_json_message('There is no diagram for this job site yet', 'warning');
            return false;
        } else {
            $location_diagram = $this->location_diagram_model->get($order->location_diagram_id);
            send_json_data(array('dataurl' => $location_diagram->diagram));
        }
    }
}
