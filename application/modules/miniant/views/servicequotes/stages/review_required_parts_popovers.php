<?php

$fields = array(
    array('type' => 'hidden', 'name' => 'new_servicequote_id', 'default_value' => $servicequote_id),
    array(
        'type' => 'autocomplete',
        'name' => 'new_part_type_id',
        'options_url' => 'miniant/servicequotes/servicequote_ajax/get_part_types/1',
        'required' => true,
        'placeholder' => 'Part type',
        'label' => 'Part type',
        'id' => 'autocomplete_part_type',
        'accept_new_value' => true
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Model number',
        'name' => 'new_part_number',
        'size' => '20',
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'placeholder' => 'Other info',
        'name' => 'new_description',
        'cols' => 50,
        'rows' => 5,
        'required' => false,
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Quantity',
        'name' => 'new_quantity',
        'size' => '10',
        'required' => true,
    ),
);
print_popover_form('New part', 'new_part', $fields);

$fields = array(
    array('type' => 'hidden', 'name' => 'part_id'),
    array('type' => 'hidden', 'name' => 'servicequote_id', 'default_value' => $servicequote_id),
    array('type' => 'dropdown', 'name' => 'part_type_id', 'options' => $dropdowns['part_types'], 'required' => true),
    array(
        'type' => 'input',
        'placeholder' => 'Model number',
        'name' => 'part_number',
        'size' => '20',
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'placeholder' => 'Other info',
        'name' => 'description',
        'cols' => 50,
        'rows' => 5,
        'required' => false,
    ),
    array(
        'type' => 'input',
        'placeholder' => 'Quantity',
        'name' => 'quantity',
        'size' => '10',
        'required' => true,
    ),
);
print_popover_form('Edit part', 'edit_part', $fields);
