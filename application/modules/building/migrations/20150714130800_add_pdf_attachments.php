<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_pdf_attachments extends CI_Migration {

    public function up() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `building_job_site_attachments` (
`id` int(10) unsigned NOT NULL,
  `job_site_id` int(10) unsigned NOT NULL,
  `directory` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'job_site_attachments',
  `filename_original` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `file_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_extension` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_size` float(16,2) unsigned DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;
");

        $this->db->query("ALTER TABLE `building_job_site_attachments` ADD PRIMARY KEY (`id`), ADD KEY `order_id` (`job_site_id`);");
        $this->db->query("ALTER TABLE `building_job_site_attachments` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
        $this->db->query("ALTER TABLE `building_job_site_attachments` ADD CONSTRAINT `job_site_attachments_ibfk_1` FOREIGN KEY (`job_site_id`) REFERENCES `building_job_sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    }

    public function down() {

    }
}







