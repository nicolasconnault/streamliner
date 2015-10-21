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
// DEPENDS ON helpers/form_template_helper.php!!!

function print_popover_form($title, $id, $fields, $multipart=false, $label=false) {
    echo '<div id="'.$id.'_popup" class="popover-markup">
                <div class="head hide">'.$title.'</div>
                <div class="content hide">';

    print_form_container_open($id, $multipart);

    foreach ($fields as $field) {
        $field['popover'] = $id;
        if (!isset($field['show_label'])) {
            $field['show_label'] = $label;
        }

        $function = 'print_'.$field['type'].'_element';

        if (function_exists($function)) {
            $function($field);
        } else {
            print_input_element($field);
        }
    }

    print_form_container_close(1, $id, $fields);

    echo '</div>
        <div class="footer hide"></div>
    </div>';
}
