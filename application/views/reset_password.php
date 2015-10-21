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
echo form_open(base_url().'login/reset_password', array('id' => 'reset_password_form', 'class' => 'form-horizontal'));
print_form_container_open();
print_static_form_element('', 'Enter your username <b><i><u>OR</u></i></b> your email address to receive a new password.');
print_input_element(array(
    'label' => 'Username',
    'name' => 'username',
    'size' => 25,
    'required' => false
));
print_input_element(array(
    'label' => 'Email address',
    'name' => 'email',
    'size' => 25,
    'required' => false
));

print_submit_container_open();
echo form_submit('button', 'Submit', 'id="submit_button" class="btn btn-default"');
print_submit_container_close();
print_form_container_close();
echo form_close();
