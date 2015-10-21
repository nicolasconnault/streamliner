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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'dowds')) : ?>
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
                        <?php echo form_open(base_url().'miniant/stages/dowds/process/index/', array('id' => 'dowd_edit_form_'.$unit->assignment_id, 'class' => 'dowd_edit_form form-horizontal')); ?>
                            <?php print_hidden_element(array('name' => 'assignment_id', 'default_value' => $unit->assignment_id)); ?>

                        <table class="unit-dowds table table-bordered">
                            <thead>
                                <tr><th>Part</th><th>Issue</th><th>DOWD</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($unit->diagnostic_issues)) : ?>
                                    <?php print_hidden_element(array('name' => 'diagnostic_id', 'default_value' => $unit->diagnostic->id)); ?>
                                    <?php foreach ($unit->diagnostic_issues as $key => $issue) : ?>
                                        <tr>
                                            <td><?=$issue->part_type_name?></td>
                                            <td><?=$issue->issue_type_name?></td>
                                            <td><?= $dowds_dropdown[$issue->dowd_id]; ?>
                                                <?php print_hidden_element(array('name' => 'dowd_id['.$issue->id.']', 'default_value' => $issue->dowd_id)); ?>
                                            </td>
                                            <td>
                                                <?php print_textarea_element(array(
                                                    'placeholder' => 'Description',
                                                    'popover' => true,
                                                    'label' => 'Description',
                                                    'name' => "dowd_text[".$issue->id."]",
                                                    'default_value' => $issue->dowd_text,
                                                    'cols' => 25,'rows' => 5,
                                                    'extra_html' => array('data-dowd_id' => $issue->dowd_id, 'style' => "margin-left: 15px;"),
                                                    'required' => true));
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php
                        print_submit_container_open();
                        if ($is_technician) {
                            echo form_submit('button', 'Save DOWDs', 'id="submit_button" class="btn btn-primary"');
                        } else {
                            echo '<p><a href="'.$this->workflow_manager->get_next_url().'" class="btn btn-primary">Continue</a></p>';
                        }
                        print_form_container_close();
                        echo '</div>';
                        echo form_close();
                    ?>
                    </div><!-- .tab-pane -->
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
