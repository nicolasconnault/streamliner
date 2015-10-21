<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_initial_building_setup extends CI_Migration {

    public function up() {
        $this->load->model('document_types_statuses_model');
        $this->load->model('status_model');
        $this->load->helper('secure_hash');
		$this->db->query("DELETE FROM statuses WHERE 1");
        $this->document_types_statuses_model->add(
            array('status_id' => $this->status_model->add(array('name' => 'OPEN', 'description' => 'The [[document]] is now open')), 'document_type' => 'job_site','sortorder' => 1));
        $this->document_types_statuses_model->add(
            array('status_id' => $this->status_model->add(array('name' => 'COMPLETED', 'description' => 'Work on this [[document]] is now completed')), 'document_type' => 'job_site','sortorder' => 2));
        $this->document_types_statuses_model->add(
            array('status_id' => $this->status_model->add(array('name' => 'ARCHIVED', 'description' => 'This [[document]] is archived')), 'document_type' => 'job_site','sortorder' => 3));
        $this->document_types_statuses_model->add(
            array('status_id' => $this->status_model->add(array('name' => 'CANCELLED', 'description' => 'The [[document]] has been cancelled')), 'document_type' => 'job_site','sortorder' => 4));

        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");

		$this->db->query("DROP TABLE IF EXISTS building_bookings;");
		$this->db->query("CREATE TABLE IF NOT EXISTS building_bookings (
id int(10) unsigned NOT NULL,
  job_site_id int(10) unsigned NOT NULL,
  booking_date int(10) unsigned NOT NULL,
  message text COLLATE utf8_unicode_ci NOT NULL,
  creation_date int(10) unsigned DEFAULT NULL,
  revision_date int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  confirmed int(11) DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;");

		$this->db->query("DROP TABLE IF EXISTS building_booking_recipients;");
		$this->db->query("CREATE TABLE IF NOT EXISTS building_booking_recipients (
id int(10) unsigned NOT NULL,
  booking_id int(11) DEFAULT NULL,
  user_id int(10) unsigned NOT NULL,
  creation_date int(10) unsigned DEFAULT NULL,
  revision_date int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;");

		$this->db->query("DROP TABLE IF EXISTS building_job_sites;");
		$this->db->query("CREATE TABLE IF NOT EXISTS building_job_sites (
id int(10) unsigned NOT NULL,
  unit varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  number varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  street varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  street_type varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  city varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  state varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'WA',
  postcode int(5) unsigned NOT NULL,
  creation_date int(10) unsigned DEFAULT NULL,
  revision_date int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;");

		$this->db->query("DROP TABLE IF EXISTS building_migrations;");
		$this->db->query("CREATE TABLE IF NOT EXISTS building_migrations (
  module varchar(20) NOT NULL,
  version bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


		$this->db->query("ALTER TABLE building_bookings
 ADD PRIMARY KEY (id), ADD KEY job_site_id (job_site_id);");

		$this->db->query("ALTER TABLE building_booking_recipients
 ADD PRIMARY KEY (id), ADD KEY booking_request_id (booking_id);");

		$this->db->query("ALTER TABLE building_job_sites
 ADD PRIMARY KEY (id);");


		$this->db->query("ALTER TABLE building_bookings
MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;");
		$this->db->query("ALTER TABLE building_booking_recipients
MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=30;");
		$this->db->query("ALTER TABLE building_job_sites
MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");

        $this->db->query("DELETE FROM capabilities WHERE name LIKE 'building:%';");

        $role_id = $this->role_model->add(array('name' => 'Manager', 'description' => 'Manager', 'parent_id' => 1));
        $cap1_id = $this->capability_model->add(array('name' => 'building:editstatuses', 'description' => 'Edit the statuses of building sites', 'type' => 'write', 'dependson' => 1));
        $cap2_id = $this->capability_model->add(array('name' => 'building:editjobsites', 'description' => 'Edit job sites', 'type' => 'write', 'dependson' => $cap1_id));
        $cap3_id = $this->capability_model->add(array('name' => 'building:writejobsites', 'description' => 'Create job sites', 'type' => 'write', 'dependson' => $cap2_id));
        $cap4_id = $this->capability_model->add(array('name' => 'building:viewjobsites', 'description' => 'View job sites', 'type' => 'read', 'dependson' => $cap3_id));
        $cap5_id = $this->capability_model->add(array('name' => 'building:deletejobsites', 'description' => 'Delete job sites', 'type' => 'write', 'dependson' => $cap2_id));

        $this->db->query("INSERT INTO roles_capabilities (role_id, capability_id) VALUES ($role_id, $cap1_id)");

        $user1_id = $this->user_model->add(array('first_name' => 'Demo 1', 'last_name' => 'User', 'username' => 'demo', 'password' => create_hash('password'), 'type' => 'staff'));
        $user2_id = $this->user_model->add(array('first_name' => 'Demo 2', 'last_name' => 'User', 'username' => 'demo2', 'password' => create_hash('password'), 'type' => 'staff'));

        $this->db->query("INSERT INTO users_roles (role_id, user_id) VALUES ($role_id, $user1_id), ($role_id, $user2_id)");
    }

    public function down() {

    }
}
