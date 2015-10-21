<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <div class="panel panel-info">
            <div class="panel-heading"><h4>Invoices</h4></div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th># of units</th>
                            <th>Date work completed</th>
                            <th>Date created</th>
                            <th>Date sent</th>
                            <th>Amount inc. GST</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['assignments'] as $assignment) : ?>
                        <tr>
                            <td><?=$assignment->type?></td>
                            <td><?=count($assignment->units)?></td>
                            <td><?=unix_to_human($assignment->time_completed)?></td>
                            <td><?=unix_to_human($assignment->invoice_creation_date)?></td>
                            <td><?=unix_to_human($assignment->sent_date)?></td>
                            <td><?php if (!empty($assignment->cost)) { ?><?=currency_format($assignment->cost + $assignment->gst)?><?php } ?></td>
                            <td><ul><?php foreach ($assignment->invoice_statuses as $status) : ?><li><?=$status?></li><?php endforeach;?></ul></td>
                            <td>
                                <?php foreach ($assignment->action_buttons as $url => $label) : ?>
                                    <a href="<?=$url?>" class="btn btn-info"><?=$label?></a>&nbsp;
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading"><h4>Job Info</h4></div>
            <div class="panel-body">
                <table class="table table-bordered table-condensed">
                    <tr><th style="width: 160px">Job type</th><td><?=$order['order_type']?></td></tr>
                    <tr><th>Call date</th><td><?=unix_to_human($order['call_date'])?></td></tr>
                    <tr><th>Status</th><td><ul><?php foreach ($order['statuses'] as $status) : ?><li><?=$status?></li><?php endforeach;?></ul></td></tr>
<?php if (!empty($order['attachment'])) { ?><tr><th>Attachment</th><td><a href="<?=$order['attachment']->url?>"><?=$order['attachment']->file_original?></a></td></tr><?php } ?>
                    <tr><th>Account name</th><td><?=$order['account_name']?></td></tr>
                    <tr>
                        <th>Billing contact</th>
                        <td>
                            <a href="mailto:<?=$order['billing_contact_email']?>">
                                <?=$order['billing_contact_first_name'].' '.$order['billing_contact_surname'].' ('.$order['billing_contact_mobile'].')'?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Billing address</th>
                        <td>
                            <?=$this->address_model->get_formatted_address($order['billing_address_id'])?>
                        </td>
                    </tr>
                    <tr>
                        <th>Job site contact</th>
                        <td>
                            <a href="mailto:<?=$order['site_contact_email']?>">
                                <?=$order['site_contact_first_name'].' '.$order['site_contact_surname'].' ('.$order['site_contact_mobile'].')'?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Job Site address</th>
                        <td>
                            <?=$this->address_model->get_formatted_address($order['site_address_id'])?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
