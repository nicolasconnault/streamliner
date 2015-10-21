<?php
// I think we may have to drop this page, it's duplicating the tech_view calendar without adding much functionality
?>
<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($list_title_options)?></div>
    <div id="assignedjobstable" class="panel-body"></div>
        <table id="ajaxtable" class="table table-bordered table-striped table-hover" style="float: left;">
            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th>Status</th>
                    <th>Time start</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $task) : ?>
                <tr>
                    <td><?=$task->id?></td>
                    <td><?=$task->status?></td>
                    <td><?=$task->time_start?></td>
                    <td>
                        <a href="<?=base_url()?>miniant/orders/role/delete_role_user/<?=$role_id?>/<?=$user->id?>">
                            <i class="fa fa-trash-o" title="Remove this user from this role" onclick="return deletethis();"/></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
