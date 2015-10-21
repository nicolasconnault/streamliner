<?php
/**
 * Parts do not use the has_types trait. Although they do have types, these types are associated with a unit type (certain part types only apply to some unit types).
 * Therefore the Parts have their own part_types table, and the model has its own methods for working with these types.
 */
class Part_Model extends MY_Model {
    public $table = 'miniant_parts';

    public function get_quoted_parts($servicequote_id) {
        $this->db->where('supplier_quote_id IS NOT NULL', null, false);
        $parts = $this->get(compact('servicequote_id'));

        foreach ($parts as $key => $part) {
            $supplier_quote = $this->supplier_quote_model->get($part->supplier_quote_id);
            $parts[$key]->unit_cost = $supplier_quote->unit_cost;
            $parts[$key]->total_cost = $supplier_quote->total_cost;
            $parts[$key]->availability = $supplier_quote->availability;
        }

        return $parts;
    }

    public function get_custom_client_quote_parts($servicequote_id) {
        $this->db->where('supplier_quote_id IS NULL AND part_type_id IS NULL', null, false);
        $parts = $this->get(compact('servicequote_id'));

        return $parts;

    }

    public function get_parts_used_during_diagnostic($assignment_id) {
        $this->db->where('servicequote_id IS NULL AND needs_sq = 0 AND for_repair_task = 0', null, false);
        return $this->get(array('assignment_id' => $assignment_id));
    }

    public function get_issue_photos($part_id) {
        $part = $this->get($part_id);
        if (!empty($part->diagnostic_issue_id)) {
            return get_photos('diagnostic_issue', null, $part->diagnostic_issue_id, 'miniant');
        }
        return array();
    }

    public function get_non_labour($servicequote_id) {
        $this->db->where('part_type_id NOT IN (SELECT id FROM miniant_part_types WHERE name = "Labour")', null, false);
        $this->db->where('part_type_id IS NOT NULL', null, false);
        return $this->part_model->get(array('servicequote_id' => $servicequote_id));
    }

    /**
     * A Custom part is one that was added manually, not automatically like diagnostic parts
     */
    public function get_custom_parts($assignment_id) {
        $this->db->where('assignment_id', $assignment_id);
        $this->db->where('(po_number IS NOT NULL OR part_type_id IS NULL)', null, false);
        $parts = $this->get();

        if (!empty($parts)) {
            foreach ($parts as $key => $part) {
                if (empty($part->part_name) && !empty($part->part_type_id)) {
                    $this->db->where('in_template', false);
                    $parts[$key]->part_name = $this->part_type_model->get_name($part->part_type_id);
                }
            }
        }
        return $parts;
    }

    public function add_custom_part($assignment_id, $part_name) {
        $part = new stdClass();
        $part->assignment_id = $assignment_id;
        $part->part_name = $part_name;
        $part->needs_sq = false;
        $part->for_repair_task = false;
        return $this->add($part);
    }
}
