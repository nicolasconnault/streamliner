<?php
class Schedule extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array(
            'orders' => array('/orders/order/browse|Jobs'),
            'schedule' => 'Schedule'
        ));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, messages doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
        $this->load->model('miniant/order_model');
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/assignment_model');
        $this->load->model('miniant/order_technician_model');

    }

    public function index($breakdowns=true, $maintenance=true, $installations=true, $repairs=true, $services=true) {
        $title = 'Job schedule';

        $this->config->set_item('replacer', array(
            'miniant' => null,
            'orders' => array('/miniant/orders/order|Jobs'),
            'assignments' => array('/miniant/orders/schedule|Schedule'),
            'edit' => $title,
            'add' => $title));

        $breakdown_order_type_id = $this->order_model->get_type_id('Breakdown');
        $maintenance_order_type_id = $this->order_model->get_type_id('Maintenance');
        $installation_order_type_id = $this->order_model->get_type_id('Installation');
        $repair_order_type_id = $this->order_model->get_type_id('Repair');
        $service_order_type_id = $this->order_model->get_type_id('Service');

        $breakdown_orders = array();
        $maintenance_orders = array();
        $maintenance_orders_without_contract = array();
        $installation_orders = array();
        $repair_orders = array();
        $service_orders = array();

        $maintenance_date = ($this->input->post('maintenance-date')) ? $this->input->post('maintenance-date') : time();

        if ($breakdowns) {
            $breakdown_orders = $this->order_model->get(array('order_type_id' => $breakdown_order_type_id));
        }

        if ($maintenance) {
            $maintenance_orders = $this->order_model->get_maintenance_orders(1, 1);

            foreach ($maintenance_orders as $year => $months) {
                foreach ($months as $month => $orders) {
                    $this->filter_orders($maintenance_orders[$year][$month]);
                }
            }
            $maintenance_orders_without_contract = $this->order_model->get(array('order_type_id' => $maintenance_order_type_id));

            foreach ($maintenance_orders_without_contract as $key => $maintenance_order) {
                if (!empty($maintenance_order->maintenance_contract_id)) {
                    unset($maintenance_orders_without_contract[$key]);
                } else {
                    $maintenance_orders_without_contract[$key]->order_type = 'Maintenance';
                }
            }
        }

        if ($installations) {
            $installation_orders = $this->order_model->get(array('order_type_id' => $installation_order_type_id));
        }

        if ($repairs) {
            $repair_orders = $this->order_model->get(array('order_type_id' => $repair_order_type_id));
        }

        if ($services) {
            $service_orders = $this->order_model->get(array('order_type_id' => $service_order_type_id));
        }

        $this->filter_orders($breakdown_orders);
        $this->filter_orders($installation_orders);
        $this->filter_orders($repair_orders);
        $this->filter_orders($service_orders);
        $this->filter_orders($maintenance_orders);
        $this->filter_orders($maintenance_orders_without_contract);

        $this->user_model->filter_by_role($this->role_model->get(array('name' => 'Technician'), true)->id);
        $technicians = $this->user_model->get();

        $title_options = array('title' => $title,
                               'help' => 'Schedule diagnostics, repairs, , services, maintenance and installations using this interface',
                               'icons' => array());
        $tasks_title_options = array('title' => 'Jobs',
                               'help' => 'List of jobs containing unscheduled tasks',
                               'icons' => array());
        $calendar_title_options = array('title' => 'Calendar',
                               'help' => 'Drag unscheduled tasks into this calendar to schedule them, or change the date or technician of scheduled tasks',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'tasks_title_options' => $tasks_title_options,
                             'calendar_title_options' => $calendar_title_options,
                             'content_view' => 'orders/schedule',
                             'orders' => array(
                                'breakdowns' => $breakdown_orders,
                                'maintenance' => $maintenance_orders,
                                'maintenance_without_contract' => $maintenance_orders_without_contract,
                                'installations' => $installation_orders,
                                'repairs' => $repair_orders,
                                'services' => $service_orders,
                                ),
                             'technicians' => $technicians,
                             'maintenance_date' => $maintenance_date,
                             'csstoload' => array('fullcalendar'),
                             'feature_type' => 'Custom Feature',
                             'module' => 'miniant',
                             'jstoload' => array(
                                 'fullcalendar/fullcalendar',
                                 'fullcalendar/fullcalendar.ipad',
                                 'orders/schedule',
                                 'jquery/datatables/media/js/jquery.dataTables',
                                 'datagrid_paging_bootstrap',
                                 'datatable_pagination'
                                 )
                             );

        $this->load->view('template/default', $pageDetails);

    }

    public function filter_orders(&$orders) {
        foreach ($orders as $key => $order) {

            if (!$this->order_model->has_schedulable_assignments($order->id)) {
                unset($orders[$key]);
                continue;
            }

            if ($order_data = $this->order_model->get_values($order->id)) {
                foreach ($order_data as $order_var => $order_val) {
                    $orders[$key]->$order_var = $order_val;
                }
            } else {
                unset($orders[$key]);
            }

            if (empty($order->units)) {
                unset($orders[$key]);
            }
        }
    }


    /**
     * An appointment is a virtual entity used only by the JS calendar to draw graphical events.
     * An Assignment represents the allocation of 1 unit to 1 time slot and 1 technician.
     */
    public function get_appointments() {
        $order_type = $this->input->post('order_type');
        $start = $this->input->post('start');
        $end = $this->input->post('end');
        $is_technician = is_tech();

        $order_type_id = $this->order_model->get_type_id($order_type);
        $technician_id = $this->session->userdata('user_id');

        if ($this->input->post('technician_id')) {
            $technician_id = $this->input->post('technician_id');
        }

        $assignments = $this->assignment_model->get_for_schedule($start, $end, $technician_id, $is_technician);

        // For technicians, only return the first assignment for the requested period, AND only if of the requested job type
        $appointments = array();

        if (!empty($assignments)) {

            foreach ($assignments as $assignment) {

                if ($assignment->order_type_id != $order_type_id) {
                    continue;
                }

                $assignment = $this->assignment_model->get_values($assignment->id);
                if ($this->assignment_model->has_statuses($assignment->id, array('COMPLETE'))) {
                    continue;
                }

                $order_technician = $this->order_technician_model->get(array('order_id' => $assignment->order_id, 'technician_id' => $assignment->technician_id), true);
                if ($this->order_technician_model->has_statuses($order_technician->id, array('COMPLETE'))) {
                    continue;
                }

                $appointment = new stdClass();
                $appointment->id = "$assignment->id";
                $appointment->title = $this->assignment_model->get_reference_id($assignment->id)."\n".$this->assignment_model->get_city($assignment->id);
                $appointment->allDay = false;
                $appointment->start = $assignment->appointment_date;
                $appointment->end = $assignment->appointment_date + $assignment->estimated_duration * 60;
                $appointment->resourceId = $assignment->technician_id;
                $appointment->order_id = $assignment->order_id;
                $appointment->order_type = $assignment->order_order_type_id;
                $appointment->is_senior = $assignment->technician_id == $assignment->order_senior_technician_id;

                if (!$is_technician) {
                    $appointment->description = $this->load->view('orders/appointment_description',
                        array(
                            'order' => (object) $this->order_model->get_values($assignment->order_id),
                            'assignment' => $assignment,
                            )
                        , true);

                    $appointments[] = $appointment;
                } else {
                    $appointment->description = ''; //No description for technicians, it goes straight to the details page

                    foreach ($appointments as $existing_appointment) {
                        if ($existing_appointment->start == $appointment->start &&
                            $existing_appointment->end == $appointment->end &&
                            $existing_appointment->resourceId == $appointment->resourceId &&
                            $existing_appointment->order_id == $appointment->order_id &&
                            $existing_appointment->order_type == $appointment->order_type) {
                            continue 2;
                        }
                    }

                    $appointments[] = $appointment;
                    break;
                }
            }
        }

        echo json_encode($appointments);
    }

    public function update_event() {

        if (IS_AJAX) {
            $assignment_id = $this->input->post('document_id');

            $technician_id = $this->input->post('technician_id');
            $start = strtotime($this->input->post('start'));
            $end = strtotime($this->input->post('end'));
            $title = $this->input->post('title');
            $all_day = $this->input->post('all_day');
            $source = $this->input->post('source');
        } else {
            var_dump($this->input->post());die();
        }

        $order_type = strtolower($source['data']['order_type']);
        $assignment = $this->assignment_model->get($assignment_id);
        $unit = $this->unit_model->get($assignment->unit_id);

        $assignment_params = array(
            'technician_id' => $technician_id,
            'appointment_date' => $start,
            'estimated_duration' => ($end - $start) / 60,
            'priority_level_id' => 1,
        );

        $this->assignment_model->edit($assignment_id, $assignment_params);

        // If only one technician is left working on this job, set him up as the senior technician
        $this->order_model->set_as_senior_if_last($assignment_id);

        if (IS_AJAX) {
            send_json_message('The event was successfully updated.');
        } else {
            return true;
        }
    }

    // This creates a assignments_units record for each unit in an job, and returns these records through JSON
    public function schedule_assignments() {
        $order_id = $this->input->post('order_id');

        $assignments = $this->assignment_model->get(array('order_id' => $order_id));

        // Create assignment first
        $assignment_params = array(
            'technician_id' => $this->input->post('technician_id'),
            'appointment_date' => strtotime($this->input->post('appointment_date')),
            'estimated_duration' => 120,
            'priority_level_id' => 1
        );

        if ($this->order_model->get_type_id('Maintenance') == $this->order_model->get($order_id)->order_type_id || $this->order_model->get_type_id('Service') == $this->order_model->get($order_id)->order_type_id) {
            $assignment_params['estimated_duration'] = 60;
        }

        $newevents = array();
        foreach ($assignments as $assignment) {
            $this->assignment_model->edit($assignment->id, $assignment_params);
            trigger_event('schedule', 'assignment', $assignment->id, false, 'miniant');
            $newevents[] = array('id' => $assignment->id);
        }

        // Set assigned technician as senior
        $this->order_model->edit($order_id, array('senior_technician_id' => $assignment_params['technician_id']));

        trigger_event('schedule', 'order', $order_id, false, 'miniant');
        trigger_event('allocate_to_technician', 'order', $order_id, false, 'miniant');
        send_json_data(array('newevents' => $newevents));
    }

    // If any assignment in an job is unscheduled, the Job re-appears in the schedulable list, and shows the number of unscheduled units
    public function unschedule_assignment($assignment_id) {
        $assignment_params = array(
            'technician_id' => null,
            'appointment_date' => null,
        );

        $this->assignment_model->edit($assignment_id, $assignment_params);
        $this->order_model->set_as_senior_if_last($assignment_id);

        trigger_event('unschedule', 'assignment', $assignment_id, false, 'miniant');
    }
}
