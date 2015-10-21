<?php
require_once(APPPATH.'modules/miniant/models/workflows/Workflow_conditions_model.php');

class Installation_model extends Workflow_conditions_model {

    public function check_parts_used_refrigerants_used($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        $unit = (object) $this->unit_model->get_values($assignment->unit_id);
        return !in_array($unit->type, array('Evaporative A/C'));
    }

    public function check_parts_used_order_dowd($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$this->check_parts_used_refrigerants_used($assignment_id) &&
                $ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED")) &&
                $this->is_senior_technician($assignment_id);
    }

    public function check_parts_used_job_list($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$this->check_parts_used_refrigerants_used($assignment_id) &&
                $ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED")) &&
                !$this->is_senior_technician($assignment_id);
    }

    public function check_parts_used_installation_checklist($assignment_id) {
        $ci = get_instance();
        return !$ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED"));
    }

    public function check_parts_used_unit_details($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$this->check_parts_used_refrigerants_used($assignment_id) &&
                !$ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED"));
    }

    public function check_refrigerants_used_order_dowd($assignment_id) {
        $ci = get_instance();
        return $ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED")) && $this->is_senior_technician($assignment_id);
    }

    public function check_refrigerants_used_job_list($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED")) &&
                !$this->is_senior_technician($assignment_id);
    }

    public function check_refrigerants_used_unit_details($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$ci->assignment_model->has_statuses($assignment_id, array("INSTALLATION TASKS COMPLETED"));
    }

    public function check_photos_job_list($assignment_id, $type) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $type == 'post-job' && !$this->order_model->has_statuses($assignment->order_id, array('SIGNATURE REQUIRED'));
    }

    public function check_photos_signature($assignment_id, $type) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $type == 'post-job' && $this->order_model->has_statuses($assignment->order_id, array('SIGNATURE REQUIRED'));
    }
}
