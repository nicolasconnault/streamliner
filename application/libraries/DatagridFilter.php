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
 * Filter class
 * @package libraries
 */
abstract class DatagridFilter {
    public $title;
    public $id;
    public $name;
    public $default;
    public $sql_condition_format = "%s = '%s'";

    public function initialise($title, $name, $default='') {

        $this->title = "Filter by $title";
        $this->id = $name . 'filter';
        $this->name = $name;
        $this->default = $default;
    }

    public function get_open_html() {
        return '<div class="filter-component">'.form_label($this->title, $this->id);
    }

    public function get_close_html() {
        return '</div>';
    }

    public function get_post() {
        $ci = get_instance();
        // CI replaces dots with underscores in POST variable keys
        return $ci->input->post(str_replace('.', '_', $this->name));
    }

    public function get_sql_condition() {
        $value = $this->get_post();
        if (!empty($value)) {
            return sprintf($this->sql_condition_format, $this->name, $value);
        } else {
            return false;
        }
    }

    /**
     * To be implemented by all children
     */
    abstract function get_input_param();
}

/**
 * A single checkbox applying a boolean filter to the SQL query. This is a "Show" filter, meaning that it is active when the checkbox is unticked,
 * leading to a smaller result set. When you tick the box, the filter is removed.
 */
class DatagridFilterCheckbox extends DatagridFilter {
    public $value;
    public $field_name;
    public $sql_condition_format = "%s <> %s";

    /**
     * Constructor
     * @param string $value The value of the field being used as a filter. For example, if the field is "status", the value might be FIELD_APPROVED.
     * @param string $value_label If the value is an integer, a string representation needs to be given as a label for that value.
     * @see filter::filter() for doc of other fields
     * @param string $field_name The actual DB field name (the $name variable must be unique, so it doesn't usually match the DB field when several filters are in place for the same field)
     * @param boolean $default If set to true, will be checked and the
     */
    public function __construct($value, $value_label='', $name, $field_name, $default=false) {
        if (empty($value_label)){
            $value_label = $value;
        }

        $this->field_name = $field_name;

        parent::initialise('', $name, $default);

        $this->value = $value;
        $this->title = "Show $value_label";

    }

    public function get_html() {
        if (empty($this->default)) {
            $this->default = 0;
        }

        return $this->get_open_html() .
            form_checkbox(array('name' => $this->name, 'id' => $this->id, 'value' => $this->value, 'checked' => $this->default)) .
            $this->get_close_html();
    }

    public function get_sql_condition() {
        $value = $this->get_post();
        if (empty($value)) {
            if ($this->value == 'NULL') {
                $this->sql_condition_format = '%s IS NULL';
            }
            return sprintf($this->sql_condition_format, $this->field_name, $this->value);
        } else {
            return false;
        }
    }

    public function get_input_param() {
        return array('name' => $this->name, 'type' => 'checkbox');
    }
}

/**
 * A text field applying a LIKE filter to the SQL query
 */
class DatagridFilterText extends DatagridFilter {
    public $sql_condition_format = "%s LIKE '%%%s%%'";
    public $constant_prefix = null;
    public $field_name;

    public function __construct($title, $jsname, $field_name=null, $default=null, $constant_prefix=null) {
        if (empty($field_name)) {
            $field_name = $jsname;
        }

        parent::initialise($title, $jsname, $default);
        $this->constant_prefix = $constant_prefix;
        $this->field_name = $field_name;
    }

    public function get_html() {
        return $this->get_open_html() .
            form_input(array('name' => $this->name, 'id' => $this->id, 'value' => $this->default)) .
            $this->get_close_html();
    }

    public function get_sql_condition() {
        $value = $this->get_post();
        if (!empty($this->constant_prefix) && !empty($value)) {
            $matching_values = search_constants_by_label($this->constant_prefix, $value);

            if (!empty($matching_values)) {
                $sql_condition = $this->field_name . ' IN (';

                foreach ($matching_values as $matching_value) {
                    $sql_condition .= "$matching_value,";
                }
                $sql_condition = substr($sql_condition, 0, -1) . ')';
                return $sql_condition;

            } else {
                return $this->field_name . " = 1 AND 1 = 0 "; // Impossible value to represent that the search returned no matches
            }
        }

        if (!empty($value)) {
            return sprintf($this->sql_condition_format, $this->field_name, $value);
        } else {
            return false;
        }
    }

    public function get_input_param() {
        return array('name' => $this->field_name, 'type' => 'text');
    }
}

/**
 * A select element applying an exact filter to the SQL query (can be multiple select)
 */
class DatagridFilterDropdown extends DatagridFilter {
    public $options=array();

    public function __construct($options, $title, $name, $default) {
        parent::initialise($title, $name, $default);
        $this->options = $options;
    }

    public function get_html() {
        return $this->get_open_html() .
            form_dropdown($this->name, $this->options, $this->default, 'id="'.$this->id.'"') .
            $this->get_close_html();

    }

    public function get_input_param() {
        return array('name' => $this->name, 'type' => 'select');
    }
}

/**
 * A calendar element applying an exact filter to the SQL query
 */
class DatagridFilterDate extends DatagridFilter {

    public function __construct($title, $name, $default) {
        parent::initialise($title, $name, $default);
    }

    public function get_html() {
        return $this->get_open_html() .
            form_input(array('name' => $this->name, 'id' => $this->id, 'class' => 'date_input')) .
            $this->get_close_html();

    }

    public function get_input_param() {
        return array('name' => $this->name, 'type' => 'date');
    }

    public function get_sql_condition() {
        $ci = get_instance();
        $ci->load->helper('date');
        $post = $ci->input->post();
        $post[$this->name] = human_to_unix($post[$this->name]);

        if (empty($post[$this->name])) {
            return null;
        }

        $sql_condition = "{$this->name} BETWEEN {$post[$this->name]} AND {$post[$this->name]} + 86399 ";

        return $sql_condition;
    }
}

/**
 * A filter composed of 3 elements: a dropdown of available fields to filter by, an operator dropdown and a value text field.
 * This is the most versatile but the most complex of the filters
 */
class DatagridFilterCombo extends DatagridFilter {
    public $fields = array();
    public $operators = array('contains' => 'contains',
                           'is exactly' => 'is exactly',
                           'is not equal to' => 'is not equal to',
                           'does not contain' => 'does not contain',
                           'is greater than' => 'is greater than',
                           'is lower than' => 'is lower than'
                           );
    public $constant_prefixes = array();
    /**
     * Constructor
     * @param string $name
     * @param array $fields An associative array of db fields => labels.
     *                      The label can optionally be appended with a pipe character and a constant prefix to trigger the search of the constant label instead of its value
     *                      The field may also be an SQL function like CONCAT(field1, ' ', field2)
     */
    public function __construct($name, $fields) {
        parent::initialise('', $name);
        foreach ($fields as $dbfield => $label) {
            if (preg_match('/^([^\|].*)\|(.*)$/', $dbfield, $matches)) {
                $this->fields[$matches[1]] = $label;
                $this->constant_prefixes[$matches[1]] = $matches[2];
            } else {
                $this->fields[htmlspecialchars($dbfield)] = $label;
            }
        }
    }

    public function get_post() {
        $ci = get_instance();
        $post = array();
        $post['search_field'] = $ci->input->post('search_field');
        $post['operator'] = $ci->input->post('operator');
        $post['search_value'] = $ci->input->post('search_value');
        return $post;
    }

    public function get_html() {
        $html = '<div class="filter-component">' . form_dropdown('search_field', $this->fields, reset($this->fields), 'id="search_field"');
        $html .= form_dropdown('operator', $this->operators, 'contains', 'id="operator"');
        $html .= form_input(array('name' => 'search_value', 'id' => 'combofilter'));
        return $html . '</div>';
    }

    public function get_sql_condition() {
        $post = $this->get_post();
        $wild = '';

        switch (trim($post['operator'])) {
            case 'contains' :
                $operator = ' LIKE ';
                $wild = '%';
                break;
            case 'does not contain' :
                $operator = ' NOT LIKE ';
                $wild = '%';
                break;
            case 'is exactly' :
                $operator = ' = ';
                break;
            case 'is greater than' :
                $operator = ' > ';
                break;
            case 'is lower than' :
                $operator = ' < ';
                break;
            case 'is not equal to' :
                $operator = ' <> ';
                break;
            default :
                $operator = ' LIKE ';
                $wild = '%';
                break;
        }

        if (empty($post['search_field']) || empty($post['operator']) || empty($post['search_value'])) {
            return false;
        }

        if (!empty($this->constant_prefixes[$post['search_field']]) && !empty($post['search_value'])) {
            $matching_values = search_constants_by_label($this->constant_prefixes[$post['search_field']], $post['search_value'], $post['operator']);
            if (!empty($matching_values)) {
                $sql_condition = $post['search_field'] . ' IN (';

                foreach ($matching_values as $matching_value) {
                    $sql_condition .= "$matching_value,";
                }
                $sql_condition = substr($sql_condition, 0, -1) . ')';
                return $sql_condition;

            } else {
                return $post['search_field'] . " = 1 AND 1 = 0 "; // Impossible value to represent that the search returned no matches
            }
        }

        // Handle date fields: recorded date will probably not fall on an exact day timestamp, so we must use a BETWEEN clause for "is exactly" and "contains" operators
        if (preg_match('/_date/', $post['search_field'])) {
            $ci = get_instance();
            $ci->load->helper('date');
            $post['search_value'] = human_to_unix($post['search_value']);
        }

        // If "Yes" or "No" is given, convert to boolean: these are probably not being used for name or description fields
        if (stristr($post['search_value'], 'yes') && strlen($post['search_value']) == 3) {
            $post['search_value'] = 1;
        } else if (stristr($post['search_value'], 'no') && strlen($post['search_value']) == 2) {
            $post['search_value'] = 0;
        }

        $sql_condition = "{$post['search_field']} $operator '$wild{$post['search_value']}$wild'";

        if (preg_match('/_date/', $post['search_field']) && ($post['operator'] == 'is exactly' || $post['operator'] == 'contains')) {
            $last_second = $post['search_value'] + 86399;
            $sql_condition = "{$post['search_field']} BETWEEN {$post['search_value']} AND $last_second";
        }

        return $sql_condition;
    }

    public function get_input_param() {
        return array('name' => $this->name, 'type' => 'combo');
    }
}
