<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Initial_setup extends CI_Migration {

    public function up() {

        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");

        $this->db->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

		$this->db->query("DROP TABLE IF EXISTS `accounts`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `abn` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_hold` tinyint(1) unsigned DEFAULT '0',
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;");

		$this->db->query("DROP TABLE IF EXISTS `addresses`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_box` int(8) unsigned DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `state` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `postcode` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `unit` varchar(16) DEFAULT NULL,
  `number` varchar(8) DEFAULT NULL,
  `type_id` tinyint(1) NOT NULL,
  `po_box_on` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;");


		$this->db->query("DROP TABLE IF EXISTS `capabilities`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `capabilities` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('read','write') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `dependson` int(3) unsigned DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `dependson` (`dependson`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=141 ;");

		$this->db->query("INSERT INTO `capabilities` (`id`, `name`, `description`, `type`, `dependson`, `creation_date`, `revision_date`, `status`) VALUES
(1, 'site:doanything', 'Do anything', 'write', NULL, 1376459828, 1376459828, 'Active'),
(2, 'users:doanything', 'Do anything to do with users', 'write', 1, 1376977609, 1376977609, 'Active'),
(3, 'users:editusers', 'Edit user accounts', 'write', 2, 1376977609, 1376977609, 'Active'),
(4, 'users:deleteusers', 'Delete user accounts', 'write', 2, 1376977609, 1376977609, 'Active'),
(5, 'users:viewusers', 'View user accounts', 'read', 3, 1376977609, 1376977609, 'Active'),
(6, 'users:writeusers', 'Create user accounts', 'write', 3, 1376977609, 1376977609, 'Active'),
(7, 'users:editroles', 'Edit roles', 'write', 2, 1376977609, 1376977609, 'Active'),
(8, 'users:deleteroles', 'Delete roles', 'write', 2, 1376977609, 1376977609, 'Active'),
(9, 'users:viewroles', 'View roles', 'read', 7, 1376977609, 1376977609, 'Active'),
(10, 'users:assignroles', 'Assign roles to users', 'write', 7, 1376977609, 1376977609, 'Active'),
(57, 'site:writeaccounts', 'Create new accounts', 'write', 63, 0, 0, 'Active'),
(63, 'site:editaccounts', 'Edit accounts', 'write', 1, 0, 0, 'Active'),
(64, 'site:deleteaccounts', 'Delete accounts', 'write', 63, 0, 0, 'Active'),
(65, 'site:viewaccounts', 'View accounts', 'read', 63, 0, 1392271671, 'Active'),
(83, 'users:editcontacts', 'Edit contacts', 'write', 2, 1392261495, 1392268805, 'Active'),
(84, 'users:viewcontacts', 'View contacts', 'read', 83, 1392261495, 1392261495, 'Active'),
(85, 'users:deletecontacts', 'Delete contacts', 'write', 2, 1392261547, 1392261547, 'Active'),
(86, 'users:writecontacts', 'Create contacts', 'write', 83, 1392261547, 1392261547, 'Active'),
(87, 'users:writecapabilities', 'Add new capabilities', 'read', 89, 1392270501, 1392271122, 'Active'),
(88, 'users:viewcapabilities', 'View capabilities', 'read', 87, 1392271089, 1392271089, 'Active'),
(89, 'users:editcapabilities', 'Edit capabilities', 'read', 2, 1392271106, 1392271106, 'Active'),
(90, 'users:deletecapabilities', 'Delete capabilities', 'read', 2, 1392271150, 1392271150, 'Active'),
(91, 'users:unassignroles', 'Remove role assignments from users', 'read', 10, 1392275193, 1392275193, 'Active'),
(100, 'users:editownaccount', 'Ability to edit one''s own account details', 'write', 3, 0, 0, 'Active'),
(125, 'reports:doanything', 'Do anything with all reports', 'read', 1, 1412650465, 1412650465, 'Active');");

		$this->db->query("DROP TABLE IF EXISTS `contacts`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_type_id` int(1) unsigned NOT NULL DEFAULT '1',
  `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone2` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile2` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `account_id` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `contact_type_id` (`contact_type_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;");

		$this->db->query("DROP TABLE IF EXISTS `document_statuses`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `document_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` int(10) unsigned NOT NULL,
  `status_id` smallint(3) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `document_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_id` (`document_id`,`status_id`,`document_type`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=207 ;");

		$this->db->query("DROP TABLE IF EXISTS `document_types_statuses`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `document_types_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` smallint(3) unsigned NOT NULL,
  `document_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned DEFAULT NULL,
  `revision_date` int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=46 ;");


		$this->db->query("DROP TABLE IF EXISTS `email_log`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `email_log` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `subject` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `from_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `to_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `recipients` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `attachments` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `host` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `port` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `auth` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `fromname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `addreplyto` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `errormsg` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `calling_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sender_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sender_id` smallint(5) unsigned DEFAULT NULL,
  `receiver_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `receiver_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_type` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

		$this->db->query("DROP TABLE IF EXISTS `events`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `events` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(2) unsigned DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `system` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=75 ;");

		$this->db->query("DROP TABLE IF EXISTS `events_log`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `events_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `event_id` int(3) unsigned NOT NULL,
  `document_id` int(10) unsigned NOT NULL,
  `notes` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=235 ;");

		$this->db->query("DROP TABLE IF EXISTS `messages`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `document_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`document_id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;");

		$this->db->query("DROP TABLE IF EXISTS `message_log`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `message_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `file` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `line` int(6) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `postdata` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


		$this->db->query("DROP TABLE IF EXISTS `priority_levels`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `priority_levels` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `value` int(2) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;");

		$this->db->query("INSERT INTO `priority_levels` (`id`, `name`, `value`, `creation_date`, `revision_date`, `status`) VALUES
(1, 'Low', 1, 1380678767, 1380678767, 'Active'),
(2, 'Medium', 2, 1380678767, 1380678767, 'Active'),
(3, 'High', 3, 1380678767, 1380678767, 'Active');");

		$this->db->query("DROP TABLE IF EXISTS `roles`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(2) unsigned DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;");

		$this->db->query("INSERT INTO `roles` (`id`, `name`, `description`, `creation_date`, `revision_date`, `status`) VALUES
(1, 'Site Admin', 'Administrator of the Intranet: can typically do and see everything', 1376531589, 1376531589, 'Active');");


		$this->db->query("DROP TABLE IF EXISTS `roles_capabilities`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `roles_capabilities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(2) unsigned NOT NULL,
  `capability_id` int(3) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `capability_id` (`capability_id`,`role_id`),
  KEY `role_id` (`role_id`),
  KEY `role_id_2` (`role_id`),
  KEY `role_id_3` (`role_id`),
  KEY `role_id_4` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=134 ;");

		$this->db->query("INSERT INTO `roles_capabilities` (`id`, `role_id`, `capability_id`, `creation_date`, `revision_date`, `status`) VALUES (1, 1, 1, 1376531589, 1376531589, 'Active');");

		$this->db->query("DROP TABLE IF EXISTS `settings`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `creation_date` int(10) unsigned DEFAULT NULL,
  `revision_date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;");


		$this->db->query("INSERT INTO `settings` (id, name, value) VALUES
(1, 'Site Name', 'Streamliner');");

		$this->db->query("DROP TABLE IF EXISTS `stages`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `stages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `in_checklist` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `granularity` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `creation_date` int(10) unsigned DEFAULT NULL,
  `revision_date` int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;");

		$this->db->query("DROP TABLE IF EXISTS `statuses`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `statuses` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` int(10) unsigned DEFAULT NULL,
  `revision_date` int(10) unsigned DEFAULT NULL,
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `sortorder` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=58 ;");

		$this->db->query("DROP TABLE IF EXISTS `status_events`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `status_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` smallint(3) unsigned NOT NULL,
  `event_id` int(3) unsigned NOT NULL,
  `state` int(1) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=135 ;");

		$this->db->query("DROP TABLE IF EXISTS `status_log`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `status_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` int(10) unsigned NOT NULL,
  `new_status_string` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `old_status_string` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `changed_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `document_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_request_id` (`document_id`),
  KEY `changed_by_id` (`changed_by_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;");
		$this->db->query("INSERT INTO `status_log` (`id`, `document_id`, `new_status_string`, `old_status_string`, `changed_by_id`, `creation_date`, `revision_date`, `status`, `document_type`) VALUES
(1, 1, '24,23', '', 7, 1421915782, 1421915782, 'Active', 'assignment');");

		$this->db->query("DROP TABLE IF EXISTS `street_types`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `street_types` (
  `id` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `abbreviation` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL,
  `revision_date` int(10) unsigned NOT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=204 ;");

		$this->db->query("INSERT INTO `street_types` (`id`, `name`, `abbreviation`, `creation_date`, `revision_date`, `status`) VALUES
(2, 'ACCESS', 'Accs', 0, 0, ''),
(3, 'ALLEY', 'Ally', 0, 0, ''),
(4, 'ALLEYWAY', 'Alwy', 0, 0, ''),
(5, 'AMBLE', 'Ambl', 0, 0, ''),
(6, 'ANCHORAGE', 'Ancg', 0, 0, ''),
(7, 'APPROACH', 'App', 0, 0, ''),
(8, 'ARCADE', 'Arc', 0, 0, ''),
(9, 'ARTERY', 'Art', 0, 0, ''),
(10, 'AVENUE', 'Ave', 0, 0, ''),
(11, 'BASIN', 'Basn', 0, 0, ''),
(12, 'BEACH', 'Bch', 0, 0, ''),
(13, 'BEND', 'Bend', 0, 0, ''),
(14, 'BLOCK', 'Blk', 0, 0, ''),
(15, 'BOULEVARD', 'Bvd', 0, 0, ''),
(16, 'BRACE', 'Brce', 0, 0, ''),
(17, 'BRAE', 'Brae', 0, 0, ''),
(18, 'BREAK', 'Brk', 0, 0, ''),
(19, 'BRIDGE', 'Bdge', 0, 0, ''),
(20, 'BROADWAY', 'Bdwy', 0, 0, ''),
(21, 'BROW', 'Brow', 0, 0, ''),
(22, 'BYPASS', 'Bypa', 0, 0, ''),
(23, 'BYWAY', 'Bywy', 0, 0, ''),
(24, 'CAUSEWAY', 'Caus', 0, 0, ''),
(25, 'CENTRE', 'Ctr', 0, 0, ''),
(26, 'CENTREWAY', 'Cnwy', 0, 0, ''),
(27, 'CHASE', 'Ch', 0, 0, ''),
(28, 'CIRCLE', 'Cir', 0, 0, ''),
(29, 'CIRCLET', 'Clt', 0, 0, ''),
(30, 'CIRCUIT', 'Cct', 0, 0, ''),
(31, 'CIRCUS', 'Crcs', 0, 0, ''),
(32, 'CLOSE', 'Cl', 0, 0, ''),
(33, 'COLONNADE', 'Clde', 0, 0, ''),
(34, 'COMMON', 'Cmmn', 0, 0, ''),
(35, 'CONCOURSE', 'Con', 0, 0, ''),
(36, 'COPSE', 'Cps', 0, 0, ''),
(37, 'CORNER', 'Cnr', 0, 0, ''),
(38, 'CORSO', 'Cso', 0, 0, ''),
(39, 'COURT', 'Ct', 0, 0, ''),
(40, 'COURTYARD', 'Ctyd', 0, 0, ''),
(41, 'COVE', 'Cove', 0, 0, ''),
(42, 'CRESCENT', 'Cres', 0, 0, ''),
(43, 'CREST', 'Crst', 0, 0, ''),
(44, 'CROSS', 'Crss', 0, 0, ''),
(45, 'CROSSING', 'Crsg', 0, 0, ''),
(46, 'CROSSROAD', 'Crd', 0, 0, ''),
(47, 'CROSSWAY', 'Cowy', 0, 0, ''),
(48, 'CRUISEWAY', 'Cuwy', 0, 0, ''),
(49, 'CUL-DE-SAC', 'Cds', 0, 0, ''),
(50, 'CUTTING', 'Cttg', 0, 0, ''),
(51, 'DALE', 'Dale', 0, 0, ''),
(52, 'DELL', 'Dell', 0, 0, ''),
(53, 'DEVIATION', 'Devn', 0, 0, ''),
(54, 'DIP', 'Dip', 0, 0, ''),
(55, 'DISTRIBUTOR', 'Dstr', 0, 0, ''),
(56, 'DRIVE', 'Dr', 0, 0, ''),
(57, 'DRIVEWAY', 'Drwy', 0, 0, ''),
(58, 'EDGE', 'Edge', 0, 0, ''),
(59, 'ELBOW', 'Elb', 0, 0, ''),
(60, 'END', 'End', 0, 0, ''),
(61, 'ENTRANCE', 'Ent', 0, 0, ''),
(62, 'ESPLANADE', 'Esp', 0, 0, ''),
(63, 'ESTATE', 'Est', 0, 0, ''),
(64, 'EXPRESSWAY', 'Exp', 0, 0, ''),
(65, 'EXTENSION', 'Extn', 0, 0, ''),
(66, 'FAIRWAY', 'Fawy', 0, 0, ''),
(67, 'FIRE TRACK', 'Ftrk', 0, 0, ''),
(68, 'FIRETRAIL', 'Fitr', 0, 0, ''),
(69, 'FLAT', 'Flat', 0, 0, ''),
(70, 'FOLLOW', 'Folw', 0, 0, ''),
(71, 'FOOTWAY', 'Ftwy', 0, 0, ''),
(72, 'FORESHORE', 'Fshr', 0, 0, ''),
(73, 'FORMATION', 'Form', 0, 0, ''),
(74, 'FREEWAY', 'Fwy', 0, 0, ''),
(75, 'FRONT', 'Frnt', 0, 0, ''),
(76, 'FRONTAGE', 'Frtg', 0, 0, ''),
(77, 'GAP', 'Gap', 0, 0, ''),
(78, 'GARDEN', 'Gdn', 0, 0, ''),
(79, 'GARDENS', 'Gdns', 0, 0, ''),
(80, 'GATE', 'Gte', 0, 0, ''),
(81, 'GATES', 'Gtes', 0, 0, ''),
(82, 'GLADE', 'Gld', 0, 0, ''),
(83, 'GLEN', 'Glen', 0, 0, ''),
(84, 'GRANGE', 'Gra', 0, 0, ''),
(85, 'GREEN', 'Grn', 0, 0, ''),
(86, 'GROUND', 'Grnd', 0, 0, ''),
(87, 'GROVE', 'Gr', 0, 0, ''),
(88, 'GULLY', 'Gly', 0, 0, ''),
(89, 'HEIGHTS', 'Hts', 0, 0, ''),
(90, 'HIGHROAD', 'Hrd', 0, 0, ''),
(91, 'HIGHWAY', 'Hwy', 0, 0, ''),
(92, 'HILL', 'Hill', 0, 0, ''),
(93, 'INTERCHANGE', 'Intg', 0, 0, ''),
(94, 'INTERSECTION', 'Intn', 0, 0, ''),
(95, 'JUNCTION', 'Jnc', 0, 0, ''),
(96, 'KEY', 'Key', 0, 0, ''),
(97, 'LANDING', 'Ldg', 0, 0, ''),
(98, 'LANE', 'Lane', 0, 0, ''),
(99, 'LANEWAY', 'Lnwy', 0, 0, ''),
(100, 'LEES', 'Lees', 0, 0, ''),
(101, 'LINE', 'Line', 0, 0, ''),
(102, 'LINK', 'Link', 0, 0, ''),
(103, 'LITTLE', 'Lt', 0, 0, ''),
(104, 'LOOKOUT', 'Lkt', 0, 0, ''),
(105, 'LOOP', 'Loop', 0, 0, ''),
(106, 'LOWER', 'Lwr', 0, 0, ''),
(107, 'MALL', 'Mall', 0, 0, ''),
(108, 'MEANDER', 'Mndr', 0, 0, ''),
(109, 'MEW', 'Mew', 0, 0, ''),
(110, 'MEWS', 'Mews', 0, 0, ''),
(111, 'MOTORWAY', 'Mwy', 0, 0, ''),
(112, 'MOUNT', 'Mt', 0, 0, ''),
(113, 'NOOK', 'Nook', 0, 0, ''),
(114, 'OUTLOOK', 'Otlk', 0, 0, ''),
(115, 'PARADE', 'Pde', 0, 0, ''),
(116, 'PARK', 'Park', 0, 0, ''),
(117, 'PARKLANDS', 'Pkld', 0, 0, ''),
(118, 'PARKWAY', 'Pkwy', 0, 0, ''),
(119, 'PART', 'Part', 0, 0, ''),
(120, 'PASS', 'Pass', 0, 0, ''),
(121, 'PATH', 'Path', 0, 0, ''),
(122, 'PATHWAY', 'Phwy', 0, 0, ''),
(123, 'PIAZZA', 'Piaz', 0, 0, ''),
(124, 'PLACE', 'Pl', 0, 0, ''),
(125, 'PLATEAU', 'Plat', 0, 0, ''),
(126, 'PLAZA', 'Plza', 0, 0, ''),
(127, 'POCKET', 'Pkt', 0, 0, ''),
(128, 'POINT', 'Pnt', 0, 0, ''),
(129, 'PORT', 'Port', 0, 0, ''),
(130, 'PROMENADE', 'Prom', 0, 0, ''),
(131, 'QUAD', 'Quad', 0, 0, ''),
(132, 'QUADRANGLE', 'Qdgl', 0, 0, ''),
(133, 'QUADRANT', 'Qdrt', 0, 0, ''),
(134, 'QUAY', 'Qy', 0, 0, ''),
(135, 'QUAYS', 'Qys', 0, 0, ''),
(136, 'RAMBLE', 'Rmbl', 0, 0, ''),
(137, 'RAMP', 'Ramp', 0, 0, ''),
(138, 'RANGE', 'Rnge', 0, 0, ''),
(139, 'REACH', 'Rch', 0, 0, ''),
(140, 'RESERVE', 'Res', 0, 0, ''),
(141, 'REST', 'Rest', 0, 0, ''),
(142, 'RETREAT', 'Rtt', 0, 0, ''),
(143, 'RIDE', 'Ride', 0, 0, ''),
(144, 'RIDGE', 'Rdge', 0, 0, ''),
(145, 'RIDGEWAY', 'Rgwy', 0, 0, ''),
(146, 'RIGHT OF WAY', 'Rowy', 0, 0, ''),
(147, 'RING', 'Ring', 0, 0, ''),
(148, 'RISE', 'Rise', 0, 0, ''),
(149, 'RIVER', 'Rvr', 0, 0, ''),
(150, 'RIVERWAY', 'Rvwy', 0, 0, ''),
(151, 'RIVIERA', 'Rvra', 0, 0, ''),
(152, 'ROAD', 'Rd', 0, 0, ''),
(153, 'ROADS', 'Rds', 0, 0, ''),
(154, 'ROADSIDE', 'Rdsd', 0, 0, ''),
(155, 'ROADWAY', 'Rdwy', 0, 0, ''),
(156, 'RONDE', 'Rnde', 0, 0, ''),
(157, 'ROSEBOWL', 'Rsbl', 0, 0, ''),
(158, 'ROTARY', 'Rty', 0, 0, ''),
(159, 'ROUND', 'Rnd', 0, 0, ''),
(160, 'ROUTE', 'Rte', 0, 0, ''),
(161, 'ROW', 'Row', 0, 0, ''),
(162, 'RUE', 'Rue', 0, 0, ''),
(163, 'RUN', 'Run', 0, 0, ''),
(164, 'SERVICE WAY', 'Swy', 0, 0, ''),
(165, 'SIDING', 'Sdng', 0, 0, ''),
(166, 'SLOPE', 'Slpe', 0, 0, ''),
(167, 'SOUND', 'Snd', 0, 0, ''),
(168, 'SPUR', 'Spur', 0, 0, ''),
(169, 'SQUARE', 'Sq', 0, 0, ''),
(170, 'STAIRS', 'Strs', 0, 0, ''),
(171, 'STATE HIGHWAY', 'Shwy', 0, 0, ''),
(172, 'STEPS', 'Stps', 0, 0, ''),
(173, 'STRAND', 'Stra', 0, 0, ''),
(174, 'STREET', 'St', 0, 0, ''),
(175, 'STRIP', 'Strp', 0, 0, ''),
(176, 'SUBWAY', 'Sbwy', 0, 0, ''),
(177, 'TARN', 'Tarn', 0, 0, ''),
(178, 'TERRACE', 'Tce', 0, 0, ''),
(179, 'THOROUGHFARE', 'Thor', 0, 0, ''),
(180, 'TOLLWAY', 'Tlwy', 0, 0, ''),
(181, 'TOP', 'Top', 0, 0, ''),
(182, 'TOR', 'Tor', 0, 0, ''),
(183, 'TOWERS', 'Twrs', 0, 0, ''),
(184, 'TRACK', 'Trk', 0, 0, ''),
(185, 'TRAIL', 'Trl', 0, 0, ''),
(186, 'TRAILER', 'Trlr', 0, 0, ''),
(187, 'TRIANGLE', 'Tri', 0, 0, ''),
(188, 'TRUNKWAY', 'Tkwy', 0, 0, ''),
(189, 'TURN', 'Turn', 0, 0, ''),
(190, 'UNDERPASS', 'Upas', 0, 0, ''),
(191, 'UPPER', 'Upr', 0, 0, ''),
(192, 'VALE', 'Vale', 0, 0, ''),
(193, 'VIADUCT', 'Vdct', 0, 0, ''),
(194, 'VIEW', 'View', 0, 0, ''),
(195, 'VILLAS', 'Vlls', 0, 0, ''),
(196, 'VISTA', 'Vsta', 0, 0, ''),
(197, 'WADE', 'Wade', 0, 0, ''),
(198, 'WALK', 'Walk', 0, 0, ''),
(199, 'WALKWAY', 'Wkwy', 0, 0, ''),
(200, 'WAY', 'Way', 0, 0, ''),
(201, 'WHARF', 'Whrf', 0, 0, ''),
(202, 'WYND', 'Wynd', 0, 0, ''),
(203, 'YARD', 'Yard', 0, 0, '');");


		$this->db->query("DROP TABLE IF EXISTS `types`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `types` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `entity` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `colour` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;");
        $this->db->query("INSERT INTO `types` (`id`, `name`, `description`, `entity`, `creation_date`, `revision_date`, `status`, `colour`) VALUES
(1, 'Billing', '', 'contact', 0, 0, 'Active', NULL),
(6, 'Supplier', '', 'contact', 0, 0, 'Active', NULL),
(8, 'Billing', '', 'address', 0, 0, 'Active', NULL),
(9, 'Site', '', 'address', 0, 0, 'Active', NULL),
(10, 'Parts pickup', '', 'address', 0, 0, 'Active', NULL),
(11, 'Project office', '', 'address', 0, 0, 'Active', NULL),
(12, 'Agent', '', 'address', 0, 0, 'Active', NULL),
(34, 'Shipping', '', 'address', 1391482828, 1391482828, 'Active', NULL);");


		$this->db->query("DROP TABLE IF EXISTS `users`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '0',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '0',
  `temp_password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `registerkey` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `signature` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `cc_number` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_type` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'visa',
  `cc_expiry` int(10) unsigned DEFAULT NULL,
  `cc_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `type` varchar(32) NOT NULL DEFAULT 'contact',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;");
		$this->db->query("INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `password`, `temp_password`, `registerkey`, `signature`, `cc_number`, `cc_type`, `cc_expiry`, `cc_name`, `creation_date`, `revision_date`, `status`, `type`) VALUES
(1, 'Nicolas', 'Connault', 'nicolasconnault', 'sha256:1000:K6c+YWzx+okNiVxrZfVG1NUvumwMIkBf:TcBF3512GebG0JjUBlwjN3NpZDwf3h/1', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1376460021, 1408323366, 'Active', 'contact');");

		$this->db->query("DROP TABLE IF EXISTS `signatures`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `signatures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(3) unsigned NOT NULL,
  `signature_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `signature_date` int(10) unsigned NOT NULL,
  `signature_text` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL,
  `revision_date` int(10) unsigned NOT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=54 ;");

		$this->db->query("DROP TABLE IF EXISTS `users_roles`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `users_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role_id` int(2) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;");

		$this->db->query("INSERT INTO `users_roles` (`id`, `user_id`, `role_id`, `creation_date`, `revision_date`, `status`) VALUES
(1, 1, 1, 1376531589, 1376531589, 'Active');");

		$this->db->query("DROP TABLE IF EXISTS `user_contacts`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `user_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `contact` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `default_choice` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `receive_notifications` int(1) NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;");
		$this->db->query("INSERT INTO `user_contacts` (`id`, `user_id`, `type`, `contact`, `default_choice`, `creation_date`, `revision_date`, `receive_notifications`, `status`) VALUES
(3, 1, 1, 'nicolasconnault@gmail.com', 1, 1379317455, 1379473399, 0, 'Active'),
(4, 1, 2, '098995494', 1, 1379317459, 1379473402, 0, 'Active');");

		$this->db->query("DROP VIEW IF EXISTS `Verbose_Stages`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `Verbose_Stages` (
`id` int(10) unsigned
,`Workflow` varchar(255)
,`stage_id` int(10) unsigned
,`number` tinyint(2) unsigned
,`Stage` varchar(255)
,`Next Stage id` int(10) unsigned
,`next number` tinyint(2) unsigned
,`Next stage` varchar(255)
);");
		$this->db->query("DROP TABLE IF EXISTS `workflows`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `workflows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;");

		$this->db->query("DROP TABLE IF EXISTS `workflow_stages`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `workflow_stages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` int(10) unsigned NOT NULL,
  `stage_id` int(10) unsigned NOT NULL,
  `stage_number` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `extra_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senior_technician_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `required` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `creation_date` int(10) unsigned DEFAULT NULL,
  `revision_date` int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `stage_id` (`stage_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=91 ;");

		$this->db->query("DROP TABLE IF EXISTS `workflow_stage_stages`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `workflow_stage_stages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_stage_id` int(10) unsigned NOT NULL,
  `next_stage_id` int(10) unsigned NOT NULL,
  `creation_date` int(10) unsigned DEFAULT NULL,
  `revision_date` int(10) unsigned DEFAULT NULL,
  `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `parent_stage_id` (`workflow_stage_id`),
  KEY `next_stage_id` (`next_stage_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=221 ;");

		$this->db->query("DROP TABLE IF EXISTS `work_time_log`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `work_time_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `document_id` int(10) unsigned DEFAULT NULL,
  `end_date` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

		$this->db->query("ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `capabilities`
  ADD CONSTRAINT `capabilities_ibfk_1` FOREIGN KEY (`dependson`) REFERENCES `capabilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`contact_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `document_statuses`
  ADD CONSTRAINT `document_statuses_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `document_types_statuses`
  ADD CONSTRAINT `document_types_statuses_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `events_log`
  ADD CONSTRAINT `events_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_log_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `message_log`
  ADD CONSTRAINT `message_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `roles_capabilities`
  ADD CONSTRAINT `roles_capabilities_ibfk_1` FOREIGN KEY (`capability_id`) REFERENCES `capabilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `roles_capabilities_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `status_events`
  ADD CONSTRAINT `status_events_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `status_events_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `users_roles`
  ADD CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `workflow_stages`
  ADD CONSTRAINT `workflow_stages_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_stages_ibfk_2` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `signatures`
  ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

		$this->db->query("ALTER TABLE `workflow_stage_stages`
  ADD CONSTRAINT `workflow_stage_stages_ibfk_2` FOREIGN KEY (`next_stage_id`) REFERENCES `workflow_stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflow_stage_stages_ibfk_3` FOREIGN KEY (`workflow_stage_id`) REFERENCES `workflow_stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1;");

    }

    public function down() {

    }
}
