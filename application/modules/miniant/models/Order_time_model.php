<?php
class Order_time_model extends MY_Model {
    public $table = 'miniant_order_times';

    // Checks if there are any recorded entries with no time_end value, and puts the time() there
    public function finish_time($order_id, $technician_id=null) {
        $params = compact('order_id');
        if (!empty($technician_id)) {
            $params['technician_id'] = $technician_id;
        }

        $times = $this->get($params);
        foreach ($times as $time) {
            if (empty($time->time_end)) {
                $this->edit($time->id, array('time_end' => time()));
            }
        }
    }

    public function push_back_starting_time($order_id, $technician_id, $value) {
        if ($earliest_record = $this->get(compact('order_id', 'technician_id'), true, 'time_start ASC')) {
            return $this->edit($earliest_record->id, array('time_start' => $earliest_record->time_start - $value));
        } else {
            return false;
        }
    }
}
