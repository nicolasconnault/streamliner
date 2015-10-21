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
class Email_log_model extends MY_Model {
    public $table = 'email_log';

    public function log_message($email_object, $calling_code, $error_msg=null, $sender_table=null, $sender_id=null, $receiver_table=null, $receiver_id=null) {

        $email_log = array(
            'subject' => $email_object->_subject,
            'from_email' => $email_object->_headers['From'],
            'to_email' => $email_object->_headers['To'],
            'message' => $email_object->_body,
            'attachments' => @implode(',', $email_object->_attach_name),
            'host' => $email_object->smtp_host,
            'port' => $email_object->smtp_port,
            'fromname' => $email_object->_headers['From'],
            'addreplyto' => $email_object->_headers['Return-Path'],
            'calling_code' => $calling_code,
            'sender_table' => $sender_table,
            'sender_id' => $sender_id,
            'receiver_table' => $receiver_table,
            'receiver_id' => $receiver_id,
            'errormsg' => $error_msg
        );
        $this->add($email_log);
    }
}
