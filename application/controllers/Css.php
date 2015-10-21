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
class Css extends MY_Controller {
    public $restricted = false;

    function __construct() {
        parent::__construct($this->restricted);
    }

    public function get_compressed($filestoload=null) {
        ob_clean();

        $csstoload = explode('~', $filestoload);

        if (empty($csstoload)) {
            $csstoload = array();
        }

        $csstoload_final = array_merge(
            array(
                // 'bootstrap-theme.min',
                'bootstrap-notify',
                'bootstrap-multiselect',
                'prettify',
                'alert-bangtidy',
                'alert-notification-animations',
                'jquery.ui',
                'jquery.ui.timepicker',
                'jquery.fileupload',
                'jquery.fileupload-ui',
                'jquery.dataTables.yadcf',
                'simplemodal',
                'select2',
                'font-awesome',
                'blueimp-gallery',
                'bootstrap-tags',
                'bootstrap-editable',
                'styles'
            ),
            $csstoload
        );

        $buffer = "";
        foreach ($csstoload_final as $cssfile) {
            if (file_exists(APPPATH."../includes/css/$cssfile.css")) {
                $buffer .= file_get_contents(APPPATH."../includes/css/$cssfile.css");
            } else {
                $modules = scandir(APPPATH.'modules');
                foreach ($modules as $module) {
                    if (in_array($module, array('.', '..'))) {
                        continue;
                    }

                    if (file_exists(APPPATH."modules/$module/includes/css/$cssfile.css")) {
                        $buffer .= file_get_contents(APPPATH."modules/$module/includes/css/$cssfile.css");
                    }
                }
            }
        }

        // Automatically load every module's mod_styles.css file
        $modules = scandir(APPPATH.'modules');
        foreach ($modules as $module) {
            if (in_array($module, array('.', '..'))) {
                continue;
            }

            if (file_exists(APPPATH."modules/$module/includes/css/mod_styles.css")) {
                $buffer .= file_get_contents(APPPATH."modules/$module/includes/css/mod_styles.css");
            }
        }

        // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

        // Remove space after colons
        $buffer = str_replace(': ', ':', $buffer);

        // Remove whitespace
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

        // Enable GZip encoding.
        ob_start();

        // Enable caching
        header('Cache-Control: public');

        // Expire in one day
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

        // Set the correct MIME type, because Apache won't set it for us
        header("Content-type: text/css");

        // Write everything out
        echo($buffer);
    }
}
