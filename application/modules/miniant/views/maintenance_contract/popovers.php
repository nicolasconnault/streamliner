<?php

$fields = array(
    array('type' => 'text', 'name' => 'first_name', 'placeholder' => 'First name', 'required' => true),
    array('type' => 'text', 'name' => 'surname', 'placeholder' => 'Surname', 'required' => true),
    array('type' => 'text', 'name' => 'phone', 'placeholder' => 'Phone', 'required' => true),
    array('type' => 'text', 'name' => 'phone2', 'placeholder' => 'Secondary Phone', 'required' => false),
    array('type' => 'text', 'name' => 'mobile', 'placeholder' => 'Mobile', 'required' => false),
    array('type' => 'text', 'name' => 'mobile2', 'placeholder' => 'Secondary Mobile', 'required' => false),
    array('type' => 'email', 'name' => 'email', 'placeholder' => 'Email', 'required' => true),
    array('type' => 'email', 'name' => 'email2', 'placeholder' => 'Secondary Email', 'required' => false),
    array('type' => 'url', 'name' => 'website', 'placeholder' => 'Website', 'required' => false)
);
print_popover_form('New Billing contact', 'newbilling_contact', $fields);

$fields = array(
    array('type' => 'text', 'name' => 'first_name', 'placeholder' => 'First name', 'required' => true),
    array('type' => 'text', 'name' => 'surname', 'placeholder' => 'Surname', 'required' => false),
    array('type' => 'text', 'name' => 'phone', 'placeholder' => 'Phone', 'required' => false),
    array('type' => 'text', 'name' => 'phone2', 'placeholder' => 'Secondary Phone', 'required' => false),
    array('type' => 'text', 'name' => 'mobile', 'placeholder' => 'Mobile', 'required' => false),
    array('type' => 'text', 'name' => 'mobile2', 'placeholder' => 'Secondary Mobile', 'required' => false),
    array('type' => 'email', 'name' => 'email', 'placeholder' => 'Email', 'required' => false),
    array('type' => 'email', 'name' => 'email2', 'placeholder' => 'Secondary Email', 'required' => false),
    array('type' => 'url', 'name' => 'website', 'placeholder' => 'Website', 'required' => false)
);
print_popover_form('New Property manager contact', 'newproperty_manager_contact', $fields);
$fields = array(
    array('type' => 'text', 'name' => 'unit', 'placeholder' => 'Unit', 'required' => false),
    array('type' => 'text', 'name' => 'number', 'placeholder' => 'Number', 'required' => true),
    array('type' => 'text', 'name' => 'street', 'placeholder' => 'Street name', 'required' => true),
    array('type' => 'autocomplete', 'name' => 'street_type', 'placeholder' => 'Street type', 'options_url' => 'addresses/get_street_types', 'required' => true, 'id' => 'autocomplete_street_type'),
    array('type' => 'text', 'name' => 'city', 'placeholder' => 'Suburb', 'required' => true),
    array('type' => 'text', 'name' => 'state', 'placeholder' => 'State', 'required' => true),
    array('type' => 'text', 'name' => 'postcode', 'placeholder' => 'Post code', 'required' => true)
);
print_popover_form('New Job Site Address', 'newsite_address', $fields);

$fields = array(
    array('type' => 'hidden', 'name' => 'account_id', 'required' => true),
    array('type' => 'text', 'name' => 'name', 'placeholder' => 'Tenancy/Owner name', 'required' => true),
);
print_popover_form('New Tenancy/Owner', 'newtenancy', $fields);

$fields = array(
    array('type' => 'hidden', 'name' => 'id', 'required' => true),
    array('type' => 'hidden', 'name' => 'account_id', 'required' => true),
    array('type' => 'text', 'name' => 'name', 'placeholder' => 'Tenancy/Owner name', 'required' => true),
);
print_popover_form('Edit Tenancy/Owner', 'edittenancy', $fields);
?>
