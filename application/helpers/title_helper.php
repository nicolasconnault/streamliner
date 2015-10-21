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
 * @package helpers
 */
/**
 * Method to produce the dynamic admin title element.
 * (displays csv and pdf link)
 *
 * Options are :
 *  - title   : The page's title
 *  - help    : The text to put in the help mouseover
 *  - icons   : An array of icons to display (possible: add, save, pdf, email)
 *  - add_url : The url for adding an element, defaults to javascript(add)
 *  - pdf_url : The url of the page generating and serving the pdf file
 *  - csv_url : The url of the page generating and serving the csv file
 *  - email_url : The url of the page for sending an email
 *  - title_icon : An optional font-awesome icon placed before the title
 *  - report_url : The url of the page for printing a report
 *  - *_url_params : If you want to pass named parameters to the URL using POST, put them in this array. The icons will be embedded in a form with hidden inputs
 *  - extra   : Extra html to show before the icons
 *  - level : Level 1 is top heading, higher levels simply get the CSS class 'subtitle'
 *
 * <code>
 * $options['title']   = 'Driver Admin';
 * $options['help']    = 'Driver Admin';
 * $options['icons']   = array('save');
 * $options['csv_url'] = 'include/csv_export.php';
 * $options['id'] = 'tableid';
 * echo admin::getHTMLTitle($options);
 * </code>
 *
 * @param array $options See above
 * @return string HTML
 */
function get_title($options) {
    $id = '';
    if (!empty($options['id'])) {
        $id = "id=\"{$options['id']}\"";
    }
    $h_level = 3;
    if (!empty($options['level']) && $options['level'] > 1) {
        $h_level = 4;
    } else if (isset($options['level'])) {
        $h_level = $options['level'];
    }

    $return = "<h$h_level>";

    if (!empty($options['title_icon'])) {
        $return .= '<i class="fa fa-'.$options['title_icon'].'"></i> ';
    }

    $return .= "{$options['title']}<div class='pull-right title-buttons'>";

    // If extra html is given, add it before the icons (like a drop-down)
    if (isset($options['extra'])) {
        $return .= $options['extra'];
    }

    // Only show icons if help is in the array
    if (array_key_exists('help', $options)) {
        $return .= '
            <button type="button" class="btn btn-info navbar-btn help btn-icon" title="'.$options['title'].'"
                data-html="true" data-content="'.$options['help'].'" data-placement="bottom" data-container="body">
                <i class="fa fa-question" ></i><span>Help</span>
            </button>';

        if (isset($options['icons'])) {

            if (in_array('add', $options['icons'])) {
                $add_url = (isset($options['add_url'])) ? base_url().$options['add_url'] : 'javascript:add();';
                $onclick = (isset($options['add_url'])) ? "window.location='$add_url';" : 'add();';

                $return .= '<button type="button" class="btn btn-default navbar-btn btn-icon" onclick="'.$onclick.'"><i class="fa fa-plus" alt="Add" title="Add"></i><span>New</span></button>';
            }

            if (!isset($options['csv_url'])) {
                $options['csv_url'] = '/include/csv_export.php';
            }
            if (!isset($options['xml_url'])) {
                $options['xml_url'] = 'serve_file.php';
            }
            $return .= make_post_icon('csv', $options, 'save', 'export_to_csv', 'CSV', 'Download an Excel spreadsheet version of this report');
            $return .= make_post_icon('xml', $options, 'code', 'export_to_xml', 'XML', 'Download an XML version of this report, for exporting to other applications');
            $return .= make_post_icon('pdf', $options, 'pdf', 'export_to_pdf', 'PDF', 'Download a PDF version of this report, for printing');
            $return .= make_post_icon('email', $options, 'envelope');
            $return .= make_post_icon('report', $options, 'table');
        }
    }

    $return .= "</div></h$h_level>";

    return $return;
}

function make_post_icon($name, $options, $icon=null, $id=null, $title=null, $help=null) {
    $return = '';
    if (in_array($name, $options['icons'])) {
        if (is_null($id)) {
            $id = "view_$name";
        }

        if (is_null($icon)) {
            $icon = "$name.png";
        }

        if (is_null($title)) {
            $title = ucfirst($name);
        }

        $return .= form_open($options[$name.'_url'], array('id' => $id));

        if (!empty($options[$name.'_url_params'])) {
            $return .= form_hidden($options[$name.'_url_params']);
        }

        if (empty($help)) {
            $return .= '<button type="button" class="btn btn-default navbar-btn btn-icon" onclick="$(this).parent().submit();">
                <i class="'.$name.' fa fa-'.$icon.'" alt="'.$title.'" title="'.$title.'" ></i><span>'.$title.'</span>
                </button>';
        } else {
            $return .= '<button type="button" class="btn btn-default navbar-btn btn-icon help" data-container="body" data-placement="left" data-content="'.$help.'" data-original-title="'.$title.'" onclick="$(this).parent().submit();">
                <i class="'.$name.' fa fa-'.$icon.'" alt="'.$title.'" title="'.$title.'" ></i><span>'.$title.'</span>
                </button>';
        }

        $return .= form_close();
    }
    return $return;
}

