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
$message_identifier = $document_type . '_' . $document_id;

echo '<div data-identifier="'.$message_identifier.'" id="messages_'.$message_identifier.'" '.$display.' class="messages-container '.get_tab_panel_class().'">';
$this->load->helper('inflector');
if (empty($in_tabbed_form)) $in_tabbed_form = false;
$capability_category = $this->inflector->pluralize($document_type);
$view_only_class = (empty($view_only)) ? 'view-only' : '';

if (empty($messages)) {
    $messages = array();
}

    ?>
        <div class="table-responsive">
        <table class="table table-bordered table-condensed message_table <?=$view_only_class?>" >
            <thead>
                <tr><th>Date</th><th>Author</th><th>Note</th><th class="actions">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (empty($view_only)) : ?>
                <tr>
                    <td colspan="4">
                        <button class="btn btn-success new_message_button" type="button" data-identifier="<?=$message_identifier?>" id="new_message_button_<?=$message_identifier?>">Write a new note</button>
                    </td>
                </tr>
            <?php endif; ?>
                <tr class="new_message_row" data-identifier="<?=$message_identifier?>" id="new_message_row_<?=$message_identifier?>" style="display: none">
                    <td colspan="3">
                        <?=form_textarea(array('name' => 'message', 'placeholder' => 'Type your note here...'))?>
                        <?=form_hidden('message_id', null)?>
                        <?=form_hidden('document_type', $document_type)?>
                        <?=form_hidden('document_id', $document_id)?>
                    </td>
                    <td class="actions">
                        <button class="btn btn-success btn-sm save_new_message" type="button" data-identifier="<?=$message_identifier?>" id="save_new_message_<?=$message_identifier?>">Save note</button>
                        <button class="btn btn-warning btn-sm cancel_new_message" type="button" data-identifier="<?=$message_identifier?>" id="cancel_new_message_<?=$message_identifier?>">Cancel</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

    <?php if ($in_tabbed_form) print_tabbed_form_navbuttons(); ?>
    </div>
