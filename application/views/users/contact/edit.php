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
echo form_open(base_url().'users/contact/process_edit/', array('id' => 'contact_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('contact_id', $contact_id);
print_form_container_open();

print_input_element(array(
    'label' => 'First name',
    'name' => 'first_name',
    'required' => true
));

print_input_element(array(
    'label' => 'Surname',
    'name' => 'surname',
    'required' => true
));

print_input_element(array(
    'label' => 'Primary phone',
    'name' => 'phone',
    'required' => true
));

print_input_element(array(
    'label' => 'Secondary phone',
    'name' => 'phone2',
    'required' => false
));

print_input_element(array(
    'label' => 'Primary mobile',
    'name' => 'mobile',
    'required' => false
));

print_input_element(array(
    'label' => 'Secondary mobile',
    'name' => 'mobile',
    'required' => false
));

print_input_element(array(
    'label' => 'Primary email',
    'name' => 'email',
    'required' => true
));

print_input_element(array(
    'label' => 'Secondary email',
    'name' => 'email2',
    'required' => false
));

print_dropdown_element(array(
    'label' => 'Type',
    'name' => 'contact_type_id',
    'options' => $types,
    'required' => true
));

print_dropdown_element(array(
    'label' => 'Account',
    'name' => 'account_id',
    'options' => $accounts,
    'required' => true
));

print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'users/contact/cancel/'.$contact_id);
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
