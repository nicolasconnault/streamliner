<?php
class Units extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    public function get_data($unit_id) {
        echo json_encode($this->unit_model->get($unit_id));
    }

    public function get_dropdowns() {
        $dropdowns = array(
            'brands' => $this->brand_model->get_dropdown('name'),
            'unit_types' => $this->unit_model->get_types_dropdown(),
            // 'part_types' => $this->part_model->get_types_dropdown(),
            );

        return $dropdowns;
    }

    public function get_parts($id) {
        send_json_data(array('parts' => $this->unit_model->get_parts($id)));
    }

    public function get_part_data($order_part_id) {
        $srp = $this->orders_part_model->get($order_part_id);
        send_json_data((array) $srp);
    }

    public function add_part() {
        $id = $this->input->post('id');
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

        $params = compact('id', 'part_type_id', 'quantity');

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
        $id = $this->input->post('id');
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

        $params = compact('id', 'part_type_id', 'quantity');

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
