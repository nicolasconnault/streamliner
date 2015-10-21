<?php
class Order_ajax extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('miniant/order_model');
    }

    public function create() {
        $client_id = $this->input->post('client_id');
        $address_id = $this->input->post('address_id');
        $call_date = human_to_unix($this->input->post('call_date'));

        $params = compact('client_id', 'call_date', 'address_id');
        $order_id = $this->order_model->add($params);
        trigger_event('create_order', 'order', $order_id, false, 'miniant');
        send_json_data(array('order_id' => $order_id));
    }

    public function get_units($order_id) {
        $values = $this->order_model->get_values($order_id);

        if (!empty($values['units'])) {
            $values['units'] = array_reverse($values['units']);
        }
        $json_params = array('current_units' => $values['units']);

        send_json_data($json_params);
    }

    public function save_unit() {
        $this->load->model('miniant/unit_model');

        $order_id = $this->input->post('order_id');
        $brand_id = $this->input->post('brand_id');
        $tenancy_id = $this->input->post('tenancy_id');
        $unit_type_id = $this->input->post('unit_type_id');
        $area_serving = $this->input->post('area_serving');
        $description = $this->input->post('description');

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

        $params = compact('brand_id', 'unit_type_id', 'tenancy_id', 'order_id', 'area_serving');

        if (empty($json_params['errors'])) {
            if (empty($unit_id)) {
                $unit_id = $this->unit_model->add($params);
                $message = 'This unit was added successfully';
            } else {
                unset($params['order_id']); // Warning: This will update the unit for ALL associated job
                $this->unit_model->edit($unit_id, $params);
                $message = 'This unit was updated successfully';
            }

            // If a description was given, create a unit message instead
            if (!empty($description)) {
                $this->message_model->add(array('document_id' =>$unit_id, 'document_type' => 'unit', 'author_id' => $this->session->userdata('user_id'), 'message' => $description));
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
        $this->load->model('miniant/unit_model');
        $unit_id = $this->input->post('unit_id');
        $order_id = $this->input->post('order_id');
        $this->unit_model->remove_from_order($unit_id, $order_id);

        send_json_message('This unit was successfully removed from this job');
    }

    public function get_values($order_id) {
        send_json_data(array('data' => $this->order_model->get_values($order_id)));
    }

    public function get_messages($order_id) {
        $values = $this->order_model->get_values($order_id);
        $json_params = array( 'messages' => array_reverse($values['messages']));
        send_json_data($json_params);
    }

    public function add_message() {
        $order_id = $this->input->post('order_id');
        $message_id = $this->input->post('message_id');
        $message = $this->input->post('message');
        $author_id = $this->session->userdata('user_id');

        $json_params = array();
        $json_params['errors'] = array();
        $type = 'success';

        if (empty($message)) {
            $json_params['errors']['message'] = 'Please enter a message';
        }

        $params = compact('message', 'order_id', 'author_id');

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
            'contact_type_id' => $this->contact_model->get_type_id($this->input->post('type_string')),
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
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/installation_template_model');
        $this->load->model('miniant/brand_model');

        $unit = new stdClass();
        $order_id = $this->input->post('order_id');
        $unit->unit_type_id = $this->input->post('unit_type_id');
        $unit->unitry_type_id = $this->input->post('unitry_type_id');
        $unit->site_address_id = $this->input->post('site_address_id');
        $brand_id = $this->input->post('brand_id');
        $brand_id_ref = $this->input->post('brand_id_ref');
        $brand_id_evap = $this->input->post('brand_id_evap');
        $unit->tenancy_id = $this->input->post('tenancy_id');
        $brand_other = $this->input->post('brand_other');
        $brand_other_evap = $this->input->post('brand_other_evap');
        $brand_other_ref = $this->input->post('brand_other_ref');
        $unit->area_serving = $this->input->post('area_serving');
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
        $description = $this->input->post('description');

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

        $new_brand = null;

        if (!empty($brand_other)) {
            $new_brand = $brand_other;
        } else if (!empty($brand_other_ref)) {
            $new_brand = $brand_other_ref;
        } else if (!empty($brand_other_evap)) {
            $new_brand = $brand_other_evap;
        }

        if (!empty($new_brand)) {
            $new_brand_params = array('unit_type_id' => $unit->unit_type_id, 'name' => $new_brand);
            if ($brand = $this->brand_model->get($new_brand_params, true)) {
                $unit->brand_id = $brand->id;
            } else {
                $unit->brand_id = $this->brand_model->add($new_brand_params);
            }
        }

        if ($unit_id = $this->unit_model->add($unit)) {
            $unit = $this->unit_model->get_values($unit_id);

            $assignment_id = $this->unit_model->add_to_order($unit_id, $order_id);

            // For installation units, copy the tasks from the template
            if ($this->order_model->get($this->input->post('order_id'))->order_type_id == $this->order_model->get_type_id('Installation')) {
                $this->installation_template_model->copy_to_unit($unit['unit_type_id'], $unit['id'], $unit['unitry_type_id']);
            }

            if (!empty($description)) {
                $this->message_model->add(array('document_id' =>$unit_id, 'document_type' => 'unit', 'author_id' => $this->session->userdata('user_id'), 'message' => $description));
            }

            send_json_message("The unit was added", 'success', array('unit' => $unit));
        } else {
            send_json_message("The unit could not be added", 'danger');
        }
    }

    public function edit_unit() {
        $this->load->model('miniant/unit_model');

        $unit = new stdClass();
        $unit->id = $this->input->post('id');
        $unit->unit_type_id = $this->input->post('unit_type_id');
        $brand_id = $this->input->post('brand_id');
        $brand_id_ref = $this->input->post('brand_id_ref');
        $brand_id_evap = $this->input->post('brand_id_evap');
        $brand_other = $this->input->post('brand_other');
        $brand_other_evap = $this->input->post('brand_other_evap');
        $brand_other_ref = $this->input->post('brand_other_ref');

        if (!empty($brand_id)) {
            $unit->brand_id = $brand_id;
        }
        if (!empty($brand_id_ref)) {
            $unit->brand_id = $brand_id_ref;
        }
        if (!empty($brand_id_evap)) {
            $unit->brand_id = $brand_id_evap;
        }

        $new_brand = null;

        if (!empty($brand_other)) {
            $new_brand = $brand_other;
        } else if (!empty($brand_other_ref)) {
            $new_brand = $brand_other_ref;
        } else if (!empty($brand_other_evap)) {
            $new_brand = $brand_other_evap;
        }


        if (!empty($new_brand)) {
            $new_brand_params = array('unit_type_id' => $unit->unit_type_id, 'name' => $new_brand);
            if ($brand = $this->brand_model->get($new_brand_params, true)) {
                $unit->brand_id = $brand->id;
            } else {
                $unit->brand_id = $this->brand_model->add($new_brand_params);
            }
        }

        $unit->area_serving = $this->input->post('area_serving');
        $unit->tenancy_id = $this->input->post('tenancy_id');
        $description = $this->input->post('description');
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

            if (!empty($description)) {
                $this->message_model->add(array('document_id' =>$unit['id'], 'document_type' => 'unit', 'author_id' => $this->session->userdata('user_id'), 'message' => $description));
            }

            send_json_message("The unit was updated", 'success', array('unit' => $unit));
        } else {
            send_json_message("The unit could not be updated", 'danger');
        }
    }

    public function get_installation_checklist() {
        $this->load->model('miniant/installation_task_model');

        $unit_id = $this->input->post('unit_id');
        $checklist = $this->installation_task_model->get(array('unit_id' => $unit_id), false, 'sortorder');

        send_json_data(array('checklist' => $checklist));
    }

    public function save_installation_checklist() {
        $this->load->model('miniant/installation_task_model');

        $checklist = $this->input->post('checklist');
        $checklist = str_replace('task[]=', '', $checklist);
        $unit_id = $this->input->post('unit_id');
        $tasks = explode('&', $checklist);

        $sortorder = 1;

        if (empty($tasks)) {
            return null;
        }

        foreach ($tasks as $task_id) {
            $this->installation_task_model->edit($task_id, compact('sortorder'));
            $sortorder++;
        }
    }

    public function toggle_installation_task() {
        $this->load->model('miniant/installation_task_model');
        $task_id = $this->input->post('task_id');
        $status = $this->input->post('status');
        $this->installation_task_model->edit($task_id, array('disabled' => (boolean) $status));
        $new_status = ($status == 'false') ? 'enabled' : 'disabled';
        send_json_message("Task successfully $new_status");
    }

    public function add_installation_task() {
        $this->load->model('miniant/installation_task_model');
        $task = $this->input->post('task');
        $unit_id = $this->input->post('unit_id');
        $task_array = array('unit_id' => $unit_id, 'task' => $task, 'sortorder' => 0);
        $task_array['id'] = $this->installation_task_model->add($task_array);
        send_json_message('Task successfully added', 'success', array('task' => $task_array));
    }

    public function save_installation_task_notes() {
        $this->load->model('miniant/installation_task_model');
        $task_id = $this->input->post('task_id');
        $notes = $this->input->post('notes');
        $this->installation_task_model->edit($task_id, array('notes' => $notes));
        send_json_message('Task successfully updated', 'success');
    }

    public function update_statuses($order_id) {
        $status_ids = $this->input->post('values');
        $this->order_model->set_statuses($order_id, $status_ids);
        send_json_message('Statuses were updated');
    }

    public function add_tenancy() {
        $this->load->model('miniant/tenancy_model');
        $account_id = $this->input->post('account_id');
        $name = $this->input->post('name');
        $id = $this->tenancy_model->add(compact('account_id', 'name'));

        send_json_message('Tenancy/Owner successfully added', 'success', array('tenancy' =>compact('id','account_id','name')));
    }

    public function edit_tenancy() {
        $this->load->model('miniant/tenancy_model');
        $id = $this->input->post('id');
        $account_id = $this->input->post('account_id');
        $name = $this->input->post('name');
        $this->tenancy_model->edit($id, compact('account_id', 'name'));

        send_json_message('Tenancy/Owner successfully updated', 'success', array('tenancy' =>compact('id','account_id','name')));
    }

    public function remove_tenancy($tenancy_id, $units_too=false) {
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/tenancy_model');

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
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/tenancy_model');

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

    public function get_assigned_technicians() {
        $this->load->model('miniant/order_technician_model');

        $order_id = $this->input->post('order_id');
        $technicians = $this->order_technician_model->get(compact('order_id'));
        $order = $this->order_model->get($order_id);

        $json_data = array('technicians' => array());

        foreach ($technicians as $technician) {
            $user = $this->user_model->get($technician->technician_id);

            if ($order->senior_technician_id == $user->id) {
                $user->is_senior = true;
            } else {
                $user->is_senior = false;
            }

            $json_data['technicians'][] = $user;
        }

        send_json_data($json_data);

    }

}
