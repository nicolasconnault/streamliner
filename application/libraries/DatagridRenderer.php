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
abstract class DatagridRenderer {
    protected $type;
    protected $jsdriver;
    protected $filters = array();
    protected $instance;
    public $datagrid_structure;
    protected $datagrid_callbacks;
    protected $columns;
    protected $num_rows;
    protected $feature_type='Streamliner Core';
    protected $total_records;
    protected $module;
    protected $title_icon;
    protected $uri_segment_1;
    protected $uri_segment_2;
    protected $row_actions;
    protected $row_action_capabilities;
    protected $row_action_conditions;
    protected $view_params=array(); // Variables for traditional MVC views that will be used for rendering the datagrid
    protected $available_export_types=array();
    protected $custom_title = false;
    protected $show_pagination = true;
    protected $wide_layout = false;

    static public function get_instance($type, $args) {
        $class_name = 'DatagridRenderer'.strtoupper($type);
        return new $class_name($args);
    }

    public function __construct($args=array()) {
        foreach ($args as $key => $val) {
            $this->{$key} = $val;
        }
        $this->datagrid_structure = $args['structure'];
    }

    abstract function render();

    /**
     * Sets up page details for all list exports. This can be overridden by any controller, either by overloading the method,
     * or by editing the resulting array directly.
     * @return array
     */
    public function setup_export_view_params() {
        $ci = get_instance();
        $ci->lang->load('general', 'english');
        $title = (empty($this->custom_title)) ? 'View ' . $ci->lang->line($this->uri_segment_2) . ' list' : $this->custom_title;
        $this->view_params = array(
            'title' => $title,
            'top_title_options' => array('title' => $title),
            'rows' => $this->datagrid_structure->rows,
            'wide_layout' => $this->wide_layout,
            'headings' => $this->datagrid_structure->headings,
            'feature_type' => $this->feature_type,
            'style' => ' style="background-color: #EEEEEE;');
        $this->hide_invisible_columns();
    }

    public function hide_invisible_columns() {
        if (!isset($this->view_params['rows'])) {
            throw new Exception("DatagridRenderer::hide_invisible_columns() must be called after view_params['rows'] has been initialised!");
        }

        foreach ($this->columns as $column_key => $column) {
            if (!in_array($this->type, $column->visible_in_outputtypes)) {
                foreach ($this->view_params['rows'] as $row_key => $row) {
                    unset($this->view_params['rows'][$row_key][$column_key]);
                }
                unset($this->view_params['headings'][$column->get_aliased_field()]);
            }
        }
    }
}

class DatagridRendererHTML extends DatagridRenderer {
    protected $type = 'html';
    protected $show_add_button = true;
    protected $group_action_icons = false;

    public function __construct($args=array()) {
        parent::__construct($args);
        $this->show_add_button = $args['show_add_button'];
        if (count($this->row_actions) > 2 && $ci->setting_model->get_value('Action icons grouped into menu') == 'Yes') {
            $this->group_action_icons = true;
        }
    }

    public function render() {
        $ci = get_instance();
        $this->add_action_column();
        $this->setup_export_view_params();
        $this->setup_html_view_params();
        $ci->load->view('template/default', $this->view_params);
    }

    protected function setup_html_view_params() {
        $ci = get_instance();

        $this->view_params += array(
            'title' => 'View ' . $ci->lang->line($this->uri_segment_2),
            'csstoload' => array('jquery.datatable'),
            'jstoloadinfooter' => array('jquery/pause',
                                        'jquery/jquery.urlparser',
                                        'jquery/datatables/media/js/jquery.dataTables',
                                        'jquery/datatables/jquery.dataTables.yadcf',
                                        'datatable_pagination',
                                        'jquery/jquery.dump',
                                        'jquery/jquery.qtip',
                                        'datagrid'),
            'content_view' => 'datagrid',
            'table_headings' => $this->datagrid_structure->headings,
            'report_title_options' => $this->get_title_options(),
            'module' => $this->module,
            'uri_segment_1' => $this->uri_segment_1,
            'uri_segment_2' => $this->uri_segment_2,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'filter_table' => $this->get_filter_table(),
            'datagrid_callbacks' => $this->get_datagrid_callbacks(),
            'url_param' => $this->url_param,
            'show_pagination' => $this->show_pagination,
            'action_column_position' => $ci->setting_model->get_value('Action column position'),
            'action_icons_menu' => $this->group_action_icons,
            'ajaxtable_params' => $this->jsdriver->get_datagrid_params(array(
                'columns' => $this->columns,
                'filters' => $this->filters,
                'module' => $this->module,
                'uri_segment_1' => $this->uri_segment_1,
                'uri_segment_2' => $this->uri_segment_2,
                'per_page' => $this->per_page,
                'url_param' => $this->url_param))
        );
    }

    protected function get_datagrid_callbacks() {
        $ci = get_instance();
        $datagrid_callbacks = $ci->load->view("datagrid_empty_callbacks", null, true);
        $module = (empty($this->module)) ? '' : "$this->module/";

        if ($this->datagrid_callbacks === true) {
            if (file_exists(APPPATH . $module. "views/$this->uri_segment_1/$this->uri_segment_2/datagrid_setup.php")) {
                $datagrid_callbacks = $ci->load->view($module."$this->uri_segment_1/$this->uri_segment_2/datagrid_setup", null, true);
            } else {
                throw new Exception("File " . APPPATH . $module."views/$this->uri_segment_1/$this->uri_segment_2/datagrid_setup.php doesn't exist!");
            }
        } else if (is_string($this->datagrid_callbacks) && strlen($this->datagrid_callbacks) > 5) {
            if (file_exists(APPPATH . "views/$this->datagrid_callbacks.php")) {
                $datagrid_callbacks = $ci->load->view($this->datagrid_callbacks, null, true);
            } else if (file_exists(APPPATH . "modules/$this->module/views/$this->datagrid_callbacks.php")) {
                $datagrid_callbacks = $ci->load->view($this->datagrid_callbacks, array('renderer' => $this), true);
            } else {
                $datagrid_callbacks = $ci->load->view($this->datagrid_callbacks, null, true);
                throw new Exception("File " . APPPATH . "views/$this->datagrid_callbacks.php");
            }
        }
        return $datagrid_callbacks;
    }

    protected function get_title_options() {
        $ci = get_instance();
        $controller_folder = ($this->uri_segment_1 == 'site') ? '' : "$this->uri_segment_1/";
        $module_folder = (empty($this->module)) ? '' : "$this->module/";

        $icons = ($this->show_add_button) ? array('add') : array();

        // Set up title bar

        $default_title = (empty($this->uri_segment_2)) ? $this->uri_segment_1 : $this->uri_segment_2;
        $title = ($this->custom_title) ? $this->custom_title : $ci->lang->line($default_title) . ' List';

        $title_options = array('title' => $title,
                               'help' => "This page allows you to browse, filter and sort the " . $ci->lang->line($this->uri_segment_2) . ' list, and perform actions on elements within that list. You can also export the list in various formats, and add new items to the list, depending on your permission level.',
                               'expand' => 'page',
                               'title_icon' => $this->title_icon,
                               'icons' => $icons + $this->available_export_types,
                               'pdf_url' => base_url().$module_folder.$controller_folder.$this->uri_segment_2.'/browse/pdf',
                               'csv_url' => base_url().$module_folder.$controller_folder.$this->uri_segment_2.'/browse/csv',
                               'xml_url' => base_url().$module_folder.$controller_folder.$this->uri_segment_2.'/browse/xml'
                           );
        return $title_options;
    }

    /**
     * Checks for capabilities for each requested action icon, then generates correct HTML based on uri_segment_1,
     *      uri_segment_2 and table headings, and appends it as a cell for each row of the data table. It then returns the table_data array
     * This modifies the datagrid_structure by adding an extra column
     * @return array
     */
    public function add_action_column() {
        $ci = get_instance();
        $ci->load->helper('inflector');
        if ($ci->setting_model->get_value('Action column position') == 'Right') {
            $this->datagrid_structure->headings['actions'] = 'Actions';
        } else if ($ci->setting_model->get_value('Action column position') == 'Left') {
            $this->datagrid_structure->headings = array('actions' => 'Actions') + $this->datagrid_structure->headings;
        }
        $controller_folder = ($this->uri_segment_1 == 'site') ? '' : "$this->uri_segment_1/";

        $actions_array = array();

        foreach ($this->row_actions as $label => $action) {

            $actions_array[$action] = DatagridActionIcon::getHTML($action, $label, $this->module, $this->uri_segment_1, $this->uri_segment_2, $this->row_action_capabilities, $this->url_param);
        }

        foreach ($this->datagrid_structure->rows as $key => $row) {
            $actions = '';
            foreach ($actions_array as $label => $action) {
                if (!empty($this->row_action_conditions[$label])) {
                    if (!$this->row_action_conditions[$label]($row)) {
                        continue;
                    }
                }
                if ($this->group_action_icons) {
                    $actions .= '<li>'.sprintf($action, $row[0]).'</li>';
                    $actions_html =
                        '<div id="row-dropdown-'.$row[0].'" class="btn-group">
                          <button type="button"
                                class="btn btn-default dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">
                            Action <span class="caret"></span>
                          </button>' .
                        '<ul class="dropdown-menu">'.$actions.'</ul></div>';
                } else {
                    $actions .= sprintf($action, $row[0]);
                    $actions_html = $actions;
                }
            }

            if ($ci->setting_model->get_value('Action column position') == 'Right') {
                $this->datagrid_structure->rows[$key][] = $actions_html;
            } else if ($ci->setting_model->get_value('Action column position') == 'Left') {
                array_unshift($this->datagrid_structure->rows[$key], $actions_html);
            }
        }
    }

    /**
     * Given an array of filter objects, builds a HTML table with the filters, ready for use by jQuery's sortable tables
     * @param array $filters
     * @param string $data_name This is the type of data being filtered (e.g. enquiries, projects, users etc.)
     * @return string HTML code
     */
    protected function get_filter_table() {
        if (empty($this->filters)) {
            return null;
        }

        $ci = get_instance();
        $ci->load->helper('title');

        $title_options = array('title' => 'Filters',
                               'help' => 'Use this box to filter the list of '.$this->uri_segment_1,
                               'expand' => 'filters',
                                'level' => 2);

        $html = '<div class="panel panel-info" id="filters"><div class="panel-heading">'.get_title($title_options).'</div><div class="panel-body">';

        foreach ($this->filters as $filter) {
            $html .= $filter->get_html();
        }

        $html .= '</div></div>';

        return $html;
    }
}

class DatagridRendererPDF extends DatagridRenderer {

    protected $type = 'pdf';
    public $encoding = 'UTF-8';
    // TODO break down the PDFs into smaller files to avoid memory overflow and long script execution
    // 1. Put the file into a queue for cron
    // 2. Split file into chunks of 5 pages
    // 3. Once finished, concatenate files into one large file using pdftk
    // 4. Alert user by email when file is ready to download (e.g., Pragmatic bookshelf method)
    public function render() {
        $ci = get_instance();
        $this->setup_export_view_params();

        $ci->load->helper('inflector');
        $ci->load->library('pdf', array('header_title' => ucfirst($ci->inflector->pluralize($this->uri_segment_2)) . ' Report', 'header_font_size' => 14));
        $ci->pdf->addpage();
        $this->view_params['pdf'] = $ci->pdf;
        $this->strip_html();
        $this->view_params['widths'] = $this->compute_column_widths();

        $this->adjust_page_orientation_based_on_widths($this->view_params['widths']);
        $ci->pdf->_config['encoding'] = $this->encoding;
        $ci->pdf->setCellPadding(55);

        if ($this->encoding != 'UTF-8') {
            $ci->pdf->setFontSubsetting(false);
            $ci->pdf->setUnicode(false);
        }

        $ci->pdf->SetSubject(ucfirst($ci->inflector->pluralize($this->uri_segment_2)) . ' Report');

        $output = $ci->load->view("datagrid_pdf", $this->view_params, true);
        if (!empty($output)) {
            $ci->pdf->writeHTML($output, false, false, false, false, '');
        }

        $ci->pdf->output("$this->uri_segment_2-report.pdf", 'D');
    }

    protected function strip_html() {
        foreach ($this->view_params['rows'] as $row_key => $row) {
            foreach ($row as $field_key => $field) {
                $this->view_params['rows'][$row_key][$field_key] = strip_tags($field);
            }
        }
    }

    protected function compute_column_widths() {
        $widths = array();

        foreach ($this->view_params['rows'] as $row) {
            foreach ($row as $key => $val) {
                if (empty($widths[$key])) {
                    $widths[$key] = 20;
                }
                $computed_width = strlen($val) * 25;
                $br_count = substr_count($val, '<br');
                if ($br_count > 0) {
                    $computed_width = $computed_width / $br_count;
                }

                if ($computed_width > 300) {
                    $computed_width = $computed_width / 2; // Collapse wide columns
                }

                if ($computed_width > $widths[$key]) {
                    $widths[$key] = $computed_width;
                }
            }
        }

        $combined_width = $this->get_combined_width($widths);
        if ($combined_width > 900 && $combined_width < 2300) { // portrait mode
            foreach ($widths as $key => $width) {
                $current_ratio = $width / $combined_width;
                $widths[$key] = round($current_ratio * 1900);
            }
        }
        if ($combined_width > 2300) { // landscape mode
            foreach ($widths as $key => $width) {
                $current_ratio = $width / $combined_width;
                $widths[$key] = round($current_ratio * 2700);
            }
        }

        // Adjust column widths to fit in a portrait or landscape A4 page
        return $widths;
    }

    protected function get_combined_width($widths) {

        $combined_width = 0;
        foreach ($widths as $width) {
            $combined_width += $width;
        }
        return $combined_width;
    }

    protected function adjust_page_orientation_based_on_widths($widths) {
        $ci = get_instance();
        $combined_width = $this->get_combined_width($widths);

        if ($combined_width > 1900) {
            $ci->pdf->_config['page_orientation'] = 'landscape';
        } else {
            $ci->pdf->_config['page_orientation'] = 'portrait';
        }
    }
}

class DatagridRendererXML extends DatagridRenderer {
    protected $type = 'xml';
    public function render() {
        $ci = get_instance();
        $this->setup_export_view_params();
        $output = $ci->load->view("datagrid_xml", $this->view_params, true);
        $ci->output->set_header("Content-type: text/xml");
        $ci->output->set_header("Content-Disposition: attachment; filename=\"$this->uri_segment_2-report.xml\"");
        $ci->output->set_output($output);
    }
}

class DatagridRendererCSV extends DatagridRenderer {
    protected $type = 'csv';
    public function render() {
        $ci = get_instance();
        $this->setup_export_view_params();
        $this->strip_html();
        $output = $ci->load->view("datagrid_csv", $this->view_params, true);
        $ci->output->set_header("Content-type: application/octet-stream");
        $ci->output->set_header("Content-Disposition: attachment; filename=\"$this->uri_segment_2-report.csv\"");
        $ci->output->set_output($output);
    }
    protected function strip_html() {
        foreach ($this->view_params['rows'] as $row_key => $row) {
            foreach ($row as $field_key => $field) {
                $this->view_params['rows'][$row_key][$field_key] = strip_tags($field);
            }
        }
    }

}
