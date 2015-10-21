<?php
class Tenancy_log_model extends MY_Model {
    public $table = 'miniant_tenancy_log';

    public function add_unit($tenancy_id, $unit_id) {
        return $this->add(array('tenancy_id' => $tenancy_id, 'unit_id' => $unit_id, 'change_type' => 'unit_id', 'new_value' => 'added'));
    }

    public function remove_unit($tenancy_id, $unit_id) {
        return $this->add(array('tenancy_id' => $tenancy_id, 'unit_id' => $unit_id, 'change_type' => 'unit_id', 'new_value' => 'removed'));
    }

    public function change_name($tenancy_id, $new_name) {
        return $this->add(array('tenancy_id' => $tenancy_id, 'change_type' => 'name', 'new_value' => $new_name));
    }

    public function delete_tenancy($tenancy_id) {
        return $this->add(array('tenancy_id' => $tenancy_id, 'change_type' => 'tenancy', 'new_value' => 'removed'));
    }

    public function create_tenancy($tenancy_id) {
        return $this->add(array('tenancy_id' => $tenancy_id, 'change_type' => 'tenancy', 'new_value' => 'created'));
    }
}
