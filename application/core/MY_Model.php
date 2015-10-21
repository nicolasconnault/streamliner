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
/**
 * File containing the MY_Model class
 * @package models
 */

/**
 * MY_Model class
 * This class takes care of common SQL operations like get, delete, add and edit, and takes care of creation and revision timestamps
 * Assumptions:
 *   - All tables have the following fields:
 *      * id smallint(5) primary key autoincrement
 *      * creation_date int(10) NOT NULL
 *      * revision_date int(10) NOT NULL
 *      * status varchar(32) NOT NULL DEFAULT 'Active'
 * @package models
 */
class MY_Model extends CI_Model {
    /**
     * @var string $table This MUST be set in the child classes
     */
    public $table=null;
    public $cache_keys=array();
    public $dbfields = array();
    static $cached_objects = array();

    function count_all_results() {

        $result = $this->db->conn_id->affected_rows;
        return $result;
    }

    function __call($name, $args) {
        if (preg_match('/get_([a-zA-Z0-9\-\_]*)/', $name, $matches)) {
            $field_name = $matches[1];
            $object = $this->get($args[0], true, @$args[2], array($field_name));
            if (!empty($object)) {
                return $object->$field_name;
            }
        }
    }

    function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {

        $this->db->from($this->table);
        if (!empty($select_fields)) {
            $this->db->select($select_fields);
        }

        if (!empty($id_or_fields)) {
            if (is_array($id_or_fields)) {
                $this->db->where($id_or_fields);
            } else {
                $this->db->where($this->table.'.id', $id_or_fields);
            }
        }

        if (!is_null($order_by)) {
            $this->db->order_by($order_by);
        }
        $query = $this->db->get();

        $return_value = null;

        $num_rows = $this->db->conn_id->affected_rows;

        if ($num_rows == 0) {
            if (is_array($id_or_fields) && !$first_only) {
                return array();
            } else {
                return null;
            }
        } else if (!is_array($id_or_fields) && !is_null($id_or_fields)) {
            $row = $query->result();
            $return_value = $row[0];
        } else if ($num_rows == 1 && !$first_only) {
            $row = $query->result();
            if (empty($row)) {
                return array();
            }
            $return_value = array($row[0]);
        } else if ($num_rows == 1 && $first_only) {
            $row = $query->result();
            $return_value = $row[0];
        } else if ($num_rows > 0) {
            $results = array();
            foreach ($query->result() as $row) {
                $results[] = $row;
            }
            $return_value = $results;
        }

        if (!is_null($return_value) && $first_only && is_array($id_or_fields) && !is_null($id_or_fields) && is_array($return_value)) {
            return reset($return_value);
        } else {
            return $return_value;
        }
    }

    function delete($id_or_fields) {
        if (!empty($id_or_fields)) {
            if (is_array($id_or_fields)) {
                $this->db->where($id_or_fields);
            } else {
                $this->db->where($this->table.'.id', $id_or_fields);
                $this->erase_from_cache($id_or_fields);
            }
        }
        $this->delete_cache_keys();
        return $this->db->delete($this->table);
    }

    /**
     * @param mixed $fields Array of stdclass
     * @return insert_id
     */
    function add($fields) {
        $fields = (array) $fields;
        $this->delete_cache_keys();


        if (isset($fields['creation_date'])) {
            $fields['revision_date'] = $fields['creation_date'];
        } else {
            $fields['creation_date'] = time();
            $fields['revision_date'] = time();
        }

        $this->db->insert($this->table, $fields);
        return $this->db->insert_id();
    }

    function delete_cache_keys() {

        $this->load->driver('cache');
        foreach ($this->cache_keys as $key) {
            if ($key == '*') {
                $this->cache->apc->clean();
            } else {
                $this->cache->apc->delete($key);
            }
        }
    }

    function add_cache_key($key) {
        if (!in_array($key, $this->cache_keys)) {
            $this->cache_keys[] = $key;
        }
    }

    function edit($id, $fields) {

        $this->delete_cache_keys();
        $fields['revision_date'] = time();
        $this->db->where($this->table.'.id', $id);
        $this->erase_from_cache($id);
        return $this->db->update($this->table, $fields);
    }

    function limit($limit, $offset=null) {

        $this->db->limit($limit, $offset);
    }

    function order_by($field, $direction='ASC') {

        $this->db->order_by($field, $direction);
    }


    /**
     * When called by children models, returns the next autoincrement value. $this->table MUST be set first.
     * @return int
     */
    public function get_next_id() {

        $query = $this->db->select_max('id')->from($this->table)->get();
        $result_array = $query->result();
        return $result_array[0]->id + 1;
    }

    /**
     * Retrieves data from $_POST and attempts to insert a record in this model's DB table. Optional prefix is used to avoid field name collisions
     * @param string $prefix
     * @param array $data Additional fields not set in $_POST
     * @return int If successful, returns the PK value of the new record
     */
    public function add_from_post($prefix=null, $data=array()) {

        $object = array();
        foreach ($_POST as $key => $val) {
            if (preg_match('/'.$prefix.'(.*)/i', $key, $matches) && $this->db->field_exists($matches[1], $this->table)) {
                $object[$matches[1]] = $this->input->post($key);
            }
        }

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $object[$key] = $val;
            }
        }

        return $this->add($object);
    }

    /**
     * Retrieves data from $_POST and attempts to update a record in this model's DB table. Optional prefix is used to avoid field name collisions
     * @param int $id Primary Key value
     * @param array $data Additional fields not set in $_POST
     * @param string $prefix
     * @return bool
     */
    public function edit_from_post($id, $prefix=null, $data=array()) {

        $object = array();
        foreach ($_POST as $key => $val) {
            if (preg_match('/'.$prefix.'(.*)/i', $key, $matches) && $matches[1] != 'id' && $this->db->field_exists($matches[1], $this->table)) {
                $object[$matches[1]] = $this->input->post($key);
            }
        }

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $object[$key] = $val;
            }
        }

        return $this->edit($id, $object);
    }

    /**
     * Returns an array for the current model indexed by ID
     * @param string $name_field The field that will be used as the option labels
     * @param string $null_option If false or null, no null option. Otherwise uses the given string for null index
     * @param closure $label_function An optional function that can perform additional formatting on the label
     * @param string $optgroups If false, optgroups not used. If a string is given, the matching field from the DB table will be used to group items by category, ready to be used in HTML optgroups
     * @param string $optgroup_constant_prefix If the grouping variable is an int represented by a constant, this prefix will obtain the matching lang string
     * @return array
     */
    public function get_dropdown($name_field, $null_option=true, $label_function=false, $optgroups=false, $optgroup_constant_prefix=null, $null_value = null, $order_by=null, $where=null) {
        $options = array();

        if (!empty($where)) {
            $this->db->where($where);
        }

        if (!empty($null_option)) {
            if ($null_option === true) {
                $null_option = '-- Select One --';
            }
            $options[$null_value] = $null_option;
        }

        if (!empty($order_by)) {
            $this->db->order_by($order_by);
        }

        $this->db->select($this->table.'.*', false);

        $objects = $this->get();

        if (is_array($objects)) {
            foreach ($objects as $object) {
                $label = ($label_function) ? $label_function($object) : $object->$name_field;
                if ($optgroups) {
                    $optgroup_label = (empty($optgroup_constant_prefix)) ? $object->$optgroups : ucfirst(get_lang_for_constant_value($optgroup_constant_prefix, $object->$optgroups));
                    if (empty($options[$optgroup_label])) {
                        $options[$optgroup_label] = array();
                    }
                    $options[$optgroup_label][$object->id] = $label;
                } else {
                    $options[$object->id] = $label;
                }
            }
        }

        return $options;
    }

    /**
     * Replaces the names of the selected columns using the given format
     * @param string $format
     * @param array $fields_to_format If empty, will return all the fields
     * @return array An array of the fields
     */
    public function get_formatted_column_names($format, $fields_to_format=array()) {

        $table_fields = $this->db->list_fields($this->table);
        $formatted_fields = array();

        if (empty($fields_to_format)) {
            $fields_to_format = $table_fields;
        }

        foreach ($table_fields as $field) {
            if (in_array($field, $fields_to_format)) {
                $formatted_fields[$this->table.'.'.$field] = sprintf($format, $field);
            }
        }

        return $formatted_fields;
    }

    public function select_foreign_table_fields($foreign_table_name, $table_alias=null) {
        if (empty($table_alias)) {
            $table_alias = $foreign_table_name;
            $singular_name = $this->inflector->singularize($table_alias);
        } else {
            $singular_name = $table_alias;
        }

        $fields = $this->db->list_fields($foreign_table_name);

        foreach ($fields as $field) {
            $this->db->select("$table_alias.$field AS ".$singular_name."_$field", false);
        }
    }

    /**
     * Used to retrieve a specific table record by primary key. If retrieved earlier in the same
     * execution, will fetch it from cache.
     *
     * Note: If the record is going to change in the same execution between two GET queries,
     * this cache must be erased before the second query!
     */
    public function get_from_cache($table_id=null) {

        if (empty(MY_Model::$cached_objects[$this->table])) {
            MY_Model::$cached_objects[$this->table] = array();
        }

        if (empty(MY_Model::$cached_objects[$this->table][$table_id])) {
            MY_Model::$cached_objects[$this->table][$table_id] = $this->get($table_id);
        }

        return MY_Model::$cached_objects[$this->table][$table_id];
    }

    public function erase_from_cache($table_id) {
        if (!empty(MY_Model::$cached_objects[$this->table][$table_id])) {
            unset(MY_Model::$cached_objects[$this->table][$table_id]);
        }
    }

    public function get_id_from_name($name) {
        if ($object = $this->get(compact('name'), true)) {
            return $object->id;
        } else {
            return null;
        }
    }
}
?>
