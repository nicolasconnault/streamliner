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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'installation_checklist')) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div id="unit-<?=$unit->id?>" class="row tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="col-md-12">

                            <div class="panel panel-info">
                                <div class="panel-heading">Installation Checklist</div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr><th>Task</th><th>Technician completed</th><?php if (!empty($supervisor)) { ?><th>Supervisor's approval</th><th>Notes</th><?php } ?></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($unit->installation_tasks as $task) :?>
                                            <tr class="installation_task" data-task_id="<?=$task->id?>" data-unit_id="<?=$unit->id?>">
                                                <td><?=$task->task?></td>
                                                <td>
                                                    <button data-task_id="<?=$task->id?>" class="completed-button tick_yes btn <?= ($task->completed_date) ? 'btn-success' : 'btn-default' ?>">Yes</button>
                                                    <button data-task_id="<?=$task->id?>" class="completed-button tick_no btn <?= (!$task->completed_date) ? 'btn-danger' : 'btn-default' ?>">No</button>
                                                </td>
                                                <?php if (!empty($supervisor)) : ?>
                                                    <td>
                                                    <?php
                                                        $yes_button_class = 'btn-default';
                                                        $no_button_class = 'btn-default';

                                                        if (!is_null($task->satisfactory) && $task->satisfactory == 0) {
                                                            $no_button_class = 'btn-danger';
                                                        }
                                                        if ($task->satisfactory == 1) {
                                                            $yes_button_class = 'btn-success';
                                                        }
                                                        ?>
                                                        <button data-task_id="<?=$task->id?>" class="verify-button tick_yes btn <?=$yes_button_class?>">Yes</button>
                                                        <button class="verify-button tick_no btn <?=$no_button_class?>"
                                                            data-task_id="<?=$task->id?>" data-notes="<?=$task->notes?>" data-name="<?=$task->task?>" data-required="1" >No</button>
                                                    </td>
                                                    <td class="notes">
                                                        <p><span id="task-notes-<?=$task->id?>"><?=$task->notes?></span><?php if (!empty($task->notes)) { ?><br /><br /><?php } ?>
                                                            <button data-name="<?=$task->task?>" data-task_id="<?=$task->id?>" data-notes="<?=$task->notes?>" type="button" data-toggle="modal" data-target="#task-notes" class="btn btn-sml btn-info">
                                                            <i class="fa-pencil"></i> Edit note</button>
                                                        </p>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <script type="text/javascript">
                                    //<![CDATA[
                                    $(function() {
                                        $('#continue-button-<?=$unit->id?>').prop('disabled', true);

                                        check_tasks_complete(<?=$unit->id?>);

                                        $('#unit-<?=$unit->id?> .installation_task .completed-button.tick_yes').on('click', function(event) {
                                            var task_id = $(this).parent().parent().attr('data-task_id');
                                            var unit_id = $(this).parent().parent().attr('data-unit_id');
                                            var button = $(this);
                                            $.post(base_url+'miniant/stages/installation_checklist/set_task_status', {type: 'completed', status: 1, installation_task_id: task_id, unit_id: unit_id}, function(data) {
                                                button.removeClass('btn-default').addClass('btn-success');
                                                button.siblings('.tick_no').removeClass('btn-danger').addClass('btn-default');
                                                check_tasks_complete(<?=$unit->id?>);

                                            }, 'json');
                                        });

                                        $('#unit-<?=$unit->id?> .installation_task .completed-button.tick_no').on('click', function(event) {
                                            var task_id = $(this).parent().parent().attr('data-task_id');
                                            var unit_id = $(this).parent().parent().attr('data-unit_id');
                                            var button = $(this);
                                            $.post(base_url+'miniant/stages/installation_checklist/set_task_status', {type: 'completed', status: 0, installation_task_id: task_id, unit_id: unit_id}, function(data) {
                                                button.removeClass('btn-default').addClass('btn-danger');
                                                button.siblings('.tick_yes').removeClass('btn-success').addClass('btn-default');
                                                check_tasks_complete(<?=$unit->id?>);
                                            }, 'json');
                                        });

                                        $('#unit-<?=$unit->id?> .installation_task .verify-button.tick_yes').on('click', function(event) {
                                            var task_id = $(this).parent().parent().attr('data-task_id');
                                            var unit_id = $(this).parent().parent().attr('data-unit_id');
                                            var button = $(this);
                                            $.post(base_url+'miniant/stages/installation_checklist/set_task_status', {type: 'satisfactory', status: 1, installation_task_id: task_id, unit_id: unit_id}, function(data) {
                                                button.removeClass('btn-default').addClass('btn-success');
                                                button.siblings('.tick_no').removeClass('btn-danger').addClass('btn-default');

                                            }, 'json');
                                        });

                                        $('#unit-<?=$unit->id?> .installation_task .verify-button.tick_no').on('click', function(event) {
                                            var task_id = $(this).parent().parent().attr('data-task_id');
                                            var unit_id = $(this).parent().parent().attr('data-unit_id');
                                            var name = $(this).attr('data-name');
                                            var notes = $(this).attr('data-notes');
                                            var required = $(this).attr('data-required');
                                            var button = $(this);
                                            // Function defined in includes/js/application/stages/installation_checklist.js
                                            open_task_notes(task_id, unit_id, name, notes, required, button);
                                        });
                                    });

                                    function check_tasks_complete(unit_id) {
                                        var all_complete = true;
                                        var any_complete = false;

                                        $('#unit-'+unit_id+' .tick_yes').each(function(key, button) {
                                            if ($(button).hasClass('btn-default')) {
                                                all_complete = false;
                                            } else {
                                                any_complete = true;
                                            }
                                        });
                                        $('#continue-button-'+unit_id).prop('disabled', !any_complete);
                                    }
                                    //]]>
                                    </script>
                                </div>
                            </div>
                            <p>
                                <form method="post" action="<?=base_url()?>miniant/stages/installation_checklist/process/<?=$unit->assignment_id?>">
                                    <input type="submit" disabled="disabled" id="continue-button-<?=$unit->id?>" class="btn btn-primary" value="Continue" />
                                </form>
                            </p>
                        </div> <!-- .col-md-12 -->
                    </div> <!-- .row -->
                <?php endif; ?>
            <?php endforeach; ?>
                <div class="modal" id="task-notes" tabindex="-1" role="dialog" arial-labelledby="task-notes-label" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                                </button>
                                <h4 class="modal-title" id="task-notes-label">Task notes</h4>
                            </div>

                            <div class="modal-body">
                                <textarea class="form-control" name="task-notes" cols="60" rows="6" data-task_id=""></textarea>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button id="save-task-notes" type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
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

