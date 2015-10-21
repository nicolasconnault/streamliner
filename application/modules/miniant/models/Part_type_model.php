<?php
class Part_type_Model extends MY_Model {
    public $table = 'miniant_part_types';

    public function get_id($name, $unit_type_id) {
        if ($object = $this->get(compact('name', 'unit_type_id'), true)) {
            return $object->id;
        } else {
            return null;
        }
    }
}
