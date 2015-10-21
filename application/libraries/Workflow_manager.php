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
 * The purpose of this class is to provide stage methods with navigation information (current navigation and redirection after submit) based
 * on workflow information in the DB and current session states, document statuses and user capabilities.
 */
class Workflow_Manager {
    public $workflow;
    public $stages = array(); // This is the list of all stages used by this workflow (each stage is represented only once)
    public $workflow_stages = array(); // These stages represent the complete workflow, including when a stage is used multiple times
    public $current_stage;
    public $current_param;
    public $module=null;
    public $extra_param=null;
    public $debug=false;

    public function initialise($params) {
        $ci = get_instance();

        if (empty($params['workflow'])) {
            die('The workflow parameter is required to initialise the Workflow_manager library!');
        }
        if (empty($params['stage'])) {
            die('The stage parameter is required to initialise the Workflow_manager library!');
        }
        if (empty($params['param'])) {
            die('The param parameter (e.g., assignment_id) is required to initialise the Workflow_manager library!');
        }
        if (!empty($params['extra_param'])) {
            $this->extra_param = $params['extra_param'];
        }

        if (!empty($params['module'])) {
            $this->module = $params['module'];
        }

        if (!$this->workflow = $ci->workflow_model->get(array('name' => strtolower($params['workflow'])), true)) {
            die("There is no workflow called {$params['workflow']} in the DB!!");
        }

        $this->stages = $ci->workflow_model->get_stages($this->workflow->id);
        $this->workflow_stages = $ci->workflow_model->get_workflow_stages($this->workflow->id);

        $this->current_stage = $this->get_stage_by_name($params['stage']);

        if (empty($this->current_stage)) {
            die("The {$params['stage']} stage is not part of the {$this->workflow->label} workflow!");
        }

        $this->current_param = $params['param'];

        return $this;
    }

    private function get_stage_by_name($stage_name) {
        foreach ($this->stages as $stage) {
            if ($stage->stage_name == $stage_name) {

                if ($this->extra_param != $stage->extra_param) {
                    continue;
                }
                return $stage;
            }
        }

        return null;
    }

    public function get_next_url() {
        $ci = get_instance();

        $next_stages = $ci->workflow_stage_stage_model->get(array('workflow_stage_id' => $this->current_stage->id));
        $module_prefix = (empty($this->module)) ? '' : $this->module.'/';

        // If only one stage can follow the current stage, conditional callbacks are ignored
        if (count($next_stages) == 1) {
            $next_stage = $ci->workflow_stage_model->get_values($next_stages[0]->next_stage_id);

            $next_url = base_url() . $module_prefix . "stages/$next_stage->stage_name/index/$this->current_param";
            if (!empty($next_stage->extra_param)) {
                $next_url .= "/$next_stage->extra_param";
            }

            return $next_url;
        } else if (count($next_stages) > 1) {
            // Step 1: check the conditional callbacks, remove any stage that returns false
            foreach ($next_stages as $key => $stage) {
                $next_stage_to_check = $ci->workflow_stage_model->get_values($stage->next_stage_id);
                $workflow_stage = $ci->workflow_stage_model->get($stage->workflow_stage_id);

                if (method_exists($this, 'skip_stage_conditions')) {
                    if ($this->skip_stage_conditions($this->current_param, $next_stage_to_check)) {
                        unset($next_stages[$key]);
                        continue;
                    }
                }

                $stage->model = $this->workflow->name;
                $stage->callback = 'check_'.$this->current_stage->stage_name.'_'.$next_stage_to_check->stage_name;

                $model_name = 'callback_model_'.rand(0,99999);

                $ci->load->model($module_prefix.'workflows/'.$stage->model.'_model', $model_name);

                $result = (int) $ci->{$model_name}->{$stage->callback}($this->current_param, $workflow_stage->extra_param);

                if ($this->debug) {
                    echo "$stage->callback() returned $result <br />";
                }

                if (!$result) {
                    unset($next_stages[$key]);
                    continue;
                }

                $next_stages[$key]->workflow_stage = $next_stage_to_check;
            }

            if (empty($next_stages)) {
                die("No valid next stages!");
            }

            // Step 2: Out of the remaining stages, return the one with the lowest stage_number value
            $stage_with_lowest_number = null;
            $lowest_number = 99999999;

            foreach ($next_stages as $stage) {
                if ($stage->workflow_stage->stage_number < $lowest_number) {
                    $stage_with_lowest_number = $stage;
                    $lowest_number = $stage->workflow_stage->stage_number;
                }
            }

            $next_url = base_url() . $module_prefix . "stages/{$stage_with_lowest_number->workflow_stage->stage_name}/index/$this->current_param";
            if (!empty($stage_with_lowest_number->workflow_stage->extra_param)) {
                $next_url .= "/" . $stage_with_lowest_number->workflow_stage->extra_param;
            }

            return $next_url;

        } else if (empty($next_stages)) {
            die("No next stage for workflow {$this->workflow->name}, stage {$this->current_stage->stage_name}!");
        }
    }
}
