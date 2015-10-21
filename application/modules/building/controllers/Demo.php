<?php

class Demo extends MY_Controller {
    function __construct() {
        parent::__construct(false);
    }

    public function run_tasks() {
        $class = new ReflectionClass('Demo');
        $methods = $class->getMethods(ReflectionMethod::IS_PRIVATE);

        foreach ($methods as $method) {
            echo "Starting task {$method->name}...\n";
            $this->{$method->name}();
            echo "Completed task {$method->name}\n";
        }
    }

    private function regenerate_database() {
        // Only run this task if there are no active (< 10m) sessions
        $user_logins = $this->user_login_model->get(array('status' => 'Active'));
        $no_active_sessions = true;

        if (!empty($user_logins)) {
            foreach ($user_logins as $user_login) {
                if (time() - $user_login->last_page_load < 600) {
                    $no_active_sessions = false;
                }
            }
        }

        if (!$no_active_sessions) {
            // TODO OR if no regeneration has happened in the last 24 hours (needs a table for recording regens)
            die('Live session detected, aborting database regeneration!');
        }

        // Truncate all tables then rebuild using SQL dump.


        $sql = "SET FOREIGN_KEY_CHECKS=0;";
        $this->db->query($sql);

        $sql = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $this->db->query($sql);

        $sql = "SET time_zone = '+00:00';";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `building_bookings`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `building_bookings` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `job_site_id` int(10) unsigned NOT NULL,
          `tradesman_type_id` int(5) unsigned NOT NULL,
          `tradesman_id` int(10) unsigned DEFAULT NULL,
          `booking_date` int(10) unsigned NOT NULL,
          `message` text COLLATE utf8_unicode_ci NOT NULL,
          `creation_date` int(10) unsigned DEFAULT NULL,
          `revision_date` int(10) unsigned DEFAULT NULL,
          `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          `confirmed` int(11) DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `job_site_id` (`job_site_id`),
          KEY `tradesman_id` (`tradesman_id`),
          KEY `tradesman_type_id` (`tradesman_type_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=20 ;";
        $this->db->query($sql);

        $sql = "INSERT INTO `building_bookings` (`id`, `job_site_id`, `tradesman_type_id`, `tradesman_id`, `booking_date`, `message`, `creation_date`, `revision_date`, `status`, `confirmed`) VALUES
        (15, 3, 52, 0, 1437408000, '', 1437451218, 1437459491, 'Active', 1),
        (16, 3, 74, 0, 1437782400, 'Call John about week-end availability', 1437459482, 1437459486, 'Active', 0),
        (17, 3, 48, 0, 1437494400, '', 1437459519, 1437459519, 'Active', 0),
        (18, 3, 87, 0, 1437436800, 'Site cleanup', 1437459527, 1437468111, 'Active', 0),
        (19, 3, 45, 12, 1437580800, '', 1437459566, 1437459566, 'Active', 1);";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `building_job_sites`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `building_job_sites` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `unit` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
          `number` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
          `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `street_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `state` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'WA',
          `postcode` int(5) unsigned NOT NULL,
          `creation_date` int(10) unsigned DEFAULT NULL,
          `revision_date` int(10) unsigned DEFAULT NULL,
          `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;";
        $this->db->query($sql);

        $sql = "INSERT INTO `building_job_sites` (`id`, `unit`, `number`, `street`, `street_type`, `city`, `state`, `postcode`, `creation_date`, `revision_date`, `status`) VALUES
        (2, '', '70', 'Connor', 'STREET', 'Canning', 'WA', 6001, 1437446617, 1437446617, 'Active'),
        (3, '', 'Lot 64', 'Henry', 'ROAD', 'Bunbury', 'WA', 6210, 1437446654, 1437446654, 'Active');";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `building_job_site_attachments`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `building_job_site_attachments` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
          `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          PRIMARY KEY (`id`),
          KEY `order_id` (`job_site_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=6 ;";
        $this->db->query($sql);

        $sql = "INSERT INTO `building_job_site_attachments` (`id`, `job_site_id`, `directory`, `filename_original`, `hash`, `description`, `file_type`, `file_extension`, `file_size`, `creation_date`, `revision_date`, `status`) VALUES
        (3, 3, 'job_site_drawings/3', 'FloorPlanOfMainLevel-MysteryCreekHouse.jpg', '7030ace228cab935e9ba6bd84c086d33.jpg', 'Floor plan', 'image/jpeg', '.jpg', 82.09, 1437447638, 1437447638, 'Active'),
        (4, 2, 'job_site_drawings/2', 'samruddhi.jpg', 'e4b082c5500d2e27cc000d506b615386.jpg', 'Floor plan', 'image/jpeg', '.jpg', 7.97, 1437448308, 1437448308, 'Active'),
        (5, 2, 'job_site_drawings/2', '03_Landscape-Plan.jpeg', '8c2abb5a893c699753c9f2c5dd58a044.jpeg', 'Test floor plan', 'image/jpeg', '.jpeg', 780.34, 1437448328, 1437448328, 'Active');";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `building_tradesmen`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `building_tradesmen` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `type_id` int(3) unsigned NOT NULL,
          `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `mobile` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
          `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `creation_date` int(10) unsigned DEFAULT NULL,
          `revision_date` int(10) unsigned DEFAULT NULL,
          `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          PRIMARY KEY (`id`),
          UNIQUE KEY `type_id_2` (`type_id`,`name`),
          KEY `type_id` (`type_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=56 ;";
        $this->db->query($sql);

        $sql = "INSERT INTO `building_tradesmen` (`id`, `type_id`, `name`, `mobile`, `email`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 88, 'Picko''s Bobcat Hire', '97958055', '', 1437450757, 1437450757, 'Active'),
        (2, 48, 'Holdens Electrical Contracting', '94937279', '', 1437450802, 1437450802, 'Active'),
        (3, 74, 'The Guy Earthmoving Contractors', '0419222555', '', 1437450826, 1437450826, 'Active'),
        (4, 74, 'Brooks Hire Service Pty Ltd', '1300276657', '', 1437450851, 1437450851, 'Active'),
        (5, 88, 'Metro Bobcats & Mini Excavators', '0407 988 130', '', 1437450873, 1437450873, 'Active'),
        (6, 70, 'Westral Home Improvements', '6466 0708', '', 1437450909, 1437450909, 'Active'),
        (7, 70, 'Abbey Blinds & Curtains', '9443 6572', '', 1437450928, 1437450928, 'Active'),
        (8, 52, 'Midland Brick', '9274 4627', '', 1437450965, 1437450965, 'Active'),
        (9, 49, 'Precision Landscape Construction Pty Ltd', '0439 579 350', '', 1437451000, 1437451000, 'Active'),
        (10, 52, 'Precision Landscape Construction Pty Ltd', '0439 579 350', '', 1437451017, 1437451017, 'Active'),
        (11, 88, 'Precision Landscape Construction Pty Ltd', '0439 579 350', '', 1437451037, 1437451037, 'Active'),
        (12, 45, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (13, 46, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (14, 47, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (15, 48, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (16, 49, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (17, 50, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (18, 51, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (19, 52, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (20, 53, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (21, 54, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (22, 55, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (23, 56, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (24, 57, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (25, 58, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (26, 59, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (27, 60, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (28, 61, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (29, 62, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (30, 63, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (31, 64, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (32, 65, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (33, 66, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (34, 67, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (35, 68, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (36, 69, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (37, 70, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (38, 71, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (39, 72, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (40, 73, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (41, 74, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (42, 75, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (43, 76, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (44, 77, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (45, 78, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (46, 79, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (47, 80, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (48, 81, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (49, 82, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (50, 83, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (51, 84, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (52, 85, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (54, 87, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active'),
        (55, 88, 'All-around building services', '0435 999 999', 'enquiries@allaroundbuildingservices.com.au', NULL, NULL, 'Active');";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `capabilities`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `capabilities` (
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
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=162 ;";
        $this->db->query($sql);

        $sql = "INSERT INTO `capabilities` (`id`, `name`, `description`, `type`, `dependson`, `creation_date`, `revision_date`, `status`) VALUES
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
        (125, 'reports:doanything', 'Do anything with all reports', 'read', 1, 1412650465, 1412650465, 'Active'),
        (126, 'building:doanything', 'Do anything in the building module', 'write', 1, 1434977917, 1434977917, 'Active'),
        (127, 'building:edittradesmen', 'Edit tradesmen', 'write', 126, 1434977917, 1434977917, 'Active'),
        (128, 'building:writetradesmen', 'Create tradesmen', 'write', 127, 1434977917, 1434977917, 'Active'),
        (129, 'building:viewtradesmen', 'View tradesmen', 'read', 128, 1434977917, 1434977917, 'Active'),
        (130, 'building:deletetradesmen', 'Delete tradesmen', 'write', 127, 1434977917, 1434977917, 'Active'),
        (152, 'building:editstatuses', 'Edit the statuses of building sites', 'write', 1, 1432564408, 1432564408, 'Active'),
        (153, 'building:editjobsites', 'Edit job sites', 'write', 152, 1432564408, 1432564408, 'Active'),
        (154, 'building:writejobsites', 'Create job sites', 'write', 153, 1432564408, 1432564408, 'Active'),
        (155, 'building:viewjobsites', 'View job sites', 'read', 154, 1432564408, 1432564408, 'Active'),
        (156, 'building:deletejobsites', 'Delete job sites', 'write', 153, 1432564408, 1432564408, 'Active'),
        (157, 'site:edittypes', 'Edit Types', 'read', 1, 1435160190, 1435160190, 'Active'),
        (158, 'site:writetypes', 'Create Types', 'read', 157, 1435160190, 1435160190, 'Active'),
        (159, 'site:viewtypes', 'Browse Types', 'read', 158, 1435160190, 1435160190, 'Active'),
        (160, 'site:deletetypes', 'Delete Types', 'read', 157, 1435160190, 1435160190, 'Active');";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `document_types_statuses`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `document_types_statuses` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `status_id` smallint(3) unsigned NOT NULL,
          `document_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `sortorder` int(11) DEFAULT NULL,
          `creation_date` int(10) unsigned DEFAULT NULL,
          `revision_date` int(10) unsigned DEFAULT NULL,
          `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          PRIMARY KEY (`id`),
          KEY `status_id` (`status_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=50 ;";
        $this->db->query($sql);

        $sql = "INSERT INTO `document_types_statuses` (`id`, `status_id`, `document_type`, `sortorder`, `creation_date`, `revision_date`, `status`) VALUES
        (46, 58, 'job_site', 1, 1432564281, 1432564281, 'Active'),
        (47, 59, 'job_site', 2, 1432564281, 1432564281, 'Active'),
        (48, 60, 'job_site', 3, 1432564281, 1432564281, 'Active'),
        (49, 61, 'job_site', 4, 1432564281, 1432564281, 'Active');";
        $this->db->query($sql);

        $sql = "
        DROP TABLE IF EXISTS `types`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `types` (
          `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `entity` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
          `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
          `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          `colour` varchar(16) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=89 ;";
        $this->db->query($sql);

        $sql = "
        INSERT INTO `types` (`id`, `name`, `description`, `entity`, `creation_date`, `revision_date`, `status`, `colour`) VALUES
        (45, 'Plumbers', '', 'tradesman', 0, 0, 'Active', NULL),
        (46, 'Roof Carpenter', '', 'tradesman', 0, 1436442572, 'Active', NULL),
        (47, 'Bricklayers', '', 'tradesman', 0, 0, 'Active', NULL),
        (48, 'Electricians', '', 'tradesman', 0, 0, 'Active', NULL),
        (49, 'Landscapers', '', 'tradesman', 0, 0, 'Active', NULL),
        (50, 'Painters', '', 'tradesman', 0, 0, 'Active', NULL),
        (51, 'Plasterer Float', '', 'tradesman', 0, 1436444385, 'Active', NULL),
        (52, 'Brick Paver', '', 'tradesman', 1435159700, 1436442632, 'Active', NULL),
        (53, 'Windows Frames', '', 'tradesman', 1436288405, 1436288787, 'Active', NULL),
        (54, 'Door Frames', '', 'tradesman', 1436288425, 1436288779, 'Active', NULL),
        (55, 'Structural Steel', '', 'tradesman', 1436288452, 1436288769, 'Active', NULL),
        (56, 'Roof Steel', '', 'tradesman', 1436288474, 1436288762, 'Active', NULL),
        (57, 'Roof Timber', '', 'tradesman', 1436288490, 1436288754, 'Active', NULL),
        (58, 'Waterproofing', '', 'tradesman', 1436442541, 1436442541, 'Active', NULL),
        (59, 'Fixing Carpenter', '', 'tradesman', 1436442588, 1436442588, 'Active', NULL),
        (60, 'Roof Cover', '', 'tradesman', 1436442598, 1436442598, 'Active', NULL),
        (61, 'Bricks', '', 'tradesman', 1436442656, 1436442656, 'Active', NULL),
        (62, 'Grano Worker', '', 'tradesman', 1436442683, 1436442696, 'Active', NULL),
        (63, 'Insulation', '', 'tradesman', 1436442720, 1436442720, 'Active', NULL),
        (64, 'Tiles', '', 'tradesman', 1436442796, 1436442796, 'Active', NULL),
        (65, 'Tiler', '', 'tradesman', 1436442818, 1436442818, 'Active', NULL),
        (66, 'Cabinet Maker', '', 'tradesman', 1436442837, 1436442837, 'Active', NULL),
        (67, 'Garage Grano', '', 'tradesman', 1436442874, 1436442874, 'Active', NULL),
        (68, 'Pest Control', '', 'tradesman', 1436442885, 1436442885, 'Active', NULL),
        (69, 'Sectional Door', '', 'tradesman', 1436442899, 1436442899, 'Active', NULL),
        (70, 'Blinds', '', 'tradesman', 1436442913, 1436442913, 'Active', NULL),
        (71, 'Carpets', '', 'tradesman', 1436442924, 1436442924, 'Active', NULL),
        (72, 'Plasterer Set', '', 'tradesman', 1436444406, 1436444406, 'Active', NULL),
        (73, 'Plasterer Sandfinish', '', 'tradesman', 1436444430, 1436444430, 'Active', NULL),
        (74, 'Earthworks', '', 'tradesman', 1436838205, 1436838205, 'Active', NULL),
        (75, 'Brickpaving', '', 'tradesman', 1437007767, 1437007780, 'Active', NULL),
        (76, 'Security Screens', '', 'tradesman', 1437007898, 1437007898, 'Active', NULL),
        (77, 'Air Conditioning', '', 'tradesman', 1437007932, 1437007932, 'Active', NULL),
        (78, 'Site Clean', '', 'tradesman', 1437007980, 1437007980, 'Active', NULL),
        (79, 'Open Trench', '', 'tradesman', 1437008039, 1437008039, 'Active', NULL),
        (80, 'Fencing', '', 'tradesman', 1437008060, 1437008060, 'Active', NULL),
        (81, 'Ceilings', '', 'tradesman', 1437008113, 1437008113, 'Active', NULL),
        (82, 'Shower Screens', '', 'tradesman', 1437008180, 1437008180, 'Active', NULL),
        (83, 'Sand', '', 'tradesman', 1437120548, 1437120548, 'Active', NULL),
        (84, 'Temp. Power', '', 'tradesman', 1437120601, 1437120601, 'Active', NULL),
        (85, 'Guilano', '', 'tradesman', 1437122667, 1437122667, 'Active', NULL),
        (87, 'General Labour', '', 'tradesman', 1437122702, 1437122702, 'Active', NULL),
        (88, 'Bobcat', '', 'tradesman', 1437122721, 1437122721, 'Active', NULL);";
        $this->db->query($sql);

        $sql = "
        DROP TABLE IF EXISTS `users`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `users` (
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
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;";
        $this->db->query($sql);

        $sql = "
        INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `password`, `temp_password`, `registerkey`, `signature`, `cc_number`, `cc_type`, `cc_expiry`, `cc_name`, `creation_date`, `revision_date`, `status`, `type`) VALUES
        (1, 'Nicolas', 'Connault', 'nicolasconnault', 'sha256:1000:K6c+YWzx+okNiVxrZfVG1NUvumwMIkBf:TcBF3512GebG0JjUBlwjN3NpZDwf3h/1', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1376460021, 1408323366, 'Active', 'contact'),
        (14, 'Demo', 'User', 'demo', 'sha256:1000:P0V8OR2g4uVX5wdCD00GlQdxvwXuurzx:iw/nOHz7PXWGkiFdnogjQzu6aUXMmkRH', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1432564408, 1436266951, 'Active', 'staff');";
        $this->db->query($sql);

        $sql = "
        DROP TABLE IF EXISTS `users_roles`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `users_roles` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned NOT NULL DEFAULT '0',
          `role_id` int(2) unsigned NOT NULL,
          `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
          `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
          `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
          PRIMARY KEY (`id`),
          UNIQUE KEY `role_id` (`role_id`,`user_id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;";
        $this->db->query($sql);

        $sql = "
        INSERT INTO `users_roles` (`id`, `user_id`, `role_id`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 1, 1, 1376531589, 1376531589, 'Active'),
        (19, 14, 12, 0, 0, 'Active');";
        $this->db->query($sql);

        $sql = "
        DROP TABLE IF EXISTS `user_contacts`;";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `user_contacts` (
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
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;";
        $this->db->query($sql);

        $sql = "
            INSERT INTO `user_contacts` (`id`, `user_id`, `type`, `contact`, `default_choice`, `creation_date`, `revision_date`, `receive_notifications`, `status`) VALUES
            (3, 1, 1, 'nicolasconnault@gmail.com', 1, 1379317455, 1379473399, 0, 'Active'),
            (4, 1, 2, '098995494', 1, 1379317459, 1379473402, 0, 'Active');";
        $this->db->query($sql);

        $sql = "SET FOREIGN_KEY_CHECKS=1;";

        $this->db->query($sql);

        // Find earliest booking
        $earliest_booking = $this->booking_model->get(array('status' => 'Active'), true, 'booking_date');

        // Compute difference between now() and earliest booking
        $time_diff = time() - $earliest_booking->booking_date;

        // Add the difference to all bookings
        $sql = $this->db->query("UPDATE building_bookings SET booking_date = booking_date + $time_diff");

        // Delete all files
        $this->rrmdir($this->config->item('files_path').'job_site_drawings/');

        // Copy floor plans back into files
        exec('cp -R '.$this->config->item('files_path').'demo_job_site_drawings '. $this->config->item('files_path').'job_site_drawings');
    }

    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir")  {
                $this->rrmdir($dir."/".$object);
                } else {
                    unlink   ($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
        }
    }
}
