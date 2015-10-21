<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_tradesmen extends CI_Migration {

    public function up() {
        $this->db->query("DROP TABLE IF EXISTS `building_tradesmen`;");
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `building_tradesmen` (
            `id` int(10) unsigned NOT NULL,
              `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `mobile` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
              `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `creation_date` int(10) unsigned DEFAULT NULL,
              `revision_date` int(10) unsigned DEFAULT NULL,
              `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
            ");

        $this->db->query(" ALTER TABLE `building_tradesmen` ADD PRIMARY KEY (`id`);");
        $this->db->query(" ALTER TABLE `building_tradesmen` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");

        $this->db->query("ALTER TABLE `building_bookings` ADD `tradesman_id` INT(10) UNSIGNED NOT NULL AFTER `job_site_id`, ADD INDEX (`tradesman_id`) ;");
    }

    public function down() {
        $this->db->query("DROP TABLE IF EXISTS `building_tradesmen`;");
        $this->db->query("ALTER TABLE `building_bookings` DROP `tradesman_id`;");
    }
}
