<?php
class Unit extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');

        $this->config->set_item('replacer', array('orders' => array('index|Jobs')));
        $this->config->set_item('exclude', array('index'));
        require_capability('orders:manageunits');
    }

    public function add($order_id) {

        $title = "Add a unit to a job";
        $help = 'Use this form to add a unit to a job';

        $this->config->set_item('replacer', array('orders' => array('/orders/order/index|Jobs'), 'edit' => $title, 'add' => $title));

        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/unit/edit',
                             'order_id' => $order_id,
                             'dropdowns' => $this->get_dropdowns(),
                             'feature_type' => 'Custom Feature',
                             'module' => 'miniant',
                             'csstoload' => array(),
                             'jstoloadinfooter' => array(
                                 'orders/unit_edit',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function edit($assignment_id) {
        $assignment = $this->assignment_model->get($assignment_id);
        $unit_data = $this->unit_model->get_values($assignment->unit_id);

        form_element::$default_data = (array) $unit_data;

        $title = "Edit unit";
        $help = 'Use this form to edit this unit';

        $this->config->set_item('replacer', array('orders' => array('/orders/order/index|Jobs'), 'edit' => $title));

        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/unit/edit',
                             'order_id' => $unit_data['order_id'],
                             'assignment_id' => $assignment_id,
                             'unit_data' => $unit_data,
                             'dropdowns' => $this->get_dropdowns(),
                             'feature_type' => 'Custom Feature',
                             'jstoloadinfooter' => array(
                                 'application/orders/unit_edit'
                             ),
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }


    public function process_edit() {

        $this->load->library('form_validation');
        $this->load->helper('date');

        $order_id = $this->input->post('order_id');
        $assignment_id = $this->input->post('assignment_id');
        $order = $this->order_model->get($order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $workflow_id = $this->workflow_model->get(array('name' => strtolower($order_type)), true)->id;

        $this->form_validation->set_rules('order_id', 'Job Number', 'required');
        $this->form_validation->set_rules('brand_id', 'Brand', 'required');
        $this->form_validation->set_rules('unit_type_id', 'Unit type', 'required');
        $this->form_validation->set_rules('location', 'Location', 'required');

        if (empty($order_unit_id)) {
            $redirect_url = 'orders/unit/add/'.$order_id;
        } else {
            $redirect_url = 'orders/unit/edit/'.$order_unit_id;
        }

        $json_params = array();

        if ($this->form_validation->run()) {
            if (empty($order_unit_id)) {
                $new_unit_params = $this->input->post();
                $new_sr_unit_params = array('order_id' => $new_unit_params['order_id']);
                unset($new_unit_params['order_id']);
                unset($new_unit_params['order_unit_id']);
                $new_sr_unit_params['unit_id'] = $this->unit_model->add($new_unit_params);
                $new_sr_unit_params['workflow_id'] = $workflow_id;

                $json_params['assignment_id'] = $this->assignment_model->add($new_sr_unit_params);
                send_json_message('This unit was successfully added to the job!', 'success', $json_params);
            } else {
                $order_unit = $this->orders_unit_model->get($order_unit_id);
                $unit_params = $this->input->post();
                unset($unit_params['order_id']);
                unset($unit_params['order_unit_id']);
                $this->unit_model->edit($order_unit->unit_id, $unit_params);

                $json_params['order_unit_id'] = $order_unit_id;
                send_json_message('This unit was successfully updated!', 'success', $json_params);
            }

        } else {
            if (IS_AJAX) {
                send_json_message(validation_errors(), 'danger');
            } else {
                add_message('Errors!', 'danger');
                redirect($redirect_url);
            }
        }
    }

    public function get_dropdowns() {
        $dropdowns = array(
            'brands' => $this->brand_model->get_dropdown('name'),
            'unit_types' => $this->unit_type_model->get_dropdown('name'),
            'part_types' => $this->part_type_model->get_dropdown('name'),
            );

        return $dropdowns;
    }

    public function get_parts($order_unit_id) {
        send_json_data(array('parts' => $this->orders_unit_model->get_parts($order_unit_id)));
    }

    public function get_part_data($order_part_id) {
        $srp = $this->orders_part_model->get($order_part_id);
        send_json_data((array) $srp);
    }

    public function add_part() {
        $order_unit_id = $this->input->post('order_unit_id');
        $part_type_id = $this->input->post('part_type_id');
        $quantity = $this->input->post('quantity');

        $json_params = array();
        $json_params['errors'] = array();
        $message = '';
        $type = 'success';

        if (empty($part_type_id)) {
            $json_params['errors']['part_type_id'] = 'Please select a part type';
        }
        if (empty($quantity)) {
            $json_params['errors']['quantity'] = 'Please enter a quantity';
        }

        $params = compact('order_unit_id', 'part_type_id', 'quantity');

        if (empty($json_params['errors'])) {
            $sr_part_id = $this->orders_part_model->add($params);
            $message = 'This part was added successfully';
            $json_params['sr_part_id'] = $sr_part_id;
            $type = 'success';
        } else {
            $type = 'danger';
            $message = 'This part could not be added';
        }

        send_json_message($message, $type, $json_params);
    }

    public function edit_part() {
        $order_part_id = $this->input->post('order_part_id');
        $order_unit_id = $this->input->post('order_unit_id');
        $part_type_id = $this->input->post('part_type_id');
        $quantity = $this->input->post('quantity');

        $json_params = array();
        $json_params['errors'] = array();
        $message = '';
        $type = 'success';

        if (empty($order_part_id)) {
            $json_params['errors']['order_part_id'] = 'Missing order_part_id';
        }
        if (empty($part_type_id)) {
            $json_params['errors']['part_type_id'] = 'Please select a part type';
        }
        if (empty($quantity)) {
            $json_params['errors']['quantity'] = 'Please enter a quantity';
        }

        $params = compact('order_unit_id', 'part_type_id', 'quantity');

        if (empty($json_params['errors'])) {
            $this->orders_part_model->edit($order_part_id, $params);
            $message = 'This part was edited successfully';
            $json_params['sr_part_id'] = $order_part_id;
            $type = 'success';
        } else {
            $type = 'danger';
            $message = 'This part could not be edited';
        }

        send_json_message($message, $type, $json_params);
    }

    public function remove_part() {
        $sr_part_id = $this->input->post('order_part_id');
        $this->orders_part_model->delete($sr_part_id);

        send_json_message('This part was successfully removed from this unit');
    }

}
