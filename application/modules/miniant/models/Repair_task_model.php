<?php
class Repair_task_model extends MY_Model {
    public $table = 'miniant_repair_tasks';

    // Adding a bit more visual info to each task
    public function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {
        $tasks = parent::get($id_or_fields, $first_only, $order_by, $select_fields);

        if ($first_only || !is_array($tasks)) {
            $tasks = array($tasks);
        }

        if (!empty($tasks)) {
            foreach ($tasks as $key => $task) {
                if (empty($task)) {
                    continue;
                }

                $diagnostic_issue = $this->diagnostic_issue_model->get_from_cache($task->diagnostic_issue_id);
                $tasks[$key]->name = $this->step_model->get_name($task->step_id) . ': ' . $this->part_type_model->get_name($diagnostic_issue->part_type_id);

                $step_past = $this->step_model->get_past_tense($task->step_id);
                $issue = $this->issue_type_model->get_name($diagnostic_issue->issue_type_id);

                if (strstr($step_past, '[[part]]')) {
                    $step_past = str_replace('[[part]]', $this->part_type_model->get_name($diagnostic_issue->part_type_id), $step_past);
                    $tasks[$key]->past_name = $step_past;
                } else {
                    $tasks[$key]->past_name = $step_past . ' ' . $this->part_type_model->get_name($diagnostic_issue->part_type_id);
                }

                $tasks[$key]->past_name = strtolower($tasks[$key]->past_name);
                $tasks[$key]->issue_description = $issue . ' ' . $this->part_type_model->get_name($diagnostic_issue->part_type_id);
            }
        }

        if ($first_only || !is_array($id_or_fields)) {
            return reset($tasks);
        }

        return $tasks;
    }

    public function update_task($status, $task_id) {
        $record = $this->get($task_id);

        if ($status) {
            $completed_date = time();
            $completed_by = $this->session->userdata('user_id');
        } else {
            $completed_date = null;
            $completed_by = null;
        }

        $this->edit($task_id, compact('completed_date', 'completed_by'));
        return true;
    }
}
