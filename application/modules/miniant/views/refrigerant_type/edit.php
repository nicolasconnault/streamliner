<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
echo form_open(base_url().'miniant/refrigerant_types/process_edit/', array('id' => 'refrigerant_type_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('refrigerant_type_id', $refrigerant_type_id);
print_form_container_open();

print_input_element(array(
    'label' => 'Refrigerant type Name',
    'name' => 'name',
    'required' => true,
    'render_static' => !has_capability('site:editrefrigerant_types')
));

if (has_capability('site:editrefrigerant_types')) {
    print_submit_container_open();
    print_submit_button();
    print_cancel_button(base_url().'miniant/refrigerant_types');
    print_submit_container_close();
}

print_form_container_close();
echo '</div>';
echo form_close();
?>
</div>
</div>
