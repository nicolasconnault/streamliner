<?php
$first_tenancy = reset($tenancies)['tenancy'];
$selected_tenancy_id = $this->session->userdata('selected_tenancy_id');
$first_tenancy_id = $first_tenancy->id;

if (empty($selected_tenancy_id)) {
    $selected_tenancy_id = $first_tenancy_id;
}

if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($assignment->id, 'signature')) : ?>
    <p>The <?=$uncompleted_stage->stage_label?> must be completed first.</p>
    <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$assignment->id?>"><?=$uncompleted_stage->stage_label?></a></p>
<?php else: ?>

<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?=get_title(array('title' => 'Job Details'))?></div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr><th style="width:200px">Job Number</th><td><?=$this->order_model->get_reference_id($assignment->order_id)?></td></tr>
                            <tr><th>Date started</th><td><?=unix_to_human(time())?></td></tr>
                            <tr><th>Time started</th><td><?=unix_to_human($time_started, '%h:%i%a')?></td></tr>
                            <tr><th>Current time</th><td><?=unix_to_human(time(), '%h:%i%a')?></td></tr>
                            <tr><th>Technician</th><td><?=$this->user_model->get_name($assignment->technician_id)?></td></tr>
                            <tr><th>Site address</th><td><?=$this->address_model->get_formatted_address($order->site_address_id)?></td></tr>
                            <tr><th>Customer PO Number</th><td><?=$order->customer_po_number?></td></tr>
                            <tr><th>Pre-job site photos</th><td><?php $this->load->view('photo_gallery', array('photos' => $site_photos['pre-job'], 'id' => 'pre-job')) ?></td></tr>
                            <tr><th>Post-job site photos</th><td><?php $this->load->view('photo_gallery', array('photos' => $site_photos['post-job'], 'id' => 'post-job')) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- .row -->

        <?php if (count($tenancies) > 1) : ?>
        <ul class="nav nav-tabs">
            <?php foreach ($tenancies as $key => $tenancy_info) :
                $tenancy = $tenancy_info['tenancy'];
                ?>
                <li <?php if ($tenancy->id == $selected_tenancy_id) echo 'class="active"'?>>
                    <a class="tenancy-tab" href="#tenancy-<?=$tenancy->id?>" data-tenancy_id="<?=$tenancy->id?>" data-toggle="tab"><?=$tenancy->name?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <div class="tabbable">
            <div class="tab-content">
            <?php foreach ($tenancies as $key => $tenancy_info) :
                $tenancy = $tenancy_info['tenancy'];
                $signature = $tenancy_info['signature'];
                ?>
                <div id="tenancy-<?=$tenancy->id?>" class="tab-pane <?php if ($tenancy->id == $selected_tenancy_id) echo 'active'?>">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><h4>Description of work performed</h4></div>
                            <div class="panel-body">
                                <p><?=$order->dowd_text?></p>
                                <table class="table table-bordered">
                                    <thead><tr><th>Unit</th><th>Location (area)</th><th>Description of Work Done</th></tr></thead>
                                    <tbody>
                                <?php foreach ($tenancy_info['units'] as $unit) :
                                    if (($is_maintenance || $is_service) && empty($unit->issues)) : ?>
                                        <tr>
                                            <td><?php $this->load->view('photo_gallery', array('photos' => $unit->photos, 'id' => $unit->id)) ?></td>
                                            <td><?=$unit->area_serving?></td>
                                            <td> <?=$assignment->dowd_text?> </td>
                                        </tr>
                                    <?php endif; ?>


                                    <?php if (empty($unit->issues)) : ?>
                                        <tr>
                                            <td><?php if (!empty($unit->photos)) $this->load->view('photo_gallery', array('photos' => $unit->photos, 'id' => $unit->id)) ?></td>
                                            <td><?=$unit->area_serving?></td>
                                            <td>
                                                <?php if ($order_type == 'Breakdown') { ?>
                                                    <?=$assignment->dowd_text?>
                                                <?php } else if ($order_type == 'Installation') { ?>
                                                The unit was installed and tested, and found to be functioning properly
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php else : ?>
                                        <tr>
                                            <td><?php if (!empty($unit->photos)) $this->load->view('photo_gallery', array('photos' => $unit->photos, 'id' => $unit->id)) ?></td>
                                            <td><?=$unit->area_serving?></td>
                                            <td>
                                                <?php if ($is_maintenance || $is_service) : ?>
                                                <p>Maintenance was completed on this unit, and the following issues were found:</p>
                                                <?php endif;?>
                                            <?php if ($is_repair) : ?>
                                                <?=$order_dowd?>
                                            <?php else : ?>
                                                <ul>
                                                <?php foreach ($unit->issues as $issue) :?>
                                                    <li><?=$issue->dowd_text?></li>
                                                <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach;?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="panel panel-info">
                            <div class="panel-heading"><h4>Parts used</h4></div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <thead><tr><th>Unit</th><th>Location (area)</th><th>Part</th><th>Quantity</th><th>Van stock/Supplier</th><th>PO Number</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($tenancy_info['units'] as $unit) : ?>
                                        <?php if (empty($unit->parts_used)) : ?>
                                            <tr>
                                                <td><?php $this->load->view('photo_gallery', array('photos' => $unit->photos, 'id' => 'parts_used_'.$unit->id)) ?></td>
                                            <td><?=$unit->area_serving?></td>
                                                <td colspan="5">No parts were used for this unit</td>
                                            </tr>
                                        <?php else :
                                            $rowspan = (count($unit->parts_used) > 0) ? count($unit->parts_used) : 1;
                                            $part_counter = 0;
                                            foreach ($unit->parts_used as $key => $part) :
                                                if ($part->quantity == 0) {
                                                    continue;
                                                }
                                            ?>
                                                <tr>
                                                    <?php if ($part_counter == 0) { ?>
                                                        <td rowspan="<?=$rowspan?>"><?php $this->load->view('photo_gallery', array('photos' => $unit->photos, 'id' => 'parts_used_'.$unit->id)) ?></td>
                                                        <td rowspan="<?=$rowspan?>"><?=$unit->area_serving?></td>
                                                    <?php } ?>
                                                    <td><?=$part->part_name?></td>
                                                    <td><?=$part->quantity?></td>
                                                    <td><?=$part->origin?></td>
                                                    <td><?=$part->po_number?></td>
                                                </tr>
                                            <?php
                                                $part_counter++;
                                            endforeach;?>
                                        <?php endif; ?>
                                    <?php endforeach;?>
                                    </tbody>
                                </table>

                                <?php if (!$is_maintenance && !$is_service && false) : // Disabled as per issue #29 on bitbucket ?>
                                <table class="table table-bordered">
                                    <tr><th style="width: 180px">Time on site</th><td><?=$total_time?></td></tr>
                                </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading"><?=get_title(array('title' => 'Signature'))?></div>
                            <div class="panel-body">
                                <!--<p>Temperature Solutions is happy to provide a service quote for this work, which will be scheduled for completion on approval of the quote.</p>-->
                                <div class="row" style="height: 500px">
                                    <div class="col-lg-4">
                                        <h4>Report Sign Off</h4>
                                        <p>Please provide a signature to indicate that you have sighted this report.  An automated copy will be forwarded to the nominated contact's email address and our invoice will follow.</p>
                                        <h4>Terms</h4>
                                        <div id="terms"><?=$terms?></div>
                                    </div>
                                    <div class="col-md-8">
        <?php
        if ($this->tenancy_model->invoice_is_signed($tenancy->id, $order->id)) { ?>
            <div id="sigpad-<?=$tenancy->id?>" class="sigPad signed">
              <div class="sigWrapper">
              <div class="typed"><?=$signature->first_name?> <?=$signature->last_name?></div>
                <canvas class="pad" width="500" height="200"></canvas>
              </div>
              <p style="margin-top: 100px">
              Signed by <?=$signature->first_name?> <?=$signature->last_name?><br />
              On <?=unix_to_human($signature->signature_date)?></p>
            </div>
            <script type="text/javascript">
            //<![CDATA[
            var sig_<?=$tenancy->id?> = <?=$signature->signature_text?>;
            $(document).ready(function () {
              $('#sigpad-<?=$tenancy->id?>').signaturePad({displayOnly:true}).regenerate(sig_<?=$tenancy->id?>);
            });
            //]]>
            </script>
            <?php
        } else {
        echo form_open(base_url().'miniant/stages/signature/process', array('id' => 'signature_form-'.$tenancy->id, 'class' => 'form-horizontal sigPad'));
        ?>
            <div class="sigPad" id="sigpad-<?=$tenancy->id?>">
              <label for="first_name">Print your first name</label>
              <input type="text" name="first_name_<?=$tenancy->id?>" id="first_name" class="first-name-<?=$tenancy->id?>" /> <br />
              <label for="last_name">Print your last name</label>
              <input type="text" name="last_name_<?=$tenancy->id?>" id="last_name" class="last-name-<?=$tenancy->id?>" />
              <ul class="sigNav">
                <li class="drawIt"><a href="#draw-it">Draw your signature</a></li>
                <li class="clearButton"><a href="#clear">Clear</a></li>
              </ul>
              <div class="sig sigWrapper">
                <div class="typed"></div>
                <canvas class="pad-<?=$tenancy->id?>" width="500" height="200"></canvas>
                <input type="hidden" name="client_signature_<?=$tenancy->id?>" class="output-<?=$tenancy->id?>">
                <input type="hidden" name="assignment_id" value="<?=$assignment->id?>" />
                <br />
                <button name="tenancy_id" value="<?=$tenancy->id?>" class="btn btn-primary" type="submit">Record client signature</button>
              </div>
            <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function () {
              $('#sigpad-<?=$tenancy->id?>').signaturePad({
                  canvas: '.pad-<?=$tenancy->id?>',
                  defaultAction: 'drawIt',
                  name: '.name-<?=$tenancy->id?>',
                  output: '.output-<?=$tenancy->id?>',
                  });
            });
            //]]>
            </script>
            </div>
        <?php
        form_close();
        } ?>
<?php if ($this->order_model->are_all_tenancies_signed($order->id)) { ?>
<p><a href="<?=$this->workflow_manager->get_next_url()?>" class="btn btn-primary">Continue</a></p>
<?php } ?>
                            </div>
                            </div>
                        </div><!-- .panel-body -->
                    </div><!-- .panel-info -->
                </div><!-- .col-md-12 -->
            </div><!-- .row -->
        </div>
    <?php endforeach; ?>
    </div>
</div>
<script type="text/javascript">
//<[CDATA[
$(function() {
    $('.tenancy-tab').on('click', function(event) {
        $.post(base_url+'miniant/stages/signature/set_selected_tenancy/'+$(this).attr('data-tenancy_id'));
    });
});
//]]>
</script>

<?php endif; ?>
