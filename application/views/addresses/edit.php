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
echo form_open(base_url().'addresses/process_edit/', array('id' => 'address_edit_form', 'class' => 'form-horizontal'));
echo form_hidden('address_id', $address_id);
print_form_container_open();
print_dropdown_element(array(
    'name' => 'type_id',
    'label' => 'Address type',
    'options' => $dropdowns['types'])
);
print_input_element(array(
    'label' => 'Unit',
    'name' => 'unit',
    'size' => 5,
    'required' => false)
);
print_input_element(array(
    'label' => 'Number',
    'name' => 'number',
    'size' => 5,
    'required' => true)
);
print_input_element(array(
    'label' => 'Street',
    'name' => 'street',
    'size' => 30,
    'required' => true)
);
print_autocomplete_element(array(
    'label' => 'Street type',
    'name' => 'street_type',
    'options_url' => 'addresses/get_street_types',
    'required' => true,
    'id' => 'autocomplete_street_type',
));
print_input_element(array(
    'label' => 'Suburb',
    'name' => 'city',
    'size' => 26,
    'required' => true)
);
print_input_element(array(
    'label' => 'Postcode',
    'name' => 'postcode',
    'size' => 12,
    'required' => true)
);


print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button" class="btn btn-primary"');
print_submit_container_close();
print_form_container_close();
echo form_close();
