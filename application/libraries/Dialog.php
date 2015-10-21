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
/**
 * The Dialog library is used to create dynamic decision-making interfaces using simple questions and answer buttons.
 * They are tied to the Events and Status systems
 */

class Dialog {
    public $id;
    public $label;
    public $min_interval_between_answers=0; // In seconds. If the interval is shorter, activate a "too short" custom function
    public $questions = array();

    public function initialise($params) {
        $this->id = (empty($params['id'])) ? 'dialog_' . rand(0,4888574) : $params['id'];
        $this->label = (empty($params['label'])) ? '' : $params['label'];
        $this->min_interval_between_answers = (empty($params['min_interval_between_answers'])) ? 0 : $params['min_interval_between_answers'];
        $this->questions = array();
    }

    public function __construct() {
    }

    public function add_question($params) {
        $params['id'] = (empty($params['id'])) ? 'question_' . rand(0,4888574) : $params['id'];
        $params['shown'] = (empty($params['shown'])) ? false : $params['shown'];
        $params['js_shown'] = (empty($params['js_shown'])) ? false : $params['js_shown'];

        if (empty($params['text'])) {
            add_message('Questions must have a text!', 'danger');
            return false;
        }

        if (empty($params['answers'])) {
            add_message('Questions must have answers!', 'danger');
            return false;
        }

        $this->questions[] = new Question($params['text'], $params['answers'], $params['shown'], $params['js_shown'], $params['id'], $this->id);
    }

    public function output() {
        $output = '<div id="'.$this->id.'" class="dialog">';

        $output .= '<script type="text/javascript">
            var min_interval_between_answers = '.$this->min_interval_between_answers.';
            var time_last_answer_clicked = 0;
        </script>
        ';

        foreach ($this->questions as $question) {
            $output .= $question->output();
        }

        $output .= '</div>';
        return $output;
    }
}

class Question {
    public $id;
    public $dialog_id;
    public $shown = false;
    public $js_shown = null;
    public $text;
    public $answers = array();

    public function __construct($text, $answers, $shown, $js_shown, $id, $dialog_id) {
        $this->text = $text;
        $this->shown = $shown;
        $this->js_shown = $js_shown;
        $this->id = $id;
        $this->dialog_id = $dialog_id;

        foreach ($answers as $params) {
            $params['dialog_id'] = $this->dialog_id;
            $this->answers[] = Question::get_answer_object($params);
        }
    }

    public function output() {
        $style = ($this->shown) ? 'block' : 'none';

        $output = '<div style="display: '.$style.'" id="'.$this->id.'" class="question">'.$this->text;
        foreach ($this->answers as $answer) {
            $output .= $answer->output();
        }
        $output .= '</div>';
        return $output;
    }

    public static function get_answer_object($params) {
        if (empty($params['type'])) {
            $params['type'] = 'text';
        }

        $classname = $params['type'] . 'Answer';

        if (!class_exists($classname)) {
            add_message("The $classname class doesn't exist!", 'danger');
            return false;
        }

        unset($params['type']);

        return new $classname($params);
    }
}

abstract class Answer {
    public $id;
    public $dialog_id;
    public $text;
    public $style = null;
    public $url = null;
    public $triggers = array();
    public $ids_to_hide = array();
    public $ids_to_show = array(); // Numerical array by default. If associative array, the question ID must be the key, and the JS condition must be the value
    public $ajax_callback; // The relative URL to a valid AJAX function that can process the input
    public $ajax_data = array(); // Associate array of data to send with the AJAX request
    public $ajax_position = 'start'; // 'start' or 'end', the position at which the ajax callback is inserted

    public function __construct($construct_params) {
        foreach ($construct_params as $param => $value) {
            if ($param == 'triggers') {
                continue;
            }

            $this->$param = $value;
        }

        if (empty($this->id)) {
            $this->id = 'answer_' . rand(0,4888574);
        }

        if (empty($this->text)) {
            add_message('Answers must have a text!', 'danger');
        }

        if (!empty($construct_params['triggers'])) {
            foreach ($construct_params['triggers'] as $params) {
                if (empty($params['system'])) {
                    add_message('Triggers must have a system!', 'danger');
                    return false;
                }
                if (empty($params['event_name'])) {
                    add_message('Triggers must have an event!', 'danger');
                    return false;
                }

                if (empty($params['js_condition'])) {
                    $params['js_condition'] = null;
                }

                if (empty($params['undo'])) {
                    $params['undo'] = false;
                }

                $params['document_id'] = (empty($params['document_id'])) ? null : $params['document_id'];

                $this->triggers[] = new Trigger($params['system'], $params['event_name'], $params['document_id'], $params['js_condition'], $params['undo'], $params['module']);
            }
        }
    }

    public abstract function output();

    public abstract function get_js();
}

class textAnswer extends Answer {
    public $undo = true; // Provides an UNDO button that appears temporarily (5 seconds) and reverts the event triggers. Doesn't work if a URL is provided
    public $short_interval_js = null;

    public function is_array_assoc($array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    public function __construct($construct_params) {
        parent::__construct($construct_params);

    }

    public function output() {
        $output = '<div id="'.$this->id.'" class="answer">';
        $output .= '<a class="btn btn-success">'.$this->text.'</a><a style="display:none;" id="clicked_'.$this->id.'" class="btn btn-info">'.$this->text.'</a>';
        $output .= $this->get_js();
        return $output;
    }

    public function get_js() {
        $output = '<script type="text/javascript">
            $(function() {
                $("#'.$this->id.' a.btn-success").on("click", function(event) {';

        if (!empty($this->ajax_callback)) {

            $json_values = '{value: null'; // the value variable is only used for dropdown menus
            if (!empty($this->ajax_data)) {
                foreach ($this->ajax_data as $param => $value) {
                    $json_values .= ", $param: '$value'";
                }
            }
            $json_values .= '}';
            $output .= '
                    $.post(base_url+"'.$this->ajax_callback.'", '.$json_values.', function(data) {
                        display_message(data.type, data.message);';
            $output .= '}, "json");';
        }

        $output .= '
                    $("a.undo").hide();
                    ';

        $output .= '$(this).hide();
                    $("#'.$this->dialog_id.' #clicked_'.$this->id.'").show();
                    time_last_answer_clicked = (new Date).getTime() / 1000;
                    ';

        if (!empty($this->short_interval_js)) {
            $output .= '
                    var current_time = ((new Date).getTime() / 1000);

                    if (min_interval_between_answers > 0 && current_time - time_last_answer_clicked < min_interval_between_answers) {
                        '.$this->short_interval_js.'
                    }
                    ';
        }

        if ($this->undo && empty($this->url)) {
            $output .= "\n".'var undo_button = document.createElement("a");
                $(undo_button).addClass("btn btn-danger undo");
                $(undo_button).attr("id", "undo_'.$this->id.'");
                $(undo_button).html("Undo");
                $(undo_button).on("click", function(event) {
            ';

            if (!empty($this->triggers)) {
                foreach ($this->triggers as $trigger) {
                    $output .= "\n".$trigger->get_js(true);
                }
            }

            $output .= '
                    $("#'.$this->dialog_id.' #undo_'.$this->id.'").remove();
                    ';

            if (!empty($this->ids_to_hide)) {
                if ($this->is_array_assoc($this->ids_to_hide)) {
                    foreach ($this->ids_to_hide as $id => $condition) {
                        $output .= "\n if ($condition) $('#$this->dialog_id #$id').show();";
                    }
                } else {
                    foreach ($this->ids_to_hide as $id) {
                        $output .= "\n $('#$this->dialog_id #$id').show();";
                    }
                }
            }
            if (!empty($this->ids_to_show)) {
                if ($this->is_array_assoc($this->ids_to_show)) {
                    foreach ($this->ids_to_show as $id => $condition) {
                        $output .= "\n if ($condition) $('#$this->dialog_id #$id').hide();";
                    }
                } else {
                    foreach ($this->ids_to_show as $id) {
                        $output .= "\n $('#$this->dialog_id #$id').hide();";
                    }
                }
            }

            $output .= '
                    $("#'.$this->dialog_id.' #'.$this->id.' a.btn-success").show();
                    $("#'.$this->dialog_id.' #clicked_'.$this->id.'").hide();
                });
            $("#'.$this->dialog_id.' #'.$this->id.'").append("&nbsp;");
            $("#'.$this->dialog_id.' #'.$this->id.'").append(undo_button);
                ';
        }

        if (!empty($this->triggers)) {
            foreach ($this->triggers as $trigger) {
                $output .= "\n".$trigger->get_js();
            }
        }

        if (empty($this->url)) {
            if (!empty($this->ids_to_hide)) {
                if ($this->is_array_assoc($this->ids_to_hide)) {
                    foreach ($this->ids_to_hide as $id => $condition) {
                        $output .= "\n if ($condition) $('#$this->dialog_id #$id').hide();";
                    }
                } else {
                    foreach ($this->ids_to_hide as $id) {
                        $output .= "\n $('#$this->dialog_id #$id').hide();";
                    }
                }
            }
            if (!empty($this->ids_to_show)) {
                if ($this->is_array_assoc($this->ids_to_show)) {
                    foreach ($this->ids_to_show as $id => $condition) {
                        $output .= "\n if ($condition) $('#$this->dialog_id #$id').show();console.log($condition);";
                    }
                } else {
                    foreach ($this->ids_to_show as $id) {
                        $output .= "\n $('#$this->dialog_id #$id').show();";
                    }
                }
            }
        } else {
            $output .= "document.location = '".$this->url."';";
        }

        if (!empty($this->ajax_callback)) {

        }

        $output .= '
                });
            });
            </script>';
        $output .= '</div>';

        return $output;
    }

}

class dropdownAnswer extends Answer {
    public $options; // Associative array of Dropdown values, required
    public $url = null; // Optional URL to which the user will be redirected after answering the question

    public function __construct($construct_params) {
        parent::__construct($construct_params);

        if (empty($this->options)) {
            add_message('You must provide options for a dropdown answer', 'danger');
        }

        if (empty($this->ajax_callback)) {
            add_message('You must provide an AJAX callback for a dropdown answer', 'danger');
        }
    }

    public function output() {
        $output = '<div id="'.$this->id.'" class="answer">';
        $output .= '<select id="dropdown_'.$this->id.'" name="dropdown_'.$this->id.'">';

        foreach ($this->options as $value => $label) {
            $output .= '<option value="'.$value.'">'.$label.'</option>';
        }

        $output .= '</select>';
        $output .= $this->get_js();
        return $output;
    }

    public function get_js() {
        $json_values = '{value: $(this).val()';
        if (!empty($this->ajax_data)) {
            foreach ($this->ajax_data as $param => $value) {
                $json_values .= ", $param: '$value'";
            }
        }
        $json_values .= '}';

        $output = '<script type="text/javascript">
            $(function() {
                $("#dropdown_'.$this->id.'").on("change", function(event) {';

        if ($this->ajax_position == 'end') {
            if (!empty($this->triggers)) {
                foreach ($this->triggers as $trigger) {
                    $output .= "\n".$trigger->get_js();
                }
            }
            $output .= '
                    $.post(base_url+"'.$this->ajax_callback.'", '.$json_values.', function(data) {
                        display_message(data.type, data.message);';
            if (!empty($this->url)) {
                $output .= "document.location = '".$this->url."';";
            }

        } else {
            $output .= '
                    $.post(base_url+"'.$this->ajax_callback.'", '.$json_values.', function(data) {
                        display_message(data.type, data.message);';
            if (!empty($this->triggers)) {
                foreach ($this->triggers as $trigger) {
                    $output .= "\n".$trigger->get_js();
                }
            }
            if (!empty($this->url)) {
                $output .= "document.location = '".$this->url."';";
            }
        }

        $output .= '
                    }, "json");
                });
            });
            </script>';
        $output .= '</div>';

        return $output;
    }
}

class Trigger {
    public $system;
    public $event_name;
    public $module=null;
    public $document_id=null;
    public $js_condition=null;
    public $undo=false;

    public function __construct($system, $event_name, $document_id, $js_condition, $undo, $module=null) {
        $this->system = $system;
        $this->event_name = $event_name;
        $this->document_id = $document_id;
        $this->module = $module;
        $this->js_condition = $js_condition;
        $this->undo = $undo;
    }

    public function get_js($undo=false) {
        $function_name = ($undo || $this->undo) ? 'undo_event' : 'trigger_event';
        $return_val = '';

        if (!empty($this->js_condition)) {
            $return_val = "if ($this->js_condition) {
                ";
        }

        $return_val .= '$.post(base_url+"events/'.$function_name.'", {
            system: "'.$this->system.'",
            event_name: "'.$this->event_name.'",
            document_id: "'.$this->document_id.'",
            module: "'.$this->module.'"
        });';

        if (!empty($this->js_condition)) {
            $return_val .= '}
                ';
        }

        return $return_val;
    }
}
