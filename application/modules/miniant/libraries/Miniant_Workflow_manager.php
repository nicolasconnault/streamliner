<?php

require_once(APPPATH.'/libraries/Workflow_manager.php');

class Miniant_Workflow_Manager extends Workflow_Manager {
    public $is_senior_technician=null;

    public function get_next_url() {
        $next_url = parent::get_next_url();
        $ci = get_instance();

        if (strstr($next_url, 'job_list')) {
            $assignment_id = $this->current_param;
            $assignment = $ci->assignment_model->get($assignment_id);
            $technician_id = $ci->session->userdata('user_id');
            $order_technician = $ci->order_technician_model->get(array('order_id' => $assignment->order_id, 'technician_id' => $technician_id), true);
            $is_technician = user_has_role($ci->session->userdata('user_id'), 'Technician');
            if ($is_technician) {
                trigger_event('is_complete', 'order_technician', $order_technician->id, false, 'miniant');
            }
        }

        return $next_url;
    }

    public function is_senior_technician($assignment_id) {
        if (is_null($this->is_senior_technician)) {
            $ci = get_instance();
            $assignment = $ci->assignment_model->get($assignment_id);
            $technician_id = $ci->session->userdata('user_id');
            $order = $ci->order_model->get($assignment->order_id);
            $this->is_senior_technician = $technician_id == $order->senior_technician_id;
        }

        return $this->is_senior_technician;
    }

    public function skip_stage_conditions($assignment_id, $next_stage_to_check) {
        $ci = get_instance();
        $is_technician = user_has_role($ci->session->userdata('user_id'), 'Technician');
        if (!$is_technician) {
            return false;
        }

        return $next_stage_to_check->senior_technician_only && !$this->is_senior_technician($this->current_param);
    }
}
