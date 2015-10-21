<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
echo form_open(base_url().'miniant/brands/process_edit/', array('id' => 'brand_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('brand_id', $brand_id);
print_form_container_open();
print_dropdown_element(array('name' => 'unit_type_id', 'label' => 'Equipment type', 'options' => $unit_types, 'required' => true, 'size' => 50));
print_input_element(array('name' => 'name', 'label' => 'Name', 'required' => true, 'size' => 50));
print_textarea_element(array('name' => 'description', 'cols' => 80, 'rows' => 6, 'label' => 'Description', 'required' => true));
print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'miniant/brands');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
?>
</div>
</div>
