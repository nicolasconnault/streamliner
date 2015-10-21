<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fix_installation_workflow_again2 extends CI_Migration {

    public function up() {
        $this->db->query("INSERT INTO `streamliner_miniant`.`workflow_stage_stages` (`workflow_stage_id`, `next_stage_id`) VALUES ('27', '28'), ('27', '79'), (27, 26), (28, 26), (28, 32), (30, 31);");
    }

    public function down() {

    }
}
