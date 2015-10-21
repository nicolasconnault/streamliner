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

        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($units as $key => $unit) : ?>
                <li <?php if ($unit->id == $selected_unit_id) echo 'class="active"'?> role="presentation">
                    <a class="unit-tab" href="#unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" role="tab" data-toggle="tab"><?=$unit->tab_label?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tabbable">
            <div class="tab-content">
            <?php foreach ($units as $key => $unit) : ?>
                <div id="unit-<?=$unit->id?>" class="row tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>" role="tabpanel">
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">Equipment Notes</div>
                            <div class="panel-body">
                                <?php $this->load->view('messages', array('document_type' => 'unit', 'document_id' => $unit->id, 'display' => ''));?>
                            </div>
                        </div>
                        <?php if ($is_repair) : ?>
                        <div class="panel panel-info">
                            <div class="panel-heading">Diagnosed issues</div>
                            <div class="panel-body">
                                <?php $this->load->view('photo_gallery', array('photos' => $unit->photos, 'id' => $unit->id)) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div> <!-- .col-md-6 -->
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">Equipment Details</div>
                            <div class="panel-body">
                                <?php
                                $is_locked = $this->order_model->has_statuses($order->id, array('LOCKED FOR REVIEW'));

                                if ((!$is_locked && $is_senior_technician) || !$is_technician) {
                                    $this->load->view('stages/unit_details_form', compact('order', 'unit', 'is_senior_technician'));
                                } else {
                                    $this->load->view('stages/unit_details_non_senior', compact('order', 'unit', 'is_senior_technician'));
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div> <!-- .row -->
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
