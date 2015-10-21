<?php
$modal_id = (empty($booking)) ? 'new-booking' : "booking-$booking->id";
$title = (empty($booking)) ? 'New booking request' : 'Edit booking request';
if (!empty($booking) && $booking->confirmed)  {
    $title = 'Edit booking';
}
?>
<div class="modal" id="<?=$modal_id?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<form action="<?=base_url()?>building/job_sites/edit_booking" method="post" class="form-horizontal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" onclick="$('.modal').modal('hide');" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><?=$title?></h3>
            </div>

            <div class="modal-body">
                <?php
            echo form_hidden('job_site_id', $job_site_id);
            print_form_container_open();
            if (!empty($booking)) {
                echo form_hidden('id', $booking->id);
                print_static_form_element('Booking ID', $booking->id);
            }
            print_textarea_element(array(
                'label' => 'Message',
                'name' => 'message',
                'required' => true,
                'rows' => 3,
                'cols' => 30,
                'default_value' => @$booking->message)
            );
            print_date_element(array(
                'label' => 'Booking date',
                'name' => 'booking_date',
                'required' => true,
                'default_value' => unix_to_human(@$booking->booking_date))
            );
            print_checkbox_element(array(
                'label' => 'Confirmed',
                'name' => 'confirmed',
                'value' => 1,
                'default_value' => @$booking->confirmed)
            );
            print_multiselect_element(array(
                'label' => 'Email recipients',
                'name' => 'recipients[]',
                'options' => $staff,
                'default_value' => $recipients)
            );
            print_form_container_close();
            ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-lg btn-primary" type="submit" onclick="return validate_form($(this).parents('form'));"><i class="fa fa-save"></i>Save</button>
                <?php if (!empty($booking)) { ?><button class="btn btn-lg btn-danger" type="button" onclick="if(deletethis()){delete_booking(<?=$booking->id?>);};" value="delete"><i class="fa fa-trash-o"></i>Delete</button><?php } ?>
                <button onclick="$('.modal').modal('hide');" type="button" class="btn btn-lg btn-default" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</form>
</div><!-- /.modal -->
