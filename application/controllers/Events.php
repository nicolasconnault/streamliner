<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
class Events extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer',
            array(
                'order' => 'Order Events',
                'repair_job' => 'Job Events',
                'diagnostic' => 'Diagnostic Events',
                'assignment' => 'Assignment Events',
            )
        );
        $this->config->set_item('exclude', array('index', 'html', 'browse', 'events', 'edit'));

        // Being a global controller, doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html', $system) {
        return $this->index($outputtype, $system);
    }

    public function index($outputtype='html', $system) {
        $sql_conditions = array("events.system = '$system'");

        $readable_system = str_replace('_', ' ', ucfirst($system));

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'row_actions' => array('edit', 'delete', 'flag'),
            'available_export_types' => array('csv'),
            'sql_conditions' => $sql_conditions,
            'row_action_capabilities' => array(
                'delete' => $system.':deleteevents',
                'edit' => $system.':editevents',
                'flag' => $system.':editevents'
            ),
            'show_add_button' => has_capability($system.':writeevents'),
            'model' => $this->event_model,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'events',
            'url_param' => $system,
            'custom_title' => "List of system events for $readable_system"
        ));

        $this->datagrid->add_column(array(
            'table' => 'events',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'event_id',
            'in_combo_filter' => true,
            'width' => '5%'
        ));
        $this->datagrid->add_column(array(
            'table' => 'events',
            'field' => 'name',
            'label' => 'Name',
            'field_alias' => 'event_name',
            'in_combo_filter' => true,
            'width' => '20%'
        ));
        $this->datagrid->add_column(array(
            'table' => 'events',
            'field' => 'description',
            'label' => 'Description',
            'field_alias' => 'event_description',
            'in_combo_filter' => true,
            'width' => '30%'
        ));
        $this->datagrid->add_column(array(
            'table' => 'roles',
            'field' => 'name',
            'label' => 'Role',
            'field_alias' => 'role_name',
            'in_combo_filter' => true,
            'width' => '10%'
        ));

        $this->datagrid->set_joins(array(
            array('table' => 'roles', 'on' => 'roles.id = events.role_id', 'type' => 'LEFT OUTER'),
        ));

        $this->datagrid->setup_filters();
        $this->datagrid->render();
    }

    public function add($system) {
        return $this->edit(null, $system);
    }

    public function edit($event_id=null, $system) {
        $this->load->helper('inflector');

        $event_data = $this->event_model->get_values($event_id);

        if (!empty($event_id)) {
            require_capability($system.':editevents');

            form_element::$default_data = (array) $event_data;

            // Set up title bar
            $title = "Edit Event {$event_data['event_name']}";
            $help = "Use this form to edit the event";
        } else { // adding a new event
            $title = "Create a new event";
            $help = 'Use this form to create a new event';
        }

        $this->config->set_item('replacer',
            array(
                'edit' => array("/events/browse/html/$system|$system events"),
                'add' => array("/events/browse/html/$system|$system events"),
                $system => $title
            )
        );
        $this->config->set_item('exclude', array('index', 'html', 'browse', 'events', 'edit'));
        $this->config->set_item('exclude_segment', array(2));


        $this->config->set_item('exclude', array('index', 'html', 'browse', 'events'));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'events/edit',
                             'event_id' => $event_id,
                             'event_data' => $event_data,
                             'system' => $system,
                             'dropdowns' => $this->get_dropdowns(),
                             'feature_type' => 'Streamliner Core',
                             'jstoloadinfooter' => array(
                                 'application/event_edit',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);

    }

    public function process_edit($system) {
        require_capability($system.':editevents');

        $required_fields = array('event_name' => "Event name", 'event_description' => 'Event description');

        if ($event_id = (int) $this->input->post('event_id')) {
            $event = $this->event_model->get($event_id);
            $redirect_url = base_url().'events/browse/html/'.$system;
        } else {
            $redirect_url = base_url().'events/add/'.$system;
            $event_id = null;
        }

        // TODO set up different required fields depending on the form stage
        foreach ($required_fields as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = ($event_id) ? 'updated' : 'created';

        if (!$success) {
            add_message('The form could not be submitted. Please check the errors below', 'danger');
            return $this->edit($event_id);
        }

        $role_id = $this->input->post('role_id');
        if ($role_id == '*') {
            $role_id = null;
        }

        $event_data = array(
            'name' => $this->input->post('event_name'),
            'description' => $this->input->post('event_description'),
            'role_id' => $role_id,
            'system' => $system
        );

        if (empty($event_id)) {
            if (!($event_id = $this->event_model->add($event_data))) {
                add_message('Could not create this event!', 'error');
                redirect($redirect_url);
            }
        } else {
            if (!$this->event_model->edit($event_id, $event_data)) {
                add_message('Could not update this event!', 'error');
                redirect($redirect_url);
            }
        }

        add_message("Event {$event_data['name']} has been successfully $action_word!", 'success');
        redirect(base_url().'events/browse/html/'.$system);
    }

    public function get_dropdowns() {
        return array('roles' => $this->role_model->get_dropdown('name', 'All roles', false, false, null, '*'));
    }

    public function delete($event_id, $model_name = null) {

        $event = $this->event_model->get($event_id);
        $result = $this->event_model->delete($event_id);
        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "Event $event->name was successfully deleted";
                $json->id = $event_id;
                $json->type = 'success';
            } else {
                $json->message = "Event $event->name could not be deleted";
                $json->id = $event_id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }

    public function statuses($event_id, $system) {
        $this->load->helper('inflector');
        $this->db->order_by('sortorder');
        $statuses = $this->status_model->get_dropdown('name', false);
        $event = $this->event_model->get($event_id);
        $title = "Status triggers for event '$event->name'";
        $this->config->set_item('replacer',
            array(
                  'event' => array('/events/index/html/'.$system.'|Events'), 'edit' => $title, 'add' => $title,
                  $this->inflector->pluralize($system) => array('/orders/order/index|Orders'
            ))
        );

        $title_options = array('title' => $title,
                               'help' => "Use this page to set up status triggers for the '$event->name' event. A status trigger will be activated when this event is fired, and will change the statuses of the document associated with that event",
                               'expand' => 'page',
                               'icons' => array('add'));

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => '/events/statuses',
                             'event_id' => $event_id,
                             'event' => $event,
                             'statuses' => $statuses,
                             'system' => $system,
                             'feature_type' => 'Streamliner Core',
                             'jstoloadinfooter' => array(
                                 'application/event_statuses',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function get_statuses($event_id) {
        $event_statuses = $this->status_model->get_statuses_for_event($event_id);
        send_json_data(array('event_statuses' => $event_statuses, 'statuses' => $this->status_model->get_dropdown('name', false)));
    }

    public function update_status_event_field() {
        $id = $this->input->post('id');
        $field = $this->input->post('field');
        $value = $this->input->post('value');

        $this->status_event_model->edit($id, array($field => $value));
        send_json_message('The status trigger was successfully updated');
    }

    public function create_status_event() {
        $status_id = $this->input->post('status_id');
        $state = $this->input->post('state');
        $event_id = $this->input->post('event_id');

        $this->status_event_model->add(compact('status_id', 'state', 'event_id'));
        send_json_message('The status trigger was successfully created');
    }

    public function delete_status_event($status_event_id) {
        $this->status_event_model->delete($status_event_id);
        send_json_message('The status trigger was successfully deleted');
    }

    public function trigger_event() {
        extract($this->input->post());
        if (empty($module)) {
            $module = null;
        }
        return trigger_event($event_name, $system, $document_id, false, $module);
    }

    public function undo_event() {
        if (empty($module)) {
            $module = null;
        }
        extract($this->input->post());
        return trigger_event($event_name, $system, $document_id, true, $module);
    }
}
