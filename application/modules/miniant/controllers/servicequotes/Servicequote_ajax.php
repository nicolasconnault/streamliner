<?php
class Servicequote_ajax extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    public function get_parts($servicequote_id) {
        $parts = $this->servicequote_model->get_parts($servicequote_id);
        send_json_data(array('parts' => $parts));
    }

    public function get_part_types_dropdown() {
        $part_type_id = $this->input->post('part_type_id');
        $unit_type_id = $this->part_type_model->get($part_type_id)->unit_type_id;
        $this->db->where('name <> "Labour"', null, false);
        $this->db->group_by('name');
        $part_types = $this->part_type_model->get_dropdown('name', false, false, false, null, null, 'name', array('unit_type_id' => $unit_type_id));
        send_json_data($part_types);
    }

    public function get_part_types($no_labour=false) {
        $term = $this->input->post('term');

        if ($no_labour) {
            $this->db->where('name <> "Labour"', null, false);
        }

        $this->db->where('name LIKE', '%'.$term.'%');
        $this->db->group_by('name');

        $part_types_array = $this->part_type_model->get_dropdown('name', false);
        $part_types = array();
        foreach ($part_types_array as $value => $label) {
            $part_type = new stdClass();
            $part_type->label = $label;
            $part_type->value = $value;
            $part_types[] = $part_type;
        }
        echo json_encode($part_types);
    }

    public function add_part() {
        require_capability('servicequotes:editsqs');
        $part = new stdClass();
        $servicequote_id = $this->input->post('servicequote_id');
        $part->servicequote_id = $servicequote_id;
        $part->quantity = $this->input->post('quantity');
        $part->part_number = $this->input->post('part_number');
        $part->description = $this->input->post('description');
        $part->part_type_id = $this->input->post('part_type_id');
        $part->assignment_id = $this->assignment_model->get(array('diagnostic_id' => $this->servicequote_model->get($servicequote_id)->diagnostic_id), true)->id;

        if (empty($part->part_type_id) && ($new_part_type = $this->input->post('new_part_type'))) {
            // Get unit type id from assignment id
            $unit = $this->unit_model->get($this->assignment_model->get_unit_id($part->assignment_id));
            $part->part_type_id = $this->part_type_model->add(array('name' => $new_part_type, 'unit_type_id' => $unit->unit_type_id, 'for_diagnostic' => 1, 'field_type' => 'int'));
        }

        $part->part_name = $this->part_type_model->get($part->part_type_id)->name;
        $part->needs_sq = true;
        $part->origin = 'Supplier';

        if ($part->id = $this->part_model->add($part)) {
            send_json_message("The part was added", 'success', array('part' => $part));
        } else {
            send_json_message("The part could not be added", 'danger');
        }
    }

    public function edit_part() {
        require_capability('servicequotes:editsqs');
        $part = new stdClass();
        $servicequote_id = $this->input->post('servicequote_id');
        $part->id = $this->input->post('id');
        $part->quantity = $this->input->post('quantity');
        $part->part_number = $this->input->post('part_number');
        $part->description = $this->input->post('description');
        $part->part_type_id = $this->input->post('part_type_id');
        $part->part_name = $this->part_type_model->get($part->part_type_id)->name;

        if ($this->part_model->edit($part->id, (array) $part)) {
            $part = $this->part_model->get($part->id);

            send_json_message("The part was updated", 'success', array('part' => $part));
        } else {
            send_json_message("The part could not be updated", 'danger');
        }
    }

    public function remove_part() {
        require_capability('servicequotes:editsqs');
        $part_id = $this->input->post('part_id');
        $this->part_model->delete($part_id);

        send_json_message('This part was successfully removed from this SQ');
    }

    public function record_supplier_quote() {
        require_capability('servicequotes:editsqs');
        $field = $this->input->post('name');
        $supplier_quote_id = $this->input->post('pk');
        $value = $this->input->post('value');

        if (empty($supplier_quote_id)) {
            $part_id = $this->input->post('part_id');
            $supplier_id = $this->input->post('supplier_id');
            $servicequote_id = $this->part_model->get($part_id)->servicequote_id;

            $params = compact('part_id', 'supplier_id', 'servicequote_id');

            if ($supplier_quote = $this->supplier_quote_model->get($params, true)) {
                $supplier_quote_id = $supplier_quote->id;
            } else {
                $params['quote_received_date'] = time();
                $supplier_quote_id = $this->supplier_quote_model->add($params);
            }
        }
        if ($field == 'unit_cost' || $field == 'total_cost') {
            if (!is_currency($value)) {
                send_json_data(array('success' => false, 'msg' => 'Please enter a valid number'));
                die();
            } else {
                $value = str_replace('$', '', $value);
                $value = str_replace(',', '', $value);
                $value = str_replace('-', '', $value);
            }
        }

        if ($this->supplier_quote_model->edit($supplier_quote_id, array($field => $value, 'quote_received_date' => time()))) {
            send_json_data(array('success' => true));
        } else {
            send_json_data(array('success' => false, 'msg' => 'Unknown error'));
        }
    }

    public function record_client_quote_data() {
        require_capability('servicequotes:editsqs');
        $field = $this->input->post('name');
        $part_id = $this->input->post('pk');
        $value = $this->input->post('value');

        if ($field == 'supplier_cost' || $field == 'client_cost') {
            if (!is_currency($value)) {
                send_json_data(array('success' => false, 'msg' => 'Please enter a valid number'));
                die();
            } else {
                $value = str_replace('$', '', $value);
                $value = str_replace(',', '', $value);
                $value = str_replace('-', '', $value);
            }
        }

        if ($this->part_model->edit($part_id, array($field => $value))) {
            send_json_data(array('success' => true));
        } else {
            send_json_data(array('success' => false, 'msg' => 'Unknown error'));
        }
    }

    public function add_client_quote_part() {
        require_capability('servicequotes:editsqs');
        $part_name = $this->input->post('value');
        $servicequote_id = $this->input->post('servicequote_id');
        $origin = 'Workshop';

        $this->part_model->add(compact('part_name', 'servicequote_id'));

        add_message('The part/labour was successfully added to this client\'s quote. Please edit the quantity and client cost before you generate the quote.');
        send_json_data(array('success' => true));
    }

    public function record_description_of_work() {
        require_capability('servicequotes:editsqs');
        $servicequote_id = $this->input->post('pk');
        $description_of_work = $this->input->post('value');

        if ($this->servicequote_model->edit($servicequote_id, compact('description_of_work'))) {
            send_json_data(array('success' => true));
        } else {
            send_json_data(array('success' => false, 'msg' => 'Unknown error'));
        }
    }

    public function record_part_received_date() {
        require_capability('servicequotes:editsqs');
        $supplier_quote_id = $this->input->post('pk');
        $value = human_to_unix($this->input->post('value'));
        $servicequote_id = $this->supplier_quote_model->get($supplier_quote_id)->servicequote_id;

        if ($this->supplier_quote_model->edit($supplier_quote_id, array('part_received_date' => $value))) {
            $this->db->where('purchase_order_id IS NOT NULL', null, false);
            $supplier_quotes = $this->supplier_quote_model->get(array('servicequote_id' => $servicequote_id));
            $all_parts_received = true;

            foreach ($supplier_quotes as $supplier_quote) {
                if (empty($supplier_quote->part_received_date)) {
                    $all_parts_received = false;
                }
            }

            trigger_event('supplier_parts_received', 'servicequote', $servicequote_id, !$all_parts_received, 'miniant');

            if ($all_parts_received) {
                $this->order_model->generate_repair_job($servicequote_id);
            }

            send_json_data(array('success' => true));
        } else {
            send_json_data(array('success' => false, 'msg' => 'Unknown error'));
        }
    }

    public function record_part_received_note() {
        require_capability('servicequotes:editsqs');
        $supplier_quote_id = $this->input->post('pk');
        $value = $this->input->post('value');
        $servicequote_id = $this->supplier_quote_model->get($supplier_quote_id)->servicequote_id;

        if ($this->supplier_quote_model->edit($supplier_quote_id, array('part_received_note' => $value))) {
            send_json_data(array('success' => true));
        } else {
            send_json_data(array('success' => false, 'msg' => 'Unknown error'));
        }
    }

    public function update_statuses($servicequote_id) {
        require_capability('servicequotes:editsqs');
        $status_ids = $this->input->post('values');
        $this->servicequote_model->set_statuses($servicequote_id, $status_ids);
        send_json_message('Statuses were updated');
    }

}
