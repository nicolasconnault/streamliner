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
<table id="events_table" class="table table-bordered table-striped table-hover">
    <thead><tr><th>Status</th><th>Value</th><th class="actions">Actions</th></tr></thead>
    <tbody>

    </tbody>
</table>
<div id="hidden_dropdowns">
    <?=form_dropdown('status_id', $statuses, null, 'onchange="update_field(this, \'status_id\')" style="display:none"');?>
    <?=form_dropdown('state', array('Off', 'On'), 'On', 'onchange="update_field(this, \'state\')" style="display:none"');?>
</div>
<script type="text/javascript">
//<![CDATA[
    var event_id = <?=$event_id?>;
//]]>
</script>
