<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
require_once(APPPATH.'/core/has_type_trait.php');
class Assignment_Model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_assignments';
    public static $assignment;

    public function get_values($assignment_id) {
        $this->load->model('miniant/order_technician_model');

        static $values = array();

        if (!empty($values[$assignment_id])) {
            return $values[$assignment_id];
        }

        $this->db->join('priority_levels', 'priority_levels.id = '.$this->table.'.priority_level_id');
        $this->db->join('miniant_diagnostics diagnostic', 'diagnostic.id = '.$this->table.'.diagnostic_id', 'LEFT OUTER');
        $this->db->join('miniant_units unit', 'unit.id = '.$this->table.'.unit_id');
        $this->db->join('miniant_orders order', 'order.id = '.$this->table.'.order_id');
        $this->db->join('types ut', 'ut.id = unit.unit_type_id', 'LEFT OUTER');
        $this->db->join('miniant_brands b', 'b.id = unit.brand_id', 'LEFT OUTER');
        $this->db->join('miniant_tenancies tenancy', 'tenancy.id = unit.tenancy_id', 'LEFT OUTER');
        $this->db->join('accounts', 'accounts.id = order.account_id', 'LEFT OUTER');
        $this->db->join('contacts', 'contacts.id = order.site_contact_id', 'LEFT OUTER');
        $this->db->join('users', 'users.id = order.senior_technician_id', 'LEFT OUTER');

        $this->db->select($this->table.'.*,
            priority_levels.name as priority,
            ut.name as unit_type,
            b.name as unit_brand,
            tenancy.name as unit_tenancy,
            accounts.name as account_name,
            CONCAT(contacts.first_name," ", contacts.surname) as site_contact_name
            ', false);

        $this->select_foreign_table_fields('miniant_units', 'unit');
        $this->select_foreign_table_fields('miniant_diagnostics', 'diagnostic');
        $this->select_foreign_table_fields('miniant_orders', 'order');
        $this->select_foreign_table_fields('users');

        $assignment = $this->get($assignment_id);

        if (empty($assignment)) {
            return null;
        }

        $assignment->status = '';
        $assignment->statuses = $this->assignment_model->get_statuses($assignment->id);

        foreach ($this->assignment_model->get_statuses($assignment->id) as $status) {
            $assignment->status .= " $status";
        }

        $assignment->reference_id = $this->get_reference_id($assignment_id);
        $assignment->senior_technician = $assignment->user_first_name;

        $values[$assignment_id] = $assignment;
        return $values[$assignment_id];
    }

    public function get_for_schedule($start, $end, $technician_id=null, $is_technician=false) {
        $this->load->model('miniant/order_technician_model');

        $is_complete_status_id = $this->order_technician_model->get_status_id('COMPLETE');
        $this->db->join('miniant_orders', 'miniant_orders.id = '.$this->table.'.order_id');

        if ($is_technician) {
            // Only show assignments that are not complete.
            $this->db->join('miniant_order_technicians', 'miniant_order_technicians.order_id = '.$this->table.'.order_id AND miniant_order_technicians.technician_id = '.$technician_id, 'LEFT OUTER');
            $this->db->where($this->table.".appointment_date BETWEEN UNIX_TIMESTAMP() - 43000 AND $end", null, false); // For technician, only retrieve assignments at most 12 hours old
            $this->db->where("miniant_orders.id NOT IN (SELECT miniant_orders.id FROM miniant_orders
                JOIN miniant_order_technicians ON miniant_order_technicians.order_id = miniant_orders.id AND miniant_order_technicians.technician_id = '$technician_id'
                JOIN document_statuses ON document_statuses.document_id = miniant_order_technicians.id AND document_statuses.document_type = 'order_technician'
                WHERE document_statuses.status_id = $is_complete_status_id)");

        } else {
            $this->db->where($this->table.".appointment_date BETWEEN $start AND $end", null, false);
        }

        $this->db->order_by($this->table.'.appointment_date', 'ASC');

        if ($is_technician && !empty($technician_id)) {
            $this->db->where($this->table.'.technician_id', $technician_id);
        }

        $this->db->select();
        $this->db->select($this->table.'.*');
        $this->db->select('miniant_orders.order_type_id');
        $assignments = $this->get();

        if (empty($assignments)) {
            return array();
        }

        if ($is_technician) {
            $first_assignment = reset($assignments);
            if ($first_assignment->appointment_date >= $start && $first_assignment->appointment_date <= $end) {
                $assignments = array($first_assignment);
            } else {
                $assignments = array();
            }
        }

        return $assignments;
    }

    public function get_reference_id($assignment_id) {
        $this->db->join('miniant_orders', 'miniant_orders.id = '.$this->table.'.order_id');
        $this->db->select('CONCAT("J",miniant_orders.id, "-", "UN", '.$this->table.'.unit_id) AS ref',  false);
        return $this->get($assignment_id)->ref;
    }

    public function get_static_object($assignment_id) {
        if (!empty(Assignment_model::$assignment) && Assignment_model::$assignment->id == $assignment_id) {
            $assignment = Assignment_model::$assignment;
        } else {
            $assignment = $this->get($assignment_id);
            Assignment_model::$assignment = $assignment;
        }

        return $assignment;
    }

    public function delete_if_empty($assignment_id) {
        $assignment = $this->get_static_object($assignment_id);

        $diagnostic = $this->diagnostic_model->get($assignment->diagnostic_id);

        if (empty($diagnostic)) {
            return $this->delete($assignment_id);
        } else {
            return false;
        }
    }

    public function is_diagnostic_complete($assignment_id) {
        $assignment = $this->get_static_object($assignment_id);
        $diagnostic = $this->diagnostic_model->get($assignment->diagnostic_id);
        $unit = $this->unit_model->get($assignment->unit_id);

        $is_complete = true;

        if (!$this->assignment_model->has_statuses($assignment_id, array('ISSUES DIAGNOSED', 'DOWD RECORDED'), 'AND')) {
            $is_complete = false;
        }

        if (!$this->unit_model->has_statuses($unit->id, array('UNIT DETAILS RECORDED'))) {
            $is_complete = false;
        }

        if (!$this->diagnostic_model->has_statuses($diagnostic->id, array('COMPLETE'))) {
            $is_complete = false;
        }


        return $is_complete;
    }

    public function get_city($assignment_id) {
        $this->db->join('miniant_orders', 'miniant_orders.id = '.$this->table.'.order_id');
        $this->db->join('addresses', 'addresses.id = miniant_orders.site_address_id');
        $this->db->select('addresses.city');
        return $this->get($assignment_id)->city;
    }

    /**
     * Before adding, also create an order_technician record, if needed
     */
    public function add($params) {
        $params = (array) $params;

        if (!empty($params['technician_id'])) {
            $this->order_technician_model->add_if_new($params['order_id'], $params['technician_id']);
        }
        return parent::add($params);
    }

    /**
     * If technician is changed, updated order_technicians
     */
    public function edit($assignment_id, $params) {

        $assignment = $this->get($assignment_id);
        $previous_technician_id = $assignment->technician_id;

        // Multiple technicians can be assigned to a single unit. In this case, create other assignments as needed, and delete existing ones if needed
        if (!empty($params['technician_id']) && is_array($params['technician_id'])) {
            $all_assignments = $this->get(array('order_id' => $assignment->order_id, 'unit_id' => $assignment->unit_id));

            foreach ($params['technician_id'] as $new_technician_id) {
                $technician_is_already_assigned = false;

                foreach ($all_assignments as $existing_assignment) {
                    if ($existing_assignment->technician_id == $new_technician_id) {
                        $technician_is_already_assigned = true;
                    }
                }

                if (!$technician_is_already_assigned) {
                    $assignment_params = array(
                        'order_id' => $assignment->order_id,
                        'unit_id' => $assignment->unit_id,
                        'workflow_id' => $assignment->workflow_id,
                        'technician_id' => $new_technician_id,
                        'appointment_date' => $assignment->appointment_date,
                        'estimated_duration' => $assignment->estimated_duration
                    );

                    if (!($this->get(array('order_id' => $assignment->order_id, 'unit_id' => $assignment->unit_id, 'technician_id' => $new_technician_id)))) {
                        $this->add($assignment_params);
                    }
                }
            }

            $all_assignments = $this->get(array('order_id' => $assignment->order_id, 'unit_id' => $assignment->unit_id));

            foreach ($all_assignments as $existing_assignment) {
                $assignment_has_a_technician = false;
                foreach ($params['technician_id'] as $new_technician_id) {
                    if ($existing_assignment->technician_id == $new_technician_id) {
                        $assignment_has_a_technician = true;
                    }
                }

                if (!$assignment_has_a_technician) {
                    $this->delete($existing_assignment->id);
                }
            }

            unset($params['technician_id']);
            $result = parent::edit($assignment_id, $params);
            // Update appointment_date and estimated_duration
            return true;
        } else {
            if (empty($params['technician_id'])) {
                return parent::edit($assignment_id, $params);
            } else {
                $this->order_technician_model->add_if_new($assignment->order_id, $params['technician_id']);
            }

            // If the order/unit/tech combination already exists, delete this assignment instead of editing it
            if ($params['technician_id'] != $assignment->technician_id && ($this->get(array('order_id' => $assignment->order_id, 'unit_id' => $assignment->unit_id, 'technician_id' => $params['technician_id'])))) {
                $result = parent::delete($assignment_id);
            } else {
                $result = parent::edit($assignment_id, $params);
            }

            $this->order_technician_model->delete_if_last($assignment->order_id, $previous_technician_id, $assignment->id);
            return $result;
        }
    }
    /**
     * Before deleting, also remove the order_technician record, if needed
     */
    public function delete($id_or_fields) {

        $assignments = $this->get($id_or_fields);

        $result = parent::delete($id_or_fields);

        if (!is_array($assignments)) {
            $assignments = array($assignments);
        }

        foreach ($assignments as $assignment) {
            $this->order_technician_model->delete_if_last($assignment->order_id, $assignment->technician_id, $assignment->id);
        }

        return $result;
    }

    public function get_assigned_technicians($order_id, $unit_id) {
        $assignments = $this->get(compact('order_id', 'unit_id'));
        $technicians = array();
        foreach ($assignments as $assignment) {
            $technicians[] = $assignment->technician_id;
        }
        return $technicians;
    }

    public function get_units($assignment_id, $technician_id=null) {
        $units = array();
        $units = $this->unit_model->get_from_assignment_id($assignment_id, $technician_id);

        return $units;
    }

    /**
     * Assignments are grouped together at the code level if they belong to the same order and start at the same time
     */
    public function get_assignment_group($assignment_id, $technician_id=null) {
        $main_assignment = $this->get($assignment_id);
        $params = array('order_id' => $main_assignment->order_id, 'appointment_date' => $main_assignment->appointment_date);

        if (!empty($technician_id)) {
            $params['technician_id'] = $technician_id;
        }

        return $this->get($params);
    }

    public function requires_parts_from_supplier($assignment_id) {
        $parts = $this->part_model->get(array('assignment_id' => $assignment_id));

        if (empty($parts)) {
            return false;
        }

        foreach ($parts as $part) {

            if ($part->origin == 'Supplier') {
                return true;
            }
        }

        return false;
    }

    public function has_sq_approved($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('SQ APPROVED'));
    }

    public function has_repairs_approved($assignment_id) {
        return $this->assignment_model->has_statuses($assignment_id, array('REPAIRS APPROVED'));
    }

    public function get_diagnostic_assignment($assignment_id) {
        $assignment = $this->get($assignment_id);
        $order = $this->order_model->get($assignment->order_id);
        $sq = $this->servicequote_model->get($order->parent_sq_id);

        $order_id = (empty($sq->order_id)) ? $order->id : $sq->order_id;

        if (empty($sq)) {
            return null;
        } else {
            return $this->get(array('order_id' => $order_id, 'unit_id' => $assignment->unit_id), true);
        }
    }
}
