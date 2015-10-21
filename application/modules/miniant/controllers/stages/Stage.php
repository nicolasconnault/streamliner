<?php

class Stage_controller extends MY_Controller {
    public $assignment;

    public function set_selected_unit($selected_unit_id) {
        $this->session->set_userdata('selected_unit_id', $selected_unit_id);
    }

    public function set_selected_tenancy($selected_tenancy_id) {
        $this->session->set_userdata('selected_tenancy_id', $selected_tenancy_id);
    }

    public function get_units($assignment_id, $technician_id, $senior_technician_id) {
        // Show all units if the technician is the senior one. We can differentiate between his and others' in the view files
        if ($technician_id == $senior_technician_id) {
            $units = $this->assignment_model->get_units($assignment_id);
        } else {
            $units = $this->assignment_model->get_units($assignment_id, $technician_id);
        }

        return $units;
    }

    public function delete_attachment($attachment_id, $unit_id, $field_name, $assignment_id) {
        $this->unit_attachment_model->delete($attachment_id);
        $this->unit_model->edit($unit_id, array($field_name => null));
        add_message('The attachment was successfully deleted');
        return $this->index($assignment_id);
    }

    public function load_stage_view($params) {
        $this->set_selected_unit($this->assignment->unit_id);

        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = $this->get_technician_id($this->assignment->id);

        if (empty($params['help'])) {
            $params['help'] = $this->workflow_manager->current_stage->stage_description;
        }

        if (empty($params['title'])) {
            $params['title'] = "$order_type  {$this->workflow_manager->current_stage->stage_label}";
        }

        $title_options = array('title' => $params['title'],
                               'help' => $params['help'],
                               'icons' => array());

        $params['assignment'] = $this->assignment;
        $params['title_options'] = $title_options;
        $params['order'] = $order;
        $params['office_messages'] = $this->message_model->get(array('document_type' => 'order', 'document_id' => $order->id));
        $params['order_type'] = $order_type;
        $params['stages'] = $this->workflow_manager->stages;
        $params['module'] = 'miniant';
        $params['current_stage'] = $this->workflow_manager->current_stage;
        $params['wide_layout'] = true;
        $params['content_view'] = "stages/{$this->workflow_manager->current_stage->stage_name}";

        if (empty($params['units'])) {
            $params['units'] = $this->get_units($this->assignment->id, $technician_id, $params['order']->senior_technician_id);
        }

        // If not senior, hide stages that are only for senior
        $unit_completions = array();
        $unit_completion_counts = array();

        if ($params['order']->senior_technician_id != $technician_id) {
            foreach ($params['stages'] as $stage_key => $stage) {
                if ($stage->senior_technician_only) {
                    unset($params['stages'][$stage_key]);
                }
            }
        }

        // Remove first and last stages from the list
        array_shift($params['stages']);
        array_pop($params['stages']);

        foreach ($params['units'] as $unit_key => $unit) {
            $unit_completion_counts[$unit_key] = 0;

            foreach ($params['stages'] as $stage_key => $stage) {
                $params['stages'][$stage_key]->completion_status[$unit->assignment_id] = $this->stage_conditions_model->is_completed($unit->assignment_id, $stage->stage_name, $stage->extra_param);
                if (empty($unit_completions[$unit_key])) {
                    $unit_completions[$unit_key] = array();
                }
                $unit_completions[$unit_key][$stage_key] = $params['stages'][$stage_key]->completion_status[$unit->assignment_id];

                if ($params['stages'][$stage_key]->completion_status[$unit->assignment_id] == 'Yes') {
                    $unit_completion_counts[$unit_key]++;
                } else {
                    $params['stages'][$stage_key]->assignment_id = $unit->assignment_id;
                    $params['stages'][$stage_key]->completion_status[$unit->assignment_id] = 'No';
                    $unit_completions[$unit_key][$stage_key] = 'No';
                }
            }
        }

        $last_completed_indices = array();
        $second_last_completed_indices = array();
        $last_uncompleted_indices = array();

        foreach ($unit_completions as $unit_key => $stages) {

            $completion_count = $unit_completion_counts[$unit_key];
            $last_completed_indices[$unit_key] = -1;
            $last_uncompleted_indices[$unit_key] = -1;
            $second_last_completed_indices[$unit_key] = -1;

            foreach ($stages as $stage_key => $stage_completion) {
                if ($stage_completion == 'Yes' && $completion_count == 1) {
                    $last_completed_indices[$unit_key] = $stage_key;
                } else if ($stage_completion == 'Yes' && $completion_count > 1) {
                    $second_last_completed_indices[$unit_key] = $last_completed_indices[$unit_key];
                    $last_completed_indices[$unit_key] = $stage_key;
                } else if ($stage_completion == 'No') {
                    $last_uncompleted_indices[$unit_key] = $stage_key;
                }
            }
        }

        // Only hyperlink the completed or last uncompleted stages.
        // If a workflow skips a couple of stages because they're not applicable, we can just flag uncompleted stages between the last two completed stages as N/A with no link
        foreach ($params['units'] as $unit_key => $unit) {
            $make_following_stages_non_accessible = false;

            foreach ($params['stages'] as $stage_key => $stage) {
                $is_required = $this->stage_conditions_model->is_required($unit->assignment_id, $stage);

                if ($last_completed_indices[$unit_key] - $second_last_completed_indices[$unit_key] > 1) {
                    if ($stage_key > $second_last_completed_indices[$unit_key] &&
                        $stage_key < $last_completed_indices[$unit_key] &&
                        $this->workflow_manager->current_stage->id != $params['stages'][$stage_key]->id) {
                        $params['stages'][$stage_key]->completion_status[$unit->assignment_id] = 'N/A';
                    }
                }

                if ($params['stages'][$stage_key]->completion_status[$unit->assignment_id] == 'No' && $last_completed_indices[$unit_key] > $last_uncompleted_indices[$unit_key]) {
                    $params['stages'][$stage_key]->completion_status[$unit->assignment_id] = 'N/A';
                }

                if ($make_following_stages_non_accessible) {
                    $params['stages'][$stage_key]->completion_status[$unit->assignment_id] = '--';
                }

                if ($is_required) {
                    if ($stage_key > $last_completed_indices[$unit_key]) {
                        $make_following_stages_non_accessible = true;
                    }

                    if ($stage_key > $last_uncompleted_indices[$unit_key] && $last_completed_indices[$unit_key] < $last_uncompleted_indices[$unit_key]) {
                        $make_following_stages_non_accessible = true;
                    }
                } else if ($stage_key < $last_uncompleted_indices && $params['stages'][$stage_key]->completion_status[$unit->assignment_id] != 'Yes') {
                    $params['stages'][$stage_key]->completion_status[$unit->assignment_id] = 'N/A';
                }
            }
        }

        if (empty($params['extra_param'])) {
            $params['extra_param'] = null;
        }

        if (empty($params['jstoload'])) {
            $params['jstoload'] = array();
        }

        $params['jstoload'][] = "stages/{$this->workflow_manager->current_stage->stage_name}";

        $this->load->view('stages', $params);

    }

    public function update_time($order_id) {
        $this->order_time_model->finish_time($order_id, $this->session->userdata('user_id'));

        if (!$this->order_model->has_statuses($order_id, array('AWAITING REVIEW'))) {
            $this->order_time_model->add(array('time_start' => time(), 'technician_id' => $this->session->userdata('user_id'), 'order_id' => $order_id));
        }
    }

    public static function get_technician_id($assignment_id) {
        $ci = get_instance();
        $user_id = $ci->session->userdata('user_id');

        if (user_has_role($user_id, 'Technician')) {
            return $user_id;
        } else {
            return $ci->assignment_model->get($assignment_id)->technician_id;
        }
    }
}

// Alias controller name
class Stage extends Stage_controller {

}
