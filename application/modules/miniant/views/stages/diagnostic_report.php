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
                <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'diagnostic_report')) : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <div class="row">
                            <div class="col-md-6">
                                <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
                            </div>
                        </div>
                    </div>

                <?php else : ?>
                    <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                        <?php if ($order_type != 'Repair') : ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-info">
                                    <?php
                                    $info_title_options['extra'] = '<a class="btn btn-primary" href="'.base_url().'miniant/stages/unit_details/index/'.$unit->assignment_id.'">Edit</a>';
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
                            <div class="col-md-6">
                                <div class="panel panel-primary">
                                    <div class="panel-heading"><?=get_title($site_title_options)?></div>
                                    <div class="panel-body">
                                        <div id="unit-<?=$unit->id?>-site_address" class="tab-pane active">
                                            <table class="table table-condensed">
                                                <tr><th>Senior technician</th><td><?=$unit->assignment->senior_technician?></td></tr>
                                                <tr><th>Site contact First name</th><td><?=$order->site_contact_first_name?></td></tr>
                                                <tr><th>Site contact Surname</th><td><?=$order->site_contact_surname?></td></tr>
                                                <tr><th>Unit</th><td><?=$order->site_address_unit?></td></tr>
                                                <tr><th>Number</th><td><?=$order->site_address_number?></td></tr>
                                                <tr><th>Street</th><td><?=$order->site_address_street?></td></tr>
                                                <tr><th>Suburb</th><td><?=$order->site_address_city?></td></tr>
                                                <tr><th>State</th><td><?=$order->site_address_state?></td></tr>
                                                <tr><th>Post code</th><td><?=$order->site_address_postcode?></td></tr>
                                            </table>
                                        </div>
                                    </div><!-- .panel-body -->
                                </div><!-- .panel -->
                            </div><!-- .col-md-6 -->
                        </div><!-- .row -->
                        <?php endif; ?>

                        <div class="row">
                            <div class="panel panel-info">
                                <div class="panel-heading"><?=get_title($diagnostic_title_options)?></div>
                                <div class="panel-body diagnosed-issues">
                                    <?php if ($unit->sq_submitted) : ?>
                                        <p class="warning message">An SQ has already been recorded for this unit, you cannot change the diagnostic issues now.</p>
                                        <?php if ($unit->assignment->no_issues_found) { ?>
                                            <p>No issues were found on this unit.</p>
                                        <?php } else { ?>
                                            <p>These issues were found:</p>
                                        <?php  } ?>
                                    <?php else :
                                        $selected_no_issues_found = $unit->assignment->no_issues_found || (is_null($unit->assignment->no_issues_found) && $order_type == 'Repair');
                                    ?>
                                        <p>
                                            <input type="radio" <?php if ($selected_no_issues_found) echo 'checked="checked"'; ?> name="no-issues" value="1" class="no-issues-radio required" data-diagnostic_id="<?=$unit->diagnostic->id?>" id="no-issues-found-<?=$unit->diagnostic->id?>" />&nbsp;
                                            <?php if ($order_type == 'Repair') : ?>
                                                <label for="no-issues-found-<?=$unit->diagnostic->id?>">This unit is now fully operational.</label>
                                            <?php else : ?>
                                                <label for="no-issues-found-<?=$unit->diagnostic->id?>">No issues were found on this unit.</label>
                                            <?php endif; ?>
                                        </p>
                                        <p>
                                            <input type="radio" <?php if (!$selected_no_issues_found) echo 'checked="checked"'; ?> name="no-issues" value="0" class="issues-found-radio required" data-diagnostic_id="<?=$unit->diagnostic->id?>" id="issues-found-<?=$unit->diagnostic->id?>" />&nbsp;
                                            <label for="issues-found-<?=$unit->diagnostic->id?>">Checked and found system not working due to:</label>
                                        </p>
                                    <?php endif;?>

                                    <table class="table diagnostic-issues" data-assignment_id="<?=$unit->assignment_id?>" data-diagnostic_id="<?=$unit->diagnostic->id?>">
                                        <thead>
                                            <th>Component type</th><th>Issue</th><th>Can be repaired now?</th><th>Photos</th>
                                                <?php if (!$unit->sq_submitted) : ?>
                                                    <th>Actions</th>
                                                <?php endif; ?>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($unit->diagnostic_issues as $diagnostic_issue) : ?>
                                            <tr data-diagnostic_issue_id="<?=$diagnostic_issue->id?>" data-can_be_fixed_now="<?=$diagnostic_issue->can_be_fixed_now?>">
                                                <td><?=$diagnostic_issue->part_type_name?></td>
                                                <td><?=$diagnostic_issue->issue_type_name?></td>
                                                <td><?=$diagnostic_issue->can_be_fixed_now_text?></td>
                                                <td><?php $this->load->view('upload', array('directory' => 'diagnostic_issue', 'document_id' => $diagnostic_issue->id, 'module' => 'miniant')); ?></td>
                                                <?php if (!$unit->sq_submitted) : ?>
                                                    <td><button class="btn btn-danger delete-issue-button">Delete</button></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (!$unit->sq_submitted) : ?>
            <tr>
                <td class="form-group"><?=form_dropdown('part_type_id_'.$unit->id, $unit->part_types_dropdown, array(), 'data-unit_id="'.$unit->id.'" class="part_type_dropdown form-control"')?></td>
                <td class="form-group"><?=form_dropdown('issue_type_id_'.$unit->id, array(0 => '-- Select a component type first --'), array(), 'data-unit_id="'.$unit->id.'" class="issue_type_dropdown form-control"')?></td>
                <td class="form-group"><?=form_dropdown('can_be_fixed_now_'.$unit->id, array(0 => 'No', 1 => 'Yes'), array(), 'data-unit_id="'.$unit->id.'" class="can_be_fixed_now_dropdown form-control"')?></td>
                <td>&nbsp;</td>
                <td><button class="btn btn-success new-issue-button unit-<?=$unit->id?>"
                        data-unit_id="<?=$unit->id?>"
                        data-diagnostic_id="<?=$unit->diagnostic->id?>"
                        disabled="disabled">
                        Add this issue
                    </button>
                </td>
            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="next_step">
                                <?=$unit->dialog?>
                            </div>
                            <div id="next_step_no_issues">
                                <?=$unit->no_issues_dialog?>
                            </div>
                        </div><!-- .row -->
                    </div><!-- .tab-pane -->
                    <script type="text/javascript">
                    //<[CDATA[
                    var sq_submitted_<?=$unit->id?> = <?=(int) $unit->sq_submitted ?>;

                    $(function() {
                        $('.unit-tab').on('click', function(event) {
                            $.post(base_url+'miniant/stages/stage/set_selected_unit/'+$(this).attr('data-unit_id'));
                        });
                    });
                    //]]>
                    </script>
                <?php endif; ?>
            <?php endforeach; ?>
            </div><!-- .tabbable -->
        </div><!-- .tab-content -->
        <?php if ($show_start_diagnostic_message) : ?>
            <div id="start-diagnostic-modal" class="modal fade">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-body">
                    <p>Start the diagnostic on this unit now. When you have finished, complete the form on this page.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                  </div>
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
            <script type="text/javascript">
                $(function() {
                    $('#start-diagnostic-modal').modal('show');
                });
            </script>
        <?php endif;?>
    </div>
</div>
