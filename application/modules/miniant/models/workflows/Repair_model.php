<?php
require_once(APPPATH.'modules/miniant/models/workflows/Workflow_conditions_model.php');
class Repair_model extends Workflow_conditions_model {

    public function check_parts_used_refrigerants_used($assignment_id) {
        $ci = get_instance();
        $assignment = $ci->assignment_model->get_values($assignment_id);
        $unit = (object) $this->unit_model->get_values($assignment->unit_id);
        return !in_array($unit->type, array('Evaporative A/C'));
    }

    public function check_diagnostic_report_parts_used($assignment_id) {
        $ci = get_instance();
        return !$ci->assignment_model->get_no_issues_found($assignment_id);
    }

    public function check_diagnostic_report_required_parts($assignment_id) {
        $ci = get_instance();
        return !$ci->assignment_model->get_no_issues_found($assignment_id);
    }
}
