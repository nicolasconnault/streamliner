<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fix_installation_workflow extends CI_Migration {

    public function up() {
        $this->load->model('workflow_stage_model');
        $this->load->model('workflow_stage_stage_model');
        $workflow_stages = $this->workflow_stage_model->get(array('workflow_id' => 2));
        foreach ($workflow_stages as $workflow_stage) {
            $this->workflow_stage_stage_model->delete(array('workflow_stage_id' => $workflow_stage->id));
        }

        $mappings = array(
            24 => 25,
            24 => 61,
            25 => 61,
            26 => 27,
            27 => 28,
            27 => 79,
            27 => 61,
            27 => 32,
            28 => 61,
            28 => 32,
            28 => 79,
            29 => 30,
            30 => 31,
            30 => 32,
            31 => 85,
            61 => 26,
            79 => 29,
            85 => 32
            );
        foreach ($mappings as $workflow_stage_id => $next_stage_id) {
            $this->workflow_stage_stage_model->add(compact('workflow_stage_id', 'next_stage_id'));
        }
    }

    public function down() {

    }
}
