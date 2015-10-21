<?php
$fields = array(
    array('type' => 'text', 'name' => 'name', 'placeholder' => 'Name', 'required' => true),
    array('type' => 'hidden', 'name' => 'unit_type_id', 'required' => true),
    array('type' => 'textarea', 'name' => 'instructions', 'required' => false, 'placeholder' => 'Instructions for technicians'),
    array('type' => 'hidden', 'name' => 'for_diagnostic', 'required' => true, 'value' => 1),
);
print_popover_form('New Component Type', 'new_part_type', $fields);

$this->db->order_by('name');
$fields = array(
    array('type' => 'dropdown', 'name' => 'issue_type_id', 'options' => $this->issue_type_model->get_dropdown('name', false), 'required' => true),
    array('type' => 'hidden', 'name' => 'part_type_id', 'required' => true),
);
print_popover_form('New Issue Type', 'new_part_type_issue_type', $fields);

$this->db->order_by('name');
$fields = array(
    array('type' => 'dropdown', 'name' => 'step_id', 'options' => $this->step_model->get_dropdown('name'), 'required' => true, 'show_label' => false),
    array('type' => 'hidden', 'name' => 'part_type_issue_type_id', 'required' => true),
    array('type' => 'checkbox', 'name' => 'required', 'label' => 'Required', 'value' => 1, 'show_label' => true, 'default_value' => 1),
    array('type' => 'checkbox', 'name' => 'needs_sq', 'label' => 'Needs SQ', 'value' => 1),
    array('type' => 'checkbox', 'name' => 'immediate', 'label' => 'Can be done immediately', 'value' => 1, 'default_value' => 1),
);
print_popover_form('New Step', 'new_part_type_issue_type_step', $fields, false, true);

$quantity_options = array();
for ($i = 1; $i < 100; $i++) {
    $quantity_options[$i] = $i;
}

$fields = array(
    array('type' => 'dropdown', 'name' => 'part_type_id', 'options' => array(), 'required' => true),
    array('type' => 'hidden', 'name' => 'part_type_issue_type_step_id', 'required' => true),
    array('type' => 'dropdown', 'name' => 'quantity', 'options' => $quantity_options, 'required' => true),
);
print_popover_form('New required part/labour', 'new_required_part', $fields);

// Edit forms
$fields = array(
    array('type' => 'text', 'name' => 'name', 'placeholder' => 'Name', 'required' => true),
    array('type' => 'hidden', 'name' => 'unit_type_id', 'required' => true),
    array('type' => 'hidden', 'name' => 'for_diagnostic', 'required' => true, 'value' => 1),
    array('type' => 'textarea', 'name' => 'instructions', 'required' => false, 'placeholder' => 'Instructions for technicians'),
    array('type' => 'hidden', 'name' => 'id', 'required' => true, 'value' => null),
);
print_popover_form('Edit Component Type', 'edit_part_type', $fields);

$this->db->order_by('name');
$fields = array(
    array('type' => 'dropdown', 'name' => 'issue_type_id', 'options' => $this->issue_type_model->get_dropdown('name', false), 'required' => true),
    array('type' => 'hidden', 'name' => 'part_type_id', 'required' => true),
    array('type' => 'hidden', 'name' => 'id', 'required' => true, 'value' => null),
);
print_popover_form('Edit Issue Type', 'edit_part_type_issue_type', $fields);

$this->db->order_by('name');
$fields = array(
    array('type' => 'dropdown', 'name' => 'step_id', 'options' => $this->step_model->get_dropdown('name', false), 'required' => true, 'show_label' => false),
    array('type' => 'hidden', 'name' => 'part_type_issue_type_id', 'required' => true),
    array('type' => 'checkbox', 'name' => 'required', 'label' => 'Required', 'value' => 1),
    array('type' => 'checkbox', 'name' => 'needs_sq', 'label' => 'Needs SQ', 'value' => 1),
    array('type' => 'checkbox', 'name' => 'immediate', 'label' => 'Can be done immediately', 'value' => 1),
    array('type' => 'hidden', 'name' => 'id', 'required' => true, 'value' => null),
);
print_popover_form('Edit Step', 'edit_part_type_issue_type_step', $fields, false, true);

$quantity_options = array();
for ($i = 1; $i < 300; $i++) {
    if ($i > 40 && $i % 10 > 0) {
        continue;
    }
    if ($i > 100 && $i % 30 > 0) {
        continue;
    }
    $quantity_options[$i] = $i;
}

$fields = array(
    array('type' => 'dropdown', 'name' => 'part_type_id', 'options' => array(), 'required' => true),
    array('type' => 'hidden', 'name' => 'part_type_issue_type_step_id', 'required' => true),
    array('type' => 'dropdown', 'name' => 'quantity', 'options' => $quantity_options, 'required' => true),
    array('type' => 'hidden', 'name' => 'id', 'required' => true, 'value' => null),
);
print_popover_form('Edit required part/labour', 'edit_required_part', $fields);

