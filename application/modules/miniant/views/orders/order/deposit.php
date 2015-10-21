<?php
echo '<div id="deposit" class="'.get_tab_panel_class().'">';
    print_checkbox_element(array(
        'label' => 'Deposit Required',
        'name' => 'deposit_required',
        'value' => 1
    ));

    print_input_element(array(
        'label' => 'Deposit amount',
        'name' => 'deposit_amount',
        'placeholder' => '0.00',
        'disabledunless' => array('deposit_required'),
        'validation' => 'number'
    ));
    print_dropdown_element(array(
        'label' => 'Credit card type',
        'name' => 'cc_type',
        'options' => array('Visa' => 'Visa', 'Mastercard' => 'Mastercard'),
        'disabledunless' => array('deposit_required')
    ));

    print_input_element(array(
        'label' => 'Credit Card number',
        'name' => 'cc_number',
        'disabledunless' => array('deposit_required')
    ));

    print_input_element(array(
        'label' => 'Credit Card expiry',
        'name' => 'cc_expiry',
        'placeholder' => '01/15',
        'size' => 5,
        'disabledunless' => array('deposit_required')
    ));

    print_input_element(array(
        'label' => 'Credit Card security (CVV)',
        'name' => 'cc_security',
        'size' => 3,
        'disabledunless' => array('deposit_required')
    ));
print_tabbed_form_navbuttons();
echo '</div>';
