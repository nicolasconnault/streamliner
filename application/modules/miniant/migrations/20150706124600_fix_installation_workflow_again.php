<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fix_installation_workflow_again extends CI_Migration {

    public function up() {
        $this->db->query("UPDATE `streamliner_miniant`.`workflow_stage_stages` SET next_stage_id = 25 WHERE workflow_stage_id = 24 AND next_stage_id = 61;");
        $this->db->query("UPDATE `streamliner_miniant`.`workflow_stage_stages` SET next_stage_id = 26, workflow_stage_id = 24 WHERE workflow_stage_id = 25 AND next_stage_id = 61;");
        $this->db->query("INSERT INTO `streamliner_miniant`.`workflow_stage_stages` (`workflow_stage_id`, `next_stage_id`) VALUES ('25', '61'), ('61', '26');");
    }

    public function down() {

    }
}
