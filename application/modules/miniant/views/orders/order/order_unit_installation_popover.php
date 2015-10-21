<?php
$evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
$ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
$trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
$other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
$mech_unit_id = $this->unit_model->get_type_id('Mechanical service');

$evap_brand_other_id = $this->brand_model->get(array('name' => 'Other', 'unit_type_id' => $evap_unit_id), true)->id;
$ref_brand_other_id = $this->brand_model->get(array('name' => 'Other', 'unit_type_id' => $ref_unit_id), true)->id;

$fields = array(
    array('type' => 'hidden', 'name' => 'new_order_id', 'default_value' => $order_id),
    array('type' => 'dropdown', 'name' => 'new_unit_type_id', 'options' => $dropdowns['unit_types'], 'required' => true),
    array(
        'type' => 'dropdown',
        'name' => 'new_unitry_type_id',
        'label' => 'Unitry',
        'options' => $dropdowns['unitry_types_refrigerated'],
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_brand_id_evap',
        'options' => $dropdowns['brands_evaporative'],
        'label' => 'Brand',
        'required' => true,
        'disabledunless' => array('new_unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'new_brand_other',
        'size' => '20',
        'required' => true,
        'disabledunless' => array('new_unit_type_id' => $evap_unit_id, 'new_brand_id_evap' => $evap_brand_other_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_brand_id_ref',
        'options' => $dropdowns['brands_refrigerated'],
        'label' => 'Brand',
        'required' => true,
        'disabledunless' => array('new_unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'new_brand_other',
        'size' => '20',
        'required' => true,
        'disabledunless' => array('new_unit_type_id' => $ref_unit_id, 'new_brand_id_ref' => $ref_brand_other_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Brand',
        'name' => 'new_brand_other',
        'size' => '20',
        'required' => true,
        'disabledunless' => array('new_unit_type_id' => "$trans_unit_id|$other_unit_id|$mech_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Serial Number',
        'name' => 'new_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Indoor Serial Number',
        'name' => 'new_indoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledif' => array('new_unit_type_id' => "$other_unit_id|$trans_unit_id|$evap_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Indoor Serial Number',
        'name' => 'new_indoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Outdoor Serial Number',
        'name' => 'new_outdoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => "$ref_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Outdoor Serial Number',
        'name' => 'new_outdoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('new_unit_type_id' => "$trans_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Area serving',
        'label' => "Area served by this unit",
        'name' => 'new_area_serving',
        'size' => '20',
        'required' => true
    ),
    array(
        'type' => 'dropdown',
        'name' => 'new_tenancy_id',
        'options' => $dropdowns['tenancies'],
        'label' => 'Tenancy/Owner',
        'required' => true,
    ),
);
print_popover_form('New Unit', 'new_unit', $fields);

$fields = array(
    array('type' => 'hidden', 'name' => 'unit_id'),
    array('type' => 'hidden', 'name' => 'order_id', 'default_value' => $order_id),
    array('type' => 'dropdown', 'name' => 'unit_type_id', 'options' => $dropdowns['unit_types'], 'required' => true),
    array(
        'type' => 'dropdown',
        'name' => 'unitry_type_id',
        'label' => 'Unitry',
        'options' => $dropdowns['unitry_types_refrigerated'],
        'required' => false,
        'disabledunless' => array('unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'brand_id_evap',
        'options' => $dropdowns['brands_evaporative'],
        'label' => 'Brand',
        'required' => true,
        'disabledunless' => array('unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'brand_other',
        'size' => '20',
        'required' => true,
        'disabledunless' => array('unit_type_id' => $evap_unit_id, 'brand_id_evap' => $evap_brand_other_id)
    ),
    array(
        'type' => 'dropdown',
        'name' => 'brand_id_ref',
        'options' => $dropdowns['brands_refrigerated'],
        'label' => 'Brand',
        'required' => true,
        'disabledunless' => array('unit_type_id' => $ref_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'brand_other',
        'size' => '20',
        'required' => true,
        'disabledunless' => array('unit_type_id' => $ref_unit_id, 'brand_id_ref' => $ref_brand_other_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Brand',
        'name' => 'brand_other',
        'size' => '20',
        'required' => true,
        'disabledunless' => array('unit_type_id' => "$trans_unit_id|$other_unit_id|$mech_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Serial Number',
        'name' => 'serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $evap_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Indoor Serial Number',
        'name' => 'indoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledif' => array('unit_type_id' => "$other_unit_id|$trans_unit_id|$evap_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Indoor Serial Number',
        'name' => 'indoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('unit_type_id' => $trans_unit_id)
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Outdoor Serial Number',
        'name' => 'outdoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('unit_type_id' => "$ref_unit_id")
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Outdoor Serial Number',
        'name' => 'outdoor_serial_number',
        'size' => '30',
        'required' => false,
        'disabledunless' => array('unit_type_id' => "$trans_unit_id")
    ),
    array(
        'type' => 'input',
        'label' => "Area served by this unit",
        'placeholder' => 'Area serving',
        'name' => 'area_serving',
        'size' => '20',
        'required' => true
    ),
    array(
        'type' => 'dropdown',
        'name' => 'tenancy_id',
        'options' => $dropdowns['tenancies'],
        'label' => 'Tenancy/Owner',
        'required' => true,
    ),
);
print_popover_form('Edit Unit', 'edit_unit', $fields);
