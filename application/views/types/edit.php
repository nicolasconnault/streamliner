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
echo form_open(base_url().'types/process_edit/', array('id' => 'type_edit_form', 'class' => 'form-horizontal'));
echo form_hidden('id', $id);
print_form_container_open();
print_input_element(array(
    'label' => 'Name',
    'name' => 'name',
    'size' => 10,
    'required' => true)
);
print_input_element(array(
    'label' => 'Entity',
    'name' => 'entity',
    'size' => 10,
    'info_text' => 'This is the type of type, in a way. If you are editing a type of address, the entity is address. If you are editing a type of building, the entity is building.',
    'required' => true)
);
print_textarea_element(array(
    'label' => 'Description',
    'name' => 'description',
    'cols' => 30,
    'required' => false)
);

print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button" class="btn btn-primary"');
print_submit_container_close();
print_form_container_close();
echo form_close();
