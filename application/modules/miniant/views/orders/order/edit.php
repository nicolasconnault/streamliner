<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
echo form_open_multipart(base_url().'miniant/orders/order/process_edit', array('id' => 'order_edit_form', 'class' => 'form-horizontal'));
print_hidden_element(array('name' => 'order_id', 'default_value' => $order_id));
echo '<div id="order_panel" class="tab-container panel-body">
    <div id="tab_overlay"></div>';
print_form_container_open();
$tab_list = array();

if (has_capability('orders:viewclientinfo')) {
    $tab_list[] = array('id' => 'account_details', 'label' => 'Account Details', 'subtabs' => array(
        array('id' => 'billing_contact', 'label' => 'Billing Contact'),
        array('id' => 'site_contact', 'label' => 'Job Site Contact'),
        array('id' => 'site_address', 'label' => 'Job Site Address')
    ));
}

$tab_list[] = array('id' => 'internal_info', 'label' => 'Info (Job details)');

if (has_capability('orders:viewclientinfo')) {
    $tab_list[] = array('id' => 'deposit', 'label' => 'Deposit');
}

if (!empty($order_id)) {
    $tab_list[] = array('id' => 'order_units', 'label' => 'Equipment');
    $tab_list[] = array('id' => 'messages_order_'.$order_id, 'label' => 'Notes');
}

setup_tabbed_form($tab_list);
print_tab_list();

/**
 * TODO implement a multi-select
 *
if (has_capability('orders:changestatus')) {
    print_dropdown_element('status_id', 'Status', $dropdowns['statuses'], true);
}
 */

$this->load->view('orders/order/account_details.php', compact('locked','order_data','dropdowns'));
$this->load->view('orders/order/info.php', compact('locked','order_data','dropdowns', 'is_maintenance'));
$this->load->view('orders/order/deposit', compact('locked','order_data','dropdowns'));

if (!empty($order_id)) {
    $this->load->view('orders/order/order_units', compact('order_id', 'is_installation'));
}

if (has_capability('orders:viewmessages') && !empty($order_id)) {
    $display = (empty($order_id)) ? ' style="display: none;" ' : '';
    $this->load->view('messages', array('display' => $display, 'document_type' => 'order', 'document_id' => $order_id, 'in_tabbed_form' => true));
}

print_submit_container_open();

if (!$locked) {
    foreach ($submit_buttons as $name => $label) {
        print_submit_button($label, $name.'_button', $name);
    }
}

if (empty($order_id)) {
    print_cancel_button(base_url().'miniant/orders/order');
} else {
    echo form_submit('return', 'Return to Jobs', 'id="return_button" class="btn btn-default"');
}

print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div></div>';

// Popover forms
$this->load->view('orders/order/popovers', array('dropdowns' => $dropdowns));

if ($is_maintenance) {
    $this->load->view('orders/order/order_unit_maintenance_popover', compact('dropdowns', 'order_id'));
} else if ($is_installation) {
    $this->load->view('orders/order/order_unit_installation_popover', compact('dropdowns', 'order_id'));
} else {
    $this->load->view('orders/order/order_unit_popover', compact('dropdowns', 'order_id'));
}
?>
<script type="text/javascript">
    //<![CDATA[
    var locked = <?php if ($locked) { echo 'true';} else { echo 'false'; }?>;
    //]]>
</script>
    <button id="locked_contact_help" style="display: none;" type="button" class="btn btn-success navbar-btn help btn-icon" title="Locked contact name" data-content="To unlock the contact name, remove all the units below" data-placement="right" data-container="body">
        <i class="fa fa-question" ></i><span>Help</span>
    </button>
