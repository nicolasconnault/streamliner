<?php
class Supplier_quote_model extends MY_Model {
    public $table = 'miniant_supplier_quotes';

    public function get_from_selected_suppliers($servicequote_id) {
        $selected_suppliers = $this->servicequote_supplier_model->get(array('servicequote_id' => $servicequote_id));
        $supplier_ids = array();
        foreach ($selected_suppliers as $selected_supplier) {
            $supplier_ids[] = $selected_supplier->supplier_id;
        }
        $this->db->where_in('supplier_id', $supplier_ids);
        return $this->get(array('servicequote_id' => $servicequote_id));
    }

    public function get_from_final_suppliers($servicequote_id) {
        $selected_suppliers = $this->servicequote_supplier_model->get(array('servicequote_id' => $servicequote_id));
        $supplier_ids = array();
        foreach ($selected_suppliers as $selected_supplier) {
            $supplier_ids[] = $selected_supplier->supplier_id;
        }
        $this->db->where('selected', true);
        $this->db->where_in('supplier_id', $supplier_ids);
        return $this->get(array('servicequote_id' => $servicequote_id));
    }

    public function update_availability($servicequote_id, $supplier_id, $availability, $update_null_only=true) {
        $this->db->where(compact('servicequote_id', 'supplier_id'));

        if ($update_null_only && $availability != 'Blank') {
            $this->db->where('(availability IS NULL OR availability = "")', null, false);
        }

        if ($availability == 'Blank') {
            $availability = '';
        }
        $this->db->update($this->table, compact('availability'));
        return true;
    }
}
