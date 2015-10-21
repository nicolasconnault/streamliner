<?php
require_once(APPPATH.'modules/miniant/models/workflows/Workflow_conditions_model.php');

class Maintenance_model extends Workflow_conditions_model {
    public function check_maintenance_checklist_diagnostic_report($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $assignment->diagnostic_required && $assignment->diagnostic_authorised;
    }

    public function check_maintenance_checklist_unit_details($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$this->check_maintenance_checklist_diagnostic_report($assignment_id) && !$ci->order_model->has_statuses($assignment->order_id, array("UNIT WORK COMPLETE"));
    }

    public function check_maintenance_checklist_order_dowd($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$this->check_maintenance_checklist_diagnostic_report($assignment_id) &&
                $ci->order_model->has_statuses($assignment->order_id, array("UNIT WORK COMPLETE"), 'OR', array(), false) &&
                $this->is_senior_technician($assignment_id);
    }

    public function check_maintenance_checklist_job_list($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return !$this->check_maintenance_checklist_diagnostic_report($assignment_id) &&
                $ci->order_model->has_statuses($assignment->order_id, array("UNIT WORK COMPLETE"), 'OR', array(), false) &&
                !$this->is_senior_technician($assignment_id);
    }

    public function check_diagnostic_report_parts_used($assignment_id) {
        $ci = get_instance();
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
        $ci = get_instance();
        return !!$ci->assignment_model->has_statuses($assignment_id, array("SQ APPROVED"));
    }


    public function check_parts_used_refrigerants_used($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        $unit = (object) $this->unit_model->get_values($assignment->unit_id);
        return !in_array($unit->type, array('Evaporative A/C'));
    }

    public function check_parts_used_required_parts($assignment_id) {
        return $this->check_diagnostic_report_required_parts($assignment_id) && !$this->check_parts_used_refrigerants_used($assignment_id);
    }

    public function check_parts_used_dowds($assignment_id) {
        return !$this->check_diagnostic_report_required_parts($assignment_id) && !$this->check_parts_used_refrigerants_used($assignment_id);
    }

    public function check_refrigerants_used_required_parts($assignment_id) {
        return $this->check_diagnostic_report_required_parts($assignment_id);
    }

    public function check_refrigerants_used_dowds($assignment_id) {
        return !$this->check_diagnostic_report_required_parts($assignment_id);
    }

    public function check_dowds_unit_details($assignment_id) {
        return !$this->check_dowds_order_dowd($assignment_id);
    }

    public function check_dowds_order_dowd($assignment_id) {
        // Only go to the job dowd once all units are ready for it
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        return $ci->order_model->has_statuses($assignment->order_id, array("UNIT WORK COMPLETE"));
    }

    public function check_dowds_job_list($assignment_id) {
        return !$this->is_senior_technician($assignment_id);
    }
}
