<?php
class Diagnostic_Tree_Model extends MY_Model {

    static public $part_types;
    static public $part_types_dropdown;
    static public $issue_types;
    static public $steps;
    static public $step_parts;
    static public $step_parts_keys;
    static public $part_type_issue_types;
    static public $part_type_issue_types_keys;
    static public $part_type_issue_type_steps;
    static public $part_type_issue_type_steps_keys;

    public function __construct() {
        parent::__construct();
        $this->load->model('miniant/part_type_model');
        $this->load->model('miniant/issue_type_model');
        $this->load->model('miniant/step_model');
        $this->load->model('miniant/step_part_model');
        $this->load->model('miniant/part_type_issue_type_model');
        $this->load->model('miniant/part_type_issue_type_step_model');

        if (empty(Diagnostic_Tree_Model::$part_types)) {
            $this->db->order_by('name');
            Diagnostic_Tree_Model::$part_types = $this->part_type_model->get();
        }
        if (empty(Diagnostic_Tree_Model::$part_types_dropdown)) {
            $this->db->order_by('name');
            Diagnostic_Tree_Model::$part_types_dropdown = $this->part_type_model->get_dropdown('name', false);
        }
        if (empty(Diagnostic_Tree_Model::$issue_types)) {
            $this->db->order_by('name');
            Diagnostic_Tree_Model::$issue_types = $this->issue_type_model->get_dropdown('name', false);
        }
        if (empty(Diagnostic_Tree_Model::$steps)) {
            $this->db->order_by('name');
            Diagnostic_Tree_Model::$steps = $this->step_model->get_dropdown('name', false);
        }

        $this->refresh_part_type_issue_types();
        $this->refresh_part_type_issue_type_steps();
        $this->refresh_step_parts();
    }

    public function get_unit_types() {
        return $this->unit_model->get_types_dropdown(false);
    }

    /**
     * @param int $unit_type_id
     * @param bool $complete_only If true, will only return part types that have a complete hierarchy recorded, down to required parts
     */
    public function get_part_types($unit_type_id=null, $complete_only=false) {
        $part_types = array();

        if (empty($unit_type_id) && !$complete_only) {
            return Diagnostic_Tree_Model::$part_types;
        }

        foreach (Diagnostic_Tree_Model::$part_types as $part_type) {

            if (!empty($unit_type_id) && $part_type->unit_type_id != $unit_type_id) {
                continue;
            }

            if ($complete_only) {
                $tree = $this->get_tree('name', true, $unit_type_id);

                foreach ($tree as $unit_type_key => $unit_type) {

                    foreach ($unit_type->part_types as $part_type_id => $part_type) {

                        if (!empty($part_type->part_type_issue_types)) {
                            $is_complete = true;

                            foreach ($part_type->part_type_issue_types as $part_type_issue_type) {
                                if (!empty($part_type_issue_type->steps)) {
                                    foreach ($part_type_issue_type->steps as $step) {
                                        if (empty($step->required_parts)) {
                                            $is_complete = false;
                                        }
                                    }
                                } else {
                                    $is_complete = false;
                                }
                            }

                            if ($is_complete) {

                                unset($part_type->part_type_issue_types);
                                $part_types[] = $part_type;
                            }
                        }
                    }
                }
            } else {
                $part_types[] = $part_type;
            }
        }

        return $part_types;
    }

    public function get_part_type_issue_type($part_type_id, $issue_type_id) {

        if (!empty(Diagnostic_Tree_Model::$part_type_issue_types[Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_id][$issue_type_id]])) {
            return Diagnostic_Tree_Model::$part_type_issue_types[Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_id][$issue_type_id]];
        }

        return null;
    }

    public function get_part_type_issue_types($part_type_id=null) {
        $part_type_issue_types = array();

        if (empty(Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_id])) {
            return array();
        }
        if (empty($part_type_id)) {
            return Diagnostic_Tree_Model::$part_type_issue_types;
        }

        foreach (Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_id] as $part_type_issue_type_key) {
            $part_type_issue_types[] = Diagnostic_Tree_Model::$part_type_issue_types[$part_type_issue_type_key];
        }

        return $part_type_issue_types;
    }

    public function get_part_type_issue_type_steps($part_type_issue_type_id=null) {
        $part_type_issue_type_steps = array();

        if (empty($part_type_issue_type_id)) {
            return Diagnostic_Tree_Model::$part_type_issue_type_steps;
        }

        if (!empty(Diagnostic_Tree_Model::$part_type_issue_type_steps_keys[$part_type_issue_type_id])) {
            foreach (Diagnostic_Tree_Model::$part_type_issue_type_steps_keys[$part_type_issue_type_id] as $part_type_issue_type_step_key) {
                $part_type_issue_type_steps[] = Diagnostic_Tree_Model::$part_type_issue_type_steps[$part_type_issue_type_step_key];
            }
        }

        return $part_type_issue_type_steps;

    }

    public function get_required_parts($part_type_issue_type_step_id=null) {
        $required_parts = array();

        if (empty($part_type_issue_type_step_id)) {
            return Diagnostic_Tree_Model::$step_parts;
        }

        if (!empty(Diagnostic_Tree_Model::$step_parts_keys[$part_type_issue_type_step_id])) {
            foreach (Diagnostic_Tree_Model::$step_parts_keys[$part_type_issue_type_step_id] as $step_part_key) {
                $required_parts[] = Diagnostic_Tree_Model::$step_parts[$step_part_key];
            }
        }

        return $required_parts;

    }

    public function get_tree($steps_order_by='name', $cached=true, $unit_type_id=null) {
        static $cached_tree;
        if (!empty($cached_tree) && $cached) {
            return $cached_tree;
        }

        $unit_types = $this->get_unit_types();

        foreach ($unit_types as $unit_type_key => $unit_type) {
            if (!is_null($unit_type_id) && $unit_type_id != $unit_type_key) {
                continue;
            }

            $tree[$unit_type_key] = new stdClass();
            $tree[$unit_type_key]->unit_type = $unit_type;

            $part_types = $this->get_part_types($unit_type_key);

            foreach ($part_types as $part_type_key => $part_type) {
                $part_type_issue_types = $this->get_part_type_issue_types($part_type->id);

                foreach ($part_type_issue_types as $part_type_issue_type_key => $part_type_issue_type) {
                    $steps = $this->get_part_type_issue_type_steps($part_type_issue_type->id, $steps_order_by);

                    foreach ($steps as $step_key => $step) {
                        $steps[$step_key]->required_parts = $this->get_required_parts($step->id);
                    }
                    $part_type_issue_types[$part_type_issue_type_key]->steps = $steps;
                }

                $part_types[$part_type_key]->part_type_issue_types = $part_type_issue_types;
            }

            $tree[$unit_type_key]->part_types = $part_types;
        }

        $cached_tree = $tree;
        return $tree;
    }

    public function get_estimate_tree($assignment_id) {
        $assignments = $this->assignment_model->get_assignment_group($assignment_id);
        $diagnostics_array = array();

        foreach ($assignments as $assignment) {
            if (!empty($assignment->diagnostic_id)) {
                $diagnostics_array[] = $this->diagnostic_model->get($assignment->diagnostic_id);
            }
        }

        $diagnostics = array();

        foreach ($diagnostics_array as $key => $diag) {
            $diagnostics[$diag->id] = (object) $this->diagnostic_model->get_values($diag->id);
            $diagnostics[$diag->id]->assignment = (object) $this->assignment_model->get($assignment_id);
            $diagnostics[$diag->id]->unit = (object) $this->unit_model->get_values($diagnostics[$diag->id]->assignment->unit_id);
            $diagnostics[$diag->id]->order = (object) $this->order_model->get_values($diagnostics[$diag->id]->assignment->order_id);
            $diagnostics[$diag->id]->diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $diag->id));

            foreach ($diagnostics[$diag->id]->diagnostic_issues as $key2 => $diag_issue) {
                $part_type_issue_type = $this->diagnostic_tree_model->get_part_type_issue_type($diag_issue->part_type_id, $diag_issue->issue_type_id);
                if (!empty($part_type_issue_type->id)) {
                    $diagnostics[$diag->id]->diagnostic_issues[$key2]->steps = $this->diagnostic_tree_model->get_part_type_issue_type_steps($part_type_issue_type->id);

                    foreach ($diagnostics[$diag->id]->diagnostic_issues[$key2]->steps as $key3 => $step) {
                        $diagnostics[$diag->id]->diagnostic_issues[$key2]->steps[$key3]->parts = $this->diagnostic_tree_model->get_required_parts($step->id);
                    }
                }
            }
        }

        return $diagnostics;
    }

    public function get_assignment_required_parts($assignment_id) {
        $assignment = $this->assignment_model->get($assignment_id);
        $diagnostic = $this->diagnostic_model->get_values($assignment->diagnostic_id);
        return $this->diagnostic_model->get_required_parts($diagnostic->id);
    }

    public function get_estimate_time($estimate_tree) {
        $total_time = 0;
        foreach ($estimate_tree as $diagnostic) {
            foreach ($diagnostic->diagnostic_issues as $diag_issue) {
                if (!empty($diag_issue->steps)) {
                    foreach ($diag_issue->steps as $step) {
                        if (!$step->required) {
                            continue;
                        }

                        foreach ($step->parts as $part) {
                            if ($part->name == 'Labour') {
                                $total_time += $part->quantity;
                            }
                        }
                    }
                }
            }
        }

        return $total_time;
    }

    public function add_part_type_issue_type($part_type_id, $issue_type_id) {
        if ($part_type_issue_type_id = $this->check_part_type_issue_type_exists($part_type_id, $issue_type_id)) {
            return $part_type_issue_type_id;
        }

        $this->db->insert('miniant_part_type_issue_types', compact('part_type_id', 'issue_type_id'));
        $part_type_issue_type = new stdClass();
        $part_type_issue_type->part_type_id = $part_type_id;
        $part_type_issue_type->issue_type_id = $issue_type_id;
        $part_type_issue_type->id = $this->db->insert_id();

        Diagnostic_Tree_Model::$part_type_issue_types[] = $part_type_issue_type;
        if (empty(Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_issue_type->part_type_id])) {
            Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_issue_type->part_type_id] = array();
        }
        end(Diagnostic_Tree_Model::$part_type_issue_types);
        Diagnostic_Tree_Model::$part_type_issue_types_keys[$part_type_issue_type->part_type_id][] = key(Diagnostic_Tree_Model::$part_type_issue_types);
        Diagnostic_Tree_Model::$part_type_issue_types[key(Diagnostic_Tree_Model::$part_type_issue_types)]->name = Diagnostic_Tree_Model::$issue_types[$part_type_issue_type->issue_type_id];
        return $part_type_issue_type->id;
    }

    public function check_part_type_issue_type_exists($part_type_id, $issue_type_id) {
        foreach (Diagnostic_Tree_Model::$part_type_issue_types as $part_type_issue_type) {
            if ($part_type_issue_type->part_type_id == $part_type_id && $part_type_issue_type->issue_type_id == $issue_type_id) {
                return $part_type_issue_type->id;
            }
        }
        return false;
    }

    public function add_part_type_issue_type_step($part_type_issue_type_step) {
        // Find out the highest sortorder of sibling steps
        $part_type_issue_type_step->sortorder = $this->get_highest_sortorder_for_steps($part_type_issue_type_step->part_type_issue_type_id) + 1;
        $query = $this->db->where(array('part_type_issue_type_id' => $part_type_issue_type_step->part_type_issue_type_id, 'step_id' => $part_type_issue_type_step->step_id), null, false)->get('miniant_part_type_issue_type_steps');

        if (!($row = $query->result())) {
            $this->db->insert('miniant_part_type_issue_type_steps', $part_type_issue_type_step);
            $part_type_issue_type_step->id = $this->db->insert_id();
            Diagnostic_Tree_Model::$part_type_issue_type_steps[] = $part_type_issue_type_step;
            if (empty(Diagnostic_Tree_Model::$part_type_issue_type_steps_keys[$part_type_issue_type_step->part_type_issue_type_id])) {
                Diagnostic_Tree_Model::$part_type_issue_type_steps_keys[$part_type_issue_type_step->part_type_issue_type_id] = array();
            }
            end(Diagnostic_Tree_Model::$part_type_issue_type_steps);
            Diagnostic_Tree_Model::$part_type_issue_type_steps_keys[$part_type_issue_type_step->part_type_issue_type_id][] = key(Diagnostic_Tree_Model::$part_type_issue_type_steps);
            Diagnostic_Tree_Model::$part_type_issue_type_steps[key(Diagnostic_Tree_Model::$part_type_issue_type_steps)]->name = Diagnostic_Tree_Model::$steps[$part_type_issue_type_step->step_id];
            return $part_type_issue_type_step->id;
        } else {
            return $row[0]->id;
        }
    }

    public function add_required_part($required_part) {
        $this->db->insert('miniant_step_parts', $required_part);
        $required_part->id = $this->db->insert_id();

        Diagnostic_Tree_Model::$step_parts[] = $required_part;
        if (empty(Diagnostic_Tree_Model::$step_parts_keys[$required_part->part_type_issue_type_step_id])) {
            Diagnostic_Tree_Model::$step_parts_keys[$required_part->part_type_issue_type_step_id] = array();
        }
        end(Diagnostic_Tree_Model::$step_parts);
        Diagnostic_Tree_Model::$step_parts_keys[$required_part->part_type_issue_type_step_id][] = key(Diagnostic_Tree_Model::$step_parts);
        Diagnostic_Tree_Model::$step_parts[key(Diagnostic_Tree_Model::$step_parts)]->name = Diagnostic_Tree_Model::$part_types_dropdown[$required_part->part_type_id];
        return $required_part->id;
    }

    public function delete_part_type($part_type_id) {
        return $this->part_type_model->delete($part_type_id);
    }

    public function delete_part_type_issue_type($part_type_issue_type_id) {
        return $this->db->delete('miniant_part_type_issue_types', array('id' => $part_type_issue_type_id));

    }

    public function delete_step($part_type_issue_type_step_id) {
        return $this->db->delete('miniant_part_type_issue_type_steps', array('id' => $part_type_issue_type_step_id));
    }

    public function delete_required_part($required_part_id) {
        return $this->db->delete('miniant_step_parts', array('id' => $required_part_id));
    }

    public function edit_part_type_issue_type($part_type_issue_type) {
        $this->db->where('id', $part_type_issue_type->id);
        unset($part_type_issue_type->id);
        return $this->db->update('miniant_part_type_issue_types', (array) $part_type_issue_type);
    }

    public function edit_part_type_issue_type_step($part_type_issue_type_step) {
        $this->db->where('id', $part_type_issue_type_step->id);
        unset($part_type_issue_type_step->id);
        return $this->db->update('miniant_part_type_issue_type_steps', (array) $part_type_issue_type_step);
    }

    public function edit_required_part($required_part) {
        $this->db->where('id', $required_part->id);
        unset($required_part->id);
        return $this->db->update('miniant_step_parts', (array) $required_part);
    }

    public function get_highest_sortorder_for_steps($part_type_issue_type_id) {
        $steps = $this->get_part_type_issue_type_steps($part_type_issue_type_id);
        $highest_sortorder = 0;

        if (empty($steps)) {
            return $highest_sortorder;
        }

        foreach ($steps as $key => $step) {
            if ($step->sortorder > $highest_sortorder) {
                $highest_sortorder = $step->sortorder;
            }
        }

        return $highest_sortorder;
    }

    public function refresh_part_type_issue_types() {
        Diagnostic_Tree_Model::$part_type_issue_types = $this->part_type_issue_type_model->get();
        if (!empty(Diagnostic_Tree_Model::$part_type_issue_types)) {
            $part_type_issue_types_keys = array();
            foreach (Diagnostic_Tree_Model::$part_type_issue_types as $key => $ptit) {
                if (empty($part_type_issue_types_keys[$ptit->part_type_id])) {
                    $part_type_issue_types_keys[$ptit->part_type_id] = array();
                }
                $part_type_issue_types_keys[$ptit->part_type_id][$ptit->issue_type_id] = $key;
                Diagnostic_Tree_Model::$part_type_issue_types[$key]->name = Diagnostic_Tree_Model::$issue_types[$ptit->issue_type_id];
            }
            Diagnostic_Tree_Model::$part_type_issue_types_keys = $part_type_issue_types_keys;
        }
    }

    public function refresh_part_type_issue_type_steps() {
        Diagnostic_Tree_Model::$part_type_issue_type_steps = $this->part_type_issue_type_step_model->get();
        if (!empty(Diagnostic_Tree_Model::$part_type_issue_type_steps)) {
            $part_type_issue_type_steps_keys = array();
            foreach (Diagnostic_Tree_Model::$part_type_issue_type_steps as $key => $ptits) {
                if (empty($part_type_issue_type_steps_keys[$ptits->part_type_issue_type_id])) {
                    $part_type_issue_type_steps_keys[$ptits->part_type_issue_type_id] = array();
                }
                $part_type_issue_type_steps_keys[$ptits->part_type_issue_type_id][] = $key;
                Diagnostic_Tree_Model::$part_type_issue_type_steps[$key]->name = Diagnostic_Tree_Model::$steps[$ptits->step_id];
            }
            Diagnostic_Tree_Model::$part_type_issue_type_steps_keys = $part_type_issue_type_steps_keys;
        }
    }

    public function refresh_step_parts() {
        Diagnostic_Tree_Model::$step_parts = $this->step_part_model->get();
        if (!empty(Diagnostic_Tree_Model::$step_parts)) {
            $step_parts_keys = array();
            foreach (Diagnostic_Tree_Model::$step_parts as $key => $step_part) {
                if (empty($step_parts_keys[$step_part->part_type_issue_type_step_id])) {
                    $step_parts_keys[$step_part->part_type_issue_type_step_id] = array();
                }
                $step_parts_keys[$step_part->part_type_issue_type_step_id][] = $key;
                Diagnostic_Tree_Model::$step_parts[$key]->name = Diagnostic_Tree_Model::$part_types_dropdown[$step_part->part_type_id];
            }
            Diagnostic_Tree_Model::$step_parts_keys = $step_parts_keys;
        }
    }
}
