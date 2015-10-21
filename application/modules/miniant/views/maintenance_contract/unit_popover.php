<?php

$evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
$ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
$trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
$other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
$mech_unit_id = $this->unit_model->get_type_id('Mechanical service');

$evap_brand_other_id = $this->brand_model->get(array('name' => 'Other', 'unit_type_id' => $evap_unit_id), true)->id;
$ref_brand_other_id = $this->brand_model->get(array('name' => 'Other', 'unit_type_id' => $ref_unit_id), true)->id;

$fields = array(
    array(
        'type' => 'hidden',
        'name' => 'new_maintenance_contract_id',
        'default_value' => $maintenance_contract_id
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_unit_type_id',
        'label' => 'Equipment type',
        'options' => $dropdowns['unit_types'],
        'required' => true
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_brand_id_evap',
        'options' => $dropdowns['brands_evaporative'],
        'label' => 'Brand',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'new_brand_other',
        'size' => '20',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $evap_unit_id, 'new_brand_id_evap' => $evap_brand_other_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_brand_id_ref',
        'options' => $dropdowns['brands_refrigerated'],
        'label' => 'Brand',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'new_brand_other',
        'size' => '20',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $ref_unit_id, 'new_brand_id_ref' => $ref_brand_other_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Brand',
        'name' => 'new_brand_other',
        'size' => '20',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => "$trans_unit_id|$other_unit_id|$mech_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Area serving',
        'name' => 'new_area_serving',
        'label' => "Area served by this unit",
        'size' => '20',
        'required' => true
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_unitry_type_id',
        'label' => 'Unitry',
        'options' => $dropdowns['unitry_types_refrigerated'],
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'textarea',
        'name' => 'new_description',
        'placeholder' => 'Notes',
        'required' => false,
        'cols' => 20,
        'rows' => 3,
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_outdoor_unit_location',
        'label' => 'Outdoor unit location',
        'required' => false,
        'options' => array(null => '-- Outdoor unit location --', 'Roof mounted' => 'Roof mounted', 'Ground mounted' => 'Ground mounted', 'Wall mounted' => 'Wall mounted'),
        'disabledif' => array('new_unit_type_id' => $trans_unit_id)
    ),
    array('type' => 'break'),
    array(
        'type' => 'input',
        'placeholder' => 'Car rego No',
        'name' => 'new_vehicle_registration',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_vehicle_type',
        'label' => 'Car type',
        'options' => array(null => '--Select car type--', 'Truck' => 'Truck', 'Van' => 'Van'),
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_palette_size',
        'label' => 'Palette size of truck',
        'options' => array_merge(array(null => '--Select palette size of truck--'), range(1,20)),
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_tenancy_id',
        'options' => $dropdowns['tenancies'],
        'label' => 'Tenancy/Owner',
        'required' => true,
    ),
);
print_popover_form('New Unit', 'new_unit', $fields, true);

$fields = array(
    array(
        'type' => 'hidden',
        'name' => 'unit_id',
    ),
    array(
        'type' => 'hidden',
        'name' => 'maintenance_contract_id',
        'default_value' => $maintenance_contract_id
    ),
    array(
        'type' => 'dropdown',
        'name' => 'unit_type_id',
        'label' => 'Equipment type',
        'options' => $dropdowns['unit_types'],
        'required' => true
    ),
    array(
        'type' => 'dropdown',
        'name' => 'brand_id_evap',
        'options' => $dropdowns['brands_evaporative'],
        'label' => 'Brand',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'brand_other',
        'size' => '20',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $evap_unit_id, 'brand_id_evap' => $evap_brand_other_id),
        'disabledif' => array('unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'brand_id_ref',
        'options' => $dropdowns['brands_refrigerated'],
        'label' => 'Brand',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'brand_other',
        'size' => '20',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $ref_unit_id, 'brand_id_ref' => $ref_brand_other_id),
        'disabledif' => array('unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Brand',
        'name' => 'brand_other',
        'size' => '20',
        'required' => false,
        'disabledunless' => array('unit_type_id' => "$trans_unit_id|$other_unit_id|$mech_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Area serving',
        'name' => 'area_serving',
        'label' => "Area served by this unit",
        'size' => '20',
        'required' => true
    ),
    array(
        'type' => 'dropdown',
        'name' => 'unitry_type_id',
        'label' => 'Unitry',
        'options' => $dropdowns['unitry_types_refrigerated'],
        'required' => false,
        'disabledunless' => array('unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'textarea',
        'name' => 'description',
        'placeholder' => 'Notes',
        'required' => false,
        'cols' => 20,
        'rows' => 3,
    ),
    array(
        'type' => 'dropdown',
        'name' => 'outdoor_unit_location',
        'label' => 'Outdoor unit location',
        'required' => false,
        'options' => array(null => '-- Outdoor unit location --', 'Roof mounted' => 'Roof mounted', 'Ground mounted' => 'Ground mounted', 'Wall mounted' => 'Wall mounted'),
        'disabledif' => array('unit_type_id' => $trans_unit_id)
    ),
    array('type' => 'break'),
    array(
        'type' => 'input',
        'placeholder' => 'Car rego No',
        'name' => 'vehicle_registration',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'vehicle_type',
        'label' => 'Car type',
        'options' => array(null => '--Select car type--', 'Truck' => 'Truck', 'Van' => 'Van'),
        'required' => false,
        'disabledunless' => array('unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'palette_size',
        'label' => 'Palette size of truck',
        'options' => array_merge(array(null => '--Select palette size of truck--'), range(1,20)),
        'required' => false,
        'disabledunless' => array('unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'tenancy_id',
        'options' => $dropdowns['tenancies'],
        'label' => 'Tenancy/Owner',
        'required' => true,
    ),
);

print_popover_form('Edit Unit', 'edit_unit', $fields, true, true);
