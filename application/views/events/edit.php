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
echo form_open(base_url().'events/process_edit/'.$system, array('id' => $system.'_event_edit_form', 'class' => 'form-horizontal'));
echo form_hidden('event_id', $event_id);
print_form_container_open();

print_input_element(array(
    'label' => 'Event name',
    'name' => 'event_name',
    'required' => true
));

print_textarea_element(array(
    'label' => 'Event description',
    'name' => 'event_description',
    'required' => true
));

print_dropdown_element(array(
    'label' => 'Role',
    'name' => 'role_id',
    'options' => $dropdowns['roles'],
    'required' => false
));

print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'events/event');
print_submit_container_close();
print_form_container_close();
echo form_close();
?>
<script type="text/javascript">
var system = '<?=$system?>';
</script>
