<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_user_logins extends CI_Migration {

    public function up() {
		$this->db->query("
            CREATE TABLE IF NOT EXISTS `user_logins` (
            `id` int(10) unsigned NOT NULL,
              `user_id` int(10) unsigned NOT NULL,
              `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `last_page_load` int(10) unsigned NOT NULL,
              `creation_date` int(10) unsigned NOT NULL,
              `revision_date` int(10) unsigned NOT NULL,
              `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
        ");


		$this->db->query("
            ALTER TABLE `user_logins`
             ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);
        ");


		$this->db->query("
            ALTER TABLE `user_logins`
            MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
        ");

    }

    public function down() {
        $this->dbforge->drop_table('user_logins');
    }
}
