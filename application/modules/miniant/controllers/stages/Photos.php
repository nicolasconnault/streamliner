<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Photos extends Stage_controller {

    public function index($assignment_id, $type=null) {
        $this->assignment = $this->assignment_model->get_values($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }

        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        // Determine if this is pre-job or post-job depending on order statuses
        // This should only be used in last resort, use the extra_param field of the workflow_stages table when possible
        if (is_null($type)) {
            $type = ($this->order_model->has_statuses($order->id, array('POST-JOB COMPLETE', 'PRE-JOB SITE PHOTOS UPLOADED'), 'AND')) ? 'post-job' : 'pre-job';
        }

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'photos', 'param' => $assignment_id, 'extra_param' => $type, 'module' => 'miniant'));

        $help = ($type == 'pre-job') ? 'Take photos of the site before any work is done, then upload them here.' : 'Take photos of the site after all work is completed, then upload them here.';

        $title = ucfirst($type) . ' site photos: ' . $this->order_model->get_reference_id($order->id);

        $this->load_stage_view(array(
            'extra_param' => $type,
            'type' => $type,
            'dialog' => $this->get_signature_required_dialog($type),
            'help' => $help
        ));
    }

    public function get_signature_required_dialog($type) {
        if ($type == 'pre-job') {
            return null;
        }

        $order = (object) $this->order_model->get_values($this->assignment->order_id);

        $this->load->library('Dialog');
        $this->dialog->initialise(array());

        $this->dialog->add_question(array(
            'id' => 'signature_required',
            'shown' => !$this->order_model->has_statuses($this->assignment->order_id, array('POST-JOB SITE PHOTOS UPLOADED')),
            'text' => 'Has the office asked you to obtain a signature from the client?',
            'answers' => array(
                array(
                    'text' => 'Yes',
                    'ids_to_show' => array('continue'),
                    'triggers' => array(array('system' => 'order', 'document_id' => $this->assignment->order_id, 'event_name' => 'set_signature_required', 'module' => 'miniant')),
                ),
                array(
                    'text' => 'No',
                    'ids_to_show' => array('continue'),
                    'triggers' => array(array('system' => 'order', 'document_id' => $this->assignment->order_id, 'event_name' => 'set_signature_not_required', 'module' => 'miniant')),
                ),
                )
            ));
        $this->dialog->add_question(array(
            'id' => 'continue',
            'shown' => $this->order_model->has_statuses($this->assignment->order_id, array('POST-JOB SITE PHOTOS UPLOADED')),
            'text' => '&nbsp;',
            'answers' => array(
                array(
                    'text' => 'Continue',
                    'url' => base_url().'miniant/stages/photos/process/'.$this->assignment->id.'/'.$type,
                ))
            ));

        return $this->dialog->output();
    }

    public function process($assignment_id, $type) {
        $assignment = $this->assignment_model->get_values($assignment_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        // $workflows_for_which_photo_are_required = array('Breakdown');
        $workflows_for_which_photo_are_required = array('');

        if (!$this->files_exist_in_upload_folder($assignment_id, $type) && $type == 'pre-job' && in_array($order_type, $workflows_for_which_photo_are_required)) {
            add_message('Please upload at least one photo', 'warning');
            return $this->index($assignment_id);
        }

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'photos', 'param' => $assignment_id, 'extra_param' => $type, 'module' => 'miniant'));

        if ($type == 'pre-job') {
            trigger_event('prejobsite_photos_uploaded', 'order', $order->id, false, 'miniant');
        } else {
            trigger_event('postjobsite_photos_uploaded', 'order', $order->id, false, 'miniant');
        }

        add_message('Photos were successfully recorded');
        redirect($this->workflow_manager->get_next_url());
    }

    private function files_exist_in_upload_folder($assignment_id, $type='pre-job') {
        $assignment = $this->assignment_model->get_values($assignment_id);
        $dir = "files/uploads/order/site-$type/$assignment->order_id";

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
