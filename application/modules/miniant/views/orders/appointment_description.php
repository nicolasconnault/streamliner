<div class="modal" id="assignment-<?=$assignment->id?>-description" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" onclick="$('.modal').modal('hide');" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><?=$order->order_type?> Assignment description</h3>
            </div>

            <div class="modal-body">
                <table class="table">
                    <tr>
                        <th>Job Number</th><td><?=$order->id?></td>
                    </tr>
                    <tr>
                        <th>Type</th><td><?=$order->order_type?></td>
                    </tr>
                    <tr>
                        <th>Status</th><td><?=$assignment->status?></td>
                    </tr>
                    <tr>
                        <th>Billing Account</th><td><?=$assignment->account_name?></td>
                    </tr>
                    <tr>
                        <th>Site Contact</th><td><?=$assignment->site_contact_name?></td>
                    </tr>
                    <tr>
                        <th>Site address</th><td><?=$this->address_model->get_formatted_address($order->site_address_id)?></td>
                    </tr>
                    <tr>
                        <th>Unit type</th><td><?=$assignment->unit_type?></td>
                    </tr>
                    <tr>
                        <th>Unit brand</th><td><?=$assignment->unit_brand?></td>
                    </tr>
                    <tr>
                        <th>Unit tenancy</th><td><?=$assignment->unit_tenancy?></td>
                    </tr>
                    <!--
                    <tr>
                        <th>Priority</th><td><?=$assignment->priority?></td>
                    </tr>
                    -->
                    <tr>
                        <th>Estimated start</th><td><?=unix_to_human($assignment->appointment_date, '%h:%i%a')?></td>
                    </tr>
                    <tr>
                        <th>Estimated time</th><td><?=get_duration($assignment->estimated_duration)?></td>
                    </tr>
                    <tr>
                        <th>Estimated finish</th><td><?=unix_to_human($assignment->appointment_date + $assignment->estimated_duration * 60, '%h:%i%a')?></td>
                    </tr>
                    <?php // TODO Display a summary of time spent on job ?>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" onclick="window.location='<?=base_url().'miniant/orders/assignments/edit/'.$assignment->id?>'">Edit</button>
                <?php if ($this->order_model->can_be_unscheduled($order->id)) { ?>
                    <button type="button" onclick="unschedule(this)" data-assignment_id="<?=$assignment->id?>" class="btn unschedule btn-danger">Un-schedule</button>
                <?php } ?>
                <button onclick="$('.modal').modal('hide');" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
