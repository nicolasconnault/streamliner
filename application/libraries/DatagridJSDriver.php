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
 * These JS drivers interpret GET or POST params sent by third-party JS datagrid libraries,
 * convert them to a standardised format, and return them for use by the QueryBuilder. They also
 * output JS includes and code specific to each third-party datagrid library.
 */
abstract class DatagridJSDriver {
    public static function get_instance($jslibrary) {
        $classname = 'DatagridJSDriver'.ucfirst($jslibrary);
        if (class_exists($classname)) {
            return new $classname();
        } else {
            add_message("Class $classname doesn't exist, please check the \$jslibrary param in your datagrid instantiation.", 'danger');
            return false;
        }
    }

    abstract function get_params_from_post();
    abstract function refresh_ajax_rows();
}

class DatagridJSDriverDatatables {
    public function get_params_from_post($output_type = 'html', $paged_output_types=array()) {
        $ci = get_instance();
        $params = array(
            'start_record' => $ci->input->post('start'),
            'length' => $ci->input->post('length'),
            'draw' => $ci->input->post('draw'),
            'sorting_column_index' => $ci->input->post('order')[0]['column'],
            'sorting_direction' => $ci->input->post('order')[0]['dir'],
            'client_columns' => $ci->input->post('columns'));

        // For exports, disable paging
        if ($output_type != 'html' && !in_array($output_type, $paged_output_types)) {
            $params['start_record'] = null;
        }

        return $params;
    }

    /**
     * If the page was loaded through an AJAX requests, outputs a JSON-encoded array for jQuery Datatables. Otherwise load the view for this controller
     * @param array $view_params
     * @param array $table_data
     * @param int $total_records
     */
    public function refresh_ajax_rows($rows, $num_rows, $total_records) {
        $ci = get_instance();
        $output = new stdClass();
        $output->echo = $ci->input->post('echo');
        $output->recordsTotal = $total_records;
        $output->recordsFiltered = $num_rows;
        $output->data = $rows;
        echo json_encode($output);
    }

    public function get_datagrid_params($args) {

        $clickable = (isset($args['clickable'])) ? $args['clickable'] : true;
        $default_sort = (isset($args['default_sort'])) ? $args['default_sort'] : array(array(0, 'desc'));

        $column_sorts = array();
        foreach ($args['columns'] as $column) {
            if (in_array('html', $column->visible_in_outputtypes)) {
                $column_sorts[] = $column->sortable;
            }
        }

        // Add the actions column as non-sortable
        $column_sorts[] = false;
        $filter_params = new stdClass();
        foreach ($args['filters'] as $filter) {
            $params = $filter->get_input_param();
            $filter_params->{$params['name']} = $params['type'];
        }

        $url = "{$args['uri_segment_1']}/{$args['uri_segment_2']}";

        if (empty($args['uri_segment_2'])) {
            $url = "{$args['uri_segment_1']}/browse";
        } else if ($args['uri_segment_1'] == 'site') {
            $url = "{$args['uri_segment_2']}/browse";
        } else {
            $url .= '/index';
        }

        if (!empty($args['module'])) {
            $url = "{$args['module']}/$url";
        }

        if ($args['url_param'] !== false) {
            $url .= "/html/{$args['url_param']}";
        }
        $json = json_encode(array($url, $column_sorts, $filter_params, $clickable, $default_sort, $args['per_page']));
        // Trim the opening and closing square brackets, to convert this from a JS array to a list of function arguments
        return substr($json, 1, -1);
    }

}
