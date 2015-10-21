<?php
class Maintenance_task_model extends MY_Model {
    public $table = 'miniant_maintenance_tasks';

    public function exists($assignment_id, $maintenance_task_template_id) {
        $record = $this->get(compact('assignment_id', 'maintenance_task_template_id'));
        return !empty($record);
    }

    public function update_task($status, $maintenance_task_template_id, $assignment_id) {
        $record = $this->get(compact('assignment_id', 'maintenance_task_template_id'), true);

        $completed_date = time();
        $completed_by = $this->session->userdata('user_id');

        if (empty($record) && $status) {
            $this->add(compact('assignment_id', 'maintenance_task_template_id', 'completed_date', 'completed_by'));
        } else if (!empty($record)) {
            if ($status) {
                $this->edit($record->id, compact('assignment_id', 'maintenance_task_template_id', 'completed_date', 'completed_by'));
            } else {
                $this->delete(array('id' => $record->id));
            }
        }
        return true;
    }

    public function all_tasks_completed($assignment_id) {
        $assignment = $this->assignment_model->get($assignment_id);
        $unit = $this->unit_model->get($assignment->unit_id);

        $template_tasks = $this->maintenance_task_template_model->get(array('unit_type_id' => $unit->unit_type_id));

        foreach ($template_tasks as $template_task) {
            if (!($completed_task = $this->maintenance_task_model->get(array('maintenance_task_template_id' => $template_task->id, 'assignment_id' => $assignment_id)))) {
                return false;
            }
        }

        return true;
    }
}
