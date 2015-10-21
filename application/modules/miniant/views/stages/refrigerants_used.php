<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
?>
<?php
$first_unit = reset($units);
$selected_unit_id = $this->session->userdata('selected_unit_id');

$first_unit_id = $first_unit->id;
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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'refrigerants_used')) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
                            </div>
                        </div>
                    </div>
                <?php elseif ($unit->type == 'Evaporative A/C') : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>This unit does not require refrigerant</p>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div id="unit-<?=$unit->id?>" class="tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="panel panel-success">
                                    <?php
                                    if ($order_type == 'Installation' || $order_type == 'Repair') {
                                        $parts_title_options['title'] = 'Parts used';
                                    } else {
                                        $parts_title_options['title'] = $this->diagnostic_model->get_reference_id($unit->diagnostic->id);
                                        $parts_title_options['extra'] = '<a class="btn btn-primary unsaved-warning" href="'.base_url().'miniant/stages/diagnostic_report/index/'.$unit->assignment_id.'">Edit issues</a>';
                                    }
                                    ?>
                                    <div class="panel-heading"><?=get_title($parts_title_options)?></div>
                                    <div class="panel-body">
                                        <table class="refrigerant-table table table-bordered" data-assignment_id="<?=$unit->assignment_id?>">
                                            <thead><tr><th>Used/Reclaimed</th><th>Refrigerant type</th><th>Qty (Kg)</th><th>Qty (g)</th><th>Bottle Serial Number</th><th>Actions</th></tr></thead>
                                            <tbody>
                                                <tr id="new-refrigerant">
                                                    <td><?=print_dropdown_element(array('name' => 'used_or_reclaimed', 'options' => array(0 => 'Used', 1 => 'Reclaimed'), 'required' => true, 'popover' => true))?></td>
                                                    <td><?=print_dropdown_element(array('name' => 'refrigerant_type_id', 'options' => $this->refrigerant_type_model->get_dropdown('name'), 'required' => true, 'popover' => true))?></td>
                                                    <td><?=print_input_element(array('name' => 'quantity_kg', 'placeholder' => '0-999', 'required' => true, 'popover' => true, 'size' => 3))?></td>
                                                    <td><?=print_input_element(array('name' => 'quantity_g', 'placeholder' => '0-999', 'required' => true, 'popover' => true, 'size' => 3))?></td>
                                                    <td><?=print_input_element(array('name' => 'serial_number', 'placeholder' => 'Serial number', 'required' => true, 'popover' => true))?></td>
                                                    <td><button type="button" id="add-button" class="btn btn-success">Add</button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div><!-- .panel-body -->
                                </div><!-- .panel -->
                            </div><!-- .col-md-6 -->
                            <div class="col-md-3">
                                <div class="panel panel-info">
                                    <?php
                                    $info_title_options['extra'] = '<a class="btn btn-primary unsaved-warning" href="'.base_url().'miniant/stages/unit_details/index/'.$unit->assignment_id.'">Edit</a>';
                                    ?>
                                    <div class="panel-heading"><?=get_title($info_title_options)?></div>
                                    <div class="panel-body">
                                        <table class="table table-condensed" style="width: 300px">
                                            <?php if ($order_type != 'Installation' && $order_type != 'Repair') { ?>
                                                <tr><th>Job Number</th><td><?=$order->id?></td></tr>
                                                <tr><th>Assignment Number</th><td><?=$unit->assignment_id?></td></tr>
                                            <?php } ?>
                                            <tr><th>Date</th><td><?=unix_to_human($unit->assignment->appointment_date)?></td></tr>
                                        </table>
                                        <?=$this->load->view('units/info', array('unit' => $unit), true);?>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .row -->
                    </div><!-- .tab-pane -->
                <?php endif; ?>
            <?php endforeach; ?>
            </div><!-- .tabbable -->
            <p><a href="<?=$this->workflow_manager->get_next_url()?>" class="btn btn-primary">Continue</a></p>
        </div><!-- .tab-content -->
    </div>
</div>
<script type="text/javascript">
//<[CDATA[
$(function() {
    $('.unit-tab').on('click', function(event) {
        $.post(base_url+'miniant/stages/stage/set_selected_unit/'+$(this).attr('data-unit_id'));
    });
});
//]]>
</script>
