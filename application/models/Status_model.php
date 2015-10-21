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
class Status_model extends MY_Model {
    /**
     * @var string The DB table used by this model
     */
    public $table = 'statuses';
    public static $document_type;

    public function get_statuses_for_event($event_id) {
        $status_events = $this->status_event_model->get(compact('event_id'));
        $statuses = array();

        foreach ($status_events as $status_event) {
            $status = $this->get($status_event->status_id);
            $status_object = new stdClass();
            $status_object->id = $status_event->id;
            $status_object->status_id = $status_event->status_id;
            $status_object->status_name = $status->name;
            $status_object->state = $status_event->state;
            $statuses[] = $status_object;
        }

        return $statuses;
    }

    public function get_for_document_type($document_type) {
        $this->db->join('document_types_statuses dts', 'dts.status_id = statuses.id');
        $this->db->where('dts.document_type', $document_type);
        $this->db->select();
        $this->db->select('statuses.*');
        $this->db->order_by('dts.sortorder');
        return $this->get();
    }

    public function get_custom_columns_callback($document_type) {
        Status_model::$document_type = $document_type;

        return function(&$db_records) {

            $users = array();

            foreach ($db_records as $key => $row) {
                $db_records[$key]['status_description'] = str_replace('[[document]]', Status_model::$document_type, $row['status_description']);
            }
        };
    }

    public function get_id_from_name($name) {
        if ($status = $this->get(array('name' => $name), true)) {
            return $status->id;
        } else {
            return null;
        }
    }
}
