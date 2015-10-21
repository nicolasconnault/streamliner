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
<?php if (ENVIRONMENT == 'demo') {
    ?>
    <div class="panel panel-primary">
        <div class="panel-heading"><h3>Request access to the demo</h3></div>
        <div class="panel-body">
    <?php
    echo form_open(base_url().'login/get_credentials', array('id' => 'get_credentials_form', 'class' => 'form-horizontal'));
    print_input_element(array(
        'label' => 'Your email address',
        'name' => 'email',
        'size' => 25,
        'required' => true
    ));
    print_submit_container_open();
    echo form_submit('button', 'Get login details', 'id="submit_button" class="btn btn-primary"');
    print_submit_container_close();
    print_form_container_close();
    echo form_close();
    echo "</div></div>";

    echo '<p style="color: red">Note: your login will only work for one session. Enter your email address in the form above to re-apply for access if your session has expired.</p>';
} else {

    echo form_open(base_url().'login', array('id' => 'login_form', 'class' => 'form-horizontal'));
    print_form_container_open();
    print_input_element(array(
        'label' => 'Username',
        'name' => 'username',
        'size' => 25,
        'required' => true
    ));
    print_password_element(array(
        'label' => 'Password',
        'name' => 'password',
        'size' => 25,
        'required' => true
    ));

    print_static_form_element('', '<a href="login/reset_password">Forgot your username or password?</a>');
    print_submit_container_open();
    echo form_submit('button', 'Login', 'id="submit_button" class="btn btn-primary"');
    print_submit_container_close();
    print_form_container_close();
    echo form_close();
}
?>
