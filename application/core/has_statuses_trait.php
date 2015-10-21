<?php
/*
 * Copyright 2015 SMB Streamline
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
trait has_statuses {

    /**
     * Document type is obtained from the name of the class from which the trait functions are called
     */
    public function get_document_type() {
        return strtolower(substr(__CLASS__, 0, -6));
    }

    public function clear_status_cache() {
        static $statuses;
        $statuses = array();
    }

    /**
     * @param array $statuses an array of status names (e.g., 'TO BE ALLOCATED')
     */
    public function limit_by_statuses($statuses) {
        $this->db->join('document_statuses', 'document_statuses.document_id = '.$this->table.'.id AND document_type = "'.$this->get_document_type().'"');
        $this->db->join('statuses', 'statuses.id = document_statuses.status_id');
        $this->db->where_in('statuses.name', $statuses);
        $this->db->select($this->table.'.*');
    }

    public function has_statuses($document_id, $statuses_to_check, $operator='OR', $statuses = array(), $cached=true) {

        if (empty($statuses)) {
            if ($cached) {
                $statuses = $this->get_statuses($document_id);
            } else {
                $statuses = $this->get_uncached_statuses($document_id);
            }
        }

        $statuses_found = array();

        if (!is_array($statuses_to_check)) {
            die('2nd param in has_statuses() method must be an array!');
        }

        foreach ($statuses_to_check as $status_to_check) {
            if (in_array($status_to_check, $statuses)) {
                if ($operator == 'OR') {
                    return true;
                } else {
                    $statuses_found[] = $status_to_check;
                }
            }
        }

        return count($statuses_found) == count($statuses_to_check);

    }

    public function has_not_statuses($document_id, $statuses_to_check, $statuses = array()) {
        if (empty($statuses)) {
            $statuses = $this->get_statuses($document_id);
        }

        foreach ($statuses_to_check as $status_to_check) {
            if (in_array($status_to_check, $statuses)) {
                return false;
            }
        }

        return true;

    }

    public function check_statuses($document_id, $whitelist=array(), $whitelist_operator='OR', $blacklist=array()) {
        $statuses = $this->get_statuses($document_id);
        $result = true;

        if (!empty($whitelist)) {
            $result = $this->has_statuses($document_id, $whitelist, $whitelist_operator, $statuses);
        }

        if (!empty($blacklist)) {
            $result = $result && $this->has_not_statuses($document_id, $blacklist, $statuses);
        }
        return $result;
    }

    public function get_statuses($document_id, $name_only=true) {
        static $statuses = array();

        if (empty($statuses[$this->get_document_type()][$document_id][$name_only])) {

            $this->db->join('document_statuses', 'document_statuses.status_id = statuses.id');
            $this->db->where('document_statuses.document_id', $document_id);
            $this->db->where('document_statuses.document_type', $this->get_document_type());

            if ($name_only) {
                $result = $this->status_model->get_dropdown('name', false);
            } else {
                $result = $this->status_model->get();
            }

            $statuses[$this->get_document_type()][$document_id][$name_only] = $result;
        }

        return $statuses[$this->get_document_type()][$document_id][$name_only];
    }

    public function get_uncached_statuses($document_id, $name_only=true) {
        $statuses = array();
        $this->db->join('document_statuses', 'document_statuses.status_id = statuses.id');
        $this->db->where('document_statuses.document_id', $document_id);
        $this->db->where('document_statuses.document_type', $this->get_document_type());

        if ($name_only) {
            $result = $this->status_model->get_dropdown('name', false);
        } else {
            $result = $this->status_model->get();
        }

        $statuses[$this->get_document_type()][$document_id][$name_only] = $result;
        return $statuses[$this->get_document_type()][$document_id][$name_only];
    }

    public function set_statuses($document_id, $status_ids) {

        $this->document_statuses_model->delete(array('document_id' => $document_id, 'document_type' => $this->get_document_type()));

        foreach ($status_ids as $status_id) {
            $params = array('document_id' => $document_id, 'status_id' => $status_id, 'document_type' => $this->get_document_type());
            if (!$this->document_statuses_model->get($params)) {
                $this->document_statuses_model->add($params);
            }
        }
        $this->log_status_change($document_id);
    }

    public function set_status($document_id, $status_name, $state=true) {
        $status_id = $this->status_model->get(array('name' => $status_name), true)->id;

        if (empty($status_id)) {
            throw new Exception("No status '$status_name' exists, please check the code!");
        }

        $params = array('document_id' => $document_id, 'status_id' => $status_id, 'document_type' => $this->get_document_type());

        $this->document_statuses_model->delete($params);
        if ($state && !$this->document_statuses_model->get($params)) { // For some strange reason sometimes the above DELETE code doesn't remove a duplicate record before the following INSERT is run... So we check here for duplicates too
            $this->document_statuses_model->add($params);
        }
        $this->log_status_change($document_id);
    }

    public function get_status_id($status_string) {
        $status = $this->status_model->get(array('name' => $status_string), true, null, array('id'));
        if (empty($status)) {
            return null;
        } else {
            return $status->id;
        }
    }

    public function get_status_string($document_id) {
        $statuses = $this->document_statuses_model->get(array('document_id' => $document_id, 'document_type' => $this->get_document_type()));
        $status_string = '';
        foreach ($statuses as $status) {
            $status_string .= "$status->status_id,";
        }
        $status_string = substr($status_string, 0, -1);
        return $status_string;
    }

    public function log_status_change($document_id) {
        $changed_by_id = $this->session->userdata('user_id');
        $old_status_string = $this->get_status_string_from_logs($document_id);
        $new_status_string = $this->get_status_string($document_id);
        $document_type = $this->get_document_type();

        if ($old_status_string != $new_status_string) {
            return $this->status_log_model->add(compact('document_id', 'changed_by_id', 'old_status_string', 'new_status_string', 'document_type'));
        } else {
            return false;
        }
    }

    /**
     * Returns the last status string from the logs for the given document_type and document_id
     */
    public function get_status_string_from_logs($document_id) {
        $this->db->order_by('creation_date', 'DESC');
        $log = $this->status_log_model->get(array('document_id' => $document_id, 'document_type' => $this->get_document_type()), true);

        $status_string = '';
        if (!empty($log)) {
            $status_string = $log->new_status_string;
        }

        return $status_string;

    }

    public function get_status_change_log($document_id, $status, $added_or_removed='added', $last_or_first='last', $changed_by_id=null) {
        $status = $this->status_model->get(array('name' => $status), true);

        if (!empty($changed_by_id)) {
            $this->db->where('changed_by_id', $changed_by_id);
        }

        if ($last_or_first == 'last') {
            $this->db->order_by('creation_date DESC');
        } else {
            $this->db->order_by('creation_date ASC');
        }

        $logs = $this->status_log_model->get(array('document_id' => $document_id, 'document_type' => $this->get_document_type()));

        foreach ($logs as $log) {
            $old_status_ids = explode(',', $log->old_status_string);
            $new_status_ids = explode(',', $log->new_status_string);

            if ($added_or_removed == 'added' && in_array($status->id, $new_status_ids) && !in_array($status->id, $old_status_ids)) {
                return $log;
            } else if ($added_or_removed == 'removed' && !in_array($status->id, $new_status_ids) && in_array($status->id, $old_status_ids)) {
                return $log;
            }
        }

        return false;
    }

    public function get_status_history($document_id) {
        $this->db->order_by('creation_date ASC');
        $status_logs = $this->status_log_model->get(array('document_type' => $this->get_document_type(), 'document_id' => $document_id));

        if (empty($status_logs)) {
            return array();
        }

        foreach ($status_logs as $key => $status_log) {
            $status_logs[$key]->changed_by_string = $this->user_model->get_name($status_log->changed_by_id);
            $status_logs[$key]->changed_date = unix_to_human($status_log->creation_date, '%d/%m/%Y at %h:%i%a');
            $new_statuses = explode(',',$status_log->new_status_string);
            $old_statuses = explode(',',$status_log->old_status_string);

            $status_logs[$key]->added_statuses = array();
            $status_logs[$key]->removed_statuses = array();

            foreach ($new_statuses as $new_status) {
                if (!in_array($new_status, $old_statuses) && !empty($new_status)) {
                    $status_logs[$key]->added_statuses[] = $this->status_model->get_name($new_status);
                }
            }
            foreach ($old_statuses as $old_status) {
                if (!in_array($old_status, $new_statuses) && !empty($old_status)) {
                    $status_logs[$key]->removed_statuses[] = $this->status_model->get_name($old_status);
                }
            }

        }

        return $status_logs;
    }
}
