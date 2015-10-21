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
 * Form Template Helper
 * This helper provides shortcuts for generating form inputs within the CodeIgniter environment
 * It could be easily adapted for working with other MVCs or even on its own, with plain HTML.
 */

function print_form_container_open($popover=false, $multipart=false) {
    if ($popover) {
        $function_name = ($multipart) ? 'form_open_multipart' : 'form_open';
        echo $function_name('#', array('id' => $popover.'_form', 'class' => 'popover-form form-horizontal'));
        echo '<div class="form-group-popover">';
    } else {
        echo '';
    }
}

function print_static_form_element($label, $html) {
    echo '<div class="form-group">
            <label class="control-label col-lg-2 static">'.$label.'</label>
        <div class="col-lg-10">'.$html.'</div></div>';
}

function print_instruction_element($text) {
    echo '<div class="form-group"><div class="col-lg-12 instruction">'.$text.'</div></div>';
}

function print_dropdown_element($params) {
    $element = new form_element_dropdown($params);
    echo $element->get_html();
}

function print_autocomplete_element($params) {
    $element = new form_element_autocomplete($params);
    echo $element->get_html();
}

function print_multiselect_element($params) {
    $element = new form_element_multiselect($params);
    echo $element->get_html();
}

function print_input_element($params) {
    $element = new form_element_input($params);
    echo $element->get_html();
}

function print_multi_input_element($params) {
    $element = new form_element_multi_input($params);
    echo $element->get_html();
}

function print_textarea_element($params) {
    $element = new form_element_textarea($params);
    echo $element->get_html();
}

function print_break_element() {
    echo '</div><div class="form-group-popover">';
}

function print_date_element($params) {
    if (empty($params['classes'])) {
        $params['classes'] = array('date_input');
    } else {
        $params['classes'][] = 'date_input';
    }

    $element = new form_element_input($params);
    echo $element->get_html();
}

function print_datetime_element($params) {
    if (empty($params['classes'])) {
        $params['classes'] = array('datetime_input');
    } else {
        $params['classes'][] = 'datetime_input';
    }

    $element = new form_element_input($params);
    echo $element->get_html();
}

function print_password_element($params) {
    $element = new form_element_password($params);
    echo $element->get_html();
}

function print_hidden_element($params) {
    $element = new form_element_hidden($params);
    echo $element->get_html();
}

function print_file_element($params) {
    $element = new form_element_file($params);
    echo $element->get_html();
}

function print_checkbox_element($params) {
    if (!is_array($params) && is_string($params)) {
        $params = array('name' => $params, 'value' => 1);
    }
    $element = new form_element_checkbox($params);
    echo $element->get_html();
}

function print_checkbox_group_element($params) {
    $element = new form_element_checkbox_group($params);
    echo $element->get_html();
}

function print_radio_element($params) {
    $element = new form_element_radio($params);
    echo $element->get_html();
}

abstract class form_element {
    public $id;
    public $name;
    public $label;
    public $placeholder;
    public $required=false;
    public $error=false;
    public $default_value=null;
    public $html;
    public $info_text;
    public $show=true; // If set to false, no element will be output
    public $render_static=false; // If set to true, element will be rendered as static (non-editable)
    public $static_value=null; // If the form element is rendered as a static element, this value is used in the data-id attribute (e.g., contact_id)
    public $disabledif=array(); // Array of form element names that, if filled/selected/checked, should disable this element
    public $disabledunless=array(); // Array of form element names that, if NOT filled/selected/checked, should disable this element
    public $popover=false; // Different formatting will apply for a popover form (e.g., no labels)
    public $show_label=null; // For popover forms, labels are disabled by default but can be enabled
    public $type;
    public $extra_html; // An array of HTML parameters to add to the form element
    public $classes = array(); // Array of HTML classes

    /**
     * If the form element is rendered as a static element, this variable is used as the displayed value (e.g., first name and surname).
     * Defaults to the same as $static_value
     *
     * @param string static_displayvalue
     */
    public $static_displayvalue=null;
    public static $default_data=array();

    public function __construct($params) {
        extract($params);

        $this->extra_html = (isset($extra_html)) ? $extra_html : array();
        $required = (isset($required)) ? $required : false;
        $info_text = (isset($info_text)) ? $info_text : null;

        $this->name = $name;

        if (empty($label) && empty($placeholder)) {
            $label = ucfirst($name);
        }

        $this->placeholder = (empty($placeholder)) ? $label : $placeholder;
        $this->label = (empty($label)) ? $placeholder : $label;
        $this->show_label = @$show_label;
        $this->required = $required;
        $this->info_text = $info_text;
        if (isset($show)) $this->show = $show;
        if (isset($id)) $this->id = $id;
        if (isset($render_static)) $this->render_static = $render_static;
        if (!empty($static_value) || (isset($static_value) && $static_value == 0)) $this->static_value = $static_value;

        if (!empty($static_displayvalue) || (isset($static_displayvalue) && $static_displayvalue == 0)) $this->static_displayvalue = $static_displayvalue;
        if (isset($disabledif)) $this->disabledif = $disabledif;
        if (isset($disabledunless)) $this->disabledunless = $disabledunless;
        if (isset($popover)) $this->popover = $popover;

        if ((empty($this->static_displayvalue) && $this->static_displayvalue != 0) && (!empty($this->static_value) || (isset($this->static_value) && $this->static_value == 0))) {
            $this->static_displayvalue = $this->static_value;
        }

        if (empty(form_element::$default_data[$this->name]) && (!isset(form_element::$default_data[$this->name]) || form_element::$default_data[$this->name] != 0)) {
            $this->default_value = set_value($this->name);

            if (empty($this->default_value) && !empty($default_value)) {
                $this->default_value = $default_value;
            }
        } else {
            $this->default_value = form_element::$default_data[$this->name];
        }

        if (empty($this->static_displayvalue) && !empty($this->default_value)) {
            $this->static_displayvalue = $this->default_value;
        }

        if (is_null($this->static_value) && !empty($this->default_value) && $this->render_static) {
            $this->static_value = $this->default_value;
        }

        $this->classes[] = 'form-control';

        if (!empty($classes)) {
            foreach ($classes as $class) {
                $this->classes[] = $class;
            }
        }

        if (!empty($params['required'])) {
            $this->extra_html['data-required'] = true;
        }

        if ($this->popover) {
            $this->extra_html['data-label'] = $this->label;
            $this->extra_html['data-popover'] = true;
        }

        $this->error = form_error($name);
    }

    public function get_extra_html_string() {
        $this->extra_html['classes'] = $this->classes;
        if (!empty($this->extra_html['class'])) {
            $this->extra_html['classes'][] = $this->extra_html['class'];
            unset($this->extra_html['class']);
        }
        return process_extra_html($this->extra_html);
    }

    public function get_classes_string($classes=array()) {
        $html = '';

        if (!empty($classes)) {
            foreach ($classes as $class) {
                $html .= " $class ";
            }
        }

        foreach ($this->classes as $class) {
            $html .= " $class ";
        }

        return $html;
    }

    public function get_html() {
        if (!$this->show) {
            return null;
        }

        $asterisk = ($this->required) ? '<span class="required">*</span>' : '';
        $required_class = ($this->required) ? ' required ' : '';
        $error_class = ($this->error) ? ' error ' : '';
        $error_html = ($this->error) ? '<span class="validation_error">'.$this->error.'</span>' : '';
        $info_html = ($this->info_text) ? get_info_icon($this->label, $this->info_text) : '';

        $disabledif_string = $this->get_disable_string('if');
        $disabledunless_string = $this->get_disable_string('unless');

        if ($this->render_static) {
            if ($this->show_label === false) {
                $this->label = '';
            }

            return print_static_form_element($this->label,
                '<span id="'.$this->name.'" data-id="'.
                $this->static_value.'" '.
                $disabledif_string.' '.
                $disabledunless_string.'>'.
                $this->static_displayvalue.'</span>');
        }

        if ($this->popover) {
            $return_html = '<div data-toggle="tooltip" data-placement="left" title="'.$this->label.'" class="form-group '.$error_class.$required_class.'" '.$disabledif_string.' '.$disabledunless_string.'>';

            if ($this->show_label) {
                $return_html .= "<label>$this->label</label>";
            }

            $return_html .= $this->html.$info_html.'</div>';
        } else {
            $return_html = '<div class="form-group '.$error_class.'">';

            if (is_null($this->show_label) || $this->show_label) {
                $return_html .= '<label class="control-label col-lg-2">'.$this->label.' '.$asterisk.$info_html.'</label>';
            }

            $return_html .= '<div class="col-lg-10 '.$required_class.'" '.$disabledif_string.' '.$disabledunless_string.'>'.$this->html.'<span class="help-inline">'.$error_html.'</span></div></div>';
        }

        return $return_html;
    }

    public function get_disable_string($if_or_unless="if") {
        $disabled_var = $this->{'disabled'.$if_or_unless};
        $disable_string = (empty($disabled_var)) ? '' : 'data-disabled'.$if_or_unless.'="';

        $keys = array_keys($disabled_var);
        if (is_string(reset($keys))) {
            foreach ($disabled_var as $field => $value) {
                if (is_bool($value)) {
                    $disable_string .= "$field,";
                } else {
                    $disable_string .= "$field=$value,";
                }
            }
            $disable_string = substr($disable_string, 0,-1);

        } else {
            $disable_string .= implode(',', $disabled_var);
        }

        $disable_string .= (empty($disabled_var)) ? '' : '"';

        return $disable_string;
    }
}

class form_element_autocomplete extends form_element {
    public $accept_new_value=false;

    /**
     * @param string $name
     * @param string $label
     * @param string $options_url Required URL to load the options via AJAX
     * @param bool $required
     * @param string $info_text
     * @param string $add_link An optional <a>add link</a> that leads to a page for adding a new element (e.g., "Add a new page" for a Page dropdown menu)
     * @param string $edit_link An optional <a>edit link</a> that leads to a page for editing the selected element (e.g., "Edit this page" for a Page dropdown menu)
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
        $params['value'] = $this->default_value;
        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }

        $params['class'] = $this->get_classes_string();
        unset($params['classes']);
        unset($params['disabledif']);
        unset($params['disabledunless']);

        unset($params['popover']);
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        if (!empty($params['accept_new_value'])) {
            $params['data-accept_new_value'] = true;
            $this->accept_new_value = $params['accept_new_value'];
        }

        unset($params['required']);

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }

        $this->html = form_input($params);

        $this->html .= '<script type="text/javascript">
            $(function() {
                var cache = {}, lastXhr;

                $("#'.$this->id.'").autocomplete({
                    minLength: 2,
                    delay: 100,
                    source: function (request, response) {
                        var term = request.term;
                        if (term in cache) {
                            response(cache[term]);
                            return;
                        }

                        lastXhr = $.post("'.$params['options_url'].'", request, function(data, status, xhr) {
                            cache[term] = data;
                            if (xhr === lastXhr) {
                                response(data);
                            }
                        }, "json");
                    },
                    change: function(event,ui) {
                        ';

        if (!$this->accept_new_value) {
            $this->html .='
                        if (ui.item==null) {
                            $("#'.$this->id.'").val("");
                            $("#'.$this->id.'").focus();
                            print_message("Please select a valid '.$this->label.'", "danger");
                        }
                        ';
        }
        $this->html .= '
                    },
                    focus: function( event, ui ) {
                        $("#'.$this->id.'").val( ui.item.label );
                        return false;
                    },
                    select: function( event, ui ) {
                        $("#'.$this->id.'" ).val( ui.item.label );
                        $("#'.$this->id.'_id" ).val( ui.item.value );

                        return false;
                    }
                });
            });
            </script>
            <input type="hidden" name="'.$this->id.'_id" id="'.$this->id.'_id" value="'.$this->default_value.'" />
        ';
    }
}

class form_element_dropdown extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param array $options The options of the dropdown menu (<select><option></select>)
     * @param bool $required
     * @param string $info_text
     * @param string $add_link An optional <a>add link</a> that leads to a page for adding a new element (e.g., "Add a new page" for a Page dropdown menu)
     * @param string $edit_link An optional <a>edit link</a> that leads to a page for editing the selected element (e.g., "Edit this page" for a Page dropdown menu)
     */
    public function __construct($params) {
        extract($params);
        $add_link = (isset($add_link)) ? $add_link : null;
        $edit_link = (isset($edit_link)) ? $edit_link : null;

        $ci = get_instance();
        parent::__construct($params);

        $this->html = form_dropdown($name, $options, $this->default_value, $this->get_extra_html_string());

        if (!empty($add_link)) {
            $this->html .= '<a id="'.$name.'_add_link" data-link="'.$add_link.'" data-return="'.base_url().$ci->uri->uri_string().'" class="dropdown-add-link">Add a new ' .$label.'</a>';
        }
        if (!empty($edit_link)) {
            $this->html .= '<a id="'.$name.'_edit_link" data-link="'.$edit_link.'" data-return="'.base_url().$ci->uri->uri_string().'" class="dropdown-edit-link">Edit this ' .$label.'</a>';
        }
    }
}

class form_element_multiselect extends form_element {

    /**
     * @param string $name
     * @param string $label
     * @param array $options The options of the dropdown menu (<select><option></select>)
     * @param bool $required
     * @param array $extra_html
     * @param string $info_text
     */
    public function __construct($params) {
        extract($params);
        $this->extra_html['multiple'] = 'multiple';

        if (!empty($non_selected_text)) {
            $this->non_selected_text = $non_selected_text;
        }

        if (!empty($callback)) {
            $this->callback = $callback;
        }

        parent::__construct($params);
        $this->classes[] = 'multiselect';
        $this->html = form_multiselect($name, $options, $this->default_value, $this->get_extra_html_string());
    }
}

class form_element_input extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $required
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
        $params['value'] = $this->default_value;
        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }

        $params['class'] = $this->get_classes_string();
        unset($params['classes']);
        unset($params['disabledif']);
        unset($params['disabledunless']);

        unset($params['popover']);
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        unset($params['required']);

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }

        $this->html = form_input($params);
    }
}

/**
 * This simply adds a class to the input, and is useless on its own. The class must be used by JS to generate a + link and additional inputs when clicked
 */
class form_element_multi_input extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $required
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
        $params['value'] = $this->default_value;

        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }
        $this->classes[] = 'multi_input';
        $params['class'] = $this->get_classes_string();

        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        unset($params['required']);

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }
        $this->html = form_input($params);
    }
}

class form_element_textarea extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $required
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
        $params['value'] = $this->default_value;
        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }

        $params['class'] = $this->get_classes_string();

        unset($params['disabledif']);
        unset($params['disabledunless']);

        unset($params['popover']);
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        unset($params['required']);

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }

        $this->html = form_textarea($params);
    }
}

class form_element_password extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $required
     * @param bool $reveal_checkbox
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);
        $reveal_checkbox = (isset($reveal_checkbox)) ? $reveal_checkbox : false;
        parent::__construct($params);
        $params['value'] = $this->default_value;
        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }
        $this->classes[] = 'password';

        $params['class'] = $this->get_classes_string();
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        unset($params['required']);

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }
        $this->html = form_password($params);
        if ($reveal_checkbox) {
            $this->html .= form_checkbox(array('name' => 'reveal', 'onclick' => "reveal_password('{$params['name']}', this);")) . form_label('Reveal', 'reveal');
        }
    }
}

class form_element_hidden extends form_element {
    public function get_html() {
        return form_hidden($this->name, $this->default_value);
    }
}

class form_element_file extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $required
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);

        parent::__construct($params);
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        unset($params['required']);

        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }
        $this->classes[] = 'file';

        $params['class'] = $this->get_classes_string();

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }

        if ($this->popover) {
            if ($this->show_label !== false) {
                $this->html = form_upload($name);
            } else {
                $this->html = $this->label . form_upload($name);
            }
        } else {
            $this->html = form_upload($name);
        }
    }
}

class form_element_checkbox extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $required
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
        if ($this->default_value) {
            $params['checked'] = true;
        }
        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }
        $this->classes[] = 'checkbox';

        $params['class'] = $this->get_classes_string();
        unset($params['disabledif']);
        unset($params['disabledunless']);
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }
        unset($params['required']);
        $this->html = form_checkbox($params);
    }

    public function get_html() {
        $asterisk = ($this->required) ? '<span class="required">*</span>' : '';
        $required_class = ($this->required) ? ' required ' : '';
        $error_class = ($this->error) ? ' error ' : '';
        $error_html = ($this->error) ? '<span class="validation_error">'.$this->error.'</span>' : '';
        $info_html = ($this->info_text) ? get_info_icon($this->label, $this->info_text) : '';

        $disabledif_string = $this->get_disable_string('if');
        $disabledunless_string = $this->get_disable_string('unless');

        if ($this->popover) {
            $return_html = '<div class="inline '.$error_class.$required_class.'" '.$disabledif_string.' '.$disabledunless_string.'>'.$this->html;
            if ($this->show_label !== false) {
                $return_html .= ' <label class="inline">'.$this->label.'</label>';
            }
            $return_html .= $info_html.'</div>';
        } else {
            $return_html = '<div class="form-group '.$error_class.'">';
            if ($this->show_label !== false) {
                $return_html .= '<label class="control-label col-lg-2">'.$this->label.' '.$asterisk.$info_html.'</label>';
            }

            $return_html .= '<div class="col-lg-10 '.$required_class.'" '.$disabledif_string.' '.$disabledunless_string.'>
                           ' .$this->html.'<span class="help-inline">'.$error_html.'</span>
                        </div>
                    </div>';
        }
        return $return_html;
    }
}

class form_element_checkbox_group extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $info_text
     * @param bool $checked
     * @param bool $required
     * @param array $options
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
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
        $disabledif_string = $this->get_disable_string('if');
        $disabledunless_string = $this->get_disable_string('unless');

        $return_html = '<div class="form-group '.$error_class.'" '.$disabledif_string.' '.$disabledunless_string.'>';

        if ($this->show_label !== false) {
            $return_html .= '<label class="control-label col-lg-2">'.$this->label.' '.$asterisk.$info_html.'</label>';
        }

        $return_html .= '<div class="col-lg-10 '.$required_class.'">'.$this->html.'<span class="help-inline">'.$error_html.'</span></div></div>';
        return $return_html;
    }
}


class form_element_radio extends form_element {
    /**
     * @param string $name
     * @param string $label
     * @param string $value
     * @param string $info_text
     * @param bool $checked
     * @param bool $required
     * @param string $class CSS rule class
     */
    public function __construct($params) {
        extract($params);
        parent::__construct($params);
        if ($this->default_value == $params['value']) {
            $params['checked'] = true;
        }
        if (!empty($params['class'])) {
            $this->classes[] = $params['class'];
        }
        $this->classes[] = 'radio';

        $params['class'] = $this->get_classes_string();
        if (!empty($params['required'])) {
            $params['data-required'] = true;
        }
        unset($params['required']);

        if (!empty($this->extra_html)) {
            foreach ($this->extra_html as $html_param => $value) {
                $params[$html_param] = $value;
            }
            unset($params['extra_html']);
        }
        $this->html = form_radio($params);
    }
}

/**
 * @param int $colspan
 * @param string $popover
 * @param array $fields If the form is a popover, the fields are used to setup some JS validation based on the 'required' param
 */
function print_form_container_close($colspan=2, $popover=false, $fields=array()) {
    if ($popover) {
        $disabledunless = '';

        foreach ($fields as $field) {
            if (!empty($field['required'])) {
                if (empty($disabledunless)) {
                    $disabledunless = ' data-disabledunless="'.$field['name'];
                } else {
                    $disabledunless .= ",{$field['name']}";
                }
            }
        }

        if (!empty($disabledunless)) {
            $disabledunless .= '"';
        }

        $disabledunless = '';
        echo '
            </div>
            <div class="popover_buttons" '.$disabledunless.'>
                <a id="'.$popover.'_submit" onclick="submit_'.$popover.'(); return false;" class="btn btn-success">Submit</a>
                <a id="'.$popover.'_cancel" onclick="close_all_popovers(); return false;" class="btn btn-warning">Cancel</a>
            </div>
        </form>
        ';
    } else {
        echo '<div class="form-group"><div class="col-lg-12 requirednote"><span class="required">*</span> denotes required field</div></div>';
    }
}

function print_form_section_heading($heading, $columns=2, $extra_html='') {
    echo '<div class="col-sm-12"><h3 class="subtitle"'. $extra_html.'>'.$heading.'</h3></div>';
}

function print_fieldset_open($legend, $fieldset_attributes=null, $legend_attributes=null) {
    echo "<fieldset ".process_extra_html($fieldset_attributes)."><legend ".process_extra_html($legend_attributes).">$legend</legend>";
}
function print_fieldset_close() {
    echo "</fieldset>";
}
function print_submit_container_open($html_params=null) {
    echo '<div class="form-group"><div class="col-lg-12 submit" '.$html_params.'>';
}

function print_submit_container_close() {
    echo '</div></div>';
}

/**
 * Given a string or an associative array of HTML params, returns a string of HTML params
 * @param mixed $extra_html
 * @return string
 */
function process_extra_html($extra_html) {
    $output = '';
    if (is_array($extra_html)) {
        foreach ($extra_html as $param => $value) {
            if ($param == 'classes') {
                $output .= 'class="';
                foreach ($value as $class) {
                    $output .= "$class ";
                }
                $output .= '" ';
                continue;
            }

            $output .= "$param=\"$value\" ";
        }
    } else {
        $output = $extra_html;
    }

    return $output;
}

function print_submit_button($label='Submit', $id='submit_button', $name='button') {
    echo form_submit($name, $label, 'id="'.$id.'" class="btn btn-primary" data-loading-text="Loading..."');
}
function print_button($type="submit", $label='Submit', $id='submit_button', $value='button', $class='primary', $icon=null, $extra_html=null) {
    $icon_html = (empty($icon)) ? '' : '<i class="fa fa-'.$icon.'"></i>';
    echo '<button type="'.$type.'" id="'.$id.'" class="btn btn-'.$class.'" name="'.$label.'" value="'.$value.'" data-loading-text="Loading..." '.$extra_html.'>'.$icon_html.$label.'</button>';
}
function print_cancel_button($return_url, $label='Cancel') {
    $id = "cancel_button_".time();
    echo form_submit('button', $label, 'id="'.$id.'" class="btn btn-default data-loading-text="Canceling...""');
    echo '
<script type="text/javascript">
    //<![CDATA[
    $("#'.$id.'").on("click", function(event) {
        event.preventDefault();
        window.location = "'.$return_url.'";
    });
    //]]>
</script>';

}
function print_tab_nav_button($targettab, $panel_id, $direction='Next') {
    $current_url = str_replace('home/','', current_url());
    echo '<a href="'.$current_url.'#" onclick="$(\'#'.$panel_id.'\').easytabs(\'select\', \'#'.$targettab.'\');" class="btn btn-info tab-nav">'.$direction.'</a>';
}

?>
