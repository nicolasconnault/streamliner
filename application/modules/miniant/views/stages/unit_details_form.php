<?php
$is_breakdown = $this->order_model->get_type_id('Breakdown') == $order->order_type_id;
$is_maintenance = $this->order_model->get_type_id('Maintenance') == $order->order_type_id;
$is_installation = $this->order_model->get_type_id('Installation') == $order->order_type_id;
$is_service = $this->order_model->get_type_id('Service') == $order->order_type_id;
$is_repair = $this->order_model->get_type_id('Repair') == $order->order_type_id;

echo form_open_multipart(base_url().'miniant/stages/'.$this->workflow_manager->current_stage->stage_name.'/process', array('id' => 'unit_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('assignment_id', $unit->assignment_id);

$evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
$ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
$trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
$other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
$mech_unit_id = $this->unit_model->get_type_id('Mechanical services');

$cassette_id = $this->unitry_type_model->get(array('name' => 'R/C Cassette'), true)->id;
$wallsplit_id = $this->unitry_type_model->get(array('name' => 'R/C Wall Split'), true)->id;
$ducted_id = $this->unitry_type_model->get(array('name' => 'R/C Ducted'), true)->id;
$under_id = $this->unitry_type_model->get(array('name' => 'Under Ceiling'), true)->id;
$rac_id = $this->unitry_type_model->get(array('name' => 'RAC'), true)->id;

$special_refrigerated_systems = array();
foreach ($this->unitry_type_model->get(array('unit_type_id' => $ref_unit_id)) as $unitry_type) {
    if (in_array($unitry_type->name, array('R/C Wall Split', 'R/C Cassette', 'Under Ceiling', 'RAC'))) {
        $special_refrigerated_systems[] = $unitry_type->id;
    }
}

$special_refrigerated_systems = implode('|', $special_refrigerated_systems);

$evap_brand_other_id = $this->brand_model->get(array('name' => 'Other', 'unit_type_id' => $evap_unit_id), true)->id;
$ref_brand_other_id = $this->brand_model->get(array('name' => 'Other', 'unit_type_id' => $ref_unit_id), true)->id;

$refrigerant_type_other_id = $this->refrigerant_type_model->get(array('name' => 'Other'), true)->id;

$is_locked = $this->order_model->has_statuses($order->id, array('LOCKED FOR REVIEW'));
$unit_has_details_recorded = $this->unit_model->has_statuses($unit->id, array('UNIT DETAILS RECORDED'));

$brand = $this->brand_model->get($unit->brand_id);
$brand_name = (empty($brand->name)) ? '' : $brand->name;

print_form_container_open();
    print_dropdown_element(array(
        'label' => 'Equipment type',
        'name' => 'unit_type_id_'.$unit->assignment_id,
        'options' => $this->unit_model->get_types_dropdown(),
        'required' => true,
        'render_static' => true,
        'default_value' => $unit->unit_type_id,
        'static_displayvalue' => $this->unit_model->get_type_string($unit->unit_type_id),
    ));
    $this->db->where('unit_type_id', $ref_unit_id);
    print_dropdown_element(array(
        'label' => 'Unitry type',
        'name' => 'unitry_type_id_'.$unit->assignment_id,
        'options' => $this->unitry_type_model->get_dropdown('name'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->unitry_type_id,
        'static_displayvalue' => @$this->unitry_type_model->get($unit->unitry_type_id)->name,
        'disabledunless' => array('unit_type_id_' .$unit->assignment_id => $ref_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Brand',
        'name' => 'brand_id_ref_'.$unit->assignment_id,
        'options' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Refrigerated A/C')),
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'default_value' => $unit->brand_id,
        'static_displayvalue' => $brand_name,
    ));
    print_input_element(array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'brand_other_'.$unit->assignment_id,
        'size' => '20',
        'required' => false,
        'disabledunless' => array('unit_type_id_' .$unit->assignment_id => $ref_unit_id, 'brand_id_ref_' .$unit->assignment_id => $ref_brand_other_id)
    ));
    print_dropdown_element(array(
        'label' => 'Brand',
        'name' => 'brand_id_evap_'.$unit->assignment_id,
        'options' => $this->brand_model->get_dropdown_by_unit_type_id($this->unit_model->get_type_id('Evaporative A/C')),
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id),
        'default_value' => $unit->brand_id,
        'static_displayvalue' => $brand_name,
    ));
    print_input_element(array(
        'type' => 'input',
        'placeholder' => 'Other Brand',
        'name' => 'brand_other_'.$unit->assignment_id,
        'size' => '20',
        'required' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id, 'brand_id_evap_'.$unit->assignment_id => $evap_brand_other_id)
    ));
    print_input_element(array(
        'label' => 'Brand',
        'name' => 'brand_other_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$trans_unit_id|$other_unit_id|$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Area Serving',
        'name' => 'area_serving_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id),
    ));
    print_input_element(array(
        'label' => 'Serial Number',
        'name' => 'serial_number_'.$unit->assignment_id,
        'size' => '30',
        'required' => $is_installation && $unit->unit_type_id != $evap_unit_id,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Indoor Serial Number',
        'name' => 'indoor_serial_number_'.$unit->assignment_id,
        'size' => '30',
        'required' => $is_installation,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id|$trans_unit_id|$evap_unit_id")
    ));
    print_input_element(array(
        'label' => 'Indoor Serial Number',
        'name' => 'indoor_serial_number_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Outdoor Serial Number',
        'name' => 'outdoor_serial_number_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$trans_unit_id|$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Model',
        'name' => 'model_'.$unit->assignment_id,
        'size' => '30',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Fan motor model',
        'name' => 'fan_motor_model_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Fan motor make',
        'name' => 'fan_motor_make_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Indoor Model',
        'name' => 'indoor_model_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$trans_unit_id|$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Outdoor Model',
        'name' => 'outdoor_model_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$trans_unit_id|$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Indoor evaporator model',
        'name' => 'indoor_evaporator_model_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id|$trans_unit_id")
    ));
    print_input_element(array(
        'label' => 'Indoor evaporator serial number',
        'name' => 'indoor_evaporator_serial_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$trans_unit_id")
    ));
    print_input_element(array(
        'label' => 'Indoor evaporator qty',
        'name' => 'indoor_evaporator_qty_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id")
    ));
    print_input_element(array(
        'label' => 'Brand of condensing unit',
        'name' => 'condensing_unit_brand_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $other_unit_id)
    ));
    print_input_element(array(
        'label' => 'Outdoor condensing unit model',
        'name' => 'outdoor_condensing_unit_model_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Outdoor condensing unit serial number',
        'name' => 'outdoor_condensing_unit_serial_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Outdoor condenser qty',
        'name' => 'outdoor_condenser_qty_'.$unit->assignment_id,
        'size' => '30',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id")
    ));
    print_dropdown_element(array(
        'label' => 'Refrigerant type',
        'name' => 'refrigerant_type_id_'.$unit->assignment_id,
        'options' => $this->refrigerant_type_model->get_dropdown('name'),
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$trans_unit_id|$other_unit_id"),
        'default_value' => $unit->refrigerant_type_id,
        'static_displayvalue' => @$this->refrigerant_type_model->get($unit->refrigerant_type_id)->name
    ));
    print_input_element(array(
        'type' => 'input',
        'placeholder' => 'Other Refrigerant type',
        'name' => 'refrigerant_type_other_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'disabledunless' => array('refrigerant_type_id_' .$unit->assignment_id => $refrigerant_type_other_id)
    ));
    print_dropdown_element(array(
        'label' => 'Mains power',
        'name' => 'electrical_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Single phase' => 'Single phase', 'Three-phase' => 'Three-phase'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->electrical,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => "$trans_unit_id")
    ));
    print_input_element(array(
        'label' => 'Mains power supply cable size (mm)',
        'name' => 'mains_cable_size_'.$unit->assignment_id,
        'size' => '4',
        'required' => false,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Mains circuit breaker size (Amp)',
        'name' => 'mains_breaker_size_'.$unit->assignment_id,
        'size' => '4',
        'required' => false,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Mains circuit breaker Make',
        'name' => 'mains_breaker_make_'.$unit->assignment_id,
        'size' => '8',
        'required' => false,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Mains isolating switch Make',
        'name' => 'mains_switch_make_'.$unit->assignment_id,
        'size' => '8',
        'required' => false,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Amperage size Main isolator',
        'name' => 'amperage_size_main_isolator_'.$unit->assignment_id,
        'size' => '8',
        'required' => false,
        'render_static' => false,
        'disabledif' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Electric drive',
        'name' => 'electrical_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Single phase' => 'Single phase', 'Three-phase' => 'Three-phase'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->electrical,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$trans_unit_id")
    ));
    print_input_element(array(
        'label' => 'Condensing Cooling Capacity (KW)',
        'name' => 'kilowatts_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$trans_unit_id")
    ));
    print_input_element(array(
        'label' => 'Condensing Cooling Capacity (KW)',
        'name' => 'kilowatts_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id")
    ));
    print_input_element(array(
        'label' => 'Type of apparatus you are working on',
        'name' => 'apparatus_type_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Unit description',
        'name' => 'description_'.$unit->assignment_id,
        'size' => '20',
        'placeholder' => 'e.g., chiller room, freezer room, cake display, fish display etc.',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id")
    ));
    print_input_element(array(
        'label' => 'Unit description',
        'name' => 'description_'.$unit->assignment_id,
        'size' => '20',
        'placeholder' => 'e.g., kitchen exhaust, toilet exhaust, lift shaft, car park, ventilation. etc.',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));

    print_input_element(array(
        'label' => 'Indoor fan motor serial number',
        'name' => 'indoor_fan_motor_serial_'.$unit->assignment_id,
        'size' => '20',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Outdoor fan motor serial number',
        'name' => 'outdoor_fan_motor_serial_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Indoor fan motor model',
        'name' => 'indoor_fan_motor_model_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Outdoor fan motor model',
        'name' => 'outdoor_fan_motor_model_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Sheetmetal duct size (L x W x H)',
        'name' => 'sheetmetal_duct_size_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Diffusion grille face size (L x W)',
        'name' => 'diffusion_grille_face_size_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_dropdown_element(array(
        'label' => 'Diffusion grille face plastic or metal',
        'name' => 'diffusion_grille_face_type_'.$unit->assignment_id,
        'required' => true,
        'render_static' => false,
        'options' => array(null => '-- Select one --', 'Plastic' => 'Plastic', 'Metal' => 'Metal'),
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Diffusion cushion head size (L x W)',
        'name' => 'diffusion_cushion_head_size_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_dropdown_element(array(
        'label' => 'Diffusion cushion head plastic or metal',
        'name' => 'diffusion_cushion_head_type_'.$unit->assignment_id,
        'required' => true,
        'render_static' => false,
        'options' => array(null => '-- Select one --', 'Plastic' => 'Plastic', 'Metal' => 'Metal'),
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Flexible duct size (mm)',
        'name' => 'flexible_duct_size_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Fire damper size (L x W x D)',
        'name' => 'fire_damper_size_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_dropdown_element(array(
        'label' => 'Variable speed drive (VSD)',
        'name' => 'vsd_'.$unit->assignment_id,
        'required' => true,
        'render_static' => false,
        'options' => array(null => '-- Select one --', 'Yes' => 'Yes', 'No' => 'No'),
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id")
    ));
    print_input_element(array(
        'label' => 'Make/brand of VSD',
        'name' => 'vsd_brand_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id", 'vsd_'.$unit->assignment_id => 'Yes')
    ));
    print_input_element(array(
        'label' => 'Model of VSD',
        'name' => 'vsd_model_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id", 'vsd_'.$unit->assignment_id => 'Yes')
    ));
    print_input_element(array(
        'label' => 'Serial number of VSD',
        'name' => 'vsd_serial_'.$unit->assignment_id,
        'size' => '20',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id", 'vsd_'.$unit->assignment_id => 'Yes')
    ));

    print_dropdown_element(array(
        'label' => 'Thermostat type',
        'name' => 'thermostat_type_'.$unit->assignment_id,
        'required' => true,
        'render_static' => false,
        'options' => array(null => '-- Select one --', 'Electronic' => 'Electronic', 'Mechanical' => 'Mechanical'),
        'default_value' => $unit->thermostat_type,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id|$trans_unit_id")
    ));
    print_input_element(array(
        'label' => 'Thermostat brand name',
        'name' => 'thermostat_brand_'.$unit->assignment_id,
        'size' => '15',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id|$trans_unit_id")
    ));
    print_dropdown_element(array(
        'label' => 'Thermostat model',
        'name' => 'thermostat_model_'.$unit->assignment_id,
        'size' => '15',
        'required' => true,
        'render_static' => false,
        'options' => array('Same as brand' => 'Same as brand', 'Universal' => 'Universal'),
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id|$trans_unit_id", 'thermostat_type_'.$unit->assignment_id => 'Electronic'),
    ));
    print_dropdown_element(array(
        'label' => 'Pad type',
        'name' => 'filter_pad_type_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Celdek' => 'Celdek', 'Aspen' => 'Aspen'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->filter_pad_type,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Pad size (LxHxW)',
        'name' => 'pad_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Water distribution groove type',
        'name' => 'water_distribution_type_groove_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Inner' => 'Inner', 'Centre' => 'Centre', 'Outer' => 'Outer', 'No groove' => 'No groove'),
        'required' => true,
        'render_static' => false,
        'disabledif' => array('filter_pad_type_'.$unit->assignment_id => 'Aspen'),
        'default_value' => $unit->water_distribution_type_groove,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Plenium dropper size (LxHxW)',
        'name' => 'plenium_dropper_size_'.$unit->assignment_id,
        'size' => '3',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $evap_unit_id)
    ));
    print_input_element(array(
        'label' => 'Dropper size (LxHxW)',
        'name' => 'dropper_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $mech_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'No. of spigots',
        'name' => 'spigots_count_'.$unit->assignment_id,
        'options' => range(1,10),
        'required' => false,
        'render_static' => false,
        'default_value' => $unit->spigots_count,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $mech_unit_id)
    ));
    print_input_element(array(
        'label' => 'Brand of apparatus you are working on',
        'name' => 'apparatus_brand_'.$unit->assignment_id,
        'size' => '20',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$mech_unit_id|$other_unit_id"),
    ));
    print_input_element(array(
        'label' => 'Number of spigots on the dropper',
        'name' => 'spigot_dropper_count_'.$unit->assignment_id,
        'size' => '3',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('plenium_dropper_size_'.$unit->assignment_id => true, 'unit_type_id_'.$unit->assignment_id => $evap_unit_id),
    ));
    print_dropdown_element(array(
        'label' => 'Spigot size (mm)',
        'name' => 'spigot_size_'.$unit->assignment_id,
        'options' => array(150 => 150, 200 => 200, 250 => 250, 300 => 300, 350 => 350, 400 => 400, 450 => 450, 500 => 500, 550 => 550, 600 => 600),
        'required' => false,
        'default_value' => $unit->spigot_size,
        'render_static' => false,
        'disabledunless' => array('plenium_dropper_size_'.$unit->assignment_id => true, 'unit_type_id_'.$unit->assignment_id => "$evap_unit_id|$mech_unit_id"),
    ));

    print_input_element(array(
        'label' => 'Return air filter size (LxHxW)',
        'name' => 'filter_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$wallsplit_id|$cassette_id|$rac_id")
    ));

    print_input_element(array(
        'label' => 'Return air filter size Frame to Frame (LxHxW in mm)',
        'name' => 'return_air_filter_size_frame_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$rac_id")
    ));

    print_input_element(array(
        'label' => 'Return air indoor fan coil boot size (LxHxW in mm)',
        'name' => 'return_air_indoor_fan_coil_boot_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));

    print_dropdown_element(array(
        'label' => 'Return air indoor coil boot No. of spigots',
        'name' => 'return_air_indoor_coil_boot_spigots_count_'.$unit->assignment_id,
        'options' => range(1,10),
        'required' => false,
        'render_static' => false,
        'default_value' => $unit->return_air_indoor_coil_boot_spigots_count,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));
    print_multiselect_element(array(
        'label' => 'Return air boot spigot sizes (mm)',
        'name' => 'return_air_boot_spigot_size_'.$unit->assignment_id,
        'options' => array(150 => 150, 200 => 200, 250 => 250, 300 => 300, 350 => 350, 400 => 400, 450 => 450, 500 => 500, 550 => 550, 600 => 600),
        'required' => false,
        'default_value' => $unit->return_air_boot_spigot_size,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));
    print_input_element(array(
        'label' => 'Supply air indoor fan coil boot size (LxHxW in mm)',
        'name' => 'supply_air_indoor_fan_coil_boot_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));

    print_dropdown_element(array(
        'label' => 'Supply air indoor coil boot No. of spigots',
        'name' => 'supply_air_indoor_coil_boot_spigots_count_'.$unit->assignment_id,
        'options' => range(1,10),
        'required' => false,
        'render_static' => false,
        'default_value' => $unit->supply_air_indoor_coil_boot_spigots_count,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));
    print_multiselect_element(array(
        'label' => 'Supply air boot spigot sizes (mm)',
        'name' => 'supply_air_boot_spigot_size_'.$unit->assignment_id,
        'options' => array(150 => 150, 200 => 200, 250 => 250, 300 => 300, 350 => 350, 400 => 400, 450 => 450, 500 => 500, 550 => 550, 600 => 600),
        'required' => false,
        'default_value' => $unit->supply_air_boot_spigot_size,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $ref_unit_id),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));
    print_input_element(array(
        'label' => 'Supply air diffuser face size (LxHxW in mm)',
        'name' => 'supply_air_diffuser_face_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$evap_unit_id"),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));

    print_input_element(array(
        'label' => 'Supply air diffuser cushion head sizes (LxHxW in mm)',
        'name' => 'supply_air_diffuser_cushion_head_sizes_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$evap_unit_id"),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));

    print_input_element(array(
        'label' => 'Quantity of supply air diffusers',
        'name' => 'supply_air_diffuser_quantity_'.$unit->assignment_id,
        'size' => '4',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$evap_unit_id"),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));

    print_dropdown_element(array(
        'label' => 'Filter type',
        'name' => 'filter_type_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Media' => 'Media', 'Disposable' => 'Disposable', 'Metal' => 'Metal'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->filter_type,
        'disabledunless' => array('filter_size_'.$unit->assignment_id)
    ));
    print_input_element(array(
        'label' => 'Filter outside frame dimensions (LxHxW)',
        'name' => 'filter_outside_frame_dimensions_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('filter_type_'.$unit->assignment_id => 'Metal|Disposable')
    ));
    print_input_element(array(
        'label' => 'Supply air duct spigot size (mm)',
        'name' => 'air_supply_duct_spigot_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$evap_unit_id"),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => "$cassette_id|$wallsplit_id|$under_id|$rac_id")
    ));
    print_dropdown_element(array(
        'label' => 'Pitch of roof in degrees',
        'name' => 'roof_pitch_'.$unit->assignment_id,
        'options' => range(1,40),
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$evap_unit_id"),
        'disabledif' => array('unitry_type_id_'.$unit->assignment_id => $rac_id),
        'default_value' => $unit->roof_pitch
    ));
    print_textarea_element(array(
        'label' => 'Room size (L x W x H)',
        'name' => 'room_size_'.$unit->assignment_id,
        'cols' => '40',
        'rows' => '3',
        'required' => false,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id|$evap_unit_id"),
    ));
    print_input_element(array(
        'label' => 'Room size (L x W x H)',
        'name' => 'room_size_cassette_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unitry_type_id_'.$unit->assignment_id => $special_refrigerated_systems, 'unitry_type_id_'.$unit->assignment_id => $cassette_id)
    ));
    print_dropdown_element(array(
        'label' => 'Outdoor unit',
        'name' => 'outdoor_unit_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Brackets' => 'Brackets', 'Mounting plastic blocks' => 'Mounting plastic blocks', 'Slabs' => 'Slabs', 'Brick paving' => 'Brick paving', 'Roof mounted brackets' => 'Roof mounted brackets', 'Top hats' => 'Top hats', 'Other' => 'Other'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->outdoor_unit
    ));
    print_input_element(array(
        'label' => 'Vehicle rego No',
        'name' => 'vehicle_registration_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Vehicle type',
        'name' => 'vehicle_type_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Truck' => 'Truck', 'Van' => 'Van'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->vehicle_type,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Type of drive',
        'name' => 'drive_type_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Diesel driven' => 'Diesel driven', 'Direct drive' => 'Direct drive'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->drive_type,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Palette size of vehicle',
        'name' => 'palette_size_'.$unit->assignment_id,
        'options' => range(1,20),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->palette_size,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Chassis No of vehicle',
        'name' => 'chassis_no_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Engine No of vehicle (on compliance plate)',
        'name' => 'engine_no_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Year of vehicle',
        'name' => 'vehicle_year_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Temperature application',
        'name' => 'temperature_application_'.$unit->assignment_id,
        'options' => array(null => '-- Select One --', 'Low temperature' => 'Low temperature', 'Medium temperature' => 'Medium temperature'),
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Aperture size (mm)',
        'name' => 'aperture_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Internal dimensions of refrigerated box (L x W x H)',
        'name' => 'refrigerated_box_dimensions_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Thickness of insulation or panels (mm)',
        'name' => 'insulation_thickness_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Type of insulation',
        'name' => 'insulation_type_'.$unit->assignment_id,
        'options' => array(null => '-- Select one --', 'Panels' => 'Panels', 'Fiberglass' => 'Fiberglass'),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->insulation_type,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_dropdown_element(array(
        'label' => 'Number of opening doors on vehicle',
        'name' => 'number_of_doors_'.$unit->assignment_id,
        'options' => range(1,10),
        'required' => true,
        'render_static' => false,
        'default_value' => $unit->number_of_doors,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Aperture size of door openings (L x W x H)',
        'name' => 'door_openings_aperture_size_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Thickness of floor (mm)',
        'name' => 'floor_thickness_'.$unit->assignment_id,
        'size' => '6',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));
    print_input_element(array(
        'label' => 'Type of floor',
        'name' => 'floor_type_'.$unit->assignment_id,
        'size' => '16',
        'required' => true,
        'render_static' => false,
        'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
    ));


if ($is_breakdown) {
    print_submit_container_open();
    if ($unit->details_recorded) {
        echo form_submit('button', 'Update unit and continue Diagnostic', 'id="submit_button" class="btn btn-primary"');
    } else {
        echo form_submit('button', 'Confirm and start Diagnostic', 'id="submit_button" class="btn btn-primary"');
    }
} else {
    print_submit_container_open();
    echo form_submit('button', 'Save and Continue', 'id="submit_button" class="btn btn-primary"');
}

print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
