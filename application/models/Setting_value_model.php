<?php
class Setting_value_model extends MY_Model {
    public $table = 'settings_values';

    public function get_options($setting_id) {
        $this->db->where(compact('setting_id'));
        return $this->get_dropdown('value', false);
    }
}
