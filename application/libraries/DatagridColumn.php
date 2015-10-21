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
class DatagridColumn {
    private $table;
    private $table_alias = null;
    private $field;
    private $field_alias = null;
    private $label;
    private $width = '50';
    private $sortable = true;
    private $visible_in_outputtypes = array('html', 'pdf', 'csv', 'xml');
    private $sql_select = null; // If used, overrides table, table_alias and field. For example: CONCAT(field1, ' ', field2)
    private $sql_filter = null;
    private $constant_prefix = null; // To search the value by its label instead of its DB value
    private $sort_direction = null; // If set to DESC or ASC, will use in SQL order_by
    private $in_combo_filter = false; // If true, will be included in the combo filter, if such is enabled
    private $checkbox_filters = array(); // If true, will be included in the combo filter, if such is enabled
    private $dropdown_filter = null; // If true, will be included in the combo filter, if such is enabled
    private $narrows_query = true; // If false, joined records will still be included if their FK doesn't match this field

    public function __construct($args) {
        foreach ($args as $key => $val) {
            $this->{$key} = $val;
        }
    }

    public function __set($name, $value) {
        $this->{$name} = $value;
    }

    public function __get($name) {
        if (!empty($this->{$name})) {
            return $this->{$name};
        }
    }

    public function get_width() {
        return (empty($this->width)) ? 20 : $this->width;
    }

    public function get_aliased_table() {
        return empty($this->table_alias) ? $this->table : $this->table_alias;
    }

    public function get_aliased_field() {
        return empty($this->field_alias) ? $this->field : $this->field_alias;
    }

    public function get_combo_filter_name() {
        if (!empty($this->sql_select)) {
            return $this->sql_select;
        } else {
            return $this->get_aliased_table().".$this->field" . ($this->constant_prefix ? "|$this->constant_prefix" : '');
        }
    }

    public function get_sql_select() {
        if (empty($this->sql_select)) {
            return $this->get_aliased_table() . '.' . ($this->field_alias ? "$this->field AS $this->field_alias" : $this->field);
        } else {
            return "$this->sql_select AS $this->field_alias";
        }
    }
}

