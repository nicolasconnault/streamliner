<?php
function print_contact_table($contacts=array()) {
    $ci = get_instance();
    echo '<table class="table table-condensed table-bordered table-responsive table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Billing Name</th>
                <th>Phone 1</th>
                <th>Phone 2</th>
                <th>Mobile 1</th>
                <th>Mobile 2</th>
                <th>Email 1</th>
                <th>Email 2</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
        ';

    foreach ($contacts as $contact) {
        echo "<tr>
            <td>$contact->id</td>
            <td>". $ci->contact_model->get_name($contact)."</td>
            <td>$contact->phone</td>
            <td>$contact->phone2</td>
            <td>$contact->mobile</td>
            <td>$contact->mobile2</td>
            <td>$contact->email</td>
            <td>$contact->email2</td>
            <td>";
        if (has_capability('users:editcontacts')) {
            echo anchor(base_url().'users/contact/edit/'.$contact->id, "Edit", 'class="btn btn-success"');
        }

        echo "</td></tr>";
    }
    echo "</tbody></table>";

}
?>

<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
echo form_open(base_url().'miniant/miniant_accounts/process_edit/', array('id' => 'account_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('account_id', $account_id);
print_form_container_open();

print_input_element(array(
    'label' => 'Billing Name',
    'name' => 'name',
    'required' => true,
    'render_static' => !has_capability('site:editaccounts')
));

print_input_element(array(
    'label' => 'ABN',
    'name' => 'abn',
    'required' => false,
    'render_static' => !has_capability('site:editaccounts')
));

print_dropdown_element(array(
    'label' => 'Credit hold',
    'name' => 'cc_hold',
    'required' => false,
    'options' => array(0 => 'No', 1 => 'Yes'),
    'info_text' => 'If a credit hold is placed on this account, it will appear in red throughout Mini-Ant',
    'render_static' => !has_capability('site:editaccounts')

));

print_fieldset_open('Billing address');

    print_hidden_element(array('name' => 'billing_address_id'));

    print_checkbox_element(array(
        'label' => 'Is this a PO Box?',
        'name' => 'billing_address_po_box_on',
        'value' => 1,
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_input_element(array(
        'label' => 'Unit',
        'name' => 'billing_address_unit',
        'required' => false,
        'disabledif' => array('billing_address_po_box_on' => 1),
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_input_element(array(
        'label' => 'Number',
        'name' => 'billing_address_number',
        'required' => true,
        'disabledif' => array('billing_address_po_box_on' => 1),
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_input_element(array(
        'label' => 'Street',
        'name' => 'billing_address_street',
        'required' => true,
        'disabledif' => array('billing_address_po_box_on' => 1),
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_autocomplete_element(array(
        'label' => 'Street type',
        'name' => 'billing_address_street_type',
        'options_url' => 'addresses/get_street_types',
        'required' => true,
        'id' => 'autocomplete_street_type',
        'disabledif' => array('billing_address_po_box_on' => 1),
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_input_element(array(
        'label' => 'PO Box',
        'name' => 'billing_address_po_box',
        'required' => true,
        'disabledunless' => array('billing_address_po_box_on' => 1),
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_input_element(array(
        'label' => 'City',
        'name' => 'billing_address_city',
        'required' => true,
        'render_static' => !has_capability('site:editaccounts')
    ));

    print_input_element(array(
        'label' => 'Post Code',
        'name' => 'billing_address_postcode',
        'required' => true,
        'render_static' => !has_capability('site:editaccounts')
    ));


print_fieldset_close();

if (has_capability('site:editaccounts')) {
    print_submit_container_open();
    print_submit_button();
    print_cancel_button(base_url().'miniant/miniant_accounts');
    print_submit_container_close();
}

if (!empty($account_id)) {
    print_fieldset_open('Billing contacts');
        if (!empty($account_data['billing_contacts'])) {
            print_contact_table($account_data['billing_contacts']);
        } else {
            echo "<p>No billing contacts</p>";
        }
    print_fieldset_close();

    print_fieldset_open('Job site contacts');
        if (!empty($account_data['site_contacts'])) {
            print_contact_table($account_data['site_contacts']);
        } else {
            echo "<p>No job site contacts</p>";
        }
    print_fieldset_close();

    print_fieldset_open('Job site addresses');
    if (!empty($account_data['site_addresses'])) {
        echo '<table class="table table-condensed table-bordered table-responsive table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Unit</th>
                    <th>Number</th>
                    <th>Street</th>
                    <th>Suburb</th>
                    <th>Post code</th>
                    <th>Jobs</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
            ';

        foreach ($account_data['site_addresses'] as $address) {
            echo "<tr>
                <td>$address->id</td>
                <td>$address->unit</td>
                <td>$address->number</td>
                <td>$address->street</td>
                <td>$address->city</td>
                <td>$address->postcode</td>
                <td>";

            if (!empty($address->orders)) {
                echo count($address->orders);
            }

            echo "</td>
                <td>";
            if (has_capability('site:editaddresses')) {
            echo anchor(base_url().'addresses/edit/'.$address->id, "Edit", 'class="btn btn-success"');
            }

            echo "</td></tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No job site addresses</p>";
    }
    print_fieldset_close();
}
print_form_container_close();
echo '</div>';
echo form_close();
?>
</div>
</div>
