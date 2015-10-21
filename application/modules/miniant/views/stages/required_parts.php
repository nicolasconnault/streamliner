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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'required_parts')) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
                            </div>
                        </div>
                    </div>
                <?php elseif (!$this->assignment_model->has_statuses($unit->assignment_id, array("SQ APPROVED"))) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>This unit doesn't require an SQ</p>
                                <p><a class="btn btn-primary" href="<?=$this->workflow_manager->get_next_url()?>">Next Page</a></p>
                            </div>
                        </div>
                    </div>

                <?php else : ?>
                    <div id="unit-<?=$unit->id?>" class="tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-success">
                                    <?php
                                    $parts_title_options['title'] = $this->diagnostic_model->get_reference_id($unit->diagnostic->id);
                                    $parts_title_options['extra'] = '<a class="btn btn-primary unsaved-warning" href="'.base_url().'miniant/stages/diagnostic_report/index/'.$unit->assignment_id.'">Edit issues</a>';
                                    ?>
                                    <div class="panel-heading"><?=get_title($parts_title_options)?></div>
                                    <div class="panel-body">
                                        <?php

                                        echo form_open(base_url().'miniant/stages/required_parts/process', array('id' => 'required_parts_form', 'class' => 'form-horizontal'));
                                        print_hidden_element(array('name' => 'id', 'default_value' => $unit->diagnostic->id));
                                        print_hidden_element(array('name' => 'sq_id', 'default_value' => $sq_id));
                                        ?>
                                        <table class="table table-condensed table-striped table-responsive table-bordered">
                                            <thead>
                                                <tr><th>Required part</th><th>Issue</th><th>Qty</th><th>Faulty part model number</th><th>Required info</th><th>Description of required info</th></tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($unit->required_parts as $part) : ?>

                                                <tr>
                                                    <td><?=$part->name?></td>
                                                    <td><?=$part->issue->issue_type_name?> <?=$part->issue->part_type_name?></td>
                                                    <td class="form-group required"><?=form_dropdown('qty['.$part->id.']',range(0,48), $part->quantity, 'class="form-control"')?></td>
                                                    <td class="form-group required">
                                                        <?=form_input(array('name' => 'part_model_number['.$part->id.']', 'value' => @$part->part_number, 'class' => 'form-control'))?>
                                                        <a class="btn btn-default" onclick="$(this).parent().find('input').val('N/A');">N/A</a>
                                                    </td>
                                                    <?php $required = empty($part->part_type->instructions) ? '' : 'required' ?>
                                                    <td class="form-group <?=$required?>">
                                                        <?=form_textarea(array('cols' => 20, 'rows' => 4, 'name' => 'part_info['.$part->id.']', 'value' => @$part->description, 'class' => 'form-control ' . $required))?>
                                                    </td>
                                                    <td><?=nl2br($part->part_type->instructions)?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php
                                        print_submit_container_open();
                                        if ($is_technician) {
                                            echo form_submit('submit', 'Submit for approval', 'class="btn btn-primary submit_button" data-loading-text="Loading..."');
                                        } else {
                                            echo '<p><a href="'.$this->workflow_manager->get_next_url().'" class="btn btn-primary">Continue</a></p>';
                                        }
                                        print_submit_container_close();
                                        echo form_close();
                                        ?>
                                    </div><!-- .panel-body -->
                                </div><!-- .panel -->
                            </div><!-- .col-md-6 -->
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-info">
                                    <?php
                                    $info_title_options['extra'] = '<a class="btn btn-primary unsaved-warning" href="'.base_url().'miniant/stages/unit_details/index/'.$unit->assignment_id.'">Edit</a>';
                                    ?>
                                    <div class="panel-heading"><?=get_title($info_title_options)?></div>
                                    <div class="panel-body">
                                        <table class="table table-condensed" style="width: 300px">
                                            <tr><th>Job Number</th><td><?=$order->id?></td></tr>
                                            <tr><th>Assignment Number</th><td><?=$unit->assignment_id?></td></tr>
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
