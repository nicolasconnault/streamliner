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

function get_shareability_menus($settings) {

    $links = array();
    if ($settings->provisioned) {
        $links[base_url().'shareability/account/index'] = array('icon' => 'cogs', 'label' => 'App settings');
        $links[base_url().'shareability/useapp/index'] = array('icon' => 'mobile-phone', 'label' => 'Use your App');
        $links[base_url().'shareability/features/index'] = array('icon' => 'table', 'label' => 'Edit features');

    } else if (!$settings->submitted) {
        $links[base_url().'shareability/account/index'] = array('icon' => 'cogs', 'label' => 'App settings');

    } else if ($settings->submitted) {
        clear_messages();
        add_message('It can take up to 10 days since the date of purchase for your App to be ready. We appreciate your patience.', 'warning');
        redirect('shareability/account/index');
    }

    if ($settings->submitted) {
        $links[base_url().'shareability/account/payment_history'] = array('icon' => 'time', 'label' => 'Payment history');
    }

    return $links;
}

