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
class Event_Model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'events';

    public function get_values($event_id) {
        if (empty($event_id)) {
            return array();
        }

        $event = $this->get($event_id);

        return array('event_name' => $event->name, 'event_description' => $event->description, 'role_id' => $event->role_id);
    }
}