<?php
class Dowd_model extends MY_Model {
    public $table = 'miniant_dowds';

    public function get_formatted_description($dowd_id, $diagnostic_issue_id) {
        $diagnostic_issue = $this->diagnostic_issue_model->get($diagnostic_issue_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $diagnostic_issue->diagnostic_id), true);

        $description = $this->get($dowd_id)->description;

        $description = str_replace('[[issues]]', $diagnostic_issue->issue_type_name . ' ' . $diagnostic_issue->part_type_name, $description);
        $unit_isolated = ($assignment->isolated_and_tagged) ? '. Unit isolated and danger tagged' : '';
        $description = str_replace('(optional:isolated system|danger tagged)', $unit_isolated, $description);

        return $description;
    }

    public function get_formatted_order_dowd($dowd_id, $order_id) {
        $this->load->model('miniant/diagnostic_tree_model');
        if (empty($dowd_id)) {
            return null;
        }

        $order = (object) $this->order_model->get_values($order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $description = $this->get($dowd_id)->description;

        if ($order_type == 'Installation') {
            $description = str_replace('[[installation_quotation_number]]', $order->installation_quotation_number, $description);
        } else if ($order_type == 'Repair') {
            // Only one assignment per repair order
            $assignment = $this->assignment_model->get(array('order_id' => $order_id), true);
            $tasks = $this->repair_task_model->get(array('assignment_id' => $assignment->id));
            $tasks_string = '';
            $issues_string = '';

            foreach ($tasks as $task) {
                $tasks_string .= $task->past_name . ', ';
                $issues_string .= $task->issue_description . ', ';
            }

            // Look for new, fixed issues
            $fixed_diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $assignment->diagnostic_id, 'can_be_fixed_now' => true));
            if (!empty($fixed_diagnostic_issues)) {
                foreach ($fixed_diagnostic_issues as $diagnostic_issue) {
                    $part_type_issue_type_steps = $this->diagnostic_tree_model->get_part_type_issue_type_steps($this->part_type_issue_type_model->get_id(array('part_type_id' => $diagnostic_issue->part_type_id, 'issue_type_id' => $diagnostic_issue->issue_type_id)));

                    if (empty($part_type_issue_type_steps)) {
                        continue;
                    }

                    foreach ($part_type_issue_type_steps as $part_type_issue_type_step) {
                        $step = $this->step_model->get($part_type_issue_type_step->step_id);

                        if (strstr($step->past_tense, '[[part]]')) {
                            $tasks_string .= str_replace('[[part]]', $diagnostic_issue->part_type_name, $step->past_tense) .', ';
                        } else {
                            $tasks_string .= $step->past_tense . ' ' . $diagnostic_issue->part_type_name . ', ';
                        }
                        $issues_string .= $diagnostic_issue->issue_type_name . ' ' . $diagnostic_issue->part_type_name . ', ';
                    }
                }
            }

            $tasks_string = ucfirst(strtolower(substr($tasks_string, 0, -2)));
            $issues_string = ucfirst(strtolower(substr($issues_string, 0, -2)));

            $description = str_replace('[[tasks]]', $tasks_string, $description);
            $description = str_replace('[[issues]]', $issues_string, $description);

            // Look for new issues
            $new_diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $assignment->diagnostic_id, 'can_be_fixed_now' => false));

            if (!empty($new_diagnostic_issues)) {
                $new_issues_string = '';
                foreach ($new_diagnostic_issues as $diagnostic_issue) {
                    $new_issues_string .= $diagnostic_issue->issue_type_name . ' ' . strtolower($diagnostic_issue->part_type_name) . ', ';
                }
                $new_issues_string = substr($new_issues_string, 0, -2);
                $description = str_replace('[[new-issues]]', $new_issues_string, $description);
            }
        }

        return $description;
    }
}
