<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
echo form_open(base_url().'miniant/orders/assignments/process_edit/', array('id' => 'assignment_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('assignment_id', $assignment_id);
echo form_hidden('unit_id', $assignment_data->unit_id);
echo form_hidden('order_id', $assignment_data->order_id);
echo form_hidden('senior_technician_id', $assignment_data->order_senior_technician_id);
print_form_container_open();

print_datetime_element(array(
    'label' => 'Appointment date',
    'name' => 'appointment_date',
    'required' => true
));

print_input_element(array(
    'label' => 'Estimated duration (Mins)',
    'name' => 'estimated_duration',
    'required' => true
));

print_multiselect_element(array(
    'label' => 'Assigned technicians',
    'name' => 'technician_id[]',
    'required' => true,
    'options' => $technicians,
    'default_value' => $assigned_technicians
));

print_dropdown_element(array(
    'label' => 'Senior technician',
    'name' => 'senior_technician_id',
    'required' => true,
    'default_value' => $senior_technician_id,
    'options' => array(),
));
/*
print_dropdown_element(array(
    'label' => 'Priority level',
    'name' => 'priority_level_id',
    'required' => true,
    'options' => $priority_levels
));
*/

if (has_capability('assignments:editstatuses')) {
    print_multiselect_element(array(
        'label' => 'Statuses',
        'name' => 'status_id[]',
        'required' => false,
        'options' => $allstatuses,
        'default_value' => $statuses,
        'extra_html' => array('style' => "width: 500px; height: auto;")
    ));
}

print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'miniant/orders/schedule');
print_submit_container_close();
print_form_container_close();
if (has_capability('orders:editassignments')) {
    echo '<a href="'.base_url().'miniant/stages/assignment_details/index/'.$assignment_id.'" class="btn btn-primary">Review this assignment</a>';
}
echo '</div>';
echo form_close();
?>
</div>
</div>
