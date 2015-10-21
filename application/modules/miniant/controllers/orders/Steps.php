<?php
class Steps extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/part_type_model');
        $this->load->model('miniant/issue_type_model');
        $this->load->model('miniant/step_model');
    }

    public function diagnostic_rules() {
        $this->load->model('miniant/diagnostic_tree_model');
        require_capability('orders:viewdiagnosticrules');
        $this->config->set_item('replacer', array('miniant' => null, 'steps' => array('/home|Administration'), 'orders' => null, 'view_tree' => array('Diagnostic rules')));
        $tree = $this->diagnostic_tree_model->get_tree();

        $title = 'Diagnostic business rules';
        $help = 'This tree can be used to edit diagnostic business rules';

        $title_options = array('title' => $title, 'help' => $help, 'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/diagnostic_rules',
                             'tree' => $tree,
                             'feature_type' => 'Custom Feature',
                             'module' => 'miniant',
                             'csstoload' => array('tree')
                             );

        $this->load->view('template/default', $pageDetails);
    }


    public function view_tree($unit_type_id) {
        $this->load->model('miniant/diagnostic_tree_model');
        require_capability('orders:viewdiagnosticrules');
        $tree = $this->diagnostic_tree_model->get_tree();

        // Remove parts that cannot have issues
        foreach ($tree as $this_unit_type_id => $unit_type_object) {
            if ($this_unit_type_id != $unit_type_id) {
                unset($tree[$this_unit_type_id]);
                continue;
            } else {
                $unit_type_name = $unit_type_object->unit_type;
            }

            foreach ($unit_type_object->part_types as $key => $part_type) {
                if (!$part_type->can_have_issues) {
                    unset($tree[$this_unit_type_id]->part_types[$key]);
                }
            }
        }

        $this->config->set_item('replacer', array('miniant' => null, 'steps' => array('/home|Administration', '/miniant/orders/steps/diagnostic_rules|Diagnostic rules'), 'orders' => null, 'view_tree' => array($unit_type_name)));
        $title = 'Diagnostic business rules';
        $help = 'This tree can be used to edit diagnostic business rules';

        $title_options = array('title' => $title, 'help' => $help, 'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'orders/steps',
                             'tree' => $tree,
                             'jstoload' => array('orders/steps'),
                             'feature_type' => 'Custom Feature',
                             'unit_type_name' => $unit_type_name,
                             'module' => 'miniant',
                             'csstoload' => array('tree')
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function add_part_type() {
        $part_type = new stdClass();
        $part_type->name = $this->input->post('name');
        $part_type->instructions = $this->input->post('instructions');

        // Check if this component type already exists for this unit type
        if ($this->part_type_model->get($this->input->post())) {
            send_json_message("A component type of this type already exists for this unit type, please use it", 'warning');
            return false;
        }

        if ($part_type_id = $this->part_type_model->add($this->input->post())) {
            $part_type->id = $part_type_id;
            send_json_message("The $part_type->name component type has been created", 'success', array('part_type' => $part_type));
        } else {
            send_json_message("The $part_type->name component type could not be created", 'danger');
        }
    }

    public function edit_part_type() {
        $part_type = new stdClass();
        $part_type->name = $this->input->post('name');
        $part_type->instructions = $this->input->post('instructions');

        $current_part_type = $this->part_type_model->get($this->input->post('id'));

        // Check if this component type already exists for this unit type
        $this->db->where('miniant_part_types.id <>', $this->input->post('id'));

        if ($this->part_type_model->get(array('name' => $this->input->post('name'), 'unit_type_id' => $current_part_type->unit_type_id))) {
            send_json_message("A component type of this type already exists for this unit type, please use it", 'warning');
            return false;
        }

        if ($this->part_type_model->edit($this->input->post('id'), array('name' => $part_type->name, 'instructions' => $part_type->instructions))) {
            $part_type->id = $this->input->post('id');
            send_json_message("The $part_type->name component type has been updated", 'success', array('part_type' => $part_type));
        } else {
            send_json_message("The $part_type->name component type could not be updated", 'danger');
        }
    }

    public function edit_part_type_issue_type() {
        $this->load->model('miniant/diagnostic_tree_model');
        $part_type_issue_type = new stdClass();
        $part_type_issue_type->issue_type_id = $this->input->post('issue_type_id');
        $part_type_issue_type->part_type_id = $this->input->post('part_type_id');

        // Check if this component type already exists for this unit type
        $this->db->where('miniant_part_type_issue_types.id <>', $this->input->post('id'));

        if ($this->diagnostic_tree_model->check_part_type_issue_type_exists($part_type_issue_type->part_type_id, $part_type_issue_type->issue_type_id)) {
            send_json_message("This issue type already exists for this component type, please use it", 'warning');
            return false;
        }

        $part_type_issue_type->id = $this->input->post('id');

        if ($this->diagnostic_tree_model->edit_part_type_issue_type($part_type_issue_type)) {
            $part_type_issue_type->id = $this->input->post('id');
            $part_type_issue_type->name = $this->issue_type_model->get($part_type_issue_type->issue_type_id, true)->name;
            send_json_message("The issue type has been updated", 'success', array('part_type_issue_type' => $part_type_issue_type));
        } else {
            send_json_message("The issue type could not be updated", 'danger');
        }
    }

    public function edit_part_type_issue_type_step() {
        $this->load->model('miniant/diagnostic_tree_model');

        if ($this->diagnostic_tree_model->edit_part_type_issue_type_step((object) $this->input->post())) {
            $part_type_issue_type_step = (object) $this->input->post();
            $part_type_issue_type_step->name = $this->step_model->get($part_type_issue_type_step->step_id, true)->name;
            send_json_message("The step has been updated", 'success', array('part_type_issue_type_step' => $part_type_issue_type_step));
        } else {
            send_json_message("The step could not be updated", 'danger');
        }
    }

    public function edit_required_part() {
        $this->load->model('miniant/diagnostic_tree_model');

        if ($this->diagnostic_tree_model->edit_required_part((object) $this->input->post())) {
            $required_part = (object) $this->input->post();
            $required_part->name = $this->part_type_model->get($required_part->part_type_id, true)->name;
            send_json_message("The part/labour has been updated", 'success', array('required_part' => $required_part));
        } else {
            send_json_message("The part/labour could not be updated", 'danger');
        }
    }

    public function add_issue_type() {
        $this->load->model('miniant/diagnostic_tree_model');
        $part_type_issue_type = new stdClass();
        $part_type_issue_type->part_type_id = $this->input->post('part_type_id');
        $part_type_issue_type->issue_type_id = $this->input->post('issue_type_id');

        if ($this->diagnostic_tree_model->check_part_type_issue_type_exists($part_type_issue_type->part_type_id, $part_type_issue_type->issue_type_id)) {
            send_json_message("This issue type already exists for this component type, please use it", 'warning');
            return false;
        }

        if ($part_type_issue_type_id = $this->diagnostic_tree_model->add_part_type_issue_type($part_type_issue_type->part_type_id, $part_type_issue_type->issue_type_id)) {
            $part_type_issue_type->id = $part_type_issue_type_id;
            $part_type_issue_type->name = $this->issue_type_model->get($part_type_issue_type->issue_type_id)->name;
            send_json_message("The issue type has been added to the component type", 'success', array('issue_type' => $part_type_issue_type));
        } else {
            send_json_message("The issue type could not be added to the component type", 'danger');
        }
    }

    public function add_step() {
        $this->load->model('miniant/diagnostic_tree_model');
        $step = (object) $this->input->post();

        $step->required = ($step->required == 'true') ? 1 : 0;
        $step->needs_sq = ($step->needs_sq == 'true') ? 1 : 0;
        $step->immediate = ($step->immediate == 'true') ? 1 : 0;

        if ($step_id = $this->diagnostic_tree_model->add_part_type_issue_type_step($step)) {
            $step->id = $step_id;
            $step->name = $this->step_model->get($step->step_id)->name;
            send_json_message("The step has been added to the issue type", 'success', array('step' => $step));
        } else {
            send_json_message("The step could not be added to the issue type", 'danger');
        }
    }

    public function add_required_part() {
        $this->load->model('miniant/diagnostic_tree_model');
        $step_part = (object) $this->input->post();

        if ($step_part_id = $this->diagnostic_tree_model->add_required_part($step_part)) {
            $step_part->id = $step_part_id;
            $step_part->name = $this->part_type_model->get($step_part->part_type_id)->name . ' ('.$step_part->quantity.')';
            send_json_message("The required part/labour has been added to the step", 'success', array('required_part' => $step_part));
        } else {
            send_json_message("The required part/labour could not be added to the step", 'danger');
        }
    }

    public function get_required_parts_dropdown() {
        $unit_type_id = $this->input->post('unit_type_id');
        $this->db->where('unit_type_id', $unit_type_id);
        // $this->db->where('for_diagnostic', false);
        $this->db->order_by('name');
        $parts = $this->part_type_model->get();
        send_json_data(array('parts' => $parts));
    }

    public function delete_tree_element() {
        $this->load->model('miniant/diagnostic_tree_model');
        $element_type = $this->input->post('element_type');
        $element_id = $this->input->post('element_id');

        $method_name = "delete_$element_type";

        if ($this->diagnostic_tree_model->{$method_name}($element_id)) {
            send_json_message("This $element_type was successfully deleted", 'success');
        } else {
            send_json_message("This $element_type could not be deleted", 'danger');
        }
    }

    /**
     * TODO Delete once used once: will reset the sortorder based on id number!
     */
    public function reorder_steps() {
        $this->load->model('miniant/diagnostic_tree_model');
        $tree = $this->diagnostic_tree_model->get_tree('id');

        foreach ($tree as $unit_type_id => $unit_type_object) {
            foreach ($unit_type_object->part_types as $key => $part_type) {
                foreach ($part_type->part_type_issue_types as $key2 => $issue_type) {
                    $sortorder = 1;
                    foreach ($issue_type->steps as $key3 => $step) {
                        $this->db->where('id', $step->id);
                        $this->db->update('miniant_part_type_issue_type_steps', array('sortorder' => $sortorder));
                        $sortorder++;
                    }
                }
            }
        }
    }

    /**
     * This is a non-destructive function that simply adds in 1 hour of labour for every steps that has no parts/labour added
     * except for steps that clearly do not require it (e.g., Replace)
    */
    public function add_labour_to_empty_steps() {
        $this->load->model('miniant/diagnostic_tree_model');
        $tree = $this->diagnostic_tree_model->get_tree();
        foreach ($tree as $unit_type_id => $unit_type_object) {

            foreach ($unit_type_object->part_types as $key => $part_type) {
                $part_type_id = $this->part_type_model->get(array('unit_type_id' => $part_type->unit_type_id, 'name' => 'Labour'), true)->id;

                foreach ($part_type->part_type_issue_types as $key2 => $issue_type) {

                    foreach ($issue_type->steps as $key3 => $step) {
                        if ($step->name != 'Replace' && empty($step->required_parts)) {
                            $required_part = new stdClass();
                            $required_part->part_type_id = $part_type_id;
                            $required_part->part_type_issue_type_step_id = $step->id;
                            $required_part->quantity = 1;
                            $this->diagnostic_tree_model->add_required_part($required_part);
                        }
                    }
                }
            }
        }
    }

    public function duplicate_refrigerated_parts() {
        $this->load->model('miniant/diagnostic_tree_model');
        $part_types = $this->diagnostic_tree_model->get_part_types($this->unit_model->get_type_id('Refrigerated A/C'));
        $unit_types = $this->unit_model->get_types_dropdown(false);

        foreach ($unit_types as $unit_type_id => $unit_type_name) {

            if (in_array($unit_type_name, array('Evaporative A/C', 'Refrigerated A/C', 'Mechanical service'))) {
                continue;
            }

            $this_unit_type_parts = $this->diagnostic_tree_model->get_part_types($unit_type_id);

            foreach ($part_types as $part_type) {
                $part_is_new = true;

                foreach ($this_unit_type_parts as $this_unit_type_part) {
                    if (strtolower($this_unit_type_part->name) == strtolower($part_type->name)) {
                        $part_is_new = false;
                    }
                }

                if (!$part_is_new) {
                    continue;
                }

                unset($part_type->id);
                $part_type->unit_type_id = $unit_type_id;
                $this->part_type_model->add($part_type);
            }
        }
    }

    public function add_default_issue_types() {
        $this->load->model('miniant/diagnostic_tree_model');
        $tree = $this->diagnostic_tree_model->get_tree();

        foreach ($tree as $unit_type_id => $unit_type_object) {

            foreach ($unit_type_object->part_types as $key => $part_type) {

                if (empty($part_type->part_type_issue_types) && $part_type->can_have_issues) {
                    $all_issue_types = Diagnostic_Tree_Model::$issue_types;
                    $all_steps = Diagnostic_Tree_Model::$steps;

                    $replace_issue_types = array('Damaged', 'Burned out');

                    foreach ($all_issue_types as $issue_type_id => $issue_type_name) {
                        if (!in_array($issue_type_name, array('Burned out', 'Damaged', 'Blocked with dirt and debris', 'Faulty'))) {
                            continue;
                        }

                        // Create new issue
                        $part_type_issue_type = new stdClass();
                        $part_type_issue_type->part_type_id = $part_type->id;
                        $part_type_issue_type->issue_type_id = $issue_type_id;
                        $part_type_issue_type->id = $this->part_type_issue_type_model->add($part_type_issue_type);

                        // Create new step
                        foreach ($all_steps as $step_id => $step_name) {
                            $part_type_issue_type_step = new stdClass();
                            $part_type_issue_type_step->required = false;
                            $step_part = new stdClass();

                            if (in_array($issue_type_name, $replace_issue_types) && $step_name == 'Replace') {
                                $step_part->part_type_id = $part_type->id;
                                $part_type_issue_type_step->required = true;
                            } else if (in_array($issue_type_name, $replace_issue_types) && $step_name == 'Repair') {
                                continue;
                            } else if ($step_name == 'Replace') {
                                continue;
                            } else if (!in_array($issue_type_name, $replace_issue_types) && $step_name == 'Repair') {
                                $part_type_issue_type_step->required = true;
                                $step_part->part_type_id = $this->part_type_model->get_id('Labour', $part_type->unit_type_id);
                            } else {
                                $step_part->part_type_id = $this->part_type_model->get_id('Labour', $part_type->unit_type_id);
                            }

                            $part_type_issue_type_step->immediate = true;
                            $part_type_issue_type_step->needs_sq = false;
                            $part_type_issue_type_step->part_type_issue_type_id = $part_type_issue_type->id;
                            $part_type_issue_type_step->step_id = $step_id;

                            $part_type_issue_type_step->id = $this->diagnostic_tree_model->add_part_type_issue_type_step($part_type_issue_type_step);

                            $step_part->part_type_issue_type_step_id = $part_type_issue_type_step->id;

                            $step_part->quantity = 1;
                            if (empty($step_part->part_type_id)) {
                                var_dump($step_part, $part_type);die();
                            }

                            $step_part->id = $this->diagnostic_tree_model->add_required_part($step_part);
                        }
                    }
                }
            }
        }
    }

    public function remove_qty_from_instructions() {
        $this->load->model('miniant/diagnostic_tree_model');
        $part_types = $this->diagnostic_tree_model->get_part_types();
        var_dump($part_types);
    }

    public function parse_rules($unit_type_id) {
        $part_types = array();
        $counter = 0;
        $this->load->model('miniant/diagnostic_tree_model');
        $files = array(18 => 'evap_rules', 19 => 'refrig_rules', 20 => 'transport_rules', 37 => 'other_rules', 41 => 'mechanical_rules');

        $handle = fopen(APPPATH.'modules/miniant/'.$files[$unit_type_id].'.txt', 'r');
        if ($handle) {

            while (($buffer = fgets($handle, 4096)) != false) {
                if (strstr($buffer, '---')) {
                    $counter++;
                    continue;
                }

                if (empty($part_types[$counter])) {
                    $part_types[$counter] = new stdClass();
                    $part_types[$counter]->name = $buffer;
                    continue;
                }

                if (stristr($buffer,'NEW ISSUE TYPE -')) {
                    continue;
                }

                if (stristr($buffer, 'EDIT -')) {
                    continue;
                }

                if (stristr($buffer, 'NEW STEP')) {
                    $bits = explode(' NEW STEP - ', $buffer);
                    if (empty($part_types[$counter]->issue_types)) {
                        $part_types[$counter]->issue_types = array();
                    }

                    $issue_type = new stdClass();
                    $issue_type->name = $bits[0];
                    $issue_type->steps = explode(',', $bits[1]);
                    $part_types[$counter]->issue_types[] = $issue_type;
                    continue;
                }

                // only notes left now
                if (empty($part_types[$counter]->notes)) {
                    $part_types[$counter]->notes = $buffer;
                } else {
                    $part_types[$counter]->notes .= $buffer;
                }
            }
        }

        foreach ($part_types as $part_type) {
            if ($existing_part_type = $this->part_type_model->get(array('unit_type_id' => $unit_type_id, 'name' => $part_type->name), true)) {
                $this->part_type_model->delete($existing_part_type->id);
            }
            if ($existing_part_type = $this->part_type_model->get(array('unit_type_id' => $unit_type_id, 'name' => ucfirst(strtolower($part_type->name))), true)) {
                $this->part_type_model->delete($existing_part_type->id);
            }

            $part_type->name = ucfirst(strtolower($part_type->name));
            $part_type->unit_type_id = $unit_type_id;
            $part_type->can_have_issues = true;
            $part_type->in_template = false;
            $part_type->instructions = $part_type->notes;
            unset($part_type->notes);
            $issue_types = $part_type->issue_types;
            unset($part_type->issue_types);

            $part_type_id = $this->part_type_model->add($part_type);

            Diagnostic_Tree_Model::$part_types[] = $part_type;
            Diagnostic_Tree_Model::$part_types_dropdown[$part_type_id] = $part_type->name;

            foreach ($issue_types as $part_type_issue_type) {
                $issue_type_params = array('name' => $part_type_issue_type->name);

                if (!($issue_type = $this->issue_type_model->get($issue_type_params, true))) {
                    $issue_type = (object) $issue_type_params;
                    $issue_type->id = $this->issue_type_model->add($issue_type_params);
                }

                $part_type_issue_type_id = $this->diagnostic_tree_model->add_part_type_issue_type($part_type_id, $issue_type->id);

                foreach ($part_type_issue_type->steps as $step) {
                    if ($this->step_model->get(array('name' => trim($step)), true)) {
                        $step_id = $this->step_model->get(array('name' => trim($step)), true)->id;
                    } else {
                        $step_id = $this->step_model->add(array('name' => trim($step), 'past_tense' => trim($step)));
                        Diagnostic_Tree_Model::$steps = $this->step_model->get_dropdown('name', false);
                    }

                    $part_type_issue_type_step = new stdClass();
                    $part_type_issue_type_step->required = false;
                    $step_part = new stdClass();

                    if ($step == 'Replace') {
                        $step_part->part_type_id = $part_type_id;
                        $part_type_issue_type_step->required = true;
                    } else {
                        $part_type_issue_type_step->required = true;
                        $step_part->part_type_id = $this->part_type_model->get_id('Labour', $unit_type_id);
                    }

                    $part_type_issue_type_step->immediate = true;
                    $part_type_issue_type_step->needs_sq = false;
                    $part_type_issue_type_step->part_type_issue_type_id = $part_type_issue_type_id;
                    $part_type_issue_type_step->step_id = $step_id;
                    $part_type_issue_type_step->id = $this->diagnostic_tree_model->add_part_type_issue_type_step($part_type_issue_type_step);

                    $step_part->part_type_issue_type_step_id = $part_type_issue_type_step->id;

                    $step_part->quantity = 1;

                    $step_part->id = $this->diagnostic_tree_model->add_required_part($step_part);

                }
            }
        }
    }
}
