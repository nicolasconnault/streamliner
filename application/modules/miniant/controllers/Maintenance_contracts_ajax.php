<?php
class Maintenance_contracts_ajax extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('miniant/maintenance_contract_model');
        $this->load->model('miniant/miniant_account_model');
        $this->load->model('miniant/brand_model');
        $this->load->model('miniant/tenancy_model');
        $this->load->model('miniant/unitry_type_model');
        $this->load->model('miniant/maintenance_contract_unit_model');
    }

    public function get_units($maintenance_contract_id) {
        $records = $this->maintenance_contract_unit_model->get(compact('maintenance_contract_id'));
        $units = array();

        foreach ($records as $record) {
            $units[] = $this->unit_model->get_from_cache($record->unit_id);
        }

        if (!empty($units)) {
            $units = array_reverse($units);
        }
        $json_params = array('current_units' => $units);

        send_json_data($json_params);
    }

    public function save_unit() {
        $maintenance_contract_id = $this->input->post('maintenance_contract_id');
        $brand_id = $this->input->post('brand_id');
        $tenancy_id = $this->input->post('tenancy_id');
        $unit_type_id = $this->input->post('unit_type_id');
        $area_serving = $this->input->post('area_serving');

        $json_params = array();
        $json_params['errors'] = array();
        $message = '';
        $type = 'success';

        if (empty($area_serving)) {
            $json_params['errors']['area_serving'] = 'Please enter the area served by this unit';
        }
        if (empty($unit_type_id)) {
            $json_params['errors']['unit_type_id'] = 'Please select a unit type';
        }
        if (empty($tenancy_id)) {
            $json_params['errors']['tenancy_id'] = 'Please select a tenancy';
        }

        $params = compact('brand_id', 'unit_type_id', 'tenancy_id', 'maintenance_contract_id', 'area_serving');

        if (empty($json_params['errors'])) {
            if (empty($unit_id)) {
                $unit_id = $this->unit_model->add($params);
                $message = 'This unit was added successfully';
            } else {
                unset($params['maintenance_contract_id']); // Warning: This will update the unit for ALL associated jobs
                $this->unit_model->edit($unit_id, $params);
                $message = 'This unit was updated successfully';
            }

            $json_params['unit_id'] = $unit_id;
            $type = 'success';
        } else {
            $type = 'danger';
            $message = 'This unit could not be added';
        }

        send_json_message($message, $type, $json_params);
    }

    public function remove_unit() {
        $unit_id = $this->input->post('unit_id');
        $maintenance_contract_id = $this->input->post('maintenance_contract_id');
        $this->maintenance_contract_unit_model->delete(compact('unit_id', 'maintenance_contract_id'));

        send_json_message('This unit was successfully removed from this maintenance contract');
    }

    public function get_values($maintenance_contract_id) {
        send_json_data(array('data' => $this->maintenance_contract_model->get_values($maintenance_contract_id)));
    }

    public function get_messages($maintenance_contract_id) {
        $values = $this->maintenance_contract_model->get_values($maintenance_contract_id);
        $json_params = array( 'messages' => array_reverse($values['messages']));
        send_json_data($json_params);
    }

    public function add_message() {
        $maintenance_contract_id = $this->input->post('maintenance_contract_id');
        $message_id = $this->input->post('message_id');
        $message = $this->input->post('message');
        $author_id = $this->session->userdata('user_id');

        $json_params = array();
        $json_params['errors'] = array();
        $type = 'success';

        if (empty($message)) {
            $json_params['errors']['message'] = 'Please enter a message';
        }

        $params = compact('message', 'maintenance_contract_id', 'author_id');

        if (empty($json_params['errors'])) {
            if (empty($message_id)) {
                $message_id = $this->message_model->add($params);
                $message = 'This message was added successfully';
            } else {
                $this->message_model->edit($message_id, $params);
                $message = 'This message was updated successfully';
            }

            $json_params['message_id'] = $message_id;
            $type = 'success';
        } else {
            $type = 'danger';
            $message = 'This message could not be added';
        }

        send_json_message($message, $type, $json_params);
    }

    public function remove_message() {
        $this->message_model->delete($this->input->post('message_id'));

        send_json_message('This message was successfully removed from this job');
    }
    public function create_contact() {
        if (!IS_AJAX) {
            return false;
        }

        $contact_type_string = ucfirst(str_replace('_', ' ', $this->input->post('type_string')));
        $contact_type_id = $this->contact_model->get_type_id($contact_type_string);

        if (empty($contact_type_id)) {
            send_json_message('There is no "'.$contact_type_string.'" contact type in the Database, please ask the administrator to add it.', 'danger');
            return false;
        }

        $new_contact = array(
            'first_name' => $this->input->post('first_name'),
            'surname' => $this->input->post('surname'),
            'phone' => $this->input->post('phone'),
            'phone2' => $this->input->post('phone2'),
            'mobile' => $this->input->post('mobile'),
            'mobile2' => $this->input->post('mobile2'),
            'email' => $this->input->post('email'),
            'email2' => $this->input->post('email2'),
            'website' => $this->input->post('website'),
            'contact_type_id' => $contact_type_id,
            'account_id' => $this->input->post('account_id'),
        );

        if ($contact_id = $this->contact_model->add($new_contact)) {
            $new_contact['id'] = $contact_id;
            $new_contact['contact'] = $this->contact_model->get($contact_id);
            send_json_message('The contact has been successfully recorded', 'success', $new_contact);
        } else {
            send_json_message('The contact could not be recorded', 'danger');
        }
    }

    public function add_unit() {
        $unit = new stdClass();
        $maintenance_contract_id = $this->input->post('maintenance_contract_id');
        $unit->unit_type_id = $this->input->post('unit_type_id');
        $unit->unitry_type_id = $this->input->post('unitry_type_id');
        $unit->site_address_id = $this->input->post('site_address_id');
        $brand_id = $this->input->post('brand_id');
        $brand_id_ref = $this->input->post('brand_id_ref');
        $brand_id_evap = $this->input->post('brand_id_evap');
        $unit->tenancy_id = $this->input->post('tenancy_id');
        $unit->brand_other = $this->input->post('brand_other');
        $unit->area_serving = $this->input->post('area_serving');
        $unit->description = $this->input->post('description');
        $unit->outdoor_unit_location = $this->input->post('outdoor_unit_location');
        $unit->electrical = $this->input->post('electrical');
        $unit->kilowatts = $this->input->post('kilowatts');
        $unit->vehicle_registration = $this->input->post('vehicle_registration');
        $unit->vehicle_type = $this->input->post('vehicle_type');
        $unit->palette_size = $this->input->post('palette_size');
        $unit->chassis_no = $this->input->post('chassis_no');
        $unit->engine_no = $this->input->post('engine_no');
        $unit->vehicle_year = $this->input->post('vehicle_year');
        $unit->aperture_size = $this->input->post('aperture_size');
        $unit->serial_number = $this->input->post('serial_number');
        $unit->indoor_serial_number = $this->input->post('indoor_serial_number');
        $unit->outdoor_serial_number = $this->input->post('outdoor_serial_number');

        if (empty($unit->unitry_type_id)) {
            $unit->unitry_type_id = null;
        }
        if (empty($brand_id)) {
            $unit->brand_id = null;
        }
        if (!empty($brand_id_ref)) {
            $unit->brand_id = $brand_id_ref;
        }
        if (!empty($brand_id_evap)) {
            $unit->brand_id = $brand_id_evap;
        }

        if ($unit_id = $this->unit_model->add($unit)) {
            $unit = $this->unit_model->get_values($unit_id);

            $assignment_id = $this->maintenance_contract_model->add_unit($unit_id, $maintenance_contract_id);

            send_json_message("The unit was added", 'success', array('unit' => $unit));
        } else {
            send_json_message("The unit could not be added", 'danger');
        }
    }

    public function edit_unit() {
        $unit = new stdClass();
        $unit->id = $this->input->post('id');
        $unit->unit_type_id = $this->input->post('unit_type_id');
        $unit->brand_other = $this->input->post('brand_other');
        $brand_id = $this->input->post('brand_id');
        $brand_id_ref = $this->input->post('brand_id_ref');
        $brand_id_evap = $this->input->post('brand_id_evap');

        if (!empty($brand_id)) {
            $unit->brand_id = $brand_id;
        }
        if (!empty($brand_id_ref)) {
            $unit->brand_id = $brand_id_ref;
        }
        if (!empty($brand_id_evap)) {
            $unit->brand_id = $brand_id_evap;
        }

        $unit->area_serving = $this->input->post('area_serving');
        $unit->tenancy_id = $this->input->post('tenancy_id');
        $unit->description = $this->input->post('description');
        $unit->outdoor_unit_location = $this->input->post('outdoor_unit_location');
        $unit->electrical = $this->input->post('electrical');
        $unit->kilowatts = $this->input->post('kilowatts');
        $unit->vehicle_registration = $this->input->post('vehicle_registration');
        $unit->vehicle_type = $this->input->post('vehicle_type');
        $unit->palette_size = $this->input->post('palette_size');
        $unit->chassis_no = $this->input->post('chassis_no');
        $unit->engine_no = $this->input->post('engine_no');
        $unit->vehicle_year = $this->input->post('vehicle_year');
        $unit->aperture_size = $this->input->post('aperture_size');
        $unit->serial_number = $this->input->post('serial_number');
        $unit->indoor_serial_number = $this->input->post('indoor_serial_number');
        $unit->outdoor_serial_number = $this->input->post('outdoor_serial_number');

        if ($this->unit_model->edit($unit->id, (array) $unit)) {
            $unit = $this->unit_model->get_values($unit->id);
            send_json_message("The unit was updated", 'success', array('unit' => $unit));
        } else {
            send_json_message("The unit could not be updated", 'danger');
        }
    }

    public function get_maintenance_checklist() {

        $unit_id = $this->input->post('unit_id');
        $checklist = $this->maintenance_task_model->get(array('unit_id' => $unit_id), false, 'sortorder');

        send_json_data(array('checklist' => $checklist));
    }

    public function save_maintenance_checklist() {

        $checklist = $this->input->post('checklist');
        $checklist = str_replace('task[]=', '', $checklist);
        $unit_id = $this->input->post('unit_id');
        $tasks = explode('&', $checklist);

        $sortorder = 1;

        if (empty($tasks)) {
            return null;
        }

        foreach ($tasks as $task_id) {
            $this->maintenance_task_model->edit($task_id, compact('sortorder'));
            $sortorder++;
        }
    }

    public function delete_maintenance_task() {
        $task_id = $this->input->post('task_id');
        $this->maintenance_task_model->delete($task_id);
        send_json_message('Task successfully deleted');
    }

    public function add_maintenance_task() {
        $task = $this->input->post('task');
        $unit_id = $this->input->post('unit_id');
        $task_array = array('unit_id' => $unit_id, 'task' => $task, 'sortorder' => 0);
        $task_array['id'] = $this->maintenance_task_model->add($task_array);
        sendjson_message('Task successfully added', 'success', array('task' => $task_array));
    }

    public function save_maintenance_task_notes() {
        $task_id = $this->input->post('task_id');
        $notes = $this->input->post('notes');
        $this->maintenance_task_model->edit($task_id, array('notes' => $notes));
        send_json_message('Task successfully updated', 'success');
    }

    public function update_statuses($maintenance_contract_id) {
        $status_ids = $this->input->post('values');
        $this->maintenance_contract_model->set_statuses($maintenance_contract_id, $status_ids);
        send_json_message('Statuses were updated');
    }

    public function add_tenancy() {
        $account_id = $this->input->post('account_id');
        $name = $this->input->post('name');
        $id = $this->tenancy_model->add(compact('account_id', 'name'));

        send_json_message('Tenancy/Owner successfully added', 'success', array('tenancy' =>compact('id','account_id','name')));
    }

    public function edit_tenancy() {
        $id = $this->input->post('id');
        $account_id = $this->input->post('account_id');
        $name = $this->input->post('name');
        $this->tenancy_model->edit($id, compact('account_id', 'name'));

        send_json_message('Tenancy/Owner successfully updated', 'success', array('tenancy' =>compact('id','account_id','name')));
    }

    public function remove_tenancy($tenancy_id, $units_too=false) {

        if ($units_too) {
            $this->unit_model->delete(array('tenancy_id' => $tenancy_id));
        }

        if ($this->unit_model->get(compact('tenancy_id'))) {
            send_json_message('This tenancy is associated with existing units', 'danger');
            return false;
        }

        $this->tenancy_model->delete($tenancy_id);

        send_json_message('Tenancy/Owner successfully deleted', 'success');
    }

    public function get_tenancy_dropdown() {
        $account_id = $this->input->post('account_id');
        $this->db->where(compact('account_id'));
        $this->db->order_by('name', 'DESC');

        $tenancies = $this->tenancy_model->get();

        foreach ($tenancies as $key => $tenancy) {
            if ($this->unit_model->get(array('tenancy_id' => $tenancy->id))) {
                $tenancies[$key]->locked = true;
            } else {
                $tenancies[$key]->locked = false;
            }
        }

        send_json_data(array('tenancies' => $tenancies));
    }

    public function get_account_data() {
        require_capability('site:viewaccounts');
        $account_id = $this->input->post('account_id');
        $maintenance_contract_id = $this->input->post('maintenance_contract_id');

        $values = $this->miniant_account_model->get_values($account_id);
        $json_params = array(
            'billing_contacts' => $values['billing_contacts'],
            'property_manager_contacts' => $values['property_manager_contacts'],
            'site_contacts' => $values['site_contacts'],
            'site_addresses' => $values['site_addresses'],
            'tenancies' => $values['tenancies'],
            'site_address_id' => null,
            'billing_contact_id' => null,
            'property_manager_contact_id' => null,
        );

        if (!empty($maintenance_contract_id)) {
            $maintenance_contract = $this->maintenance_contract_model->get_from_cache($maintenance_contract_id);
            $json_params['site_address_id'] = $maintenance_contract->site_address_id;
            $json_params['billing_contact_id'] = $maintenance_contract->billing_contact_id;
            $json_params['property_manager_contact_id'] = $maintenance_contract->property_manager_contact_id;
        }

        unset($values['billing_contacts']);
        unset($values['property_manager_contacts']);
        unset($values['site_addresses']);

        $json_params['values'] = $values;
        send_json_data($json_params);
    }
}
