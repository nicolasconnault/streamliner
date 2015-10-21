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
class Setting_Model extends MY_Model {
    public $table = 'settings';

    public function get_value($name_or_id) {
        $setting = $this->get(array('name' => $name_or_id), true);
        if (empty($setting)) {
            $setting = $this->get($name_or_id);
        }

        if (empty($setting)) {
            return null;
        }

        if (empty($setting->value)) {
            add_message("The setting '$name' doesn't exist!", 'danger');
            return null;
        } else {
            if ($this->setting_field_type_model->has_options($setting->field_type_id)) {

                $value = explode(',', $setting->value);

                if (strstr($setting->value, ',')) {
                    $values = array();
                    foreach ($value as $value_id) {
                        $values[] = $this->setting_value_model->get($value_id)->value;
                    }
                    return $values;
                } else {
                    return $this->setting_value_model->get($setting->value)->value;
                }
            } else {
                return $setting->value;
            }
        }
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            if (empty($db_records)) {
                return null;
            }
            foreach ($db_records as $key => $row) {
                $db_records[$key]['value'] = $this->setting_model->get_value($row['setting_id']);
            }
        };
    }
}
