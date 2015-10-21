<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_last_name_to_signatures extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `signatures` CHANGE `signature_name` `first_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
        $this->db->query("ALTER TABLE `signatures` ADD `last_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `first_name`;");
    }

    public function down() {

    }
}
