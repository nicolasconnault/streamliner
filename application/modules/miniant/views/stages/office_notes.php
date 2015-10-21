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

    <?php
    if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($assignment->id, 'order_dowd')) : ?>
        <p>The <?=$uncompleted_stage->stage_label?> must be completed first.</p>
        <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$uncompleted_stage->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
    <?php else: ?>
        <?php if (empty($office_messages)) : ?>
            <p>There are no office notes</p>
        <?php else : ?>
            <?php foreach ($office_messages as $message) : ?>
                <p><?=$message->message?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="<?=base_url()?>miniant/stages/office_notes/process/" class="office_notes_edit_form form-horizontal" method="post">
            <input type="hidden" name="order_id" value="<?=$assignment->order_id?>" />
            <input type="hidden" name="assignment_id" value="<?=$assignment->id?>" />
        <?php
            echo form_submit('button', 'Continue', 'id="submit_button" class="btn btn-primary"');
        ?>
        </form>
    <?php endif; ?>
    </div>
</div>
