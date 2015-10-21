<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_instructions_to_text extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `miniant_part_types` CHANGE `instructions` `instructions` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ;");
    }

    public function down() {

    }
}
