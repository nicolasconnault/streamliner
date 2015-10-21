<?php
echo form_open(base_url().'building/tradesmen/process_edit/', array('id' => 'tradesman_edit_form', 'class' => 'form-horizontal'));
echo form_hidden('id', $tradesman_id);
print_form_container_open();
print_input_element(array(
    'label' => 'Name',
    'name' => 'name',
    'size' => 40,
    'required' => true,
    'autocomplete' => 'off')
);
print_dropdown_element(array(
    'label' => 'Type',
    'name' => 'type_id',
    'options' => $types,
    'required' => true)
);
print_input_element(array(
    'label' => 'Mobile',
    'name' => 'mobile',
    'size' => 16,
    'required' => false)
);
print_input_element(array(
    'label' => 'Email',
    'name' => 'email',
    'size' => 60,
    'required' => false)
);
print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'building/tradesmen');
print_submit_container_close();
print_form_container_close();
echo form_close();
