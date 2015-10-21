<?php
class Job_sites extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('building' => null, 'job_sites' => array('/job_sites|Job sites')));
        $this->config->set_item('exclude', array('index', 'browse', 'html'));

        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function index($outputtype='html') {
        require_capability('building:viewjobsites');

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'job_sites',
            'module' => 'building',
            'row_actions' => array('edit', 'delete', 'calendar', 'attachments'),
            'row_action_capabilities' => array('edit' => 'building:editjobsites', 'delete' => 'building:deletejobsites', 'calendar' => 'building:viewjobsites', 'attachments' => 'building:viewjobsites'),
            'feature_type' => 'Custom Feature',
            'available_export_types' => array('pdf', 'csv'),
            'model' => $this->job_site_model,
            'custom_title' => 'List of job sites',
            'title_icon' => 'home',
            'custom_columns_callback' => $this->job_site_model->get_custom_columns_callback()
        ));

        $this->datagrid->add_column(array(
            'table' => 'building_job_sites',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'job_site_id'));
        $this->datagrid->add_column(array(
            'table' => 'building_job_sites',
            'field' => 'city',
            'field_alias' => 'job_site_city',
            'label' => 'Suburb',
            'sortable' => true));
        $this->datagrid->add_column(array(
            'table' => 'building_job_sites',
            'field' => 'street',
            'field_alias' => 'job_site_street',
            'label' => 'Address',
            'sortable' => true));
        $this->datagrid->add_column(array(
            'table' => 'building_job_sites',
            'field' => 'number',
            'field_alias' => 'job_site_number',
            'label' => 'Number',
            'sortable' => true));
        $this->datagrid->add_column(array(
            'table' => 'building_job_sites',
            'field' => 'unit',
            'field_alias' => 'job_site_unit',
            'label' => 'Unit',
            'sortable' => true));
        $this->datagrid->add_column(array(
            'label' => 'Statuses',
            'field_alias' => 'statuses',
            'sortable' => false
        ));

        $this->datagrid->set_joins(array(
            array('table' => 'document_statuses', 'on' => 'document_statuses.document_id = building_job_sites.id AND document_statuses.document_type = "job_site"'),
        ));

        $this->datagrid->render();
    }

    public function add() {
        return $this->edit();
    }

    public function edit($job_site_id=null) {

        require_capability('building:writejobsites');

        if (!empty($job_site_id)) {
            require_capability('building:editjobsites');
            $job_site_data = (array) $this->job_site_model->get($job_site_id);

            form_element::$default_data = $job_site_data;

            // Set up title bar
            $title = "Edit Job site";
            $help = "Use this form to edit the job site.";
        } else { // adding a new job_site
            $title = "Create a new job site";
            $help = 'Use this form to create a new job site.';
        }

        $this->config->set_item('replacer', array('building' => null, 'job_sites' => array('/building/job_sites/index|Job sites'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'job_site/edit',
                             'job_site_id' => $job_site_id,
                             'feature_type' => 'Custom feature',
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('building:editjobsites');

        $required_fields = array('number' => 'Number', 'street' => 'Street', 'city' => 'City', 'state' => 'State', 'postcode' => 'Postcode');

        if ($job_site_id = (int) $this->input->post('id')) {
            $job_site = $this->job_site_model->get($job_site_id);
            $redirect_url = base_url().'building/job_sites/edit/'.$job_site_id;
        } else {
            $redirect_url = base_url().'building/job_sites/add';
            $job_site_id = null;
        }

        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();

        $action_word = ($job_site_id) ? 'updated' : 'created';

        if (IS_AJAX) {
            $json = new stdClass();
            if ($success) {
                $json->result = 'success';
                $json->message = "Job site $job_site_id has been successfully $action_word!";
            } else {
                $json->result = 'error';
                $json->message = $this->form_validation->error_string(' ', "\n");
                echo json_encode($json);
                return null;
            }
        } else if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            $errors = validation_errors();
            return $this->edit($job_site_id);
        }

        $job_site_data = array(
            'unit' => $this->input->post('unit'),
            'number' => $this->input->post('number'),
            'street' => $this->input->post('street'),
            'street_type' => $this->input->post('street_type'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'postcode' => $this->input->post('postcode'),
        );

        if (empty($job_site_id)) {
            if (!($job_site_id = $this->job_site_model->add($job_site_data))) {
                add_message('Could not create this Job site!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->job_site_model->edit($job_site_id, $job_site_data)) {
                add_message('Could not update this Job site!', 'error');
                redirect($redirect_url);
            }
        }

        // If requested through AJAX, echo response, do not redirect
        if (IS_AJAX) {
            echo json_encode($json);
            return null;
        }

        add_message("Job site $job_site_id has been successfully $action_word!", 'success');
        redirect(base_url().'building/job_sites');
    }

    public function update_statuses($job_site_id) {
        $status_ids = $this->input->post('values');
        $this->job_site_model->set_statuses($job_site_id, $status_ids);
        send_json_message('Statuses were updated');
    }

    public function calendar($job_site_id, $current_date=null) {

        if (empty($current_date)) {
            $current_date = time();
        }

        $this->config->set_item('replacer', array('building' => null, 'job_sites' => array('/building/job_sites/index|Job sites')));
        $this->config->set_item('exclude', array($job_site_id));

        $job_site = $this->job_site_model->get($job_site_id);
        $staff = $this->user_model->get_dropdown_full_name(false);
        $tradesmen = $this->tradesman_model->get_sorted_dropdown();
        $tradesman_types = $this->tradesman_model->get_types_dropdown();

        $title = 'Job schedule for '. $this->address_model->get_formatted_address($job_site);

        $title_options = array('title' => $title,
                               'help' => 'Schedule sub-contractors for this job site using this interface',
                               'icons' => array());
        $calendar_title_options = array('title' => 'Calendar',
                               'help' => '',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'calendar_title_options' => $calendar_title_options,
                             'content_view' => 'job_site/calendar',
                             'csstoload' => array('fullcalendar'),
                             'feature_type' => 'Custom Feature',
                             'module' => 'building',
                             'staff' => $staff,
                             'current_date' => $current_date,
                             'tradesmen' => $tradesmen,
                             'tradesman_types' => $tradesman_types,
                             'recipients' => array(),
                             'job_site_id' => $job_site_id,
                             'csstoload' => array('fullcalendar', 'fullcalendar.twoweeks'),
                             'jstoload' => array(
                                 'moment',
                                 'fullcalendar',
                                 'fullcalendar.ipad',
                                 'calendar',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function get_bookings() {
        $start = human_to_unix($this->input->post('start'), true);
        $end = human_to_unix($this->input->post('end'), true);
        $confirmed = $this->input->post('confirmed');
        $job_site_id = $this->input->post('job_site_id');
        $tradesmen = $this->tradesman_model->get_sorted_dropdown();
        $tradesman_types = $this->tradesman_model->get_types_dropdown();

        $bookings = array();

        $bookings = $this->booking_model->get_for_schedule($start, $end, $job_site_id, $confirmed);

        $events = array();

        foreach ($bookings as $booking) {
            $recipients = $this->booking_model->get_recipients($booking->id);
            $staff = $this->user_model->get_dropdown_full_name(false);

            $event = new stdClass();
            $event->id = "$booking->id";
            $event->title = $this->load->view('job_site/booking_title', compact('booking', 'confirmed'), true);
            $event->allDay = true;
            $event->start = unix_to_human($booking->booking_date, '%Y-%m-%d');
            $event->end = unix_to_human($booking->booking_date + 60, '%Y-%m-%d');
            $event->job_site_id = $booking->job_site_id;
            $event->confirmed = $confirmed;
            $event->description = $this->load->view('job_site/booking_description', compact('booking', 'tradesmen', 'tradesman_types', 'job_site_id', 'recipients', 'staff'), true);
            $events[] = $event;
        }

        echo json_encode($events);
    }

    public function update_event() {
        $id = $this->input->post('id');

        $job_site_id = $this->input->post('job_site_id');
        $confirmed = $this->input->post('confirmed');
        $start = $this->input->post('start');
        $title = $this->input->post('title');
        $all_day = $this->input->post('all_day');
        $source = $this->input->post('source');

        $booking = $this->booking_model->get($id);
        $booking_params = array('booking_date' => $start);

        $this->booking_model->edit($id, $booking_params);

        send_json_message('The booking was successfully updated.');
    }

    public function edit_booking() {
        $job_site_id = $this->input->post('job_site_id');
        $booking_id = $this->input->post('id');
        $message = $this->input->post('message');
        $tradesman_id = $this->input->post('tradesman_id');
        $tradesman_type_id = $this->input->post('tradesman_type_id');
        $confirmed = $this->input->post('confirmed');
        $booking_date = human_to_unix($this->input->post('booking_date'));
        $recipients = $this->input->post('recipients');

        if (empty($confirmed)) {
            $confirmed = false;
        }

        $booking_name = ($confirmed) ? 'Booking' : 'Booking request';
        $original_booking = null;

        if (empty($booking_id)) {
            $booking_id = $this->booking_model->add(compact('message', 'booking_date', 'confirmed', 'job_site_id', 'tradesman_id', 'tradesman_type_id'));
            add_message("The $booking_name was successfully created!");
        } else {
            $original_booking = $this->booking_model->get($booking_id);
            $this->booking_model->edit($booking_id, compact('message', 'booking_date', 'confirmed', 'tradesman_id', 'tradesman_type_id'));
            add_message("The $booking_name was successfully updated!");

            // Delete all existing recipient records for this booking
            $this->booking_recipient_model->delete(array('booking_id' => $booking_id));
        }

        foreach ($recipients as $user_id) {
            $this->booking_recipient_model->add(array('booking_id' => $booking_id, 'user_id' => $user_id));

            if (!$confirmed) {
                $this->send_request_notification($user_id, $booking_id, $original_booking, $recipients);
            }
        }

        redirect(base_url().'building/job_sites/calendar/'.$job_site_id.'/'.$booking_date);
    }

    public function delete_booking($booking_id) {
        $booking = $this->booking_model->get($booking_id);

        $this->booking_model->delete($booking_id);
        $booking_name = ($booking->confirmed) ? 'Booking' : 'Booking request';
        add_message("The $booking_name was successfully deleted!");
        redirect(base_url().'building/job_sites/calendar/'.$booking->job_site_id.'/'.$booking->booking_date);
    }

    public function send_request_notification($user_id, $booking_id, $original_booking = null, $new_recipients=array()) {
        $new_booking = $this->booking_model->get($booking_id);
        $old_recipients = $this->booking_model->get_recipients($booking_id);
        $job_site_address = $this->address_model->get_formatted_address($this->job_site_model->get($new_booking->job_site_id));

        $user = (object) $this->user_model->get_values($user_id);

        // Only send a notification if the booking request is new, or if it is different from the original, or if recipients have changed.
        if (!is_null($original_booking)) {
            if ($new_booking->booking_date == $original_booking->booking_date && $new_booking->tradesman_id == $original_booking->tradesman_id && !$this->booking_model->have_recipients_changed($old_recipients, $new_recipients)) {
                return false;
            }
        }

        $this->email->clear(true);
        $this->email->from($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Site name') . ' Admin');
        $this->email->subject('G2: Booking request notification');
        $this->email->message($this->load->view('job_site/notification', compact('user', 'job_site_address', 'new_booking', 'original_booking'), true));
        $this->email->to($user->user_email);
        $this->email->mailtype = 'html';
        $email_object = clone($this->email);

        if (ENVIRONMENT == 'demo') {
            $result = true;
        } else {
            $result = $this->email->send();
        }

        if ($result) {
            $error_message = null;
        } else {
            $error_message = $this->email->print_debugger();
        }

        $this->email_log_model->log_message($email_object, __FILE__ . ' at line ' . __LINE__, null, 'users', $this->session->userdata('user_id'), 'contacts', $user->id);
    }

    public function delete($id, $model_name=null) {

        $result = $this->job_site_model->delete($id);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "Job site $id was successfully deleted";
                $json->id = $id;
                $json->type = 'success';
            } else {
                $json->message = "Job site $id could not be deleted";
                $json->id = $id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }
}
