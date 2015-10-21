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
<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($assignment->id, 'repair_checklist')) : ?>
            <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
            <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$assignment->id?>"><?=$uncompleted_stage->stage_label?></a></p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr><th>Task</th><th>Completed</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task) :?>
                    <tr class="repair_task" data-task_id="<?=$task->id?>" data-assignment_id="<?=$assignment->id?>">
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

                $('.repair_task .tick_yes').on('click', function(event) {
                    var task_id = $(this).parent().parent().attr('data-task_id');
                    var button = $(this);
                    $.post(base_url+'miniant/stages/repair_checklist/set_task_status', {status: 1, repair_task_id: task_id}, function(data) {
                        button.removeClass('btn-default').addClass('btn-success');
                        button.siblings('.tick_no').removeClass('btn-danger').addClass('btn-default');

                    }, 'json');
                });

                $('.repair_task .tick_no').on('click', function(event) {
                    var task_id = $(this).parent().parent().attr('data-task_id');
                    var button = $(this);
                    $.post(base_url+'miniant/stages/repair_checklist/set_task_status', {status: 0, repair_task_id: task_id}, function(data) {
                        button.removeClass('btn-default').addClass('btn-danger');
                        button.siblings('.tick_yes').removeClass('btn-success').addClass('btn-default');
                    }, 'json');
                });
            });

            //]]>
            </script>
            <p>
                <?=$dialog?>
            </p>
        <?php endif; ?>
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
