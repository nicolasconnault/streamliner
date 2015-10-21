<?php
echo '<div id="internal_info" class="'.get_tab_panel_class().'">';
    print_datetime_element(array(
        'label' => 'Call date',
        'name' => 'call_date',
        'render_static' => $locked || !has_capability('orders:editcalldate'),
        'show' => true,
        'static_value' => $order_data['call_date'],
        'static_displayvalue' => $order_data['call_date']
    ));

    print_dropdown_element(array(
        'label' => 'Job type',
        'name' => 'order_type_id',
        'render_static' => $locked || !has_capability('orders:editordertype') || !empty($order_data['order_type_id']),
        'show' => true,
        'static_value' => @$order_data['order_type'],
        'static_displayvalue' => $dropdowns['order_types'][$order_data['order_type_id']],
        'options' => $dropdowns['order_types'],
        'required' => true
    ));

    if (!empty($order_data['order_type_id'])) {
        print_hidden_element(array(
            'name' => 'order_type_id',
            'default_value' => $order_data['order_type_id'],
        ));

        if ($is_installation) {
            print_input_element(array(
                'label' => 'Quotation number',
                'name' => 'installation_quotation_number',
                'render_static' => $locked,
                'show' => true,
                'static_value' => @$order_data['installation_quotation_number'],
                'required' => true
            ));
        }
    } else {
        print_input_element(array(
            'label' => 'Quotation number',
            'name' => 'installation_quotation_number',
            'render_static' => $locked,
            'show' => true,
            'static_value' => @$order_data['installation_quotation_number'],
            'disabledunless' => array('order_type_id' => $this->order_model->get_type_id('Installation')),
            'required' => true
        ));
    }

    /*
    if (has_capability('orders:setappointmentdate') && !$locked) {
        print_datetime_element('Appointment date', array('name' => 'appointment_date'));
    } else if (!empty($order_id) || $locked) {
        print_static_form_element('Appointment date', '<span id="appointment_date">'.$order_data['appointment_date'].'</span>');
    }

    if (has_capability('orders:allocateorders') && !$locked) {
        print_dropdown_element('technician_id', 'Assigned technician', $dropdowns['technicians'], false);
    } else if (!empty($order_id) || $locked) {
        print_static_form_element('Assigned technician', '<span id="technician_name" data-id="'.$order_data['technician_id'].'">'.$order_data['technician_first_name'] . ' ' . $order_data['technician_last_name'].'</span>');
    }
     */

    if ($is_maintenance || empty($order_id)) {
        $params = array(
            'label' => 'Start date',
            'name' => 'maintenance_preferred_start_date',
            'render_static' => $locked || !has_capability('orders:editpreferredstartdate'),
            'show' => true,
            'static_value' => $order_data['preferred_start_date'],
            'static_displayvalue' => $order_data['preferred_start_date'],
            'required' => true,
        );

        if (empty($order_id)) {
            // $params['disabledunless'] = array('order_type_id' => $this->order_model->get_type_id('Maintenance'));
        }

        print_date_element($params);

    } else if (!$is_maintenance || empty($order_data)) {
        $params = array(
            'label' => 'Preferred job date',
            'name' => 'preferred_start_date',
            'render_static' => $locked || !has_capability('orders:editpreferredstartdate'),
            'show' => true,
            'static_value' => $order_data['preferred_start_date'],
            'static_displayvalue' => $order_data['preferred_start_date']
        );

        if (empty($order_id)) {
            $params['disabledif'] = array('order_type_id' => $this->order_model->get_type_id('Maintenance'));
        }

        print_date_element($params);
    }

    print_input_element(array(
        'label' => 'Customer\'s PO number',
        'name' => 'customer_po_number',
        'render_static' => $locked || !has_capability('orders:editcustomerponumber'),
        'show' => true,
        'static_value' => @$order_data['customer_po_number']
    ));

    $attachment = $order_data['attachment'];
    $static_displayvalue = (empty($attachment->filename_original)) ? '' : anchor($attachment->url, $attachment->filename_original, array('target' => '_blank')) . nbs(2);
    $static_displayvalue .= anchor(base_url().'miniant/orders/order/delete_attachment/'.$order_id, '<i class="fa fa-trash-o" onclick="return deletethis();" title="Delete this attachment?"></i>');

    print_file_element(array(
        'label' => 'Attachment',
        'name' => 'attachment',
        'render_static' => $locked || !has_capability('orders:addattachment') || !empty($order_data['attachment']->filename_original),
        'show' => true,
        'static_displayvalue' => $static_displayvalue,
        'required' => false,
    ));

print_tabbed_form_navbuttons();
echo '</div>';

