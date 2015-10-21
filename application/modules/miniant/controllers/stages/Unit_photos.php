<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Unit_Photos extends Stage_controller {

    public function index($assignment_id, $type=null) {
        require_capability('orders:editunitdetails');

        $this->assignment = $this->assignment_model->get_values($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = $this->session->userdata('user_id');

        parent::update_time($order->id);

        if (is_null($type)) {
            $type = ($this->order_model->has_statuses($order->id, array('POST-JOB COMPLETE', 'PRE-JOB SITE PHOTOS UPLOADED'), 'AND')) ? 'post-job' : 'pre-job';
        }

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'unit_photos', 'param' => $assignment_id, 'module' => 'miniant'));

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        $help = 'Upload photos of the unit you are working on only (e.g., Indoor, Outdoor, Thermostat)';

        $title = 'Equipment photos';

        $this->load_stage_view(array(
             'units' => $units,
        ));

    }

    public function process($assignment_id) {
        $assignment = $this->assignment_model->get_values($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        if (!$this->files_exist_in_upload_folder($assignment_id) && in_array($order_type, array('Breakdown'))) {
            add_message('Please upload at least one photo', 'warning');
            return $this->index($assignment_id);
        }

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'unit_photos', 'param' => $assignment_id, 'extra_param' => $type, 'module' => 'miniant'));

        add_message('Equipment photos were successfully recorded');
        trigger_event('unit_photos_uploaded', 'assignment', $assignment_id, false, 'miniant');
        redirect($this->workflow_manager->get_next_url());
    }

    private function files_exist_in_upload_folder($assignment_id) {
        $assignment = $this->assignment_model->get_values($assignment_id);
        $dir = "application/modules/miniant/files/uploads/assignment/$assignment_id/$assignment->unit_id";

        if (!is_readable($dir)) return NULL;

        $handle = opendir($dir);

        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && $entry != 'thumbs') {
                return true;
            }
        }

        return false;
    }
}
