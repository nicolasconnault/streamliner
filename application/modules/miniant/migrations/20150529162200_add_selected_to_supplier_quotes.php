<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_selected_to_supplier_quotes extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `miniant_supplier_quotes` ADD `selected` INT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `availability`");
    }

    public function down() {

    }
}
