<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
$order_unit_id = (empty($order_unit_id)) ? null : $order_unit_id;
echo form_open(base_url().'miniant/orders/event/process_edit', array('id' => 'order_unit_edit_form', 'class' => 'form-horizontal sigpad'));
echo '<div class="panel-body">';
echo form_hidden('order_unit_id', $order_unit_id);
echo form_hidden('order_id', $order_id);
print_form_container_open();

print_fieldset_open('Unit information');
print_input_element('Location serving', array('name' => 'location'), true);
print_input_element('Faults', array('name' => 'faults'), true);
print_dropdown_element('brand_id', 'Brand', $dropdowns['brands'], true);
print_dropdown_element('unit_type_id', 'Unit type', $dropdowns['unit_types'], true);
print_input_element('Model', array('name' => 'model'), false);
print_input_element('Serial No.', array('name' => 'serial_number'), false);
print_input_element('Outdoor Model', array('name' => 'outdoor_model'), false);
print_input_element('Outdoor Serial No.', array('name' => 'outdoor_serial_number'), false);
print_input_element('Indoor Model', array('name' => 'indoor_model'), false);
print_input_element('Indoor Serial No.', array('name' => 'indoor_serial_number'), false);
print_input_element('Electrical', array('name' => 'electrical'), false);
print_input_element('Gas', array('name' => 'gas'), false);
print_input_element('Kw', array('name' => 'kilowatts'), false);
print_fieldset_close();

echo '<div id="parts-section" ';
if (empty($order_unit_id)) {
    echo 'style="display: none;"';
}
echo '>';
print_fieldset_open('Parts and Labour');
?>
<br />
<div class="table-responsive">
<table id="parts_table" class="table table-condensed table-bordered">
    <thead><tr><th>Part/Labour</th><th>Quantity</th><th style="width: 220px">Actions</th></tr></thead>
    <tbody>
        <tr>
            <td colspan="3">
                <button class="btn btn-success" type="button" id="new_part_button">Add a part/labour</button>
            </td>
        </tr>
        <tr id="new_part_row" style="display: none">
            <td><?=form_dropdown('part_type_id', $dropdowns['part_types'])?><?=form_hidden('part_id', null)?></td>
            <td><?=form_input(array('name' => 'quantity', 'placeholder' => 'Quantity'))?></td>
            <td class="actions" style="width: 220px">
                <button class="btn btn-success btn-sm" type="button" id="save_new_part">Save Part/Labour</button>&nbsp;
                <button class="btn btn-warning btn-sm" type="button" id="cancel_new_part">Cancel</button>
            </td>
        </tr>
    </tbody>
</table>
</div>
<?php
print_fieldset_close();
echo "</div>";

print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'miniant/orders/order/edit/'.$order_id, 'Return to job #'.$order_id);
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
echo '</div>';
