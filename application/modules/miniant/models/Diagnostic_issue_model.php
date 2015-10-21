<?php
class Diagnostic_Issue_Model extends MY_Model {
    public $table = 'miniant_diagnostic_issues';

    // Add some descriptive data
    public function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {

        $diagnostic_issues = parent::get($id_or_fields, $first_only, $order_by, $select_fields);

        if (!empty($diagnostic_issues) && !is_array($diagnostic_issues)) {
            $diagnostic_issues->part_type_name = $this->part_type_model->get($diagnostic_issues->part_type_id, true, null, array('name'))->name;
            $diagnostic_issues->issue_type_name = $this->issue_type_model->get($diagnostic_issues->issue_type_id, true, null, array('name'))->name;
            $diagnostic_issues->can_be_fixed_now_text = ($diagnostic_issues->can_be_fixed_now) ? 'Yes' : 'No';
        } else if (!empty($diagnostic_issues)) {
            foreach ($diagnostic_issues as $id => $issue) {
                $diagnostic_issues[$id]->part_type_name = $this->part_type_model->get($issue->part_type_id, true, null, array('name'))->name;
                $diagnostic_issues[$id]->issue_type_name = $this->issue_type_model->get($issue->issue_type_id, true, null, array('name'))->name;
                $diagnostic_issues[$id]->can_be_fixed_now_text = ($issue->can_be_fixed_now) ? 'Yes' : 'No';
            }
        }

        return $diagnostic_issues;
    }

    public function create_parts($diagnostic_issue, $needs_sq=false) {
        $this->load->model('miniant/diagnostic_tree_model');
        $diagnostic = $this->diagnostic_model->get($diagnostic_issue->diagnostic_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $diagnostic->id), true);

        $part_type_issue_type = $this->diagnostic_tree_model->get_part_type_issue_type($diagnostic_issue->part_type_id, $diagnostic_issue->issue_type_id);
        if (is_array($part_type_issue_type)) {
            $part_type_issue_type = reset($part_type_issue_type);
        }
        $steps = $this->diagnostic_tree_model->get_part_type_issue_type_steps($part_type_issue_type->id);
        foreach ($steps as $step) {
            $required_parts = $this->diagnostic_tree_model->get_required_parts($step->id);

            foreach ($required_parts as $required_part) {
                $params = array(
                    'assignment_id' => $assignment->id,
                    'part_type_id' => $required_part->part_type_id,
                    'needs_sq' => $needs_sq,
                    'diagnostic_issue_id' => $diagnostic_issue->id
                );

                if (!($this->part_model->get($params))) {
                    $this->part_model->add($params);
                }
            }
        }
    }
}
