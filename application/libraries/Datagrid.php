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
require_once(APPPATH.'/libraries/DatagridFilter.php');
require_once(APPPATH.'/libraries/DatagridColumn.php');
require_once(APPPATH.'/libraries/DatagridJSDriver.php');
require_once(APPPATH.'/libraries/DatagridStructure.php');
require_once(APPPATH.'/libraries/DatagridRenderer.php');
require_once(APPPATH.'/libraries/DatagridActionIcon.php');
require_once(APPPATH.'/libraries/DatagridQueryBuilder.php');
require_once(APPPATH.'/helpers/constantfinder_helper.php');

/**
 * The interface class, named Datagrid to reduce confusion from the controller's point of view
 * Controllers can only interact with this class.
 * Its job is to interpret the configuration and use the other classes to present the required output.
 * It contains no complex logic, handles no SQL, performs no transformation on data.
 * @TODO Add ability to inject a custom column, with custom SQL based on the rest of the row data, just before the datagrid is rendered
 *
 */
class Datagrid {
    protected $columns = array(); // Array of column objects
    protected $filters = array();
    protected $jslibrary = 'datatables';
    protected $available_export_types = array();
    protected $per_page = 20;
    protected $combo_filter = false;
    protected $custom_columns_callback; // A PHP closure to add extra data to the datagrid_structure prior to rendering
    protected $joins = array(); // For complex joins, use this array instead of letting Datagrid try to figure out the joins based on column definitions
    protected $outputtype='html'; // string (html,pdf,xml,csv...)
    protected $datagrid_callbacks = false; // If true, will load views/$uri_segment_1/$uri_segment_2/datagrid_setup.php. An explicit view can be given instead
    protected $uri_segment_1=''; // the first URI segment of your page,refers to a major section of the website, and is usually in the plural form (e.g., enquiries)
    protected $uri_segment_2=''; // the second URI segment of your page, always singular, and is also the name of the associated Model (e.g., user for the user_model)
    protected $paginate = true;
    protected $model; // A CI Model object
    protected $row_actions = array('edit', 'delete');
    protected $row_action_capabilities = array(); // Use the $row_actions values as keys, and capabilities as values, to restrict visibility of action icons
    protected $row_action_conditions = array(); // Use the $row_actions values as keys, and condition-checking anonymous functions as values. The function will receive a $row as parameter, and must return a boolean. False means the action icon will NOT be shown
    protected $formatting_rules = array();
    protected $sql_conditions = array();
    protected $jsdriver;
    protected $query_builder;
    protected $structure;
    protected $feature_type='Streamliner Core';
    protected $renderer;
    protected $title_icon=null; // The name of a font-awesome icon to be used as the prefix of the page heading
    protected $group_by = null;
    protected $show_add_button=true;
    protected $url_param=false;
    protected $custom_title=false;
    protected $module=null;
    protected $wide_layout=false;
    protected $debug=false;

    public function __construct($args) {
        $ci = get_instance();

        foreach ($args as $key => $val) {
            $this->{$key} = $val;
        }

        if (isset($args['paginate']) &&
            $args['paginate'] === false) {
            $this->paginate = false;
        } else if (empty($args['paginate']) && $args['outputtype'] != 'html') {
            $this->paginate = false;
        }

        // Guess uri segments if not explicity given
        if (empty($args['uri_segment_1'])) {
            $this->uri_segment_1 = $ci->uri->segment(1);
        }

        if (empty($args['uri_segment_2'])) {
            $this->uri_segment_2 = $ci->uri->segment(2);
        }

        if (empty($args['model'])) {

            if (empty($this->module)) {
                $model_name = $this->uri_segment_2.'_model';
                $ci->load->model($this->uri_segment_1.'/'.$model_name);
            } else {
                $model_name = $ci->uri->segment(3).'_model';
                $ci->load->model($this->module.'/'.$model_name);
            }

            $this->model = $ci->{$model_name};
        }
    }

    public function set_joins($joins) {
        foreach ($joins as $join) {
            $this->joins[] = new DatagridSQLJoin($join);
        }
    }

    /**
     * Columns are the main data definition tool for Datagrid
     */
    public function add_column($column_definition) {
        if (!empty($column_definition['requires_capability']) && !has_capability($column_definition['requires_capability'])) {
            return null;
        }
        $this->columns[] = new DatagridColumn($column_definition);
    }

    public function setup_filters() {
        if ($this->combo_filter) {
            $this->add_combo_filter('combo');
        }

        $this->add_dropdown_filters();
        $this->add_checkbox_filters();
    }

    /**
     * Abstraction for most of the logic about to be unleashed on the world...
     * This may be called either through a normal URI request, or through AJAX.
     * If the call comes through AJAX, we don't render the whole datagrid, just the params for
     * updating it.
     */
    public function render($return=false) {
        $this->setup_jsdriver();
        $this->setup_query_builder();
        $this->setup_structure();
        $this->setup_renderer();

        if (IS_AJAX) {
            $this->renderer->add_action_column();
            $this->jsdriver->refresh_ajax_rows($this->renderer->datagrid_structure->rows, $this->query_builder->get_num_rows(), $this->query_builder->get_total_records());
            return null;
        }

        $output = $this->renderer->render();

        if ($return) {
            return $output;
        } else {
            echo $output;
        }

    }

    /**
     * Checkbox filters are built from the info in the $columns array
     * TODO give users more freedom in the positioning of the various filters
     */
    public function add_checkbox_filters() {
        foreach ($this->columns as $column) {
            $filters = $column->checkbox_filters;
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if (empty($filter['checked'])) {
                        $filter['checked'] = false;
                    }

                    $this->add_checkbox_filter($filter['value'], $filter['label'], $filter['alias'], $column->table.'.'.$column->field, $filter['checked']);
                }
            }
        }
    }

    public function add_dropdown_filters() {
        foreach ($this->columns as $column) {
            if (!empty($column->dropdown_filter)) {
                $this->add_dropdown_filter($column->dropdown_filter, $column->label, $column->field);
            }
        }
    }

    public function add_combo_filter($name=null) {
        $combo_vars = array();

        foreach ($this->columns as $column) {
            if ($column->in_combo_filter) {
                $combo_vars[$column->get_combo_filter_name()] = $column->label;
            }
        }
        $this->filters[] = new DatagridFilterCombo($name, $combo_vars);
    }

    public function add_checkbox_filter($value, $label, $name, $field_name, $checked=false) {
        $this->filters[] = new DatagridFilterCheckbox($value, $label, $name, $field_name, $checked);
    }

    public function add_dropdown_filter($options, $label, $name, $default=null) {
        $this->filters[] = new DatagridFilterDropdown($options, $label, $name, $default);
    }

    public function add_date_filter($label, $name, $default=null) {
        $this->filters[] = new DatagridFilterDate($label, $name, $default);
    }
    public function add_text_filter($title, $jsname, $field_name=null, $default=null, $constant_prefix) {
        $this->filters[] = new DatagridFilterText($title, $jsname, $field_name, $default, $constant_prefix);
    }

    public function add_sql_condition($sql_condition) {
        $this->sql_conditions[] = $sql_condition;
    }

    protected function add_custom_columns() {
        if ($this->custom_columns_callback) {
            $callback = $this->custom_columns_callback;
            $callback($this->renderer->datagrid_structure);
        }
    }

    protected function setup_query_builder() {
        $this->query_builder = new DatagridQueryBuilder(array(
            'model' => $this->model,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'post_params' => $this->jsdriver->get_params_from_post($this->outputtype),
            'paginate' => $this->paginate,
            'per_page' =>$this->per_page,
            'joins' => $this->joins,
            'group_by' => $this->group_by,
            'sql_conditions' => $this->sql_conditions,
            'debug' => $this->debug));
    }

    protected function setup_jsdriver() {
        $this->jsdriver = DatagridJSDriver::get_instance($this->jslibrary);
    }

    protected function setup_structure() {
        $this->structure = DatagridStructure::get_structure(array(
            'db_records' => $this->query_builder->get_db_records(),
            'columns' => $this->columns,
            'custom_columns_callback' => $this->custom_columns_callback));
    }

    protected function setup_renderer() {
        $this->renderer = DatagridRenderer::get_instance($this->outputtype, array(
            'jsdriver' => $this->jsdriver,
            'structure' => $this->structure,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'row_actions' => $this->row_actions,
            'row_action_capabilities' => $this->row_action_capabilities,
            'row_action_conditions' => $this->row_action_conditions,
            'datagrid_callbacks' => $this->datagrid_callbacks,
            'available_export_types' => $this->available_export_types,
            'num_rows' => $this->query_builder->get_num_rows(),
            'total_records' => $this->query_builder->get_total_records(),
            'per_page' => $this->per_page,
            'feature_type' => $this->feature_type,
            'module' => $this->module,
            'title_icon' => $this->title_icon,
            'uri_segment_1' => $this->uri_segment_1,
            'uri_segment_2' => $this->uri_segment_2,
            'url_param' => $this->url_param,
            'show_add_button' => $this->show_add_button,
            'custom_title' => $this->custom_title,
            'show_pagination' => $this->paginate,
            'wide_layout' => $this->wide_layout));
    }

}
