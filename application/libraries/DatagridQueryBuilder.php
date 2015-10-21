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
 * The querybuilder class is set up with an array of Filter objects, pagination, sorting, and search criteria.
 * It should build and execute the SQL query required to obtain the right records from the DB
 * It should then return the raw data from the DB, and some stats about records obtained.
 */
class DatagridQueryBuilder {

    protected $post_params = array();
    protected $filters = array();
    protected $paginate = true;
    protected $per_page = 10;
    protected $model;
    protected $columns=array();
    protected $calling_table;
    protected $start_record = 0;
    protected $total_records;
    protected $num_rows;
    protected $joins = array(); // array of DatagridSQLJoin objects
    protected $sorting_column_index = 0; // TODO support multiple sorts
    protected $sorting_direction = 'ASC';
    protected $db_records=array();
    protected $sql_conditions = array();
    protected $joined_columns = array();
    protected $joining_tables = array();
    protected $group_by = null;
    protected $debug = false;

    public function __construct($args) {

        if (!empty($args['post_params'])) {
            $this->post_params = $args['post_params'];
        }

        if (!empty($args['filters'])) {
            $this->filters = $args['filters'];
        }

        if (isset($args['paginate'])) {
            $this->paginate = $args['paginate'];
        }

        if (isset($args['per_page'])) {
            $this->per_page = $args['per_page'];
        }

        if (isset($args['debug'])) {
            $this->debug = $args['debug'];
        }

        $this->group_by = $args['group_by'];

        if (empty($args['model'])) {
            throw Exception("Argument 'model' is required for instantiating a DatagridQueryBuilder!");
        } else {
            $this->model = $args['model'];
        }

        if (empty($args['columns'])) {
            throw Exception("Argument 'columns' is required for instantiating a DatagridQueryBuilder!");
        } else {
            $this->columns = $args['columns'];
        }

        $this->joins = $args['joins'];
        $this->sql_conditions = $args['sql_conditions'];

        $this->calling_table = $this->columns[0]->table;
        $this->build_sql();
    }

    protected function build_sql() {

        foreach ($this->post_params as $key => $val) {
            if (!empty($val)) {
                $this->{$key} = $val;
            }
        }

        $ci = get_instance();
        $ci->db->from($this->calling_table);
        $this->build_sql_select();
        $this->build_sql_joins();
        $this->build_sql_where();
        $this->build_sql_group_by();
        $this->build_sql_order_and_sort();

        // At the moment, pagination is handled entirely by the Javascript, so we don't need to limit the query at all
        $this->per_page = 9999999;

        $query = $ci->db->get();
        $this->total_records = $this->model->count_all_results();

        $ci->db->from($this->calling_table);
        $this->build_sql_select();
        $this->build_sql_joins();
        $this->build_sql_where();
        $this->build_sql_group_by();
        $this->build_sql_order_and_sort();
        if ($this->paginate) {
            $ci->db->limit($this->per_page, $this->start_record);
        }

        $query = $ci->db->get();

        if ($this->debug) {
            add_message($ci->db->last_query());
        }

        $this->num_rows = $this->total_records;

        foreach ($query->result() as $row) {
            // Remove the first index of each row: it represents the SQL_CALC_FOUND_ROWS query element
            $row = (array) $row;
            array_shift($row);
            $this->db_records[] = $row;
        }
    }


    public function get_db_records() {
        return $this->db_records;
    }

    public function get_num_rows() {
        return $this->num_rows;
    }

    public function get_total_records() {
        return $this->total_records;
    }

    protected function build_sql_select() {
        $ci = get_instance();
        $ci->db->select('');
        // This is a MySQL trick. It won't work with other RMDBs! It must be followed by a SELECT FOUND_ROWS() to be useful
        $ci->db->select('DISTINCT SQL_CALC_FOUND_ROWS '.$this->columns[0]->get_aliased_table().'.'.$this->columns[0]->field, false);
        foreach ($this->columns as $column) {
            $field = $column->field;
            if (empty($field)) {
                continue;
            }
            $ci->db->select( $column->get_sql_select(), false);
        }
    }

    protected function build_sql_joins() {
        $ci = get_instance();

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $ci->db->join($join->table, $join->on, $join->type);
            }
        }
    }

    protected function build_sql_group_by() {
        $ci = get_instance();
        if (!empty($this->group_by)) {
            $ci->db->group_by($this->group_by);
        }
    }

    /**
     * The where clause is built from two sources: Filter objects and DatagridColumn:sql_filter variables
     */
    protected function build_sql_where() {
        $ci = get_instance();

        foreach ($this->filters as $filter) {
            $sql_condition = $filter->get_sql_condition();

            if (!empty($sql_condition)) {
                $ci->db->where($sql_condition);
            }
        }
        foreach ($this->columns as $key => $column) {
            if ($column->sql_filter) {
                $ci->db->where("$column->table.$column->sql_filter");
            }

            $client_column = $this->post_params['client_columns'][$key];
            $search_value = $client_column['search']['value'];

            if ($client_column['searchable'] && !empty($search_value)) {
                // Detect range search
                if (preg_match('/(.*)-yadcf_delim-(.*)/', $search_value, $matches)) { // This delimiter is hard-coded in includes/js/jquery/datatables/jquery.dataTables.yadcf.js
                    $lower_limit = $matches[1];
                    $higher_limit = $matches[2];

                    if (preg_match('/_date/', $column->field)) {
                        $lower_limit = $lower_limit / 1000;
                        $higher_limit = $higher_limit / 1000;
                    }

                    if (!empty($lower_limit)) {
                        $ci->db->where("$column->table.$column->field >", $lower_limit);
                    }
                    if (!empty($higher_limit)) {
                        $ci->db->where("$column->table.$column->field <", $higher_limit);
                    }
                } else {
                    $ci->db->where("$column->table.$column->field", $search_value);
                }
            }
        }

        foreach ($this->sql_conditions as $condition) {
            $ci->db->where($condition, null, false);
        }
    }

    protected function build_sql_order_and_sort() {
        $ci = get_instance();
        if (isset($this->sorting_column_index) && !is_null($this->sorting_column_index)) {
            $sorting_column = $this->get_visible_column_by_index();

            if ($sorting_column->field_alias == 'roles') { // TODO remove this coupling!! Create exception parameter if required
                $orderby = '';
            } else {
                $orderby = $sorting_column->get_aliased_field() . ' ' . $this->sorting_direction;
            }

            if (!empty($orderby)) {
                $ci->db->order_by($orderby);
            }
        }
    }

    /**
     * Invisible columns cannot be sorted, but they are still part of the Columns array.
     * This function returns the proper visible column based on a given index
     * TODO this isn't the right place for this code, the Query builder shouldn't need to know about the visibility of columns
     */
    protected function get_visible_column_by_index() {
        $ci = get_instance();
        $visible_columns = array();
        foreach ($this->columns as $column) {
            if ($column->visible_in_outputtypes !== false) {
                $visible_columns[] = $column;
            }
        }
        return $visible_columns[$this->sorting_column_index];
    }
}

class DatagridSQLJoin {
    public $table;
    public $on;
    public $type = 'LEFT';

    public function __construct($args) {
        foreach ($args as $key => $val) {
            $this->{$key} = $val;
        }
    }
}

