<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Assignment_details extends Stage_controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('miniant/assignment_model');
        $this->load->model('miniant/order_time_model');
        $this->load->model('miniant/order_technician_model');
    }

    public function index($assignment_id) {
        $this->assignment = $this->assignment_model->get_values($assignment_id);

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }
        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'assignment_details', 'param' => $assignment_id, 'module' => 'miniant'));
        $next_page_url = $this->workflow_manager->get_next_url();

        parent::update_time($order->id);

        $technician_id = parent::get_technician_id($assignment_id);
        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);

        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');

        $order_technician_id = @$order_technician->id;

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        $notes = $this->message_model->get_with_author_names(array('document_type' => 'order', 'document_id' => $order->id));

        $equipment_type = null;
        $equipment_types = array();

        foreach ($units as $key => $unit) {
            $units[$key]->notes = $this->message_model->get_with_author_names(array('document_type' => 'assignment', 'document_id' => $assignment_id));

            if (is_null($equipment_type)) {
                $equipment_type = $this->unit_model->get_type_string($unit->unit_type_id);
            }

            if (!in_array($unit->unit_type_id, $equipment_types)) {
                $equipment_types[] = $unit->unit_type_id;
            }
        }

        if (count($equipment_types) > 1) {
            $equipment_type = 'Mixed';
        }

        $this->load->library('Dialog');
        $this->dialog->initialise(array('min_interval_between_answers' => 300));

        if ($is_technician) {

            $this->dialog->add_question(array(
                'id' => 'ready_to_travel',
                'shown' => !$this->order_technician_model->has_statuses($order_technician_id, array('STARTED TRAVEL')),
                'text' => 'Are you ready to travel to the job?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => array('ready_to_start'),
                        'triggers' => array(array('system' => 'order_technician', 'document_id' => $order_technician_id, 'event_name' => 'leaving', 'module' => 'miniant')),
                    ))
                ));

            $this->dialog->add_question(array(
                'id' => 'continue',
                'shown' => $this->order_technician_model->has_statuses($order_technician_id, array('STARTED')),
                'text' => 'This job is currently in progress',
                'answers' => array(
                    array(
                        'text' => 'Continue this job',
                        'url' => $next_page_url,
                    ))
                ));

            $question_text = ($order_type == 'Installation') ? 'Are you ready to start the installation?' : 'Are you ready to start the diagnostic?';
            $this->dialog->add_question(array(
                'id' => 'ready_to_start',
                'shown' => !$this->order_technician_model->has_statuses($order_technician_id, array('STARTED')) && $this->order_technician_model->has_statuses($order_technician_id, array('STARTED TRAVEL')),
                'text' => $question_text,
                'answers' => array(
                    array(
                        'text' => 'Yes',
                            'triggers' => array(
                                array('system' => 'order_technician', 'document_id' => $order_technician_id, 'event_name' => 'start', 'module' => 'miniant'),
                                array('system' => 'order', 'document_id' => $order->id, 'event_name' => 'start', 'module' => 'miniant')
                            ),
                        'url' => $next_page_url,
                        'short_interval_js' => '$("#did_you_forget").show();return false;',
                    ))
                )
            );

            $this->dialog->add_question(array(
                'id' => 'did_you_forget',
                'shown' => false,
                'text' => 'Did you forget to click the "Are you ready to travel" button before you left?',
                'answers' => array(
                        array(
                            'text' => 'Yes',
                            'undo' => false,
                            'ids_to_show' => array('time_estimate'),
                        ),
                        array(
                            'text' => 'No',
                            'triggers' => array(
                                array('system' => 'order_technician', 'document_id' => $order_technician_id, 'event_name' => 'start', 'module' => 'miniant'),
                                array('system' => 'order', 'document_id' => $order->id, 'event_name' => 'start', 'module' => 'miniant')
                            ),
                            'url' => $next_page_url,
                        ),
                    )
                )
            );

            $this->dialog->add_question(array(
                'id' => 'time_estimate',
                'shown' => false,
                'text' => 'Estimate how long it took you to travel',
                'answers' => array(
                        array(
                            'text' => 'Travel time',
                            'type' => 'dropdown',
                            'options' => array(
                                0 => '-- Select One --',
                                900 => '15 minutes',
                                1800 => '30 minutes',
                                2700 => '45 minutes',
                                3600 => '1 hour',
                                4500 => '1 hour 15 minutes',
                                5400 => '1 hour 30 minutes',
                                6300 => '1 hour 45 minutes',
                                7200 => '2 hours',
                                8100 => '2 hours 15 minutes',
                                9000 => '2 hours 30 minutes',
                                9900 => '2 hours 45 minutes',
                                10800 => '3 hours',
                                11700 => '3 hours 15 minutes',
                                12600 => '3 hours 30 minutes',
                                13500 => '3 hours 45 minutes',
                                14400 => '4 hours',
                                15300 => '4 hours 15 minutes',
                                16200 => '4 hours 30 minutes',
                                17100 => '4 hours 45 minutes',
                                18000 => '5 hours'
                            ),
                            'ajax_callback' => 'miniant/stages/assignment_details/add_travel_time/'.$assignment_id,
                            'ajax_position' => 'end',
                            'ajax_data' => array('assignment_id' => $assignment_id),
                            'triggers' => array(array('system' => 'order_technician', 'document_id' => $order_technician_id, 'event_name' => 'start', 'module' => 'miniant')),
                            'url' => $next_page_url,
                        ),
                    )
                )
            );
        } else {
            $this->dialog->add_question(array(
                'id' => 'review',
                'shown' => true,
                'text' => ' ',
                'answers' => array(
                    array(
                        'text' => 'Review this job',
                        'url' => $next_page_url,
                    ))
                ));
        }

        $this->load_stage_view(array(
             'units' => $units,
             'notes' => $notes,
             'dialog' => $this->dialog->output(),
             'equipment_type' => $equipment_type,
        ));
    }

    public function add_travel_time() {
        $assignment_id = $this->input->post('assignment_id');
        $value = $this->input->post('value');
        $assignment = $this->assignment_model->get($assignment_id);
        $this->order_time_model->push_back_starting_time($assignment->order_id, $assignment->technician_id, $value);
        send_json_message('The starting time has been updated.', 'success');
    }
}
