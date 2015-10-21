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

function setup_tabbed_form($tabs) {
    tabbed_form::$tabs = $tabs;
}

function print_tab_list() {
    tabbed_form::print_tab_list();
}

function print_tabbed_form_navbuttons() {
    static $tab_counter = 1;
    tabbed_form::print_tabbed_form_navbuttons($tab_counter);
    $tab_counter++;
}

function get_tab_panel_class() {
    static $tab_counter = 0;
    $tab_counter++;
    return tabbed_form::get_tab_panel_class($tab_counter);
}

class tabbed_form {
    public static $tabs = array();

    public static function print_tab_list() {
        $ci = get_instance();

        $errors = validation_errors();
        echo '<script type="text/javascript" src="'.base_url().'includes/js/application/tabbed_form.js"></script>';

        $current_url = str_replace('home/','', current_url());
        echo '<ul class="nav nav-tabs">';

        $tab_counter = 1;

        foreach (tabbed_form::$tabs as $tab) {
            echo '<li class="tab tab-'.$tab_counter.' tab-'.$tab['id'].'">';
            echo '<a href="'.$current_url.'#'.$tab['id'].'">'.$tab['label'].'</a>';
            echo '</li>';
            $tab_counter++;
        }

        echo '</ul>';
    }

    public static function print_tabbed_form_navbuttons($tab_number) {
        $next_button = ($tab_number < count(tabbed_form::$tabs)) ? '<a href="#" class="next-tab btn btn-info success" rel="'.($tab_number+1).'">Next Page »</a>' : '';
        $prev_button = ($tab_number > 1) ? '<a href="#" class="prev-tab btn btn-info success" rel="'.($tab_number-1).'">« Prev Page</a>' : '';
        echo '<div class="tabbed_form_navbuttons">'.$prev_button .  $next_button.'</div>';
    }

    public static function get_tab_panel_class($tab_number) {
        return "tab-panel tab-panel-".$tab_number;
    }
}
