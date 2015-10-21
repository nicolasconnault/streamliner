<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_tradesman_type extends CI_Migration {

    public function up() {

        $this->db->query("ALTER TABLE `building_bookings` CHANGE `tradesman_id` `tradesman_id` INT(10) UNSIGNED NULL DEFAULT NULL;");
        $this->db->query("ALTER TABLE `building_bookings` ADD `tradesman_type_id` INT(5) UNSIGNED NOT NULL AFTER `job_site_id`, ADD INDEX (`tradesman_type_id`) ;");
    }

    public function down() {
        $this->db->query("ALTER TABLE `building_bookings` DROP `tradesman_type_id`;");
        $this->db->query("ALTER TABLE `building_bookings` CHANGE `tradesman_id` `tradesman_id` INT(10) UNSIGNED NOT NULL;");
    }
}
