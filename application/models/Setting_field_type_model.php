<?php
class Setting_field_type_model extends MY_Model {
    public $table = 'settings_field_types';

    public function has_options($field_type_id) {
        $field_type = $this->get($field_type_id);
        return in_array($field_type->field_type, array('radio', 'dropdown', 'checkbox'));
    }
}
