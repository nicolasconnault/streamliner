<?php
class Orders_task_model extends MY_Model {
    public $table = 'miniant_orders_tasks';

    public function update_order_task($status, $order_task_id, $order_id) {
        $record = $this->get(compact('order_id', 'order_task_id'), true);

        if (empty($record) && $status) {
            return $this->add(compact('order_id', 'order_task_id'));
        } else {
            if ($status) {
                return $this->edit($record->id, compact('order_id', 'order_task_id'));
            } else {
                return $this->delete($record->id);
            }
        }
    }
}
