<div class="row">
    <div class="col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading"><h3>SQ Details</h3></div>
            <div class="panel-body">
                <table class="table table-condensed table-bordered" id="sq-history">
                    <tr><th>Invoice ID</th><td><?=$servicequote_id?></td><th>Request type</th><td><?=$order['order_type']?></td></tr>
                    <tr><th>Site suburb</th><td><?=$order['site_address_city']?></td><th>Account</th><td><?=$order['account_name']?></td></tr>
                    <tr><th>Billing contact</th><td><?=$order['billing_contact_first_name']?> <?=$order['billing_contact_surname']?></td><th>Site contact</th><td><?=$order['site_contact_first_name']?> <?=$order['site_contact_surname']?></td></tr>
                    <tr><th>Date created</th><td><?=unix_to_human($servicequote->creation_date)?></td><th>Technician</th><td><?=$technician_name?></td></tr>
                    <tr><th>Unit type</th><td><?=$assignment->unit_type?></td><th>Issues</th><td><?=ul($issues)?></td></tr>
                    <tr><th>Statuses</th></th><td><?=ul($statuses)?><th>Photos</th><td><?php $this->load->view('photo_gallery', array('photos' => $unit['photos'], 'id' => $unit['id'])) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading"><h3>SQ history</h3></div>
            <div class="panel-body">
                <table class="table table-condensed table-bordered" id="sq-history">
                    <thead>
                        <tr><th>Date/Time</th><th>Statuses added</th><th>Statuses removed</th><th>Changed by</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->servicequote_model->get_status_history($servicequote_id) as $status) : ?>
                            <tr>
                                <td><?=$status->changed_date?></td>
                                <td><?=ul($status->added_statuses)?></td>
                                <td><?=ul($status->removed_statuses)?></td>
                                <td><?=$status->changed_by_string?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><h3>Steps</h3></div>
            <div class="panel-body">
                <?php $this->load->view('servicequotes/stages'); ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><?=get_title($title_options)?></div>
            <div class="panel-body">
                <?php $this->load->view('servicequotes/stages/'.$content_stage); ?>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="servicequote_id" value="<?=$servicequote_id?>" />
