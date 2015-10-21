<?php
require_once(APPPATH.'core/has_statuses_trait.php');
class Order_technician_model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_order_technicians';

    public function add_if_new($order_id, $technician_id) {
        if (!($order_technician = $this->get(compact('order_id', 'technician_id')))) {
            return $this->add(compact('order_id', 'technician_id'));
        }
    }

    /**
     * Only delete this record if no other assignments are assigned to this technician for this order
     */
    public function delete_if_last($order_id, $technician_id, $assignment_id) {
        $assignments = $this->assignment_model->get(array('order_id' => $order_id, 'technician_id' => $technician_id));
        if (empty($assignments)) {
            return $this->delete(compact('order_id', 'technician_id'));
        }
    }

    public function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {
        static $order_technician = array();

        if (!empty($order_technician[serialize($id_or_fields)]) && $first_only && is_null($order_by) && empty($select_fields)) {
            return $order_technician[serialize($id_or_fields)];
        }

        $result = parent::get($id_or_fields, $first_only, $order_by, $select_fields);

        if ($first_only && is_null($order_by) && empty($select_fields)) {
            $order_technician[serialize($id_or_fields)] = $result;
        }

        return $result;
    }
}
