<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_diagnostic_time_to_servicequotes extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `miniant_servicequotes` ADD `diagnostic_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `sent_date`");
        $this->db->query("ALTER TABLE `miniant_servicequotes` ADD `diagnostic_cost` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `diagnostic_time`");
    }

    public function down() {

    }
}
