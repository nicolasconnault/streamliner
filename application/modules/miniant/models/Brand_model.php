<?php
class Brand_Model extends MY_Model {
    public $table = 'miniant_brands';

    public function get_dropdown_by_unit_type_id($unit_type_id, $null_option='--Select brand--') {
        $this->db->where(compact('unit_type_id'));
        $this->db->where('name <>', 'Other');

        $brands = $this->get_dropdown('name', $null_option);

        $other_brand_params = array('name' => 'Other', 'unit_type_id' => $unit_type_id);
        if (!($other_brand = $this->get($other_brand_params, true))) {
            $other_brand = new stdClass();
            $other_brand->id = $this->add($other_brand_params);
        }

        $brands[$other_brand->id] = 'Other';
        return $brands;
    }
}
