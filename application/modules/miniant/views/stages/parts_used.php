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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'parts_used')) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
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
                                        $parts_title_options['title'] = $this->diagnostic_model->get_reference_id(
                                            $unit->diagnostic->id);
                                        $parts_title_options['extra'] = '<a class="btn btn-primary unsaved-warning" href="'.base_url().'miniant/stages/diagnostic_report/index/'.$unit->assignment_id.'">Edit issues</a>';
                                    }
                                    ?>
                                    <div class="panel-heading"><?=get_title($parts_title_options)?></div>
                                    <div class="panel-body">
                                        <?php

                                        echo form_open(base_url().'miniant/stages/parts_used/process', array('id' => 'parts_used_form', 'class' => 'form-horizontal'));
                                        if ($order_type != 'Installation' && $order_type != 'Repair') {
                                            print_hidden_element(array('name' => 'diagnostic_id', 'default_value' => $unit->diagnostic->id));
                                        }
                                        print_hidden_element(array('name' => 'assignment_id', 'default_value' => $unit->assignment_id));
                                        print_hidden_element(array('name' => 'type', 'default_value' => $type));
                                        ?>
                                        <table class="table table-condensed table-striped table-responsive table-bordered">
                                            <thead>
                                                <tr><th>Part used</th><th>Qty/Notes</th><th>PO Number</th><th>Origin</th><th class="actions">Actions</th></tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($unit->required_parts as $part) :
                                            ?>
                                                <tr>
                                                    <td><?=$part->part_name?></td>
                                                    <td><?=print_textarea_element(array('name' => 'description['.$part->part_type_id.']', 'placeholder' => 'Qty', 'rows' => 2, 'cols' => 40, 'popover' => true, 'required' => true));?></td>
                                                    <td>
                                                        <?=print_input_element(array('name' => 'po_number['.$part->part_type_id.']', 'popover' => true, 'required' => true))?>
                                                    </td>
                                                    <td>
                                                        <?=print_dropdown_element(array(
                                                            'name' => 'origin['.$part->part_type_id.']',
                                                            'popover' => true,
                                                            'required' => true,
                                                            'options' => array('Van stock' => 'Van stock', 'Supplier' => 'Supplier', 'Workshop' => 'Workshop'),
                                                            'label' => 'Where did this part come from?')
                                                        )?>
                                                    </td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php foreach ($unit->template_parts_used as $part) : ?>
                                                <tr>
                                                    <td><?=$part->name?></td>
                                                    <td><?=print_textarea_element(array('name' => 'description['.$part->id.']', 'placeholder' => $part->instructions, 'rows' => 2, 'cols' => 40, 'popover' => true, 'required' => true, 'default_value' => $part->description));?></td>
                                                    <td>
                                                        N/A
                                                    </td>
                                                    <td>
                                                        <?=print_dropdown_element(array(
                                                            'name' => 'origin['.$part->id.']',
                                                            'popover' => true,
                                                            'required' => true,
                                                            'options' => array('Van stock' => 'Van stock', 'Supplier' => 'Supplier', 'Workshop' => 'Workshop'),
                                                            'label' => 'Where did this part come from?')
                                                        )?>
                                                    </td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php
                                            foreach ($unit->custom_parts as $part) :
                                            ?>
                                                <tr>
                                                    <td><?=$part->part_name?></td>
                                                    <td><?=print_textarea_element(array('name' => 'custom_description['.$part->id.']', 'placeholder' => 'Qty', 'rows' => 2, 'cols' => 40, 'popover' => true, 'required' => true));?></td>
                                                    <td>
                                                        <?=print_input_element(array('name' => 'custom_po_number['.$part->id.']', 'popover' => true, 'required' => true))?>
                                                    </td>
                                                    <td>
                                                        <?=print_dropdown_element(array(
                                                            'name' => 'custom_origin['.$part->id.']',
                                                            'popover' => true,
                                                            'required' => true,
                                                            'options' => array('Van stock' => 'Van stock', 'Supplier' => 'Supplier', 'Workshop' => 'Workshop'),
                                                            'label' => 'Where did this part come from?')
                                                        )?>
                                                    </td>
                                                    <td>
                                                        <a class="delete" href="<?=base_url()?>miniant/stages/parts_used/delete_custom_part/<?=$part->id?>" title="Delete">
                                                            <i class="action-icon fa fa-trash-o" title="Delete this part" onclick="return deletethis();"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                                <tr id="new-part">
                                                    <td colspan="4">
                                                        <button class="btn btn-success">
                                                            Add a part
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <?php
                                        print_submit_container_open();
                                        echo form_submit('submit', 'Save and continue', 'class="btn btn-primary submit_button" data-loading-text="Loading..."');
                                        print_submit_container_close();
                                        echo form_close();
                                        ?>
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
        </div><!-- .tab-content -->
    </div>
</div>
<script type="text/javascript">
//<[CDATA[
$(function() {
    $('.unit-tab').on('click', function(event) {
        $.post(base_url+'miniant/stages/stage/set_selected_unit/'+$(this).attr('data-unit_id'));
    });

    $('#new-part button').click(function(event) {
        event.preventDefault();
        $(this).parents('tr').hide();
        var newpart_row = document.createElement('tr');
        $(newpart_row).addClass('new-part');
        $(newpart_row).html('<td colspan="4">' +
                '<input type="hidden" name="assignment_id" value="<?=$unit->assignment_id?>" />' +
                '<label for="new-part-input">Part name: </label> ' +
                '<input id="new-part-input" type="text" name="part_name" /> ' +
                '<button class="btn btn-success" id="add-new-part">Add</button></td>');
        $(this).parents('table').append(newpart_row);

    });
});
//]]>
</script>
