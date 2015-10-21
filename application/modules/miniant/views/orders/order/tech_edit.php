<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
<?php
$srd = $order_data;
$sr_id = $order_id;

echo form_open(base_url().'miniant/orders/order/process_tech_edit', array('id' => 'order_edit_form', 'class' => 'form-horizontal sigpad'));
echo '<div class="panel-body">';
echo form_hidden('order_id', $sr_id);
print_form_container_open();

print_fieldset_open('Dates');
print_static_form_element('Call date', '<span id="call_date">'.$srd['call_date'].'</span>');
print_static_form_element('Appointment date', '<span id="appointment_date">'.$srd['appointment_date'].'</span>');
print_fieldset_close();

print_fieldset_open('Location');
print_static_form_element('Business name', $srd['company_name']);
$full_address = "{$srd['address_street']}, {$srd['address_city']}, {$srd['address_state']}, {$srd['address_postcode']}";
print_static_form_element('Job site address', $full_address);
print_static_form_element('Address details', $srd['address_description']);
print_fieldset_close();

$can_view_unit_management = $this->order_model->check_statuses($sr_id, array('ALLOCATED', 'LOCKED FOR AMENDMENT'), 'OR', array('REVIEWED', 'READY FOR REVIEW', 'LOCKED FOR REVIEW')) && !$locked;

$statuses_for_summary_table = array('UNDER REVIEW', 'REVIEWED', 'ON HOLD', 'COMPLETE', 'ARCHIVED', 'NEEDS JOB NUMBER');

if ($can_view_unit_management) {
    print_fieldset_open('Service details', 'id="order_units"');
    print_static_form_element('Notes', $srd['notes']);

    ?>
        <div class="table-responsive">
        <table class="table table-bordered table-condensed">
            <thead>
                <tr><th>Type</th><th>Brand</th><th>Location served</th><th>Fault(s)</th><th class="actions">Actions</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5">
                        <button class="btn btn-success" type="button" id="new_unit_button">Create a unit</button>
                    </td>
                </tr>
                <tr id="new_unit_row" style="display: none">
                <td><?=form_dropdown('unit_type_id', $dropdowns['unit_types'])?><?=form_hidden('unit_id', null)?></td>
                    <td><?=form_dropdown('brand_id', $dropdowns['brands'])?></td>
                    <td><?=form_input(array('name' => 'location', 'placeholder' => 'Location'))?></td>
                    <td><?=form_textarea(array('name' => 'faults', 'placeholder' => 'Faults'))?></td>
                    <td class="actions">
                        <button class="btn btn-success btn-sm" type="button" id="save_new_unit">Save Unit</button>
                        <button class="btn btn-warning btn-sm" type="button" id="cancel_new_unit">Cancel</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    <?php
    print_fieldset_close();
} else if (has_capability('orders:manageunits') && $this->order_model->has_statuses($sr_id, $statuses_for_summary_table) ) {
    echo 'TODO: Implement summary table of all units and parts';
}

if (has_capability('orders:viewmessages')) {

    print_fieldset_open('Messages', 'id="order_messages"');
    ?>
        <div class="table-responsive">
        <table class="table table-bordered table-condensed" >
            <thead>
                <tr><th>Date</th><th>Author</th><th>Message</th><th class="actions">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($srd['messages'] as $message) : ?>
                <tr>
                    <td><?=$message->date?></td>
                    <td><?=$message->author?></td>
                    <td><?=$message->message?></td>

                    <?php if (has_capabilities(array('orders:deletemessages', 'orders:editmessages'), 'OR')) : ?>
                        <td class="actions">
                            <?php if (has_capability('orders:deletemessages')) : ?>
                                <button type="button" class="btn btn-danger btn-sml btn-icon" onclick="remove_message(<?=$message->id?>)">
                                    <i class="fa fa-trash-o remove_message"></i>Remove
                                </button>
                            <?php endif; ?>

                            <?php if (has_capability('orders:editmessages')) : ?>
                                <button type="button" class="btn btn-default btn-sml btn-icon" onclick="edit_message(<?=$message->id?>)">
                                    <i class="fa fa-pencil edit_message"></i>Edit
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4">
                        <button class="btn btn-success" type="button" id="new_message_button">Write a new message</button>
                    </td>
                </tr>
                <tr id="new_message_row" style="display: none">
                    <td colspan="3"><?=form_textarea(array('name' => 'message', 'placeholder' => 'Type your message here...'))?><?=form_hidden('message_id', null)?></td>
                    <td class="actions">
                        <button class="btn btn-success btn-sm" type="button" id="save_new_message">Save message</button>
                        <button class="btn btn-warning btn-sm" type="button" id="cancel_new_message">Cancel</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

    <?php
    print_fieldset_close();
}

if ($this->order_model->has_statuses($sr_id, array('SIGNED BY CLIENT'))) {
    print_fieldset_open('Authorised signature');
    ?>
    <div class="sigPad signed">
      <div class="sigWrapper">
          <div class="typed"><?=$order_data['first_name']?> <?=$order_data['last_name']?></div>
          <canvas style="display: none" class="pad" width="300" height="100"></canvas>
      </div>
      <p><?=$order_data['first_name']?> <?=$order_data['last_name']?><br><?=$order_data['signature_date']?></p>
    </div>
    <script type="text/javascript">
    <!-- TODO: Replace the following by a PHP-generated PNG image -->
    //<![CDATA[
    var sig = <?=$order_data['client_signature']?>;
    $(document).ready(function () {
        var image = document.createElement('img');
        $(image).attr('src', $('.sigPad.signed').signaturePad({displayOnly:true, penColour: '#000000'}).getSignatureImage());
        $('.sigWrapper').append(image);
    });
    //]]>
    </script>
    <?php
    print_fieldset_close();
} else if ($this->order_model->has_statuses($sr_id, array('REVIEWED')) && !$locked) {
    print_fieldset_open('Authorisation');
    ?>
      <label for="first_name">Print your first name</label>
      <input type="text" name="first_name_<?=$tenancy->id?>" id="first_name" class="first-name" /> <br />
      <label for="last_name">Print your last name</label>
      <input type="text" name="last_name_<?=$tenancy->id?>" id="last_name" class="last-name" />
      <ul class="sigNav">
        <li class="drawIt"><a href="#draw-it">Draw your signature</a></li>
        <li class="clearButton"><a href="#clear">Clear</a></li>
      </ul>
      <div class="sig sigWrapper">
        <div class="typed"></div>
        <canvas class="pad" width="300" height="100"></canvas>
        <input type="hidden" name="client_signature" class="output">
      </div>
    <?php
    print_fieldset_close();
}

print_submit_container_open();

foreach ($submit_buttons as $name => $label) {
    echo form_submit($name, $label, 'id="'.$name.'_button" class="btn btn-primary submit_button"');
}

echo form_submit('cancel', 'Cancel', 'id="cancel_button" class="btn btn-default"');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
echo '</div>';
echo '</div>';
?>
<script type="text/javascript">
    //<![CDATA[
    var locked = <?php if ($locked) { echo 'true';} else { echo 'false'; }?>;
    //]]>
</script>
