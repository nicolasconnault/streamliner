<?php
require_once(APPPATH.'/core/has_type_trait.php');
class Tradesman_model extends MY_Model {
    use has_types;

    public $table = 'building_tradesmen';

    public function __construct() {
        parent::__construct();
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {
                $db_records[$key]['type'] = $this->tradesman_model->get_type_string($row['type']);
            }
        };
    }

    public function get_sorted_dropdown() {
        $tradesmen = $this->get_dropdown('name', false, false, 'type_id');

        // Give proper name to optgroups, and highlight trady in RED if already booked for that day
        $final_tradesmen = array();

        foreach ($tradesmen as $type_id => $tradesman_array) {
            $type_string = $this->get_type_string($type_id);

            $final_tradesmen[$type_string] = $tradesman_array;
        }
        ksort($final_tradesmen);
        $final_tradesmen = array(null => '-- Select One --') + $final_tradesmen;
        return $final_tradesmen;
    }

    public function delete($id_or_fields) {

        if (is_array($id_or_fields)) {
            $tradesmen = $this->get($id_or_fields);
        } else {
            $tradesmen = array($this->get($id_or_fields));
        }

        $result = parent::delete($id_or_fields);

        foreach ($tradesmen as $tradesman) {
            $bookings = $this->booking_model->get(array('tradesman_id' => $tradesman->id));
            foreach ($bookings as $booking) {
                $this->booking_model->delete($booking->id);
            }
        }

        $this->db->query("DELETE FROM `building_bookings` WHERE tradesman_id NOT IN ( SELECT id FROM building_tradesmen)");

        return $result;
    }

    public function is_unique($tradesman_data, $tradesman_id=null) {
        if (!empty($tradesman_id)) {
            $tradesman = $this->get($tradesman_id);
            return $tradesman->name != $tradesman_data['name'] && $tradesman->type_id != $tradesman_data['type_id'];
        }

        if ($existing_tradesman = $this->get(array('name' => $tradesman_data['name'], 'type_id' => $tradesman_data['type_id']))) {
            return false;
        } else {
            return true;
        }
    }
}
