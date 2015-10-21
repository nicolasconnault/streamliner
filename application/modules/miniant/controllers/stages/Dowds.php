<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Dowds extends Stage_controller {

    public function index($assignment_id) {
        $this->assignment_id = $assignment_id;
        $this->assignment = $this->assignment_model->get($this->assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $diagnostic = $this->diagnostic_model->get($this->assignment->diagnostic_id);
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = parent::get_technician_id($assignment_id);
        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');
        $is_senior_technician = $technician_id == $order->senior_technician_id;

        parent::update_time($order->id);

        $is_maintenance = $order->order_type_id == $this->order_model->get_type_id('Maintenance');
        $is_repair = $order->order_type_id == $this->order_model->get_type_id('Repair');
        $is_breakdown = $order->order_type_id == $this->order_model->get_type_id('Breakdown');
        $is_installation = $order->order_type_id == $this->order_model->get_type_id('Installation');
        $is_service = $order->order_type_id == $this->order_model->get_type_id('Service');

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'dowds', 'param' => $assignment_id, 'module' => 'miniant'));

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        foreach ($units as $key => $unit) {
            $sq = $this->servicequote_model->get(array('order_id' => $order->id, 'diagnostic_id' => $unit->assignment->diagnostic_id), true);
            $units[$key]->assignment = (object) $this->assignment_model->get_values($unit->assignment_id);
            $units[$key]->order = (object) $this->order_model->get_values($order->id);

            if (!empty($units[$key]->assignment->diagnostic_id)) {
                $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($units[$key]->assignment->diagnostic_id);
                $diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $unit->diagnostic->id));
            }

            if (empty($diagnostic_issues)) {
                form_element::$default_data['dowd_text'] = $this->assignment->dowd_text;
                $diagnostic_issues = array();
            } else {
                foreach ($diagnostic_issues as $key2 => $diagnostic_issue) {
                    if (empty($diagnostic_issue->dowd_id)) {

                        // TODO Improve the automatic selection of a unit DOWD. Must cover all scenarios!
                        if (!$this->assignment_model->has_repairs_approved($unit->assignment->id) && !$this->assignment_model->has_statuses($unit->assignment->id, array('SQ APPROVED'))) {
                            $dowd = $this->dowd_model->get(array('name' => 'ISSUES FOUND - NO ACTION', 'order_type_id' => $order->order_type_id), true);
                        } else if ($diagnostic_issue->can_be_fixed_now) {
                            // If at least one part must be picked up from supplier, use the supplier DOWD
                            if ($this->assignment_model->requires_parts_from_supplier($unit->assignment_id)) {
                                $dowd = $this->dowd_model->get(array('name' => 'PARTS REQUIRED - TO BE PICKED UP FROM SUPPLIER', 'order_type_id' => $order->order_type_id), true);
                            } else {
                                $dowd = $this->dowd_model->get(array('name' => 'PARTS REQUIRED FROM VAN STOCK', 'order_type_id' => $order->order_type_id), true);
                            }
                        } else {
                            $dowd = $this->dowd_model->get(array('name' => 'WAITING ON APPROVAL', 'order_type_id' => $order->order_type_id), true);
                        }

                        if (empty($dowd)) {
                            add_message("No DOWD found for $order->order_type job type, issue $diagnostic_issue->issue_type_name $diagnostic_issue->part_type_name", 'danger');
                            break;
                        }

                        $diagnostic_issues[$key2]->dowd_id = $dowd->id;
                        form_element::$default_data['dowd_id['.$diagnostic_issue->id.']'] = $dowd->id;
                        form_element::$default_data['dowd_text['.$diagnostic_issue->id.']'] = $this->dowd_model->get_formatted_description($dowd->id, $diagnostic_issue->id);
                    }
                }
            }

            $units[$key]->diagnostic_issues = $diagnostic_issues;

            $units[$key]->isolated = $this->assignment_model->has_statuses($unit->assignment_id, array("ISOLATED AND TAGGED"));
        }

        $dowds_dropdown = $this->dowd_model->get_dropdown('name', false);

        $dowds_dropdown[null] = null; // Prevent a PHP error if no DOWD was found

        $this->load_stage_view(array(
             'units' => $units,
             'is_maintenance' => $is_maintenance,
             'is_repair' => $is_repair,
             'is_breakdown' => $is_breakdown,
             'is_installation' => $is_installation,
             'is_service' => $is_service,
             'is_senior_technician' => $is_senior_technician,
             'dowds_dropdown' => $dowds_dropdown,
             'is_technician' => $is_technician
        ));
    }

    public function process() {
        $dowd_text = $this->input->post('dowd_text');

        $diagnostic_id = $this->input->post('diagnostic_id');
        $assignment = $this->assignment_model->get_from_cache($this->input->post('assignment_id'));

        if (!empty($diagnostic_id)) {
            $diagnostic = $this->diagnostic_model->get($diagnostic_id);
        }

        $order = (object) $this->order_model->get_values($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = $this->session->userdata('user_id');
        $is_senior_technician = $technician_id == $order->senior_technician_id;
        $dowd_ids = $this->input->post('dowd_id');
        $dowd_texts = $this->input->post('dowd_text');

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'dowds', 'param' => $assignment->id, 'module' => 'miniant'));

        $diagnostic_issue_ids = array();
        $part_ids = array();

        if (empty($dowd_ids)) {
            $this->form_validation->set_rules('dowd_text', 'Description for NO ISSUES FOUND DOWD' , 'trim|required');
        } else {
            foreach ($dowd_texts as $diagnostic_issue_id => $unused) {
                $diagnostic_issue_ids[] = $diagnostic_issue_id;
                $diagnostic_issue = $this->diagnostic_issue_model->get($diagnostic_issue_id);
                // $this->form_validation->set_rules('dowd_id['.$diagnostic_issue_id.']', 'DOWD for '.$diagnostic_issue->part_type_name, 'trim|required');
                $this->form_validation->set_rules('dowd_text['.$diagnostic_issue_id.']', 'Description for '.$diagnostic_issue->part_type_name, 'trim|required');
            }
        }

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Make sure you complete all the required fields below (in Yellow)', 'danger');
            return $this->index($assignment->id);
        }

        if (empty($diagnostic_issue_ids)) {
            $this->assignment_model->edit($assignment->id, array('dowd_text' => $dowd_text));
        } else {
            foreach ($diagnostic_issue_ids as $diagnostic_issue_id) {
                $this->diagnostic_issue_model->edit($diagnostic_issue_id, array(
                    'dowd_id' => $dowd_ids[$diagnostic_issue_id],
                    'dowd_text' => $dowd_texts[$diagnostic_issue_id]));
            }
        }

        trigger_event('dowd_recorded', 'assignment', $assignment->id, false, 'miniant');
        if ($this->all_dowds_recorded($assignment->id, $order->id)) {
            trigger_event('unit_work_complete', 'order', $order->id, false, 'miniant');
        }

        redirect($this->workflow_manager->get_next_url());
    }

    public function get_dowd_description() {
        $dowd_id = $this->input->post('dowd_id');
        $diagnostic_issue_id = $this->input->post('diagnostic_issue_id');
        $description = $this->dowd_model->get_formatted_description($dowd_id, $diagnostic_issue_id);
        send_json_data(array('description' => $description));
    }

    public function all_dowds_recorded($assignment_id, $order_id) {
        $technician_id = $this->session->userdata('user_id');
        $units = $this->get_units($assignment_id, 1, 1);
        foreach ($units as $unit) {
            if (!$this->assignment_model->has_statuses($unit->assignment_id, array("DOWD RECORDED"), 'OR', array(), false)) {
                return false;
            }
        }
        return true;
    }
}
