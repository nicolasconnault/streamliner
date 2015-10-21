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

/**
 * Role: Put together the structure that will underlie all rendered datagrids.
 * It includes headings and rows of data. By design, it returns a plain stdClass object,
 * because its purpose is only to organise and hold data.
 */
class DatagridStructure {
    protected $custom_columns_callback;
    protected $db_records;
    protected $columns;
    public static $instance;

    public static function get_structure($args) {
        $structure = new stdClass;
        $datagrid_structure = DatagridStructure::get_instance($args);
        $structure->headings = $datagrid_structure->build_headings();
        $structure->rows = $datagrid_structure->build_rows();
        return $structure;
    }

    protected function __construct($args) {
        $this->db_records = $args['db_records'];
        $this->columns = $args['columns'];
        $this->custom_columns_callback = $args['custom_columns_callback'];
    }

    static protected function get_instance($args) {
        if (empty(DatagridStructure::$instance)) {
            DatagridStructure::$instance = new DatagridStructure($args);
        }
        return DatagridStructure::$instance;
    }

    protected function build_headings() {
        $headings = array();
        foreach ($this->columns as $column) {
            if ($column->visible_in_outputtypes !== false) {
                $headings[$column->get_aliased_field()] = $column->label;
            }
        }
        return $headings;
    }

    protected function build_rows() {
        $ci = get_instance();
        $ci->load->helper('date');
        $rows = array();

        $custom_column_callback = $this->custom_columns_callback;
        if (!empty($custom_column_callback)) {
            $custom_column_callback($this->db_records);
        }

        foreach ($this->db_records as $record_index => $record) {
            // Current implementation uses numerically indexed array for rows, and uses headings to get field names for filters and sorting
            // Use column definitions to remove hidden values
            $rows[$record_index] = array();
            $row_values = DatagridStructure::format_row_values(array_values($record));

            foreach ($this->columns as $column_index => $column) {
                if ($column->visible_in_outputtypes !== false) {
                    $rows[$record_index][] = ($column_index == 0) ? reset($row_values) : next($row_values);
                }
            }
        }
        return $rows;
    }

    protected function format_row_values($row_values) {
        $ci = get_instance();
        if (empty($this->columns) || empty($row_values)) {
            return false;
        }

        $row = array();
        $ci->load->helper('date');
        $ci->load->helper('constantfinder');

        foreach ($this->columns as $column_index => $column) {
            if (!$column->visible_in_outputtypes) {
                continue;
            }

            if (preg_match('/_datetime/', $column->get_aliased_field())) {
                $row[] = unix_to_human($row_values[$column_index], '%d/%m/%Y %h:%i%a');
            } else if (preg_match('/_date$/', $column->get_aliased_field())) {
                $row[] = unix_to_human($row_values[$column_index]);
            } else if ($column->constant_prefix) {
                $row[] = get_lang_for_constant_value($column->constant_prefix, $row_values[$column_index]);
            } else {
                if (count($this->columns) != count($row_values)) {
                    throw new Exception('Error: mismatch of number of columns defined ('.count($this->columns).') and actual columns! ('.count($row_values).')');
                }

                if (is_null($row_values[$column_index])) {
                    $row_values[$column_index] = '';
                }
                $row[] = $row_values[$column_index];
            }
        }

        return $row;
    }
}
