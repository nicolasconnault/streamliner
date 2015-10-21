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
<nav id="left-menu" style="display: none">
    <table id="stage-navigation" class="table table-striped table-hover table-condensed table-responsive table-bordered">
        <thead>
        <tr>
            <td></td>
            <th style="text-align: center" colspan="<?=count($units)?>">
                Units
            </th>
        </tr>
        <tr>
            <th>Notes</th>
            <?php foreach ($units as $unit) : ?>
                <td style="text-align: center"><a href="<?=base_url().'miniant/stages/unit_details/index/'.$unit->assignment_id?>" class="btn btn-info">Notes</a></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th>Stage</th>
            <?php foreach ($units as $unit) : ?>
                <th style="text-align: center"><?=$unit->id?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($stages as $stage) : ?>
            <tr data-stage_id="<?=$stage->id?>">
                <td><?=$stage->stage_label?></td>
                <?php foreach ($units as $unit) : ?>
                    <td class="stage-completion-cell  <?= ($current_stage->id == $stage->id) ? 'current-stage' : ''?>" style="text-align: center" data-stage_id="<?=$stage->id?>" data-assignment_id="<?=$unit->assignment_id?>">
                        <?php if (in_array($stage->completion_status[$unit->assignment_id], array('Yes', 'No'))) : ?>

                            <a class="stage-completed-<?=$stage->completion_status[$unit->assignment_id]?>"
                                href="<?=base_url()?>miniant/stages/<?=$stage->stage_name?>/index/<?=$unit->assignment_id?>/<?=$stage->extra_param?>"><?=$stage->completion_status[$unit->assignment_id]?>
                            </a>
                        <?php else : ?>
                            <?=$stage->completion_status[$unit->assignment_id]?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($office_messages)) { ?>
    <p>
        <button class="btn btn-info" type="button" data-toggle="modal" data-target="#office_messages" data-position="right">Office notes</button>
    </p>
    <?php } ?>
</nav>
