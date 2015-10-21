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
<div id="detailsmessage"></div>
<?=form_open($form_action, array('id' => 'userform', 'class' => 'form-horizontal', 'role' => 'form'))?>
<div class="panel panel-info">
    <?php print_hidden_element(array('name' => 'user_id'))?>
    <?php print_hidden_element(array('name' => 'action'))?>
    <div class="panel-heading"><?=get_title($details_title_options)?></div>
    <div id="details" class="panel-body">
        <div id="detailsmessage"></div>
<?php
        if (!empty($user_id)) print_static_form_element('ID', $user_id);
        print_input_element(array('name' => 'first_name', 'label' => 'First Name', 'required' => true));
        print_input_element(array('name' => 'last_name', 'label' => 'Last Name', 'required' => true));

        $back_link = base_url()."users/user";
        if ($type == 'staff') {
            print_input_element(array('name' => 'username', 'label' =>'Username'));

            // Only show password if admin has donanything for users
            if (empty($user_id) || (has_capability('site:doanything') || ($this->session->userdata('user_id') == $user_id && has_capability('users:editownaccount')))) {
                print_password_element(array('label' => 'Password', 'name' => 'password', 'required' => false));
            }

            print_textarea_element(array('name' => 'signature', 'cols' => 40, 'rows' => 3, 'label' => 'Signature'));
        } else if ($type == 'contact') {
            $back_link = base_url()."users/contact";
        }

        print_submit_container_open();
        echo form_submit('submit', 'Submit', 'id="submit_button" class="btn btn-default"');
        if (has_capability('users:viewallusers')) {
            echo form_button(array('name' => 'button', 'content' => 'Back to '.ucfirst($type).' list', 'class'=>'btn btn-default', 'onclick' => "window.location='".$back_link."';"));
        }
        print_submit_container_close();
?>
    </div>
<?=form_close()?>
</div>

<div class="panel panel-info">
    <div class="panel-heading"><?=get_title($contacts_title_options)?></div>
    <div id="contactsmessage"></div>
    <div class="panel-body"></div>
    <table id="user-contact-details" class="table table-bordered">
        <tr><th>Emails</th><td id="email"></td></tr>
        <tr><th>Work Phones</th><td id="phone"></td></tr>
        <tr><th>Mobiles</th><td id="mobile"></td></tr>
        <tr><th>Faxes</th><td id="fax"></td></tr>
    </table>
</div>

<script type="text/javascript"> /*<![CDATA[ */
var user_id = <?php echo (!empty($user_id)) ? $user_id: '0'; ?>;
var type = '<?=$type?>';
var uri_section = (type == 'staff') ? 'user' : 'contact';
//]]></script>
