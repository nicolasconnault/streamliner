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
function get_status_icon($params=array()) {
    extract($params);

    if (empty($system) || empty($document_id) || empty($statuses) || empty($url)) {
        add_message('DEBUG MESSAGE: Check the required params of the get_status_icon() function (application/helpers/status_icon_helper.php)', 'warning');
        return false;
    }

    if (empty($url_text)) $url_text = 'Complete now';
    if (empty($not_required)) $not_required = false;
    if (empty($html_params)) $html_params = array();

    $ci = get_instance();
    $model_name = $system.'_model';

    if (empty($url_text)) {
        $url_text = 'Complete now';
    }

    if ($not_required) {
        return 'Not required';
    }

    if ($ci->{$model_name}->has_statuses($document_id, $statuses)) {
        return '<i class="status-icon fa fa-check-square fa-success"></i>';
    } else {
        if (empty($html_params)) {
            return '<a class="btn btn-warning" href="'.$url.'">'.$url_text.'</a>';
        } else {
            $html = '<a class="status-icon btn btn-warning" href="'.$url.'"';
            foreach ($html_params as $param => $value) {
                $html .= " $param=\"$value\" ";
            }
            $html .= '>'.$url_text.'</a>';
            return $html;
        }
    }
}
