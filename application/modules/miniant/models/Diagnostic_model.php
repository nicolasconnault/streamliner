<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
class Diagnostic_Model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_diagnostics';

    public function get_values($diagnostic_id) {
        if (empty($diagnostic_id)) {
            add_message('diagnostic_model->get_values() was called with an empty diagnostic_id parameter');
            return false;
        }

        $this->db->join('miniant_assignments assignment', 'assignment.diagnostic_id = '.$this->table.'.id');
        $this->db->join('miniant_units unit', 'unit.id = assignment.unit_id', 'LEFT OUTER');

        $this->db->select($this->table.'.*');
        $this->select_foreign_table_fields('miniant_assignments', 'assignment');
        $this->select_foreign_table_fields('miniant_units', 'unit');

        $diagnostic = $this->get($diagnostic_id);

        $diagnostic->status = '';

        foreach ($this->diagnostic_model->get_statuses($diagnostic->id) as $status) {
            $diagnostic->status .= " $status";
        }

        $diagnostic->reference_id = $this->get_reference_id($diagnostic_id);

        $diagnostic->diagnostic_issues = $this->diagnostic_issue_model->get(compact('diagnostic_id'));

        return $diagnostic;
    }

    public function get_reference_id($diagnostic_id) {
        $this->db->join('miniant_assignments assignment', 'assignment.diagnostic_id = '.$this->table.'.id');
        $this->db->join('miniant_orders order', 'order.id = assignment.order_id');
        $this->db->select('CONCAT("J",order.id, "-", "AS", assignment.id, "-", "DI", '.$this->table.'.id) AS ref',  false);
        return $this->get($diagnostic_id)->ref;
    }

    public function get_city($diagnostic_id) {
        $this->db->join('miniant_assignments assignment', 'assignment.diagnostic_id = '.$this->table.'.id');
        $this->db->join('miniant_orders order', 'order.id = assignment.order_id');
        $this->db->join('addresses', 'addresses.id = order.site_address_id');
        $this->db->select('addresses.city');
        return $this->get($diagnostic_id)->city;
    }

    public function get_dowds($diagnostic_id) {

        $this->db->join('miniant_diagnostic_dowds dowd', 'dowd.dowd_id = miniant_dowds.id');
        $this->db->where('dowd.diagnostic_id', $diagnostic_id);
        return $this->dowd_model->get();
    }

    public function get_for_technician($assignment_object, $technician_id=null, $senior_technician_id=null) {
        $assignment_params = array('order_id' => $assignment_object->order_id);

        if (!empty($technician_id) && $technician_id != $senior_technician_id) {
            $assignment_params['technician_id'] = $technician_id;
        }

        $all_assignments = $this->assignment_model->get($assignment_params);
        $diagnostics = array();

        foreach ($all_assignments as $assignment) {
            // Some assignments may not have a diagnostic yet, ignore them
            if (!empty($assignment->diagnostic_id)) {
                $diagnostic = $this->get_values($assignment->diagnostic_id);

                if (!empty($assignment->technician_id)) {
                    $diagnostic->technician_id = $assignment->technician_id;
                    $diagnostic->unit_id = $assignment->unit_id;
                    $diagnostics[] = $diagnostic;
                }
            }
        }

        return $diagnostics;
    }

    /**
     * These are the parts required to fix the issues diagnosed during a diagnostic
     */
    public function get_required_parts($diagnostic_id, $servicequote_id=null, $for_fixing_now=false) {
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $diagnostic_id), true);

        if ($for_fixing_now) {
            $this->db->where('can_be_fixed_now', true);
        } else if (!$for_fixing_now && !empty($servicequote_id)) {
            $this->db->where('can_be_fixed_now', false);
        }

        $diagnostic_issues = $this->diagnostic_issue_model->get(compact('diagnostic_id'));

        $required_parts = array();

        foreach ($diagnostic_issues as $diagnostic_issue) {
            $this->db->where("part_type_id NOT IN (SELECT id FROM miniant_part_types WHERE name = 'Labour')", null, false);
            if (!$diagnostic_issue->can_be_fixed_now && !empty($servicequote_id)) {
                $parts = $this->part_model->get(array('servicequote_id' => $servicequote_id, 'diagnostic_issue_id' => $diagnostic_issue->id));
            } else {
                $parts = $this->part_model->get(array('assignment_id' => $assignment->id, 'diagnostic_issue_id' => $diagnostic_issue->id));
            }

            if (empty($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $part->name = $this->part_type_model->get($part->part_type_id)->name;
                $part->issue = $diagnostic_issue;
                $part->part_type = $this->part_type_model->get($part->part_type_id);
                $required_parts[] = $part;
            }
        }

        return $required_parts;
    }
}
