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
echo form_open(base_url().'settings/process_edit/', array('id' => 'setting_edit_form', 'class' => 'form-horizontal'));
echo '<div class="panel-body">';
echo form_hidden('setting_id', $setting_id);
print_form_container_open();
print_input_element(array('name' => 'name', 'label' => 'Name', 'required' => true, 'size' => 50, 'render_static' => true));

$options = $this->setting_value_model->get_options($setting->id);
switch ($this->setting_field_type_model->get_field_type($setting->field_type_id)) {
    case 'textarea':
        print_textarea_element(array('name' => 'value', 'cols' => 80, 'rows' => 6, 'label' => 'value', 'default_value' => $setting->value));
        break;
    case 'text':
        print_text_element(array('name' => 'value', 'label' => 'value', 'default_value' => $setting->value));
        break;
    case 'date':
        print_date_element(array('name' => 'value', 'label' => 'value', 'default_value' => $setting->value));
        break;
    case 'dropdown':
        print_dropdown_element(array('name' => 'value', 'label' => 'value', 'options' => $options, 'default_value' => $setting->value));
        break;
    case 'radio':
        foreach ($options as $option_id => $option) {
            $params = array('name' => 'value', 'label' => $option, 'value' => $option_id, 'default_value' => false);
            if ((int) $setting->value == $option_id) {
                $params['checked'] = true;
            }
            print_radio_element($params);
        }
        break;
    case 'checkbox':
        foreach ($options as $option_id => $option) {
            $setting_values = $this->setting_model->get_value($setting->id);
            if (is_array($setting_values)) {
                $checked = in_array($option, $setting_values);
            } else {
                $checked = $setting->value == $option_id;
            }

            print_checkbox_element(array('name' => 'value[]', 'label' => $option, 'value' => $option_id, 'checked' => $checked));
        }
        break;
}
print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'settings');
print_submit_container_close();
print_form_container_close();
echo '</div>';
echo form_close();
?>
