<?php
$first_unit = reset($units);
$address = $this->address_model->get_formatted_address($order->site_address_id);
$selected_unit_id = $this->session->userdata('selected_unit_id');
$first_unit_id = reset($units)->id;
if (empty($selected_unit_id)) {
    $selected_unit_id = $first_unit_id;
}

?>
<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">

        <ul class="nav nav-tabs">
            <?php foreach ($units as $key => $unit) : ?>
                <li <?php if ($unit->id == $selected_unit_id) echo 'class="active"'?>>
                    <a class="unit-tab" href="#unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" data-toggle="tab"><?=$unit->tab_label?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tabbable">
            <div class="tab-content">
            <?php foreach ($units as $key => $unit) : ?>
                <?php if (!$this->unit_model->has_statuses($unit->id, array("UNIT DETAILS RECORDED"))) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-12">
                                <p>Mandatory details about this unit have not yet been recorded.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/unit_details/index/<?=$unit->assignment_id?>">Record unit details</a></p>
                            </div>
                        </div>
                    </div>

                <?php elseif (empty($unit->serial_number) && empty($unit->indoor_serial_number) && empty($unit->outdoor_serial_number)) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-12">
                                <p>Please record the unit's serial number(s) first</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/unit_serial/index/<?=$unit->assignment_id?>">Record unit serial number(s)</a></p>
                            </div>
                        </div>
                    </div>

                <?php else : ?>
                    <div id="unit-<?=$unit->id?>" class="row tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="col-md-12">
                            <?php $this->load->view('upload', array('directory' => 'assignment', 'document_id' => $unit->id, 'subdirectory' => $unit->assignment_id, 'module' => 'miniant')); ?>
                            <p>
                                <form method="post" action="<?=base_url()?>miniant/stages/unit_photos/process/<?=$assignment->id?>">
                                    <input type="submit" class="btn btn-primary" value="Continue" />
                                </form>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </div>
    </div> <!-- .panel-body -->
</div> <!-- .panel -->
<script type="text/javascript">
//<[CDATA[
var assignment_id = <?=$assignment->id?>;
$(function() {
    $('.unit-tab').on('click', function(event) {
        $.post(base_url+'miniant/stages/stage/set_selected_unit/'+$(this).attr('data-unit_id'));
    });
});
//]]>
</script>
