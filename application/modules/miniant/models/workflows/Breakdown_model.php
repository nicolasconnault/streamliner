<?php
require_once(APPPATH.'modules/miniant/models/workflows/Workflow_conditions_model.php');
/**
 * TODO This file should be automatically generated from a GUI. Names of methods are based on entries in workflow_stage_stages table
 * When multiple methods return true (i.e., a stage can lead to several other stages, more than one of which has its conditions satisfied),
 * the earliest stage is used (lowest workflow_stages.stage_number)
 */
class Breakdown_model extends Workflow_conditions_model {

    public function check_photos_unit_serial($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$ci->order_model->has_statuses($assignment->order_id, array('POST-JOB SITE PHOTOS UPLOADED'));
    }

    public function check_photos_signature($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $ci->order_model->has_statuses($assignment->order_id, array('POST-JOB SITE PHOTOS UPLOADED'));
    }

    public function check_diagnostic_report_parts_used($assignment_id) {
        $ci = get_instance();

        // If no repairs or SQ authorised, the NO ISSUES FOUND status is used. Maybe it should have a NO REPAIRS AUTHORISED status instead
        if ($ci->assignment_model->has_statuses($assignment_id, array("NO ISSUES FOUND"))) {
            return false;
        }

        $assignment = $ci->assignment_model->get_values($assignment_id);
        $diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $assignment->diagnostic_id));

        foreach ($diagnostic_issues as $issue) {
            if ($issue->can_be_fixed_now) {
                return true;
            }
        }

        return false;
    }

    public function check_diagnostic_report_required_parts($assignment_id) {
        $ci = get_instance();
        return $ci->assignment_model->has_statuses($assignment_id, array("SQ APPROVED"));
    }

    public function check_diagnostic_report_dowds($assignment_id) {
        return !$this->check_diagnostic_report_required_parts($assignment_id);
    }

    // Only go to refrigerants stage if unit type is of refrigerated type
    public function check_parts_used_refrigerants_used($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        $unit = (object) $this->unit_model->get_values($assignment->unit_id);
        return !in_array($unit->type, array('Evaporative A/C'));
    }

    public function check_parts_used_required_parts($assignment_id) {
        $ci = get_instance();
        return $ci->assignment_model->has_statuses($assignment_id, array("SQ APPROVED"));
    }

    public function check_parts_used_dowds($assignment_id) {
        return !$this->check_parts_used_refrigerants_used($assignment_id) && !$this->check_parts_used_required_parts($assignment_id);
    }

    public function check_refrigerants_used_required_parts($assignment_id) {
        $ci = get_instance();
        return $ci->assignment_model->has_statuses($assignment_id, array("SQ APPROVED"));
    }

    public function check_refrigerants_used_dowds($assignment_id) {
        return !$this->check_refrigerants_used_required_parts($assignment_id);
    }

    public function check_dowds_unit_serial($assignment_id) {
        return !$this->check_dowds_order_dowd($assignment_id) && $this->is_senior_technician($assignment_id);
    }

    public function check_dowds_unit_details($assignment_id) {
        return !$this->check_dowds_order_dowd($assignment_id) && !$this->is_senior_technician($assignment_id);
    }

    public function check_dowds_order_dowd($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $ci->order_model->has_statuses($assignment->order_id, array("UNIT WORK COMPLETE"), 'OR', array(), false);
    }

    public function check_dowds_job_list($assignment_id) {
        return !$this->is_senior_technician($assignment_id);
    }
}
