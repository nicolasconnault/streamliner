<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Diagnostic_report extends Stage_controller {

    /**
     * A single assignment_id is given, but it may represent a group of units (as an assignment).
     * The reason we pass the $assignment_id instead of the $assignment_id is to highlight the last edited Unit tab on this page
     * The Diagnostic is created on this page
     */
    public function index($assignment_id) {
        $this->load->model('miniant/diagnostic_tree_model');

        $this->assignment = $this->assignment_model->get($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $unit = $this->unit_model->get($this->assignment->unit_id);
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = parent::get_technician_id($assignment_id);

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        $this->db->cache_delete();
        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'diagnostic_report', 'param' => $assignment_id, 'module' => 'miniant'));

        $part_types_by_unit_type = array();

        foreach ($units as $key => $unit) {
            $units[$key]->assignment = (object) $this->assignment_model->get_values($unit->assignment_id);

            if ($units[$key]->assignment->no_issues_found) {
                $this->diagnostic_issue_model->delete(array('diagnostic_id' => $units[$key]->assignment->diagnostic_id));
            }


            if (empty($units[$key]->assignment->diagnostic_id)) {
                $diagnostic_id = $this->diagnostic_model->add(array());

                $this->assignment_model->edit($unit->assignment_id, array('diagnostic_id' => $diagnostic_id));

                $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($diagnostic_id);
            } else {
                $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($units[$key]->assignment->diagnostic_id);
            }

            $units[$key]->order = (object) $this->order_model->get_values($order->id);
            $units[$key]->diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $units[$key]->diagnostic->id));
            $units[$key]->dialog = $this->get_diagnostic_report_dialog($units[$key]->diagnostic->id, $order, $technician_id, $this->workflow_manager);
            $units[$key]->no_issues_dialog = $this->get_diagnostic_report_no_issues_dialog($units[$key]->diagnostic->id, $order, $technician_id, $this->workflow_manager);

            if (empty($part_types_by_unit_type[$unit->unit_type_id])) {
                $part_types_by_unit_type[$unit->unit_type_id] = $this->diagnostic_tree_model->get_part_types($unit->unit_type_id, true);
            }

            $part_types_dropdown = array(0 => '-- Select One --');
            foreach ($part_types_by_unit_type[$unit->unit_type_id] as $part_type) {
                $part_types_dropdown[$part_type->id] = $part_type->name;
            }

            $units[$key]->part_types_dropdown = $part_types_dropdown;
            $units[$key]->sq_submitted = $this->assignment_model->has_statuses($unit->assignment_id, array('AWAITING REVIEW'));
        }

        $info_title_options = array('title' => 'Unit info',
                               'help' => 'Information',
                               'icons' => array(),
                               );
        $site_title_options = array('title' => 'Site info',
                               'help' => 'Site address and name of the site contact',
                               'icons' => array());
        $diagnostic_title_options = array('title' => 'Diagnosed issues',
                               'help' => 'Record the issues discovered during diagnosis',
                               'icons' => array());

        $this->load_stage_view(array(
             'units' => $units,
             'info_title_options' => $info_title_options,
             'diagnostic_title_options' => $diagnostic_title_options,
             'site_title_options' => $site_title_options,
             'show_start_diagnostic_message' => $order_type != 'Repair' && !$this->assignment_model->has_statuses($assignment_id, array('ISSUES DIAGNOSED'))
        ));
    }

    /**
     * This dialog code was useful for simple dialogs, but it's becoming a mess now. The issues are:
     *  - No hooks for PHP callbacks in event triggers (can't change data for diagnostics, assignments etc. without using JS callbacks, which are messy)
     *  - No rule about whether to use STATUSES or table columns to store data about things like Assignment->no_issues_found
     */
    public function get_diagnostic_report_dialog($diagnostic_id, $order, $technician_id, $workflow_manager) {
        $assignment = $this->assignment_model->get(compact('diagnostic_id'), true);
        $is_senior_technician = $technician_id == $order->senior_technician_id;
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');

        $this->load->library('Dialog');
        $this->dialog->initialise(array('id' => 'dialog-'.$assignment->id));


        if ($is_technician) {
            $ids_to_show = ($is_senior_technician) ? array('hide_issue_photos') : array('hide_issue_photos'); // Keeping this here in case the workflow changes later on for non-senior technicians

            $diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $diagnostic_id));
            $has_repair_issues = false;
            $has_sq_issues = false;

            foreach ($diagnostic_issues as $issue) {
                if ($issue->can_be_fixed_now) {
                    $has_repair_issues = true;
                } else {
                    $has_sq_issues = true;
                }
            }

            $this->dialog->add_question(array(
                'id' => 'recorded_all_issues',
                'shown' => $diagnostic_issues && !$this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')),
                'text' => 'Have you recorded all the issues for this unit?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => $ids_to_show,
                        'triggers' => array(
                            array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'diagnosed', 'module' => 'miniant'),
                        ),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_no_issues_found/'.$assignment->id.'/0',
                    ))
                ));

            $this->dialog->add_question(array(
                'id' => 'hide_issue_photos',
                'shown' => $this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')) && !$assignment->no_issues_found,
                'text' => 'Has the office asked you to hide the issue photos?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'triggers' => array(array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'issue_photos_hiding_setting_recorded', 'module' => 'miniant')),
                        'ids_to_show' => array('isolated_and_tagged'),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_issue_photo_hiding/'.$assignment->id.'/1',
                    ),
                    array(
                        'text' => 'No',
                        'ids_to_show' => array('isolated_and_tagged'),
                        'triggers' => array(array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'issue_photos_hiding_setting_recorded', 'module' => 'miniant')),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_issue_photo_hiding/'.$assignment->id.'/0',
                    )),
                ));

            $this->dialog->add_question(array(
                'id' => 'isolated_and_tagged',
                'shown' => $this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')) && !$assignment->no_issues_found,
                'text' => 'Have you isolated and tagged this system?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'triggers' => array(array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'isolated_and_tagged_recorded', 'module' => 'miniant')),
                        'ids_to_show' => array('called_office'),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_isolated_and_tagged/'.$assignment->id.'/true',
                    ),
                    array(
                        'text' => 'No',
                        'ids_to_show' => array('called_office'),
                        'triggers' => array(array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'isolated_and_tagged_recorded', 'module' => 'miniant')),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_isolated_and_tagged/'.$assignment->id.'/false',
                    )),
                ));

            $this->dialog->add_question(array(
                'id' => 'called_office',
                'shown' => !$this->assignment_model->has_statuses($assignment->id, array('CALLED OFFICE')) &&
                           !$assignment->no_issues_found &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISSUE PHOTOS HIDING SETTING RECORDED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISOLATED AND TAGGED SETTING RECORDED')),

                'text' => 'Have you called the office?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => array(
                            'repair_approved' => '$("table[data-assignment_id='.$assignment->id.'] tr[data-can_be_fixed_now=1]").length > 0',
                            'sq_approved' => '$("table[data-assignment_id='.$assignment->id.'] tr[data-can_be_fixed_now=0]").length > 0',
                            'continue' => '$("table[data-assignment_id='.$assignment->id.'] tr[data-can_be_fixed_now]").length == 0' // No issues found
                        ),
                        'triggers' => array(array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'called_office', 'module' => 'miniant')),
                    ))
                ));

            $redirect_url = 'workflow/redirect/'.$order_type.'/diagnostic_report/'.$assignment->id.'/miniant';
            $no_sq_issues_js_condition = '$("table[data-assignment_id='.$assignment->id.'] tr[data-can_be_fixed_now=0]").length == 0';

            $this->dialog->add_question(array(
                'id' => 'repair_approved',
                'shown' => !$this->diagnostic_model->has_statuses($diagnostic_id, array('COMPLETE')) &&
                           !$assignment->no_issues_found &&
                           !$this->assignment_model->has_statuses($assignment->id, array('REPAIRS APPROVED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('CALLED OFFICE')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISSUE PHOTOS HIDING SETTING RECORDED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISOLATED AND TAGGED SETTING RECORDED')) &&
                           $has_repair_issues,
                'text' => 'Has the office asked you to repair this unit now?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'triggers' => array(
                            array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'repairs_approved', 'module' => 'miniant'),
                            array('system' => 'diagnostic', 'document_id' => $diagnostic_id, 'event_name' => 'completed', 'js_condition' => $no_sq_issues_js_condition, 'module' => 'miniant')
                        ),
                        'ids_to_show' => array(
                            'sq_approved' => '$("table[data-assignment_id='.$assignment->id.'] tr[data-can_be_fixed_now=0]").length > 0',
                            'continue' => $no_sq_issues_js_condition
                        ),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_no_issues_found/'.$assignment->id.'/0',
                    ),
                    array(
                        'text' => 'No',
                        'triggers' => array(
                            array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'repairs_approved', 'undo' => true, 'module' => 'miniant'),
                            array('system' => 'diagnostic', 'document_id' => $diagnostic_id, 'event_name' => 'completed', 'js_condition' => $no_sq_issues_js_condition, 'module' => 'miniant')
                        ),
                        'ids_to_show' => array(
                            'sq_approved' => '$("table[data-assignment_id='.$assignment->id.'] tr[data-can_be_fixed_now=0]").length > 0',
                            'continue' => $no_sq_issues_js_condition
                        ),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_no_issues_found/'.$assignment->id.'/0',
                    )),
                ));

            $this->dialog->add_question(array(
                'id' => 'sq_approved',
                'shown' => !$this->diagnostic_model->has_statuses($diagnostic_id, array('COMPLETE')) &&
                           !$assignment->no_issues_found &&
                           !$this->assignment_model->has_statuses($assignment->id, array('SQ APPROVED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('CALLED OFFICE')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISSUE PHOTOS HIDING SETTING RECORDED')) &&
                           $this->assignment_model->has_statuses($assignment->id, array('ISOLATED AND TAGGED SETTING RECORDED')) &&
                           $has_sq_issues,
                'text' => 'Has the office asked you to complete an SQ for this unit?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'triggers' => array(
                            array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'sq_approved', 'module' => 'miniant'),
                            array('system' => 'diagnostic', 'document_id' => $diagnostic_id, 'event_name' => 'completed', 'module' => 'miniant')
                        ),
                        'ids_to_show' => array('continue'),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_no_issues_found/'.$assignment->id.'/0',
                    ),
                    array(
                        'text' => 'No',
                        'triggers' => array(
                            array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'sq_approved', 'undo' => true, 'module' => 'miniant'),
                            array('system' => 'diagnostic', 'document_id' => $diagnostic_id, 'event_name' => 'completed', 'module' => 'miniant')
                        ),
                        'ids_to_show' => array('continue'),
                        'ajax_callback' => 'miniant/stages/diagnostic_report/set_no_issues_found/'.$assignment->id.'/0',
                    )),
                ));

            $this->dialog->add_question(array(
                'id' => 'continue',
                'shown' => $this->diagnostic_model->has_statuses($diagnostic_id, array('COMPLETE')) && !$assignment->no_issues_found,
                'text' => '&nbsp;',
                'answers' => array(
                    array(
                        'text' => 'Continue',
                        'url' => $redirect_url
                    ))
                ));
        } else {
            $this->dialog->add_question(array(
                'id' => 'continue',
                'shown' => true,
                'text' => ' ',
                'answers' => array(
                    array(
                        'text' => 'Continue',
                        'url' => $this->workflow_manager->get_next_url()
                    ))
                ));
        }
        $dialog = $this->dialog->output();
        return $dialog;
    }

    public function get_diagnostic_report_no_issues_dialog($diagnostic_id, $order, $technician_id, $workflow_manager) {
        $assignment = $this->assignment_model->get(compact('diagnostic_id'), true);
        $is_senior_technician = $technician_id == $order->senior_technician_id;
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Dialog');
        $this->dialog->initialise(array('id' => 'dialog_no_issues-'.$assignment->id));

        $this->dialog->add_question(array(
            'id' => 'are_you_sure',
            'shown' => !$this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED')) && !$assignment->no_issues_found,
            'text' => 'Are you sure there are no issues with this unit?',
            'answers' => array(
                array(
                    'text' => 'Yes',
                    'ids_to_show' => array('continue'),
                    'triggers' => array(
                        array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'diagnosed', 'module' => 'miniant'),
                        array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'issue_photos_hiding_setting_recorded', 'module' => 'miniant'),
                        array('system' => 'diagnostic', 'document_id' => $diagnostic_id, 'event_name' => 'completed', 'module' => 'miniant'),
                        array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'isolated_and_tagged_recorded', 'undo' => true, 'module' => 'miniant'),
                        array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'called_office', 'undo' => true, 'module' => 'miniant'),
                        array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'sq_approved', 'undo' => true, 'module' => 'miniant'),
                    ),
                    'ajax_callback' => 'miniant/stages/diagnostic_report/set_no_issues_found/'.$assignment->id.'/1',
                ))
            ));

        $shown = ($order_type == 'Repair') ? $assignment->no_issues_found : $assignment->no_issues_found && $this->assignment_model->has_statuses($assignment->id, array('ISSUES DIAGNOSED'));
        $this->dialog->add_question(array(
            'id' => 'continue',
            'shown' => $shown,
            'text' => '&nbsp;',
            'answers' => array(
                array(
                    'text' => 'Continue',
                    'url' => 'workflow/redirect/'.$order_type.'/diagnostic_report/'.$assignment->id.'/miniant',
                    'triggers' => array(
                        array('system' => 'assignment', 'document_id' => $assignment->id, 'event_name' => 'dowd_recorded', 'module' => 'miniant'),
                    )
                ))
            ));

        $dialog = $this->dialog->output();
        return $dialog;
    }

    public function get_issue_types($part_type_id) {
        $this->load->model('miniant/diagnostic_tree_model');
        $issue_types = $this->diagnostic_tree_model->get_part_type_issue_types($part_type_id);
        send_json_data(array('issue_types' => $issue_types));
    }

    public function add_diagnostic_issue() {
        $diagnostic_issue = (object) $this->input->post();
        $diagnostic_issue->id = $this->diagnostic_issue_model->add($diagnostic_issue);
        $diagnostic = $this->diagnostic_model->get_values($diagnostic_issue->diagnostic_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $diagnostic->id), true);

        $diagnostic_issue->unit_id = $diagnostic->unit_id;

        $this->diagnostic_issue_model->create_parts($diagnostic_issue, !$diagnostic_issue->can_be_fixed_now);

        send_json_data(array('diagnostic_issue' => $diagnostic_issue, 'upload_view' => $this->load->view('upload', array('directory' => 'diagnostic_issue', 'document_id' => $diagnostic_issue->id, 'module' => 'miniant'), true)));
    }

    public function delete_diagnostic_issue() {
        $diagnostic_issue_id = $this->input->post('diagnostic_issue_id');
        $this->diagnostic_issue_model->delete($diagnostic_issue_id);
        send_json_data(array('diagnostic_issue_id' => $diagnostic_issue_id));

    }

    public function set_issue_photo_hiding($assignment_id, $state) {
        $this->assignment_model->edit($assignment_id, array('hide_issue_photos' => (bool) $state));
    }

    public function set_isolated_and_tagged($assignment_id, $state) {
        $this->assignment_model->edit($assignment_id, array('isolated_and_tagged' => (bool) $state));
    }

    public function set_no_issues_found($assignment_id, $state) {
        $this->assignment_model->edit($assignment_id, array('no_issues_found' => (bool) $state));
    }
}
