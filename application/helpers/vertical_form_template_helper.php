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
function print_form_container_open() {
    echo '';
}

function print_static_form_element($label, $html) {
    $ci = get_instance();
    echo '<div class="form-group">
            <label class="  static">'.$label.'</label>
        <div class="">'.$html.'</div></div>';
}

function print_dropdown_element($name, $label, $options, $required=false, $extra_html=null, $info_text=null) {
    $element = new form_element_dropdown($name, $label, $options, $required, $extra_html, $info_text);
    echo $element->get_html();
}

function print_multiselect_element($name, $label, $options, $required=false, $extra_html=null, $info_text=null) {
    $element = new form_element_multiselect($name, $label, $options, $required, $extra_html, $info_text);
    echo $element->get_html();
}

function print_input_element($label, $params, $required=false, $info_text=null) {
    $element = new form_element_input($label, $params, $required, $info_text);
    echo $element->get_html();
}

function print_multi_input_element($label, $params, $required=false, $info_text=null) {
    $element = new form_element_multi_input($label, $params, $required, $info_text);
    echo $element->get_html();
}

function print_textarea_element($label, $params, $required=false, $info_text=null) {
    $element = new form_element_textarea($label, $params, $required, $info_text);
    echo $element->get_html();
}

function print_date_element($label, $params, $required=false, $info_text=null) {
    if (empty($params['class'])) {
        $params['class'] = 'date_input';
    } else {
        $params['class'] .= ' date_input';
    }

    $element = new form_element_input($label, $params, $required, $info_text);
    echo $element->get_html();
}

function print_password_element($label, $params, $required=false, $reveal_checkbox=false, $info_text=null) {
    $element = new form_element_password($label, $params, $required, $reveal_checkbox, $info_text);
    echo $element->get_html();
}

function print_hidden_element($name) {
    $element = new form_element_hidden($name);
    echo $element->get_html();
}

function print_file_element($name, $label, $required=false, $info_text=null) {
    $element = new form_element_file($name, $label, $required, $info_text);
    echo $element->get_html();
}

function print_checkbox_element($label, $params, $required=false, $info_text=null) {
    if (!is_array($params) && is_string($params)) {
        $params = array('name' => $params, 'value' => 1);
    }
    $element = new form_element_checkbox($label, $params, $required, $info_text);
    echo $element->get_html();
}

function print_checkbox_group_element($grouplabel, $name, $options, $required=false, $info_text=null) {
    $element = new form_element_checkbox_group($grouplabel, $name, $options, $required, $info_text);
    echo $element->get_html();
}

function print_radio_element($label, $params, $required=false, $info_text=null) {
    $element = new form_element_radio($label, $params, $required, $info_text);
    echo $element->get_html();
}

abstract class form_element {
    public $name;
    public $label;
    public $required=false;
    public $error=false;
    public $default_value=null;
    public $html;
    public $info_text;
    public static $default_data=array();

    public function __construct($name, $label, $required=false, $info_text=null) {
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;
        $this->info_text = $info_text;

        if (empty(form_element::$default_data[$this->name])) {
            $this->default_value = set_value($this->name);
        } else {
            $this->default_value = form_element::$default_data[$this->name];
        }
        $this->error = form_error($name);
    }

    public function get_html() {
        $asterisk = ($this->required) ? '<span class="required">*</span>' : '';
        $required_class = ($this->required) ? ' required ' : '';
        $error_class = ($this->error) ? ' error ' : '';
        $error_html = ($this->error) ? '<span class="validation_error">'.$this->error.'</span>' : '';
        $info_html = ($this->info_text) ? get_info_icon($this->label, $this->info_text) : '';

        return '
            <div class="form-group '.$error_class.'">
                <label class=" ">'.$this->label.' '.$asterisk.$info_html.'</label>
                <div class=" '.$required_class.'">'.$this->html.'<span class="help-inline">'.$error_html.'</span></div>
            </div>
            ';
    }

    /**
     * Given a string or an associative array of HTML params, returns a string of HTML params
     * @param mixed $extra_html
     * @return string
     */
    public function process_extra_html($extra_html) {
        $output = '';
        if (is_array($extra_html)) {
            foreach ($extra_html as $param => $value) {
                $output .= "$param=\"$value\" ";
            }
        } else {
            $output = $extra_html;
        }

        return $output;
    }
}

class form_element_dropdown extends form_element {
    public function __construct($name, $label, $options, $required=false, $extra_html=null, $info_text=null) {
        parent::__construct($name, $label, $required, $info_text);

        $class = 'form-control';
        $extra_html['class'] = (empty($extra_html['class'])) ? $class : $extra_html['class'] . " $class";
        $this->html = form_dropdown($name, $options, $this->default_value, $this->process_extra_html($extra_html));
    }
}

class form_element_multiselect extends form_element {
    public function __construct($name, $label, $options, $required=false, $extra_html=null, $info_text=null) {
        parent::__construct($name, $label, $required, $info_text);
        $class = 'form-control';
        $extra_html['class'] = (empty($extra_html['class'])) ? $class : $extra_html['class'] . " $class";
        $this->html = form_multiselect($name, $options, $this->default_value, $extra_html);
    }
}

class form_element_input extends form_element {
    public function __construct($label, $params, $required=false, $info_text=null) {
        parent::__construct($params['name'], $label, $required, $info_text);
        $params['value'] = $this->default_value;
        $params['class'] = 'form-control';
        $this->html = form_input($params);
    }
}

/**
 * This simply adds a class to the input, and is useless on its own. The class must be used by JS to generate a + link and additional inputs when clicked
 */
class form_element_multi_input extends form_element {
    public function __construct($label, $params, $required=false, $info_text=null) {
        parent::__construct($params['name'], $label, $required, $info_text);
        $params['value'] = $this->default_value;
        if (empty($params['class'])) {
            $params['class'] = 'multi_input';
        } else {
            $params['class'] .= ' multi_input';
        }
        $params['class'] .= ' form-control';
        $this->html = form_input($params);
    }
}

class form_element_textarea extends form_element {
    public function __construct($label, $params, $required=false, $info_text=null) {
        parent::__construct($params['name'], $label, $required, $info_text);
        $params['value'] = $this->default_value;
        $params['class'] = ' form-control';
        $this->html = form_textarea($params);
    }
}

class form_element_password extends form_element {
    public function __construct($label, $params, $required=false, $reveal_checkbox=false, $info_text=null) {
        parent::__construct($params['name'], $label, $required, $info_text);
        $params['value'] = $this->default_value;
        $params['class'] = ' form-control';
        $this->html = form_password($params);
        if ($reveal_checkbox) {
            $this->html .= form_checkbox(array('name' => 'reveal', 'onclick' => "reveal_password('{$params['name']}', this);")) . form_label('Reveal', 'reveal');
        }
    }
}

class form_element_hidden extends form_element {
    public function __construct($name) {
        parent::__construct($name, null);
    }

    public function get_html() {
        return form_hidden($this->name, $this->default_value);
    }
}

class form_element_file extends form_element {
    public function __construct($name, $label, $required=false, $info_text=null) {
        parent::__construct($name, $label, $required, $info_text);
        $this->html = form_upload($name);
    }
}

class form_element_checkbox extends form_element {
    public function __construct($label, $params, $required=false, $info_text=null) {
        parent::__construct($params['name'], $label, $required, $info_text);
        if ($this->default_value) {
            $params['checked'] = true;
        }
        $params['class'] = ' form-control';
        $this->html = form_checkbox($params);
    }

    public function get_html() {
        $asterisk = ($this->required) ? '<span class="required">*</span>' : '';
        $required_class = ($this->required) ? ' required ' : '';
        $error_class = ($this->error) ? ' error ' : '';
        $error_html = ($this->error) ? '<span class="validation_error">'.$this->error.'</span>' : '';
        $info_html = ($this->info_text) ? get_info_icon($this->label, $this->info_text) : '';

        return '
            <div class="checkbox '.$error_class.'">
                <label class=" ">'.$this->label.' '.$asterisk.$info_html.$this->html.'<span class="help-inline">'.$error_html.'</span></label>
            </div>
            ';
    }
}

class form_element_checkbox_group extends form_element {
    public function __construct($grouplabel, $name, $options, $required=false, $info_text=null) {
        parent::__construct($name, $grouplabel, $required, $info_text);
        $this->html = '';
        foreach ($options as $option_id => $option) {
            if (!empty($this->default_value)) {
                $checked = in_array($option_id, $this->default_value);
            } else {
                $checked = false;
            }
            $this->html .= '<label class="checkbox">'.form_checkbox(array('name' => $name, 'class' => 'form-control', 'value' => $option_id, 'checked' => $checked)).$option.'</label>';
        }
    }

    public function get_html() {
        $asterisk = ($this->required) ? '<span class="required">*</span>' : '';
        $required_class = ($this->required) ? ' required ' : '';
        $error_class = ($this->error) ? ' error ' : '';
        $error_html = ($this->error) ? '<span class="validation_error">'.$this->error.'</span>' : '';
        $info_html = ($this->info_text) ? get_info_icon($this->label, $this->info_text) : '';

        return '
            <div class="form-group '.$error_class.'">
                <label class=" ">'.$this->label.' '.$asterisk.$info_html.'</label>
                <div class=" '.$required_class.'">'.$this->html.'<span class="help-inline">'.$error_html.'</span></div>
            </div>
            ';
    }
}


class form_element_radio extends form_element {
    public function __construct($label, $params, $required=false, $info_text=null) {
        parent::__construct($params['name'], $label, $required, $info_text);
        if ($this->default_value) {
            $params['checked'] = true;
        }
        $params['class'] = ' form-control';
        $this->html = form_radio($params);
    }
}

function print_form_container_close($colspan=2) {
    echo '<div class="form-group"><div class=" requirednote"><span class="required">*</span> denotes required field</div></div>';
}

function print_form_section_heading($heading, $columns=2, $extra_html=null) {
    echo '<h3 class="subtitle"'. $extra_html.'>'.$heading.'</h3>';
}

function print_fieldset_open($legend, $fieldset_attributes=null, $legend_attributes=null) {
    echo "<fieldset $fieldset_attributes><legend $legend_attributes>$legend</legend>";
}
function print_fieldset_close() {
    echo "</fieldset>";
}
function print_submit_container_open() {
    echo '<div class="form-group"><div class=" submit">';
}

function print_submit_container_close() {
    echo '</div></div>';
}
?>
