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
<?php // echo $unit->junior_technician_dialog ?>
<?php if ($this->unit_model->has_statuses($unit->id, array('UNIT DETAILS RECORDED'))) : ?>
    <p>
        <form method="post" action="<?=base_url()?>miniant/stages/<?=$this->workflow_manager->current_stage->stage_name?>/process">
            <input type="hidden" name="assignment_id" value="<?=$unit->assignment_id?>" />
            <input type="submit" id="continue-button-<?=$unit->id?>" class="btn btn-primary" value="Continue" />
        </form>
    </p>
<?php else : ?>
    <p>Details about this unit have not yet been recorded by the senior technician. Please refresh the page when this step is completed.</p>
<?php endif; ?>
