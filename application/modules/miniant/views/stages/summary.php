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
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Unit serial #</th><th>Diagnostic #</th><th>Unit details recorded</th><th>Diagnosed issues</th><th>SQ submitted</th><th>DOWD recorded</th></tr>
            </thead>
            <tbody>
                <?php foreach ($units as $unit) : ?>
                <tr>
                    <td><?=$unit->serial?></td>
                    <td><?=$unit->diagnostic->reference_id?></td>
                    <td>
                        <?=get_status_icon(array(
                        'system' => 'unit',
                        'document_id' => $unit->id,
                        'statuses' => array('UNIT DETAILS RECORDED'),
                        'url' => 'stages/unit_details/index/'.$unit->diagnostic->assignment_id,
                        'html_params' => array('data-unit_id' => $unit->diagnostic->unit_id)
                        ))?>
                    </td>
                    <td>
                        <?=get_status_icon(array(
                        'system' => 'assignment',
                        'document_id' => $unit->assignment->id,
                        'statuses' => array('ISSUES DIAGNOSED'),
                        'url' => 'stages/diagnostic_report/index/'.$unit->assignment->id,
                        'html_params' => array('data-unit_id' => $unit->id)
                        ))?>
                    </td>
                    <td>
                        <?=get_status_icon(array(
                        'system' => 'assignment',
                        'document_id' => $unit->assignment->id,
                        'statuses' => array('AWAITING REVIEW'),
                        'url' => 'stages/required_parts/index/'.$unit->assignment->id,
                        'not_required' => !$this->assignment_model->has_statuses($unit->assignment_id, array('SQ APPROVED')),
                        'html_params' => array('data-unit_id' => $unit->id)
                        ))?>
                    </td>
                    <td>
                        <?=get_status_icon(array(
                        'system' => 'assignment',
                        'document_id' => $unit->assignment->id,
                        'statuses' => array('DOWD RECORDED'),
                        'url' => 'stages/dowds/index/'.$unit->assignment->id,
                        'html_params' => array('data-unit_id' => $unit->id),
                        'not_required' => $unit->assignment->no_issues_found
                        ))?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br />
        <?php if ($completed) { ?>
            <a href="<?=base_url()?>miniant/stages/postjob_checklist/index/<?=$assignment->id?>" class="btn btn-primary">Next</a>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
//<[CDATA[
$(function() {
    $('.status-icon').on('click', function(event) {
        event.preventDefault();
        var href = $(this).attr('href');

        $.post(base_url+'miniant/stages/stage/set_selected_unit/'+$(this).attr('data-unit_id'), function(data) {
            window.location = href;
        });
    });
});
//]]>
</script>
