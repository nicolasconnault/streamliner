<?php
class Step_Model extends MY_Model {
    public $table = 'miniant_steps';
    public function get_id_from_name($name) {
        if ($step = $this->get(compact('name'), true)) {
            return $step->id;
        } else {
            return null;
        }
    }
}
