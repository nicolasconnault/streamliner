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

function print_recursive_list($list, $name_field='name', $children_field='children', $label_field=null, $toggle_url=null, $toggle_id_field='id') {
    foreach ($list as $item) {
        $href = '';

        if (!is_null($toggle_url) && !empty($item->$toggle_id_field)) {
            $href = $toggle_url . $item->$toggle_id_field;
        }

        echo "<li>";
        $label = (is_null($label_field)) ? 'Select this item' : $item->$label_field;

        if (!empty($href)) {
            echo "<a href=\"$href\" title=\"$label\">";
        }
        echo "<span>{$item->$name_field}</span>\n";
        if (!empty($href)) {
            echo "</a>\n";
        }

        if (!empty($item->$children_field)) {
            echo "\n<ul>";
            print_recursive_list($item->$children_field, $name_field, $children_field, $label_field, $toggle_url, $toggle_id_field);
            echo "</ul>\n";
        }
        echo "</li>\n";
    }
}
