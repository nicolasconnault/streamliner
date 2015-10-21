<?php
class Assignments extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('miniant/order_model');
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/assignment_model');
        $this->load->model('miniant/order_technician_model');
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html', $order_id=null) {
        require_capability('site:viewbrands');

        $this->config->set_item('replacer', array(
            'miniant' => array('/miniant/orders/order|Jobs', '/miniant/orders/order/edit/'.$order_id.'|J'.$order_id),
            'assignments' => array('/miniant/orders/schedule|Assignments')));
        $this->config->set_item('exclude', array('index', 'html'));

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'module' => 'miniant',
            'uri_segment_1' => 'orders',
            'uri_segment_2' => 'assignments',
            'row_actions' => array('edit', 'review'),
            'feature_type' => 'Custom Feature',
            'url_param' => $order_id,
            'sql_conditions' => array('miniant_assignments.order_id = '.$order_id),
            'available_export_types' => array(),
            'custom_title' => 'Assignments for J'.$order_id,
            'model' => $this->assignment_model
        ));

        $this->datagrid->add_column(array(
            'table' => 'miniant_assignments',
            'field' => 'id',
            'label' => 'Assignment ID',
            'field_alias' => 'assignment_id'));
        $this->datagrid->add_column(array(
            'table' => 'users',
            'field' => 'first_name',
            'label' => 'Technician',
            'field_alias' => 'technician_name'));

        $this->datagrid->set_joins(array(
            array('table' => 'miniant_orders', 'on' => 'miniant_orders.id = miniant_assignments.order_id', 'type' => 'LEFT OUTER'),
            array('table' => 'users', 'on' => 'users.id = miniant_assignments.technician_id', 'type' => 'LEFT OUTER'),
        ));

        $this->datagrid->render();
    }

    public function edit($assignment_id) {
        require_capability('assignments:edit');

        $assignment_data = $this->assignment_model->get_values($assignment_id);

        if (empty($assignment_data)) {
            redirect(base_url().'miniant/orders/schedule');
        }

        $assignment_data->appointment_date = unix_to_human($assignment_data->appointment_date, '%d/%m/%Y %h:%i');
        $order = (object) $this->order_model->get_values($assignment_data->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $statuses = $this->assignment_model->get_statuses($assignment_id, false);

        $current_statuses = array();
        if (!empty($statuses)) {
            foreach ($statuses as $status) {
                $current_statuses[] = $status->status_id;
            }
        }

        $allstatuses_array = $this->status_model->get_for_document_type('assignment');
        $allstatuses = array();
        foreach ($allstatuses_array as $status) {
            $allstatuses[$status->id] = $status->name;
        }

        $assigned_technicians = $this->assignment_model->get_assigned_technicians($order->id, $assignment_data->unit_id);

        $this->user_model->filter_by_role($this->role_model->get(array('name' => 'Technician'), true)->id);
        $technicians = $this->user_model->get_dropdown('first_name', false);

        form_element::$default_data = (array) $assignment_data;
        form_element::$default_data['technician_id'] = $assigned_technicians;
        form_element::$default_data['senior_technician_id'] = $order->senior_technician_id;

        // Set up title bar
        $title = "Edit {$assignment_data->reference_id} assignment";
        $help = "Use this form to edit the assignment";

        $this->config->set_item('replacer', array(
            'miniant' => null,
            'orders' => array('/miniant/orders/order|Jobs'),
            'assignments' => array('/miniant/orders/schedule|Schedule'),
            'edit' => $title,
            'add' => $title));

        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'assignments/edit',
                             'order_id' => $order->id,
                             'assignment_id' => $assignment_id,
                             'assignment_data' => $assignment_data,
                             'technicians' => $technicians,
                             'senior_technician_id' => $order->senior_technician_id,
                             'statuses' => $current_statuses,
                             'allstatuses' => $allstatuses,
                             'module' => 'miniant',
                             'feature_type' => 'Custom Feature',
                             'module' => 'miniant',
                             'assigned_technicians' => $assigned_technicians,
                             'priority_levels' => $this->priority_level_model->get_dropdown('name'),
                             'jstoloadinfooter' => array('orders/assignment_edit'),
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {
        require_capability('orders:editorders');

        if ($this->input->post('return')) {
            redirect(base_url().'miniant/orders/order/browse');
        }

        $assignment_id = $this->input->post('assignment_id');

        $required_fields = array(
            'assignment_id' => "assignment ID",
            'appointment_date' => "Appointment date",
            'estimated_duration' => "Estimated duration",
            'senior_technician_id' => "Senior technician",
            // 'priority_level_id' => "Priority level"
        );

        $assignment = $this->assignment_model->get($assignment_id);

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'required');
        }

        $success = $this->form_validation->run();

        if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            return $this->edit($assignment_id);
        }

        $assignment_params = array(
            'appointment_date' => human_to_unix($this->input->post('appointment_date')),
            'estimated_duration' => $this->input->post('estimated_duration'),
            'order_id' => $assignment->order_id,
        );

        $this->assignment_model->edit($assignment_id, $assignment_params);

        $this->order_model->edit($assignment->order_id, array('senior_technician_id' => $this->input->post('senior_technician_id')));

        $this->update_statuses($assignment_id, $this->input->post('status_id'));

        add_message("assignment has been successfully updated!", 'success');
        redirect(base_url().'miniant/orders/schedule');
    }

    public function update_statuses($assignment_id, $status_ids=array()) {
        if (empty($status_ids)) {
            $status_ids = $this->input->post('values');
        }

        $this->assignment_model->set_statuses($assignment_id, $status_ids);

        if (IS_AJAX) {
            send_json_message('Statuses were updated');
        }
    }

    public function add_or_remove_technician() {
        $added_id = $this->input->post('added_id');
        $removed_id = $this->input->post('removed_id');
        $assignment_id = $this->input->post('assignment_id');
        $order_id = $this->input->post('order_id');
        $unit_id = $this->input->post('unit_id');

        if (!empty($added_id)) {
            if (!($assignment = $this->assignment_model->get(array('order_id' => $order_id, 'technician_id' => $added_id, 'unit_id' => $unit_id)))) {
                $assignment = $this->assignment_model->get($assignment_id);

                $this->assignment_model->add(array(
                    'appointment_date' => $assignment->appointment_date,
                    'estimated_duration' => $assignment->estimated_duration,
                    'technician_id' => $added_id,
                    'order_id' => $order_id,
                    'unit_id' => $assignment->unit_id,
                    'workflow_id' => $assignment->workflow_id,
                    'location_token' => $assignment->location_token,
                    'diagnostic_required' => $assignment->diagnostic_required,
                    'diagnostic_authorised' => $assignment->diagnostic_authorised,
                    'no_issues_found' => $assignment->no_issues_found,
                    'dowd_text' => $assignment->dowd_text
                ));
            }
        } else if (!empty($removed_id)) {
            if ($assignment = $this->assignment_model->get(array('order_id' => $order_id, 'technician_id' => $removed_id, 'unit_id' => $unit_id), true)) {
                $this->assignment_model->delete($assignment->id);
            }
        }

        send_json_message('success');
    }
}
