<?php
echo form_open(base_url().'building/job_sites/process_edit/', array('id' => 'job_site_edit_form', 'class' => 'form-horizontal'));
echo form_hidden('id', $job_site_id);
print_form_container_open();
print_input_element(array(
    'label' => 'Unit',
    'name' => 'unit',
    'size' => 5,
    'required' => false)
);
print_input_element(array(
    'label' => 'Number',
    'name' => 'number',
    'size' => 5,
    'required' => true)
);
print_input_element(array(
    'label' => 'Street',
    'name' => 'street',
    'size' => 30,
    'required' => true)
);
print_autocomplete_element(array(
    'label' => 'Street type',
    'name' => 'street_type',
    'options_url' => 'addresses/get_street_types',
    'required' => true,
    'id' => 'autocomplete_street_type',
));
print_input_element(array(
    'label' => 'Suburb',
    'name' => 'city',
    'size' => 26,
    'required' => true)
);
print_input_element(array(
    'label' => 'Postcode',
    'name' => 'postcode',
    'size' => 12,
    'required' => true)
);
print_dropdown_element(array(
    'label' => 'State',
    'name' => 'state',
    'options' => array( 'ACT' => 'ACT', 'NSW' => 'NSW', 'NT' => 'NT', 'QLD' => 'QLD', 'SA' => 'SA', 'TAS' => 'TAS', 'VIC' => 'VIC', 'WA' => 'WA'),
    'required' => true,
    'default_value' => 'WA')
);
print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'building/job_sites');
print_submit_container_close();
print_form_container_close();
echo form_close();
