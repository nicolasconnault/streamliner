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
class Message_Model extends MY_Model {
    public $table = 'messages';

    public function get_with_author_names($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {
        $messages = parent::get($id_or_fields, $first_only, $order_by, $select_fields);

        foreach ($messages as $key => $message) {
            $messages[$key]->author = $this->user_model->get_name($message->author_id);
            $messages[$key]->date = unix_to_human($message->creation_date);
        }

        return $messages;
    }
}

