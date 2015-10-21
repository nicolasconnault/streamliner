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
 * Depending on which page of the site we're on, generate different navigation items
 */
function get_top_nav_items() {
    $ci = get_instance();
    $nav_items = array(new nav_item('MiniAnt', 'home'));

    $user_id = $ci->session->userdata('user_id');

    if ($user_id) {
        foreach ($nav_items as $key => $nav_item) {
            if ($nav_item->label == 'Login') {
                unset($nav_items[$key]);
            }
        }
        array_push($nav_items, new nav_item('My account','account'), new nav_item('Logout', 'logout'));

    }
    return $nav_items;
}

function get_current_site_name() {
    $ci = get_instance();
    $current_site = 'miniant';
    if (is_array($segments = $ci->uri->segment_array())) {
        if (!empty($segments[1]) && in_array($segments[1], array())) {
            $current_site = $segments[1];
        }
    }
    return $current_site;
}

class nav_item {
    public $label;
    public $url;
    public $target;

    public function __construct($label, $url, $target='_self') {
        $this->label = $label;
        $this->url = $url;
        $this->target = $target;
    }
}
