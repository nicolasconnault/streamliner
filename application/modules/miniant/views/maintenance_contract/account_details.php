<?php
$locked = false;

if (has_capability('maintenance_contracts:viewcontracts')) {
    echo '<div id="account_details" class="'.get_tab_panel_class().'">';

    print_dropdown_element(array(
        'label' => 'Service Job Number',
        'name' => 'original_order_id',
        'render_static' => $locked || !has_capability('maintenance_contracts:editcontracts'),
        'show' => true,
        'static_value' => $maintenance_contract_data['original_order_id'],
        'static_displayvalue' => $maintenance_contract_data['original_order_id'] . ' (' . $maintenance_contract_data['account_name'] . ')',
        'options' => $dropdowns['service_orders'],
        'extra_html' => array('onchange' => 'update_from_original_order_id(this);'),
        'required' => false
    ));

    print_dropdown_element(array(
        'label' => 'Billing Account',
        'name' => 'account_id',
        'render_static' => $locked || !has_capability('maintenance_contracts:editcontracts'),
        'show' => true,
        'static_value' => $maintenance_contract_data['account_id'],
        'static_displayvalue' => $maintenance_contract_data['account_name'],
        'options' => $dropdowns['accounts'],
        'extra_html' => array('onchange' => 'update_account_details(this);'),
        'add_link' => base_url().'accounts/add',
        'required' => true
    ));

    if (has_capability('orders:editbillingcontact') && !$locked) {
        print_dropdown_element(array(
            'label' => 'Billing contact',
            'name' => 'billing_contact_id',
            'options' => array(null => '-- Select or create --', 0 => 'Create a new billing contact'),
            'render_static' => $locked || !has_capability('orders:editbillingcontact'),
            'static_value' => $maintenance_contract_data['billing_contact_id'],
            'default_value' => $maintenance_contract_data['billing_contact_id'],
            'static_displayvalue' => $maintenance_contract_data['billing_contact_first_name'] . ' ' . $maintenance_contract_data['billing_contact_surname'],
            'show' => has_capability('orders:viewbillingcontact'),
            'extra_html' => array('class' => 'popover_trigger'),
            'edit_link' => base_url().'users/contact/edit/'.$maintenance_contract_data['billing_contact_id'],
            'required' => true
        ));
    }

    print_dropdown_element(array(
        'label' => 'Property Manager',
        'name' => 'property_manager_contact_id',
        'options' => array(null => '-- Select or create --', 0 => 'Create a new property manager'),
        'render_static' => $locked,
        'static_value' => $maintenance_contract_data['property_manager_contact_id'],
        'default_value' => $maintenance_contract_data['property_manager_contact_id'],
        'static_displayvalue' => $maintenance_contract_data['property_manager_contact_first_name'] . ' ' . $maintenance_contract_data['property_manager_contact_surname'],
        'extra_html' => array('class' => 'popover_trigger'),
        'edit_link' => base_url().'users/contact/edit/'.$maintenance_contract_data['property_manager_contact_id'],
        'required' => true
    ));

    if (has_capability('orders:editsiteaddress') && !$locked) {
        print_dropdown_element(array(
            'label' => 'Site address',
            'name' => 'site_address_id',
            'options' => array(null => '-- Select or create --', 0 => 'Create a new job site address'),
            'render_static' => $locked || !has_capability('orders:editsiteaddress'),
            'static_value' => $maintenance_contract_data['site_address_id'],
            'static_displayvalue' => $this->address_model->get_formatted_address($maintenance_contract_data['site_address_id']),
            'show' => has_capability('orders:viewsiteaddress'),
            'extra_html' => array('class' => 'popover_trigger'),
            'edit_link' => base_url().'addresses/edit/'.$maintenance_contract_data['site_address_id'],
            'required' => true
        ));
    }

    ?>
    <div id="tenancy_table" class="panel panel-info" style="display:none">
        <div class="panel-heading">
            <h3>Tenancies
                <div class="pull-right title-buttons">
                    <button type="button" class="btn btn-info navbar-btn help btn-icon" title="Tenancies" data-content="Add at least one tenancy here. If the site has no tenancies, create one with the name of the billing account." data-placement="left" data-container="body">
                        <i class="fa fa-question" ></i><span>Help</span>
                    </button>
                    <button id="new-tenancy-button" class="btn btn-success navbar-btn btn-icon"><i class="fa fa-plus"></i> New</button>
                </div>
            </h3>
        </div>
        <div class="panel-body">
            <table id="tenancy-table" class="table table-condensed">
                <thead>
                    <tr><th>Name</th><th>Actions</th></tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
    <?php
    print_tabbed_form_navbuttons();
    echo '</div>';
}
