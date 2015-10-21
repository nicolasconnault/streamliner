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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'maintenance_checklist')) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="unit-<?=$unit->id?>" class="row tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="col-md-12">

                            <div class="panel panel-info">
                                <div class="panel-heading">Maintenance Checklist</div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr><th>Task</th><th>Completed</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($unit->maintenance_tasks as $task) :?>
                                            <tr class="maintenance_task" data-task_id="<?=$task->id?>" data-assignment_id="<?=$unit->assignment_id?>">
                                                <td><?=$task->name?></td>
                                                <td>
                                                    <button class="tick_yes btn <?= ($task->completed_date) ? 'btn-success' : 'btn-default' ?>">Yes</button>
                                                    <button class="tick_no btn <?= (!$task->completed_date) ? 'btn-danger' : 'btn-default' ?>">No</button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <script type="text/javascript">
                                    //<![CDATA[
                                    $(function() {
                                        $('#continue-button-<?=$unit->id?>').prop('disabled', true);

                                        check_tasks_complete(<?=$unit->id?>);

                                        $('#unit-<?=$unit->id?> .maintenance_task .tick_yes').on('click', function(event) {
                                            var task_id = $(this).parent().parent().attr('data-task_id');
                                            var assignment_id = $(this).parent().parent().attr('data-assignment_id');
                                            var button = $(this);
                                            $.post(base_url+'miniant/stages/maintenance_checklist/set_task_status', {status: 1, maintenance_task_template_id: task_id, assignment_id: assignment_id}, function(data) {
                                                button.removeClass('btn-default').addClass('btn-success');
                                                button.siblings('.tick_no').removeClass('btn-danger').addClass('btn-default');
                                                check_tasks_complete(<?=$unit->id?>);

                                            }, 'json');
                                        });

                                        $('#unit-<?=$unit->id?> .maintenance_task .tick_no').on('click', function(event) {
                                            var task_id = $(this).parent().parent().attr('data-task_id');
                                            var assignment_id = $(this).parent().parent().attr('data-assignment_id');
                                            var button = $(this);
                                            $.post(base_url+'miniant/stages/maintenance_checklist/set_task_status', {status: 0, maintenance_task_template_id: task_id, assignment_id: assignment_id}, function(data) {
                                                button.removeClass('btn-default').addClass('btn-danger');
                                                button.siblings('.tick_yes').removeClass('btn-success').addClass('btn-default');
                                                check_tasks_complete(<?=$unit->id?>);
                                            }, 'json');
                                        });
                                    });

                                    function check_tasks_complete(unit_id) {
                                        var all_complete = true;

                                        $('#unit-'+unit_id+' .tick_yes').each(function(key, button) {
                                            if ($(button).hasClass('btn-default')) {
                                                all_complete = false;
                                            }
                                        });
                                        $('#unit-'+unit_id+' .dialog').toggle(all_complete);
                                    }
                                    //]]>
                                    </script>
                                </div>
                            </div>
                            <p>
                                <?=$unit->dialog?>
                            </p>
                        </div> <!-- .col-md-12 -->
                    </div> <!-- .row -->
                <?php endif; ?>
            <?php endforeach; ?>
            </div><!-- .tab-content -->
        </div><!-- .tabbable -->
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
