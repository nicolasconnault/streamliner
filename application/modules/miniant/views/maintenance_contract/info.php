<?php
$locked = false;

echo '<div id="internal_info" class="'.get_tab_panel_class().'">';
    print_datetime_element(array(
        'label' => 'Creation date',
        'name' => 'creation_date',
        'render_static' => $locked,
        'show' => true,
        'required' => true,
        'static_value' => $maintenance_contract_data['creation_date'],
        'static_displayvalue' => $maintenance_contract_data['creation_date']
    ));

    print_date_element(array(
        'label' => 'Next maintenance date',
        'name' => 'next_maintenance_date',
        'render_static' => $locked || !has_capability('orders:editpreferredstartdate'),
        'show' => true,
        'required' => true,
        'static_value' => $maintenance_contract_data['next_maintenance_date'],
        'static_displayvalue' => $maintenance_contract_data['next_maintenance_date'],
    ));

    print_dropdown_element(array(
        'label' => 'Schedule type',
        'name' => 'schedule_interval',
        'required' => true,
        'options' => array('3' => 'Quarterly', '6' => '6-monthly', '12' => 'Yearly'),
    ));

print_tabbed_form_navbuttons();
echo '</div>';

