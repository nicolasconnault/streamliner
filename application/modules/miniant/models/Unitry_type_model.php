<?php
class Unitry_type_model extends MY_Model {
    public $table = 'miniant_unitry_types';

    public function get_by_unit_type_id($unit_type_id) {
        return $this->get(compact('unit_type_id'));
    }

    public function get_dropdown_by_unit_type_id($unit_type_id) {
        $this->db->where(compact('unit_type_id'));

        return $this->get_dropdown('name', '--Select unitry--');
    }
}
