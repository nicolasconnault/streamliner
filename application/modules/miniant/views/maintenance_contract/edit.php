<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
echo form_open_multipart(base_url().'miniant/maintenance_contracts/process_edit', array('id' => 'maintenance_contract_edit_form', 'class' => 'form-horizontal'));
echo '<div id="maintenance_contract_panel" class="tab-container panel-body">
    <div id="tab_overlay"></div>';
print_hidden_element(array('name' => 'maintenance_contract_id', 'default_value' => $maintenance_contract_id));
print_form_container_open();
$tab_list = array();

if (has_capability('maintenance_contracts:viewcontracts')) {
    $tab_list[] = array('id' => 'account_details', 'label' => 'Account Details', 'subtabs' => array(
        array('id' => 'billing_contact', 'label' => 'Billing Contact'),
        array('id' => 'site_contact', 'label' => 'Job Site Contact'),
        array('id' => 'site_address', 'label' => 'Job Site Address')
    ));
}

$tab_list[] = array('id' => 'internal_info', 'label' => 'Info (Maintenance contract details)');

if (!empty($maintenance_contract_id)) {
    $tab_list[] = array('id' => 'maintenance_contract_units', 'label' => 'Equipment');
    $tab_list[] = array('id' => 'messages_maintenance_contract_'.$maintenance_contract_id, 'label' => 'Notes');
}

setup_tabbed_form($tab_list);
print_tab_list();

$this->load->view('maintenance_contract/account_details.php', compact('locked','maintenance_contract_data','dropdowns'));
$this->load->view('maintenance_contract/info.php', compact('locked','maintenance_contract_data','dropdowns'));

if (!empty($maintenance_contract_id)) {
    $this->load->view('maintenance_contract/units', compact('maintenance_contract_id'));
}

if (has_capability('maintenance_contracts:viewcontracts') && !empty($maintenance_contract_id)) {
    $display = (empty($maintenance_contract_id)) ? ' style="display: none;" ' : '';
    $this->load->view('messages', array('display' => $display, 'document_type' => 'maintenance_contract', 'document_id' => $maintenance_contract_id, 'in_tabbed_form' => true));
}

print_submit_container_open();

if (!empty($maintenance_contract_id)) {
    print_submit_button("Submit for allocation", 'submit_button', 'submit');
} else {
    print_submit_button("Save and record units", 'submit_button', 'submit');
}

if (empty($maintenance_contract_id)) {
    print_cancel_button(base_url().'miniant/maintenance_contracts');
} else {
    echo form_submit('return', 'Return to Maintenance contracts', 'id="return_button" class="btn btn-default"');
}

print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div></div>';

// Popover forms
$this->load->view('maintenance_contract/popovers', array('dropdowns' => $dropdowns));
$this->load->view('maintenance_contract/unit_popover', array('dropdowns' => $dropdowns, 'maintenance_contract_id' => $maintenance_contract_id));

?>
<script type="text/javascript">
    //<![CDATA[
    var locked = false;
    //]]>
</script>
