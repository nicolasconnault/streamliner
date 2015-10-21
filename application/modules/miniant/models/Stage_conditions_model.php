<?php
class Stage_conditions_model extends CI_Model {

    public function get_next_uncompleted_assignment_for_stage($assignment_id, $stage_name, $extra_param=null) {
        extract($this->get_assignment_info($assignment_id));
        $assignments = $this->assignment_model->get(array('order_id' => $order->id, 'appointment_date' => $assignment->appointment_date));

        foreach ($assignments as $assignment) {
            $is_completed = $this->is_completed($assignment->id, $stage_name, $extra_param, 'assignment');
            if ($is_completed != 'Yes') {
                return $assignment;
            }
        }
        return null;
    }

    public function get_first_uncompleted_and_required_stage($assignment_id, $stage_name, $extra_param=null) {
        extract($this->get_assignment_info($assignment_id));

        $current_stage = $this->stage_model->get(array('name' => $stage_name), true);

        foreach ($this->workflow_manager->stages as $stage_key => $stage) {
            if ($stage_key == 0) {
                continue;
            }

            if ($stage_name == $stage->stage_name) {
                return false; // No need to continue
            }

            $this_assignment_id = (!empty($stage->assignment_id) && is_int($stage->assignment_id)) ? $stage->assignment_id : $assignment_id;
            // $is_stage_completed = $this->is_completed($this_assignment_id, $stage->stage_name, $stage->extra_param, $current_stage->granularity);
            $is_stage_completed = $stage->completion_status[$assignment_id];

            if ($this->is_required($this_assignment_id, $stage, $extra_param) && $is_stage_completed != 'Yes') {
                // If this is a senior_only stage, skip it if the technician isn't senior
                if ($stage->senior_technician_only && $technician_id != $order->senior_technician_id) {
                    continue;
                }

                $stage->assignment_id = $is_stage_completed;
                return $stage;
            }
        }

        return false;
    }

    public function is_required($assignment_id, $workflow_stage, $extra_param=null) {
        if (!$workflow_stage->required) {
            return false;
        }

        // A stage may be generally required, but made non-applicable due to the info entered in other stages
        if (method_exists($this, $workflow_stage->stage_name.'_required')) {
            return $this->{$workflow_stage->stage_name.'_required'}($assignment_id, $workflow_stage, $extra_param);
        } else {
            die("No method called {$workflow_stage->stage_name}_required in stage_conditions_model");
        }
    }

    public function is_completed($assignment_id, $stage_name, $extra_param=null, $granularity='assignment') {
        extract($this->get_assignment_info($assignment_id));

        $assignment_ids = array($assignment_id);

        if ($granularity == 'order') {
            $assignment_ids = array();
            if ($technician_id == $order->senior_technician_id) {
                $units = $this->assignment_model->get_units($assignment_id);
            } else {
                $units = $this->assignment_model->get_units($assignment_id, $technician_id);
            }

            foreach ($units as $unit) {
                $assignment_ids[] = $unit->assignment_id;
            }
        }

        $is_completed = true;
        $uncompleted_assignment = null;

        foreach ($assignment_ids as $assignment_id) {
            if (method_exists($this, $stage_name.'_complete')) {
                $assignment_is_completed = $this->{$stage_name.'_complete'}($assignment_id, $extra_param);

                if (!$assignment_is_completed && is_null($uncompleted_assignment)) {
                    $uncompleted_assignment = $assignment_id;
                }

                $is_completed = $is_completed && $assignment_is_completed;
            } else {
                die("No method called {$stage_name}_complete in stage_conditions_model");
            }
        }

        if ($is_completed) {
            return 'Yes';
        } else {
            return $uncompleted_assignment;
        }
    }

    public function assignment_details_complete($assignment_id) {
        extract($this->get_assignment_info($assignment_id));
        return $this->order_model->has_statuses($order->id, array('STARTED'));
    }

    public function photos_complete($assignment_id, $type) {
        extract($this->get_assignment_info($assignment_id));

        if ($type == 'pre-job') {
            return $this->order_model->has_statuses($order->id, array('PRE-JOB SITE PHOTOS UPLOADED'));
        } else if ($type == 'post-job') {
            return $this->order_model->has_statuses($order->id, array('POST-JOB SITE PHOTOS UPLOADED'));
        } else {
            die('Photos must be called with pre-job or post-job extra param');
        }
    }

    public function unit_serial_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('UNIT SERIAL NUMBER ENTERED'));
    }

    public function location_diagram_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('LOCATION INFO RECORDED'));
    }

    public function unit_details_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('UNIT DETAILS RECORDED'));
    }

    public function unit_photos_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('UNIT PHOTOS UPLOADED'));
    }

    public function installation_checklist_complete($assignment_id) {
        $result = $this->assignment_model->has_statuses($assignment_id, array('INSTALLATION TASKS COMPLETED'));
        return $result;
    }

    public function maintenance_checklist_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('MAINTENANCE TASKS COMPLETED'));
    }

    public function repair_checklist_complete($assignment_id) {
        extract($this->get_assignment_info($assignment_id));
        return $this->order_model->has_statuses($order->id, array('REPAIR TASKS COMPLETED'));
    }

    public function diagnostic_report_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('ISSUES DIAGNOSED'));
    }

    public function parts_used_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('USED PARTS RECORDED'));
    }

    public function refrigerants_used_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('USED REFRIGERANT RECORDED'));
    }

    public function required_parts_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('REQUIRED PARTS RECORDED'));
    }

    public function dowds_complete($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('DOWD RECORDED'));
    }

    public function order_dowd_complete($assignment_id) {
        extract($this->get_assignment_info($assignment_id));
        return $this->order_model->has_statuses($order->id, array('DOWD RECORDED'));
    }

    public function postjob_checklist_complete($assignment_id) {
        extract($this->get_assignment_info($assignment_id));
        return $this->order_model->has_statuses($order->id, array('POST-JOB COMPLETE'));
    }

    public function signature_complete($assignment_id) {
        $this->load->model('miniant/order_technician_model');
        extract($this->get_assignment_info($assignment_id));
        return $this->order_technician_model->has_statuses($order_technician->id, array('SIGNED BY CLIENT'));
    }

    public function office_notes_complete($assignment_id) {
        $this->load->model('miniant/order_model');
        extract($this->get_assignment_info($assignment_id));
        return $this->order_model->has_statuses($order->id, array('OFFICE NOTES SIGHTED'));
    }

    public function job_list_complete($assignment_id) {
        return true;
    }

    public function assignment_details_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function photos_required($assignment_id, $workflow_stage, $type) {

        return $workflow_stage->required;
    }

    public function unit_serial_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function location_diagram_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function unit_details_required($assignment_id, $workflow_stage) {
        extract($this->get_assignment_info($assignment_id));
        if ($order_type == 'Installation') {
            return false;
        }
        return $workflow_stage->required;
    }

    public function unit_photos_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function installation_checklist_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function maintenance_checklist_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function repair_checklist_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function diagnostic_report_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function parts_used_required($assignment_id, $workflow_stage) {
        extract($this->get_assignment_info($assignment_id));

        if ($order_type == 'Installation') {
            return true;
        }
        if (!$this->assignment_model->has_statuses($assignment_id, array('REPAIRS APPROVED'))) {
            return false;
        }

        if ($assignment->no_issues_found) {
            return false;
        }

        return $workflow_stage->required;
    }

    public function refrigerants_used_required($assignment_id, $workflow_stage) {
        extract($this->get_assignment_info($assignment_id));
        if ($unit->type == 'Evaporative A/C') {
            return false;
        }

        if (!$this->assignment_model->has_statuses($assignment_id, array('REPAIRS APPROVED'))) {
            return false;
        }

        if ($assignment->no_issues_found) {
            return false;
        }

        return $workflow_stage->required;
    }

    public function required_parts_required($assignment_id, $workflow_stage) {
        if (!$this->assignment_model->has_statuses($assignment_id, array('SQ APPROVED'))) {
            return false;
        } else {
            return $workflow_stage->required;
        }
    }

    public function dowds_required($assignment_id, $workflow_stage) {
        extract($this->get_assignment_info($assignment_id));
        if ($order_type == 'Installation') {
            return false;
        } else if ($order_type == 'Maintenance' || $order_type == 'Service') {
            if ($assignment->no_issues_found) {
                return false;
            }

            if ($this->order_model->has_statuses($order->id, array('UNIT WORK COMPLETE'))) {
                return false;
            }
        }
        return $workflow_stage->required;
    }

    public function order_dowd_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function postjob_checklist_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function signature_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function office_notes_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    public function job_list_required($assignment_id, $workflow_stage) {
        return $workflow_stage->required;
    }

    private function get_assignment_info($assignment_id) {
        $this->load->model('miniant/order_technician_model');

        $assignment = $this->assignment_model->get($assignment_id);
        if (!is_object($assignment)) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);die();
        }
        $unit = (object) $this->unit_model->get_values($assignment->unit_id);
        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = Stage_controller::get_technician_id($assignment_id);
        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);
        return compact('assignment', 'order', 'order_type', 'technician_id', 'order_technician', 'unit');
    }
}
