<?php
require_once(APPPATH.'/core/has_type_trait.php');
require_once(APPPATH.'/core/has_statuses_trait.php');
class Unit_Model extends MY_Model {
    use has_types;
    use has_statuses;
    public $table = 'miniant_units';

    function get_values($unit_id=null) {
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/assignment_model');
        $this->load->model('miniant/order_signature_model');

        static $values = array();

        if (!empty($values[$unit_id])) {
            return $values[$unit_id];
        }

        $this->db->from($this->table)->where($this->table.'.id', $unit_id);
        $this->db->join('miniant_brands brand', 'brand.id = '.$this->table.'.brand_id', 'LEFT OUTER');
        $this->db->join('types', 'types.id = '.$this->table.'.unit_type_id', 'LEFT OUTER');
        $this->db->join('miniant_refrigerant_types refrigerant_type', 'refrigerant_type.id = '.$this->table.'.refrigerant_type_id', 'LEFT OUTER');
        $this->db->join('miniant_unitry_types unitry_type', 'unitry_type.id = '.$this->table.'.unitry_type_id', 'LEFT OUTER');
        $this->db->join('miniant_tenancies tenancy', 'tenancy.id = '.$this->table.'.tenancy_id', 'LEFT OUTER');

        $this->db->select(''.$this->table.'.*,
            brand.name AS brand,
            types.name AS type,
            refrigerant_type.name AS refrigerant_type,
            unitry_type.name AS unitry_type,
            tenancy.name AS tenancy',
            false);

        $this->select_foreign_table_fields('miniant_brands', 'brand');
        $this->select_foreign_table_fields('types');
        $this->select_foreign_table_fields('miniant_refrigerant_types', 'refrigerant_type');
        $this->select_foreign_table_fields('miniant_unitry_types', 'unitry_type');
        $this->select_foreign_table_fields('miniant_tenancies', 'tenancy');

        $unit_data = array();

        $query = $this->db->get();

        if ($result = $query->result()) {
            $unit_data = (array) $result[0];
            $unit_data['serial'] = $this->get_serial_string($result[0]);
            $unit_data['model'] = $this->get_model_string($result[0]);
            $values[$unit_id] = $unit_data;
            return $values[$unit_id];
        } else {
            return array();
        }
    }

    public function get_serial_string($unit) {
        if (!empty($unit->serial_number)) {
            return $unit->serial_number;
        } else if (!empty($unit->outdoor_serial_number)) {
            return $unit->outdoor_serial_number;
        } else if (!empty($unit->indoor_serial_number)) {
            return $unit->indoor_serial_number;
        } else {
            return null;
        }
    }

    public function get_model_string($unit) {
        if (!empty($unit->model)) {
            return $unit->model;
        } else if (!empty($unit->outdoor_model)) {
            return $unit->outdoor_model;
        } else if (!empty($unit->indoor_model)) {
            return $unit->indoor_model;
        } else {
            return null;
        }
    }

    public function get_brand_string($unit) {

    }

    public function get_orders($unit_id) {
        $this->db->where('miniant_assignments.unit_id', $unit_id);
        $this->db->join('miniant_assignments', 'miniant_assignments.order_id = miniant_orders.id');
        return $this->order_model->get();
    }

    public function get_from_order_id($order_id, $technician_id=null) {
        $this->db->select(''.$this->table.'.*, miniant_assignments.id AS assignment_id');
        $this->db->where('miniant_assignments.order_id', $order_id);

        if (!empty($technician_id)) {
            $this->db->where('miniant_assignments.technician_id', $technician_id);
        }

        $this->db->join('miniant_assignments', 'miniant_assignments.unit_id = '.$this->table.'.id');
        $units = parent::get();

        if (empty($units)) {
            return array();
        }

        if (!is_array($units)) {
            $unit = (object) $this->unit_model->get_values($units->id);
            $unit->assignment_id = $units->assignment_id;
            return $unit;
        }

        foreach ($units as $key => $unit) {
            $units[$key] = (object) $this->unit_model->get_values($unit->id);
            $units[$key]->assignment_id = $unit->assignment_id;
        }

        return $units;
    }

    /**
     * If the technician_id is given, retrieve all units that are at the same job/time/technician as the assignment given
     * If no technician_id is given, retrieve all units that are at the same job/time as the assignment given
     */
    public function get_from_assignment_id($assignment_id, $technician_id=null) {
        $assignment = $this->assignment_model->get($assignment_id);
        $order = $this->order_model->get($assignment->order_id);
        $is_senior_technician = $order->senior_technician_id == $this->session->userdata('user_id');

        $params = array('order_id' => $assignment->order_id, 'appointment_date' => $assignment->appointment_date);

        if ($technician_id) {
            $params['technician_id'] = $technician_id;
        }

        $units = array();

        $assignments = $this->assignment_model->get($params);

        $this->remove_duplicate_assignments($assignments, $technician_id);

        foreach ($assignments as $assignment2) {
            $unit = (object) $this->get_values($assignment2->unit_id);
            $unit->assignment_id = $assignment2->id;
            $unit->assignment = (object) $this->assignment_model->get_values($assignment2->id);
            $unit->tab_label = "Unit $unit->id";

            if ($is_senior_technician) {
                $unit->tab_label .= " (".$this->user_model->get($assignment2->technician_id)->first_name.')';
            }

            $units[] = $unit;
        }

        return $units;
    }

    /**
     * If two technicians are assigned to the same unit, $units will have a duplicate.
     * In that case, who should be able to edit the unit details?
     * Only the senior technician? What if he is just acting as a supervisor, and the technicians are the ones gathering the data?
     * Then the junior technicians come to the senior tech and give him the details, and HE enters them in his tablet.
     * Junior techs just enter when they start and finish the job
     * SOOO.... NO DUPLICATE UNITS! ONly show the one that belongs to the logged in user
     * BUt... if junior tech has a separate assignment for that same unit, that means the assignment will get completed before any diagnostic or SQ is completed.
     */
    public function remove_duplicate_assignments(&$assignments) {
        $unit_ids = array();
        $current_technician_id = $this->session->userdata('user_id');

        foreach ($assignments as $key => $assignment) {
            if (!empty($unit_ids[$assignment->unit_id])) {
                if ($unit_ids[$assignment->unit_id]->technician_id == $current_technician_id) {
                    unset($assignments[$key]);
                    continue;
                } else {
                    foreach ($assignments as $key2 => $assignment2) {
                        if ($assignment2->id == $unit_ids[$assignment->unit_id]->id) {
                            unset($assignments[$key2]);
                        }
                    }
                }
            }

            $unit_ids[$assignment->unit_id] = $assignment;
        }
    }

    public function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array(), $no_extra_values=false) {
        $this->db->select('');
        $this->db->select(''.$this->table.'.*');
        $units = parent::get($id_or_fields, $first_only, $order_by, $select_fields);

        if ($no_extra_values) {
            return $units;
        }

        if (!is_array($units) && !is_null($units)) {
            return (object) $this->unit_model->get_values($units->id);
        }

        if (is_null($units)) {
            return array();
        }

        foreach ($units as $key => $unit) {
            $units[$key] = (object) $this->unit_model->get_values($unit->id);
        }

        return $units;
    }

    // Override parent add() function. If order_id is set, create a record in the assignments table
    public function add($fields) {
        $fields = (array) $fields;

        if (!empty($fields['order_id'])) {
            $order_id = $fields['order_id'];
            unset($fields['order_id']);
            $unit_id = parent::add($fields);
            $this->db->insert('miniant_assignments', array('order_id' => $order_id, 'unit_id' => $unit_id));

            if (!empty($fields['tenancy_id'])) {
                $this->tenancy_log_model->add_unit($params['tenancy_id'], $unit->id);
            }
            return $unit_id;
        } else {
            return parent::add($fields);
        }
    }

    public function add_to_order($unit_id, $order_id) {
        if (empty($unit_id) || empty($order_id)) {
            return false;
        }

        $order = $this->order_model->get($order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $workflow_id = $this->workflow_model->get(array('name' => strtolower($order_type)), true)->id;
        if (empty($workflow_id)) {
            die("Workflow with name $order_type doesn't exist!");
        }


        return $this->assignment_model->add(compact('order_id', 'unit_id', 'workflow_id'));
    }

    public function remove_from_order($unit_id, $order_id) {
        if (empty($unit_id) || empty($order_id)) {
            return false;
        }

        return $this->db->delete('miniant_assignments', compact('order_id', 'unit_id'));
    }

    // If tenancy_id has changed, log the change with tenancy_log
    public function edit($unit_id, $params) {
        $unit = $this->get($unit_id);
        if (!empty($params['tenancy_id']) && $unit->tenancy_id != $params['tenancy_id']) {

            if (!empty($unit->tenancy_id)) {
                $this->tenancy_log_model->remove_unit($unit->tenancy_id, $unit->id);
            }

            $this->tenancy_log_model->add_unit($params['tenancy_id'], $unit->id);
        }

        return parent::edit($unit_id, $params);
    }

    /**
     * Once a unit's serial number has been identified by a technician,
     * the system checks if that serial number already exists at this site address.
     * If yes, the current unit is deleted, and the ID of the existing unit is returned.
     * The assignment is also updated to reflect the change.
     */
    public function merge_if_serial_exists($unit_data, $assignment_id) {
        // Get all the units recorded at this site address
        $assignment = $this->assignment_model->get($assignment_id);
        $order = $this->order_model->get($assignment->order_id);

        $units = $this->get(array('site_address_id' => $order->site_address_id));

        $serial_matches = false;

        foreach ($units as $unit) {
            $has_same_serial_number = strtolower($unit_data['serial_number']) == strtolower($unit->serial_number);
            $has_same_indoor_serial_number = (empty($unit_data['indoor_serial_number'])) ? false : strtolower($unit_data['indoor_serial_number']) == strtolower($unit->indoor_serial_number);
            $has_same_outdoor_serial_number = (empty($unit_data['outdoor_serial_number'])) ? false : strtolower($unit_data['outdoor_serial_number']) == strtolower($unit->outdoor_serial_number);

            if (!empty($unit_data['serial_number']) && $has_same_serial_number) {
                $serial_matches = $unit->id;
                break;
            }

            if (!empty($unit_data['indoor_serial_number']) && $has_same_outdoor_serial_number) {
                $serial_matches = $unit->id;
            }

            if (!empty($unit_data['outdoor_serial_number']) && !$has_same_outdoor_serial_number) {
                $serial_matches = false;
                break;
            } else if (!empty($unit_data['outdoor_serial_number']) && $has_same_outdoor_serial_number) {
                if (!$has_same_indoor_serial_number) {
                    $serial_matches = false;
                } else {
                    $serial_matches = $unit->id;
                }
            }
        }

        $assignment = $this->assignment_model->get($assignment_id);

        if ($serial_matches) {
            $this->assignment_model->edit($assignment_id, array('unit_id' => $serial_matches));
            if ($assignment->unit_id != $serial_matches) {
                $this->unit_model->delete($assignment->unit_id);
            }
            return $serial_matches;
        } else {
            $this->unit_model->edit($assignment->unit_id, $unit_data);
            return $assignment->unit_id;
        }
    }
}
