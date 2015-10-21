<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_initial_miniant_setup extends CI_Migration {

    public function up() {
        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("technician_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("priority_level_id int(2) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("unit_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("diagnostic_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("repair_job_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("workflow_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("location_token varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("appointment_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("estimated_duration smallint(3) unsigned NOT NULL DEFAULT '2'");
        $this->dbforge->add_field("diagnostic_required tinyint(1) unsigned DEFAULT NULL");
        $this->dbforge->add_field("diagnostic_authorised tinyint(1) unsigned DEFAULT NULL");
        $this->dbforge->add_field("hide_issue_photos tinyint(1) unsigned DEFAULT '0'");
        $this->dbforge->add_field("isolated_and_tagged tinyint(1) unsigned DEFAULT NULL");
        $this->dbforge->add_field("no_issues_found tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("dowd_text text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned zerofill NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('technician_id');
        $this->dbforge->add_key('priority_level_id');
        $this->dbforge->add_key('order_id');
        $this->dbforge->add_key('unit_id');
        $this->dbforge->add_key('diagnostic_id');
        $this->dbforge->add_key('repair_job_id');
        $this->dbforge->add_key('workflow_id');
        $this->dbforge->create_table('miniant_assignments');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("refrigerant_type_id int(2) unsigned NOT NULL");
        $this->dbforge->add_field("assignment_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("quantity_kg tinyint(2) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("quantity_g smallint(3) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("bottle_serial_number varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");
        $this->dbforge->add_field("reclaimed tinyint(1) unsigned NOT NULL DEFAULT '0'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('refrigerant_type_id');
        $this->dbforge->add_key('assignment_id');
        $this->dbforge->create_table('miniant_assignment_refrigerant');

        $this->dbforge->add_field("id smallint(3) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_type_id int(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("description varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_type_id');
        $this->dbforge->create_table('miniant_brands');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("estimated_time int(4) unsigned NOT NULL DEFAULT '120' COMMENT 'In minutes'");
        $this->dbforge->add_field("bypassed tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_diagnostics');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("diagnostic_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("part_type_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("part_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("issue_type_id tinyint(2) unsigned NOT NULL");
        $this->dbforge->add_field("dowd_id smallint(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("dowd_text text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("can_be_fixed_now tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('diagnostic_id');
        $this->dbforge->add_key('part_type_id');
        $this->dbforge->add_key('issue_type_id');
        $this->dbforge->add_key('dowd_id');
        $this->dbforge->add_key('part_id');
        $this->dbforge->create_table('miniant_diagnostic_issues');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("diagnostic_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("maintenance_task_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('diagnostic_id');
        $this->dbforge->add_key('maintenance_task_id');
        $this->dbforge->create_table('miniant_diagnostic_tasks');

        $this->dbforge->add_field("id smallint(3) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_type_id int(3) unsigned NOT NULL");
        $this->dbforge->add_field("name varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("granularity enum('assignment','order') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'assignment'");
        $this->dbforge->add_field("description text COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_type_id');
        $this->dbforge->create_table('miniant_dowds');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("task varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("sortorder tinyint(2) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("completed_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("notes text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("satisfactory tinyint(1) unsigned DEFAULT NULL");
        $this->dbforge->add_field("disabled tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("completed_by int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("reviewed_by int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_id');
        $this->dbforge->create_table('miniant_installation_tasks');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("name varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_installation_task_categories');

        $this->dbforge->add_field("id smallint(3) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_type_id int(32) unsigned NOT NULL");
        $this->dbforge->add_field("unitry_type_id tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("installation_task_category_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("task varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("sortorder tinyint(2) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) NOT NULL");
        $this->dbforge->add_field("revision_date int(10) NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_type_id');
        $this->dbforge->add_key('installation_task_category_id');
        $this->dbforge->add_key('unitry_type_id');
        $this->dbforge->create_table('miniant_installation_templates');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_id');
        $this->dbforge->create_table('miniant_invoices');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("invoice_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("tenancy_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("signature_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("system_time int(10) unsigned NOT NULL");
        $this->dbforge->add_field("technician_time int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('invoice_id');
        $this->dbforge->add_key('tenancy_id');
        $this->dbforge->add_key('signature_id');
        $this->dbforge->create_table('miniant_invoice_tenancies');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("invoice_tenancy_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("part_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('invoice_tenancy_id');
        $this->dbforge->add_key('part_id');
        $this->dbforge->create_table('miniant_invoice_tenancy_parts');

        $this->dbforge->add_field("id tinyint(2) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("description text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_issue_types');

        $this->dbforge->add_field("id int(1) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");
        $this->dbforge->add_field("diagram text");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_location_diagrams');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("original_order_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("account_id int(1) unsigned NOT NULL");
        $this->dbforge->add_field("site_address_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("billing_contact_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("site_contact_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("property_manager_contact_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("created_by int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("schedule_interval tinyint(2) unsigned NOT NULL DEFAULT '6' COMMENT 'In months'");
        $this->dbforge->add_field("next_maintenance_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('original_order_id');
        $this->dbforge->add_key('account_id');
        $this->dbforge->add_key('site_address_id');
        $this->dbforge->add_key('billing_contact_id');
        $this->dbforge->add_key('property_manager_contact_id');
        $this->dbforge->add_key('site_contact_id');
        $this->dbforge->create_table('miniant_maintenance_contracts');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("maintenance_contract_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("unit_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('maintenance_contract_id');
        $this->dbforge->add_key('unit_id');
        $this->dbforge->create_table('miniant_maintenance_contract_units');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("maintenance_task_template_id smallint(2) unsigned NOT NULL");
        $this->dbforge->add_field("assignment_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("completed_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("completed_by int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('maintenance_task_template_id');
        $this->dbforge->add_key('assignment_id');
        $this->dbforge->add_key('completed_by');
        $this->dbforge->create_table('miniant_maintenance_tasks');

        $this->dbforge->add_field("id smallint(2) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_type_id int(3) unsigned NOT NULL");
        $this->dbforge->add_field("sortorder tinyint(2) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("name varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("completed_description text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("required tinyint(1) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_type_id');
        $this->dbforge->create_table('miniant_maintenance_task_templates');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("account_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("site_address_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("priority_level_id int(2) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("billing_contact_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("order_type_id int(3) unsigned NOT NULL");
        $this->dbforge->add_field("parent_sq_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("site_contact_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("senior_technician_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("location_diagram_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("maintenance_contract_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("dowd_id smallint(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("customer_po_number varchar(255) NOT NULL");
        $this->dbforge->add_field("dowd_text text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("call_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("preferred_start_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");
        $this->dbforge->add_field("deposit_required int(1) unsigned DEFAULT NULL");
        $this->dbforge->add_field("deposit_amount float(10,2) unsigned NOT NULL DEFAULT '0.00'");
        $this->dbforge->add_field("installation_quotation_number varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("cc_type enum('Visa','Mastercard') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'Visa'");
        $this->dbforge->add_field("cc_number varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("cc_expiry varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("cc_security smallint(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("cc_hold tinyint(1) unsigned DEFAULT '0'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('account_id');
        $this->dbforge->add_key('site_address_id');
        $this->dbforge->add_key('priority_level_id');
        $this->dbforge->add_key('billing_contact_id');
        $this->dbforge->add_key('order_type_id');
        $this->dbforge->add_key('parent_sq_id');
        $this->dbforge->add_key('site_contact_id');
        $this->dbforge->add_key('senior_technician_id');
        $this->dbforge->add_key('location_diagram_id');
        $this->dbforge->add_key('maintenance_contract_id');
        $this->dbforge->add_key('dowd_id');
        $this->dbforge->create_table('miniant_orders');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("order_task_id smallint(2) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_task_id');
        $this->dbforge->add_key('order_id');
        $this->dbforge->create_table('miniant_orders_tasks');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("directory varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'shareability'");
        $this->dbforge->add_field("filename_original tinytext COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("hash varchar(64) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("description text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("file_type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("file_extension varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("file_size float(16,2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_id');
        $this->dbforge->create_table('miniant_order_attachments');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("tenancy_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("signature_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_id');
        $this->dbforge->add_key('tenancy_id');
        $this->dbforge->create_table('miniant_order_signatures');

        $this->dbforge->add_field("id smallint(2) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("sortorder tinyint(2) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("name varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("completed_description text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("required tinyint(1) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_order_tasks');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("technician_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_id');
        $this->dbforge->add_key('technician_id');
        $this->dbforge->create_table('miniant_order_technicians');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("technician_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("time_start int(10) unsigned NOT NULL");
        $this->dbforge->add_field("time_end int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_id');
        $this->dbforge->add_key('technician_id');
        $this->dbforge->create_table('miniant_order_times');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("servicequote_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("part_type_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("assignment_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("supplier_contact_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("supplier_quote_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("diagnostic_issue_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("needs_sq tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("for_repair_task tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("client_cost float(10,2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("supplier_cost float(10,2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("quantity int(10) unsigned DEFAULT '1'");
        $this->dbforge->add_field("description text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("client_notes text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("part_number varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("part_name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("po_number varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("origin enum('Supplier','Van stock','Workshop') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Van stock'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('servicequote_id');
        $this->dbforge->add_key('part_type_id');
        $this->dbforge->add_key('assignment_id');
        $this->dbforge->add_key('supplier_contact_id');
        $this->dbforge->add_key('supplier_quote_id');
        $this->dbforge->add_key('diagnostic_issue_id');
        $this->dbforge->create_table('miniant_parts');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_type_id int(3) unsigned NOT NULL");
        $this->dbforge->add_field("name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("for_diagnostic tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("in_template tinyint(1) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("instructions varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("field_type enum('int','text') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'int'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_type_id');
        $this->dbforge->create_table('miniant_part_types');

        $this->dbforge->add_field("id smallint(3) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("part_type_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("issue_type_id tinyint(2) unsigned NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('part_type_id');
        $this->dbforge->add_key('issue_type_id');
        $this->dbforge->create_table('miniant_part_type_issue_types');

        $this->dbforge->add_field("id smallint(3) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("part_type_issue_type_id smallint(5) unsigned NOT NULL");
        $this->dbforge->add_field("step_id tinyint(2) unsigned NOT NULL");
        $this->dbforge->add_field("required tinyint(1) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("needs_sq tinyint(1) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("immediate tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Can this step be performed without leaving the site?'");
        $this->dbforge->add_field("description varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('part_type_issue_type_id');
        $this->dbforge->add_key('step_id');
        $this->dbforge->create_table('miniant_part_type_issue_type_steps');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("servicequote_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("supplier_contact_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("sent_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("total_cost float(10,2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('servicequote_id');
        $this->dbforge->add_key('supplier_contact_id');
        $this->dbforge->create_table('miniant_purchase_orders');

        $this->dbforge->add_field("id int(2) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("name varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("description varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_refrigerant_types');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("diagnostic_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("description text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('diagnostic_id');
        $this->dbforge->create_table('miniant_repair_jobs');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("part_type_id int(10) NOT NULL");
        $this->dbforge->add_field("job_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("issue_type_id tinyint(2) NOT NULL");
        $this->dbforge->add_field("step_id tinyint(2) NOT NULL");
        $this->dbforge->add_field("estimated_time int(3) DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('part_type_id');
        $this->dbforge->add_key('job_id');
        $this->dbforge->add_key('issue_type_id');
        $this->dbforge->add_key('step_id');
        $this->dbforge->create_table('miniant_repair_job_issues');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("part_type_id int(10) NOT NULL");
        $this->dbforge->add_field("job_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("issue_type_id tinyint(2) NOT NULL");
        $this->dbforge->add_field("step_id tinyint(2) NOT NULL");
        $this->dbforge->add_field("estimated_time int(3) DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('part_type_id');
        $this->dbforge->add_key('job_id');
        $this->dbforge->add_key('issue_type_id');
        $this->dbforge->add_key('step_id');
        $this->dbforge->create_table('miniant_repair_job_tasks');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("diagnostic_issue_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("step_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("assignment_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("completed_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("completed_by int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('diagnostic_issue_id');
        $this->dbforge->add_key('step_id');
        $this->dbforge->add_key('assignment_id');
        $this->dbforge->add_key('completed_by');
        $this->dbforge->create_table('miniant_repair_tasks');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("order_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("signature text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("description_of_work text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("sent_date int(10) unsigned DEFAULT NULL COMMENT 'The date this quote was sent to the client'");
        $this->dbforge->add_field("client_response enum('Accepted','Rejected','On hold') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'On hold'");
        $this->dbforge->add_field("client_response_notes text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("client_response_date int(10) unsigned DEFAULT NULL COMMENT 'Response is recorded as a status (see document_statuses table)'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");
        $this->dbforge->add_field("diagnostic_id int(10) unsigned NOT NULL");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('order_id');
        $this->dbforge->add_key('diagnostic_id');
        $this->dbforge->create_table('miniant_servicequotes');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("servicequote_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("directory varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'shareability'");
        $this->dbforge->add_field("filename_original tinytext COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("hash varchar(64) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("description text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("file_type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("file_extension varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("file_size float(16,2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('servicequote_id');
        $this->dbforge->create_table('miniant_servicequote_attachments');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("servicequote_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("type_id int(3) unsigned NOT NULL");
        $this->dbforge->add_field("recipient_contact_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("filepath varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('servicequote_id');
        $this->dbforge->add_key('recipient_contact_id');
        $this->dbforge->add_key('type_id');
        $this->dbforge->create_table('miniant_servicequote_documents');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("servicequote_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("supplier_id int(10) unsigned NOT NULL COMMENT 'Refers to contacts table'");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('servicequote_id');
        $this->dbforge->add_key('supplier_id');
        $this->dbforge->create_table('miniant_servicequote_suppliers');

        $this->dbforge->add_field("id tinyint(2) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("name varchar(255) NOT NULL");
        $this->dbforge->add_field("past_tense varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_steps');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("part_type_issue_type_step_id tinyint(3) unsigned NOT NULL");
        $this->dbforge->add_field("part_type_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("quantity int(10) unsigned NOT NULL DEFAULT '1'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('part_type_issue_type_step_id');
        $this->dbforge->add_key('part_type_id');
        $this->dbforge->create_table('miniant_step_parts');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("part_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("supplier_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("servicequote_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("purchase_order_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("unit_cost float(10,2) DEFAULT NULL");
        $this->dbforge->add_field("total_cost float(10,2) DEFAULT NULL");
        $this->dbforge->add_field("availability varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("request_sent_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("quote_received_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("part_received_note text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("part_received_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('part_id');
        $this->dbforge->add_key('servicequote_id');
        $this->dbforge->add_key('supplier_id');
        $this->dbforge->add_key('purchase_order_id');
        $this->dbforge->create_table('miniant_supplier_quotes');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("name varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");
        $this->dbforge->add_field("account_id int(10) unsigned NOT NULL");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_tenancies');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("tenancy_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("change_type enum('name','unit_id','tenancy') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'name'");
        $this->dbforge->add_field("new_value varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("unit_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('tenancy_id');
        $this->dbforge->add_key('unit_id');
        $this->dbforge->create_table('miniant_tenancy_log');

        $this->dbforge->add_field("id tinyint(2) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_type_id int(3) unsigned NOT NULL");
        $this->dbforge->add_field("name varchar(255) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_type_id');
        $this->dbforge->create_table('miniant_unitry_types');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("unit_type_id int(3) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("brand_id smallint(3) unsigned DEFAULT '0'");
        $this->dbforge->add_field("refrigerant_type_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("unitry_type_id tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("site_address_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("tenancy_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("unit_number varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("model varchar(255) DEFAULT NULL");
        $this->dbforge->add_field("indoor_model varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("outdoor_model varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("fan_motor_model varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("fan_motor_make varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("serial_number varchar(255) DEFAULT NULL");
        $this->dbforge->add_field("indoor_serial_number varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("outdoor_serial_number varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("description text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("apparatus_type text CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("apparatus_brand varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("brand_other varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("electrical varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("gas varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("kilowatts varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("filter_pad_type enum('Celdek','Aspen') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("pad_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("outdoor_unit_location varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'Roof mounted'");
        $this->dbforge->add_field("mains_cable_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("mains_breaker_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("mains_breaker_make varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("mains_switch_make varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("amperage_size_main_isolator varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("water_distribution_type_groove varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("plenium_dropper_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("spigot_dropper_count varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("spigot_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("thermostat_model varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("filter_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("filter_type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("filter_outside_frame_dimensions varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("air_supply_spigot_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("air_supply_duct_spigot_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("return_air_filter_size_frame varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("return_air_indoor_fan_coil_boot_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("return_air_indoor_coil_boot_spigots_count tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("return_air_boot_spigot_size smallint(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("supply_air_indoor_fan_coil_boot_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("supply_air_indoor_coil_boot_spigots_count tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("supply_air_boot_spigot_size smallint(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("supply_air_diffuser_face_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("supply_air_diffuser_cushion_head_sizes varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("supply_air_diffuser_quantity smallint(3) unsigned DEFAULT NULL");
        $this->dbforge->add_field("thermostat_type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("thermostat_brand varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("roof_pitch tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("palette_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("vehicle_registration varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("vehicle_type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("drive_type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("chassis_no varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("engine_no varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("vehicle_year varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("aperture_size int(16) unsigned DEFAULT NULL");
        $this->dbforge->add_field("refrigerated_box_dimensions varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("insulation_thickness int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("insulation_type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("number_of_doors tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("door_openings_aperture_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("floor_thickness int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("floor_type varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("room_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("dropper_size varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("spigots_count tinyint(2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("outdoor_unit varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("area_serving varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('unit_type_id');
        $this->dbforge->add_key('brand_id');
        $this->dbforge->add_key('unitry_type_id');
        $this->dbforge->add_key('refrigerant_type_id');
        $this->dbforge->add_key('site_address_id');
        $this->dbforge->add_key('tenancy_id');
        $this->dbforge->create_table('miniant_units');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("directory varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'shareability'");
        $this->dbforge->add_field("filename_original tinytext COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("hash varchar(64) COLLATE utf8_unicode_ci NOT NULL");
        $this->dbforge->add_field("description text COLLATE utf8_unicode_ci");
        $this->dbforge->add_field("file_type varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("file_extension varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->dbforge->add_field("file_size float(16,2) unsigned DEFAULT NULL");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('miniant_unit_attachments');

        $this->dbforge->add_field("id int(10) unsigned NOT NULL AUTO_INCREMENT");
        $this->dbforge->add_field("user_id int(10) unsigned NOT NULL");
        $this->dbforge->add_field("document_id int(10) unsigned DEFAULT NULL");
        $this->dbforge->add_field("end_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("creation_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("revision_date int(10) unsigned NOT NULL DEFAULT '0'");
        $this->dbforge->add_field("status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'");

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('document_id');
        $this->dbforge->create_table('miniant_work_time_log');

        $this->db->query("INSERT INTO `miniant_dowds` (`id`, `order_type_id`, `name`, `granularity`, `description`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 27, 'NO PARTS REQUIRED', 'assignment', 'Checked and found system not operating due to [[issues]]. Repaired, checked and left system(s) all operational.', NULL, NULL, 'Active'),
        (2, 27, 'PARTS REQUIRED FROM VAN STOCK', 'assignment', 'Checked and found system not operating due to [[issues]]. Removed old part(s), replaced with new, checked and left system(s) all operational.', NULL, NULL, 'Active'),
        (3, 27, 'PARTS REQUIRED - TO BE PICKED UP FROM SUPPLIER', 'assignment', 'Checked and found system not operating due to [[issues]]. Removed old part(s), picked up new, returned to site and replaced. Checked and left system(s) all operational.', NULL, NULL, 'Active'),
        (4, 27, 'WAITING ON APPROVAL', 'assignment', 'Checked and found system not operating due to [[issues]]. (optional:isolated system|danger tagged). Advised client/tenant of situation and will return once repairs have been authorised.', NULL, NULL, 'Active'),
        (5, 28, 'UNITS INSTALLED', 'order', 'Attended site and completed works as per quote [[installation_quotation_number]]. Checked and left all operational.', NULL, NULL, 'Active'),
        (6, 27, 'NO ISSUES FOUND', 'assignment', 'The unit reported to be malfunctioning due to [Type reported issue here] was found to be functioning properly [Enter any action you took to get the unit to work].', NULL, NULL, 'Active'),
        (7, 29, 'MAINTENANCE COMPLETED NO ISSUES', 'order', 'Checked and found systems operating correctly, completed maintenance checks, Found to be all satisfactory. Advised client / tenant to monitor, left all operational.', NULL, NULL, 'Active'),
        (8, 44, 'TASKS COMPLETED', 'assignment', 'Checked and found systems operating correctly, completed maintenance checks, Found to be all satisfactory. Advised client / tenant to monitor, left all operational.\n', NULL, NULL, 'Active'),
        (9, 29, 'MAINTENANCE COMPLETED ISSUES FOUND', 'order', 'Completed maintenance as per schedule, however found [[issues]]. Advised client / tenant of situation and will return once repairs have been authorised.', NULL, NULL, 'Active'),
        (10, 27, 'WAITING ON APPROVAL', 'order', 'Checked and found system(s) not operating. Advised client/tenant of situation and will return once repairs have been authorised.', NULL, NULL, 'Active'),
        (11, 27, 'WAITING ON APPROVAL - SOME ISSUES REPAIRED', 'order', 'Checked and found system(s) not operating. Repairs were performed where possible. Advised client/tenant of situation and will return once repairs have been authorised.', NULL, NULL, 'Active'),
        (12, 27, 'ISSUES FOUND AND REPAIRED', 'order', 'All diagnosed issues were repaired, see issue description below. All systems left operational.', NULL, NULL, 'Active'),
        (13, 27, 'NO REPAIRS AUTHORISED', 'order', 'Issues were found but repairs were not authorised. Left units non operational. See issues below.', NULL, NULL, 'Active'),
        (14, 27, 'NO ISSUES FOUND', 'order', 'No issues found', NULL, NULL, 'Active'),
        (15, 27, 'ISSUES FOUND - NO ACTION', 'assignment', 'Checked and found system not operating due to [[issues]]. No action taken.', NULL, NULL, 'Active'),
        (16, 42, 'REPAIR TASKS COMPLETED - OPERATIONAL', 'order', 'All repair tasks were completed. Left all systems operational.', NULL, NULL, 'Active'),
        (17, 42, 'REPAIR TASKS COMPLETED - SQ', 'order', 'All repair tasks were completed. Found [[issues]]. Advised client / tenant of situation and will return once additional repairs have been authorised.', NULL, NULL, 'Active'),
        (18, 44, 'SERVICE COMPLETED ISSUES FOUND', 'order', 'Completed service as per schedule, however found [[issues]]. Advised client / tenant of situation and will return once repairs have been authorised.', NULL, NULL, 'Active'),
        (19, 44, 'SERVICE COMPLETED NO ISSUES', 'order', 'Checked and found systems operating correctly, completed service checks, Found to be all satisfactory. Advised client / tenant to monitor, left all operational.', NULL, NULL, 'Active')
        (20, 44, 'PARTS REQUIRED - TO BE PICKED UP FROM SUPPLIER', 'assignment', 'Checked and found system not operating due to [[issues]]. Removed old part(s), picked up new, returned to site and replaced. Checked and left system(s) all operational.', NULL, NULL, 'Active')
        (21, 44, 'PARTS REQUIRED FROM VAN STOCK', 'assignment', 'Checked and found system not operating due to [[issues]]. Removed old part(s), replaced with new, checked and left system(s) all operational.', NULL, NULL, 'Active'),
        (22, 44, 'WAITING ON APPROVAL', 'assignment', 'Checked and found system not operating due to [[issues]]. (optional:isolated system|danger tagged). Advised client/tenant of situation and will return once repairs have been authorised.', NULL, NULL, 'Active'),
        (23 , '29', 'WAITING ON APPROVAL', 'assignment', 'Checked and found system not operating due to [[issues]] (optional:isolated system|danger tagged). Advised client/tenant of situation and will return once repairs have been authorised.', NULL , NULL , 'Active')");


        $this->db->query("INSERT INTO `miniant_installation_task_categories` (`id`, `name`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 'Indoor fix (Prelay)', NULL, NULL, 'Active'),
        (2, 'Outdoor fix', NULL, NULL, 'Active'),
        (3, 'Toilet exhaust', NULL, NULL, 'Active'),
        (4, 'Commissioning', NULL, NULL, 'Active'),
        (5, 'Indoor fix', NULL, NULL, 'Active'),
        (6, 'Final fix', NULL, NULL, 'Active')");

        $this->db->query("INSERT INTO `miniant_installation_templates` (`id`, `unit_type_id`, `unitry_type_id`, `installation_task_category_id`, `task`, `sortorder`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 19, 4, 1, 'Indoor unit', 1, 0, 0, 'Active'),
        (2, 19, 4, 1, 'Drain tray', 2, 0, 0, 'Active'),
        (3, 19, 4, 1, 'Supply boots', 3, 0, 0, 'Active'),
        (4, 19, 4, 1, 'Return boots', 4, 0, 0, 'Active'),
        (5, 19, 4, 1, 'Supply & return ducts', 5, 0, 0, 'Active'),
        (6, 19, 4, 1, 'Exair boxes', 6, 0, 0, 'Active'),
        (7, 19, 4, 1, 'Zone motors cables', 7, 0, 0, 'Active'),
        (8, 19, 4, 1, 'Refrigeration pipes', 8, 0, 0, 'Active'),
        (9, 19, 4, 1, 'Drain pipes', 9, 0, 0, 'Active'),
        (10, 19, 4, 1, 'Interconnecting cables', 10, 0, 0, 'Active'),
        (11, 19, 4, 1, 'Zone controller cables', 11, 0, 0, 'Active'),
        (12, 19, 4, 1, 'Thermostat controller cables', 12, 0, 0, 'Active'),
        (13, 19, 4, 1, 'Slave thermostat controller cables', 13, 0, 0, 'Active'),
        (14, 19, 4, 1, 'Test/check drain fall', 14, 0, 0, 'Active'),
        (15, 19, 4, 2, 'Duct cover', 15, 0, 0, 'Active'),
        (16, 19, 4, 2, 'Dektek', 16, 0, 0, 'Active'),
        (17, 19, 4, 2, 'Ground blocks', 17, 0, 0, 'Active'),
        (18, 19, 4, 2, 'Roof bracket / top hats', 18, 0, 0, 'Active'),
        (19, 19, 4, 2, 'Outdoor unit', 19, 0, 0, 'Active'),
        (20, 19, 4, 2, 'Connect pipes', 20, 0, 0, 'Active'),
        (21, 19, 4, 2, 'Connect interconnecting cables', 21, 0, 0, 'Active'),
        (22, 19, 4, 2, 'Connect power supply from isolator', 22, 0, 0, 'Active'),
        (23, 19, 4, 2, 'Check flares', 23, 0, 0, 'Active'),
        (24, 19, 4, 2, 'Check interconnecting cables sequence', 24, 0, 0, 'Active'),
        (25, 19, 4, 6, 'Thermostat controller', 25, 0, 0, 'Active'),
        (26, 19, 4, 6, 'Zone controller', 26, 0, 0, 'Active'),
        (27, 19, 4, 6, 'Cutout vents', 27, 0, 0, 'Active'),
        (28, 19, 4, 6, 'Supply & return vents / grilles', 28, 0, 0, 'Active'),
        (29, 19, 4, 4, 'Check incoming power supply', 29, 0, 0, 'Active'),
        (30, 19, 4, 4, 'Check interconnecting', 30, 0, 0, 'Active'),
        (31, 19, 4, 4, 'Add gas', 31, 0, 0, 'Active'),
        (32, 19, 4, 4, 'System commission', 32, 0, 0, 'Active'),
        (33, 19, 4, 4, 'System adjustments', 33, 0, 0, 'Active'),
        (34, 19, 4, 4, 'Stickers', 34, 0, 0, 'Active'),
        (35, 19, 4, 4, 'Recording data', 35, 0, 0, 'Active'),
        (36, 19, 4, 4, 'Operation manual', 36, 0, 0, 'Active'),
        (37, 19, 2, 1, 'Core drill', 1, 0, 0, 'Active'),
        (38, 19, 2, 1, 'Indoor unit', 2, 0, 0, 'Active'),
        (39, 19, 2, 1, 'Refrigeration pipes', 3, 0, 0, 'Active'),
        (40, 19, 2, 1, 'Drain pipes', 4, 0, 0, 'Active'),
        (41, 19, 2, 1, 'Interconnecting cables', 5, 0, 0, 'Active'),
        (42, 19, 2, 1, 'Thermostat controller cables', 6, 0, 0, 'Active'),
        (43, 19, 2, 1, 'Test/check drain fall', 7, 0, 0, 'Active'),
        (44, 19, 2, 2, 'Duct cover', 8, 0, 0, 'Active'),
        (45, 19, 2, 2, 'Dektek', 9, 0, 0, 'Active'),
        (46, 19, 2, 2, 'Ground blocks', 10, 0, 0, 'Active'),
        (47, 19, 2, 2, 'Roof bracket / top hats', 11, 0, 0, 'Active'),
        (48, 19, 2, 2, 'Outdoor unit', 12, 0, 0, 'Active'),
        (49, 19, 2, 2, 'Connect pipes', 13, 0, 0, 'Active'),
        (50, 19, 2, 2, 'Connect interconnecting cables', 14, 0, 0, 'Active'),
        (51, 19, 2, 2, 'Connect power supply from isolator', 15, 0, 0, 'Active'),
        (52, 19, 2, 2, 'Check flares', 16, 0, 0, 'Active'),
        (53, 19, 2, 2, 'Check interconnecting cables sequence', 17, 0, 0, 'Active'),
        (54, 19, 2, 4, 'Check incoming power supply', 18, 0, 0, 'Active'),
        (55, 19, 2, 4, 'Check interconnecting', 19, 0, 0, 'Active'),
        (56, 19, 2, 4, 'Add gas', 20, 0, 0, 'Active'),
        (57, 19, 2, 4, 'System commission', 21, 0, 0, 'Active'),
        (58, 19, 2, 4, 'System adjustments', 22, 0, 0, 'Active'),
        (59, 19, 2, 4, 'Stickers', 23, 0, 0, 'Active'),
        (60, 19, 2, 4, 'Recording data', 24, 0, 0, 'Active'),
        (61, 19, 2, 4, 'Operation manual', 25, 0, 0, 'Active'),
        (62, 18, NULL, 2, 'Penetration', 1, 0, 0, 'Active'),
        (63, 18, NULL, 2, 'Dropper with overflashing', 2, 0, 0, 'Active'),
        (64, 18, NULL, 2, 'Brackets', 3, 0, 0, 'Active'),
        (65, 18, NULL, 2, 'Evaporative unit', 4, 0, 0, 'Active'),
        (66, 18, NULL, 2, 'Connect power supply from isolator', 5, 0, 0, 'Active'),
        (67, 18, NULL, 2, 'Control cables', 6, 0, 0, 'Active'),
        (68, 18, NULL, 2, 'Connect water pipe from tap', 7, 0, 0, 'Active'),
        (69, 18, NULL, 2, 'Connect drain pipe', 8, 0, 0, 'Active'),
        (70, 18, NULL, 5, 'Castellated collar', 9, 0, 0, 'Active'),
        (71, 18, NULL, 5, 'Ducts', 10, 0, 0, 'Active'),
        (72, 18, NULL, 5, 'Cutout vents', 11, 0, 0, 'Active'),
        (73, 18, NULL, 5, 'Supply vents / grilles', 12, 0, 0, 'Active'),
        (74, 18, NULL, 5, 'Thermostat controller cables', 13, 0, 0, 'Active'),
        (75, 18, NULL, 4, 'Check incoming power supply', 14, 0, 0, 'Active'),
        (76, 18, NULL, 4, 'System commission', 15, 0, 0, 'Active'),
        (77, 18, NULL, 4, 'System adjustments', 16, 0, 0, 'Active'),
        (78, 18, NULL, 4, 'Air balancing', 17, 0, 0, 'Active'),
        (79, 18, NULL, 4, 'Stickers', 18, 0, 0, 'Active'),
        (80, 18, NULL, 4, 'Recording data', 19, 0, 0, 'Active'),
        (81, 18, NULL, 4, 'Operation manual', 20, 0, 0, 'Active'),
        (82, 41, NULL, 3, 'Penetration', 1, 0, 0, 'Active'),
        (83, 41, NULL, 3, 'Dropper with overflashing', 2, 0, 0, 'Active'),
        (84, 41, NULL, 3, 'Castellated collar', 3, 0, 0, 'Active'),
        (85, 41, NULL, 3, 'Roof cowls', 4, 0, 0, 'Active'),
        (86, 41, NULL, 3, 'Roof cowl fans', 5, 0, 0, 'Active'),
        (87, 41, NULL, 3, 'Inline fans', 6, 0, 0, 'Active'),
        (88, 41, NULL, 3, 'Connect power supply from isolator / socket outlet', 7, 0, 0, 'Active'),
        (89, 41, NULL, 3, 'Connect control switch', 8, 0, 0, 'Active'),
        (90, 41, NULL, 3, 'Ducts', 9, 0, 0, 'Active'),
        (91, 41, NULL, 3, 'Exchaust grilles', 10, 0, 0, 'Active'),
        (92, 41, NULL, 4, 'Check incoming power supply', 11, 0, 0, 'Active'),
        (93, 41, NULL, 4, 'System commission', 12, 0, 0, 'Active'),
        (94, 41, NULL, 4, 'Fan rotation', 13, 0, 0, 'Active'),
        (95, 41, NULL, 4, 'System adjustments', 14, 0, 0, 'Active'),
        (96, 41, NULL, 4, 'Air balancing', 15, 0, 0, 'Active'),
        (97, 41, NULL, 4, 'Stickers', 16, 0, 0, 'Active'),
        (98, 41, NULL, 4, 'Recording data', 17, 0, 0, 'Active'),
        (99, 41, NULL, 4, 'Operation manual', 18, 0, 0, 'Active'),
        (100, 19, 5, 2, 'Penetration', 1, 0, 0, 'Active'),
        (101, 19, 5, 2, 'Dropper with overflashing', 2, 0, 0, 'Active'),
        (102, 19, 5, 2, 'Castellated collar', 3, 0, 0, 'Active'),
        (103, 19, 5, 2, 'Roof cowls', 4, 0, 0, 'Active'),
        (104, 19, 5, 2, 'Roof cowl fans', 5, 0, 0, 'Active'),
        (105, 19, 5, 2, 'Inline fans', 6, 0, 0, 'Active'),
        (106, 19, 5, 2, 'Connect power supply from isolator / socket outlet', 7, 0, 0, 'Active'),
        (107, 19, 5, 2, 'Connect control switch', 8, 0, 0, 'Active'),
        (108, 19, 5, 2, 'Ducts', 9, 0, 0, 'Active'),
        (109, 19, 5, 2, 'Exchaust grilles', 10, 0, 0, 'Active'),
        (110, 19, 5, 4, 'Check incoming power supply', 11, 0, 0, 'Active'),
        (111, 19, 5, 4, 'System commission', 12, 0, 0, 'Active'),
        (112, 19, 5, 4, 'Fan rotation', 13, 0, 0, 'Active'),
        (113, 19, 5, 4, 'System adjustments', 14, 0, 0, 'Active'),
        (114, 19, 5, 4, 'Air balancing', 15, 0, 0, 'Active'),
        (115, 19, 5, 4, 'Stickers', 16, 0, 0, 'Active'),
        (116, 19, 5, 4, 'Recording data', 17, 0, 0, 'Active'),
        (117, 19, 5, 4, 'Operation manual', 18, 0, 0, 'Active')");

        $this->db->query("INSERT INTO `miniant_issue_types` (`id`, `name`, `description`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 'Dirty', NULL, 0, 0, 'Active'),
        (2, 'Broken', NULL, 0, 0, 'Active'),
        (3, 'Damaged', NULL, 0, 0, 'Active'),
        (4, 'Empty', NULL, 0, 0, 'Active'),
        (5, 'Jammed', NULL, 0, 0, 'Active'),
        (6, 'Burned out', NULL, 0, 0, 'Active'),
        (7, 'Faulty', NULL, 0, 0, 'Active'),
        (8, 'Out of calibration', NULL, 0, 0, 'Active'),
        (9, 'Blocked with dirt and debris', NULL, 0, 0, 'Active'),
        (10, 'Failure', NULL, 0, 0, 'Active'),
        (11, 'Low', 'Low on refrigerant', 0, 0, 'Active')");

        $this->db->query("INSERT INTO `miniant_maintenance_task_templates` (`id`, `unit_type_id`, `sortorder`, `name`, `completed_description`, `required`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 18, 1, 'Reservoir is cleaned and disinfected', 'Reservoir was cleaned and disinfected', 1, 0, 0, 'Active'),
        (2, 18, 2, 'Tension on belts is checked (if applicable)', 'Tension on belts was checked', 0, 0, 0, 'Active'),
        (3, 18, 3, 'Water pumped is checked', 'Water pumped was checked', 1, 0, 0, 'Active'),
        (4, 18, 4, 'All water drainage is checked', 'All water drainage was checked', 1, 0, 0, 'Active'),
        (5, 18, 5, 'Reservoir level is set', 'Reservoir level was set', 1, 0, 0, 'Active'),
        (6, 18, 6, 'Bearings are lubricated (requires grease gun)', 'Bearings were lubricated', 1, 0, 0, 'Active'),
        (7, 18, 7, 'Bleed rate is set', 'Bleed rate was set', 1, 0, 0, 'Active'),
        (8, 18, 8, 'Clean and drain filter pads', 'Filter pads were cleaned and drained', 1, 0, 0, 'Active'),
        (9, 18, 9, 'Motor operation is checked', 'Motor operation was checked', 1, 0, 0, 'Active'),
        (10, 18, 10, 'Dump valve is checked', 'Dump valve was checked', 1, 0, 0, 'Active'),
        (11, 18, 11, 'Flashings and condition of roof around the unit are checked', 'Flashings and condition of roof around the unit were checked', 1, 0, 0, 'Active'),
        (12, 18, 12, 'Company stickers on units & wall controllers', 'Company stickers were placed on unit & wall controllers', 1, 0, 0, 'Active'),
        (13, 19, 1, 'Check and clean filters', 'Filters were checked and cleaned', 1, 0, 0, 'Active'),
        (14, 19, 2, 'Clean and inspect all drainages (if applicable)', 'All drainages were cleaned and inspected', 0, 0, 0, 'Active'),
        (15, 19, 3, 'Clean around condensing units', 'Cleaning was done around condensing units', 1, 0, 0, 'Active'),
        (16, 19, 4, 'Visual look at electrical terminals on condensing units', 'Visual inspection of electrical terminals on condensing units was completed', 1, 0, 0, 'Active'),
        (17, 19, 5, 'Tension on belts is checked (if applicable)', 'Tension on belts was checked', 0, 0, 0, 'Active'),
        (18, 19, 6, 'Check all refrigerant charges', 'Refrigerant charges were checked', 1, 0, 0, 'Active'),
        (19, 19, 7, 'Company stickers on units & wall controllers', 'Company stickers were placed on unit & wall controllers', 1, 0, 0, 'Active'),
        (20, 37, 1, 'Clean condenser oil', 'Condensor oil was cleaned', 1, 0, 0, 'Active'),
        (21, 37, 2, 'Clean and inspect all drainages (if applicable)', 'Drainages were inspected and cleaned', 0, 0, 0, 'Active'),
        (22, 37, 3, 'Clean around condensing units', 'Space around condensing units was cleaned', 1, 0, 0, 'Active'),
        (23, 37, 4, 'Visual look at evaporators', 'Evaporators were inspected', 1, 0, 0, 'Active'),
        (24, 37, 5, 'Check all refrigerant charges', 'Refrigerant charges were checked', 1, 0, 0, 'Active'),
        (25, 37, 6, 'Check oil chargers in compressors', 'Oil chargers in compressors were checked', 1, 0, 0, 'Active'),
        (26, 37, 7, 'Check temperatures of system', 'Temperatures of system were checked', 1, 0, 0, 'Active'),
        (27, 37, 8, 'Visual look at electrical terminals on units', 'Electrical terminals on units were inspected', 1, 0, 0, 'Active'),
        (28, 37, 9, 'Company stickers on units & temperature controllers where applicable', 'Company stickers were placed on unit & temperature controllers', 0, 0, 0, 'Active'),
        (29, 41, 1, 'Visual look at electrical terminals on units', 'Electrical terminals on units were inspected', 1, 0, 0, 'Active'),
        (30, 41, 2, 'Check fan operation (direction of motor)', 'Fan operation (direction of motor) was checked', 1, 0, 0, 'Active'),
        (31, 41, 3, 'Flashings and condition of roof around the unit is checked (photos if required)', 'Flashings and condition of roof around the unit were checked', 1, 0, 0, 'Active'),
        (32, 41, 4, 'Company stickers on units', 'Company stickers were placed on units', 1, 0, 0, 'Active'),
        (33, 41, 5, 'Photos on kitchen exhaust if uncleaned with debris (cooking grease oil)', 'Photos of kitchen exhaust were taken due to obstruction by cooking grease oil', 0, 0, 0, 'Active'),
        (34, 20, 7, 'Clean condenser oil', 'Condensor oil was cleaned', 1, 1405048713, 1405048713, 'Active'),
        (35, 20, 8, 'Clean and inspect all drainages (if applicable)', 'Drainages were inspected and cleaned', 0, 1405048713, 1405048713, 'Active'),
        (36, 20, 9, 'Clean around condensing units', 'Space around condensing units was cleaned', 1, 1405048713, 1405048713, 'Active'),
        (37, 20, 10, 'Visual look at evaporators', 'Evaporators were inspected', 1, 1405048713, 1405048713, 'Active'),
        (38, 20, 11, 'Check all refrigerant charges', 'Refrigerant charges were checked', 1, 1405048713, 1405048713, 'Active'),
        (39, 20, 12, 'Check oil chargers in compressors', 'Oil chargers in compressors were checked', 1, 1405048713, 1405048713, 'Active'),
        (40, 20, 13, 'Check temperatures of system', 'Temperatures of system were checked', 1, 1405048713, 1405048713, 'Active'),
        (41, 20, 14, 'Visual look at electrical terminals on units', 'Electrical terminals on units were inspected', 1, 1405048713, 1405048713, 'Active'),
        (42, 20, 15, 'Company stickers on units & temperature controllers where applicable', 'Company stickers were placed on unit & temperature controllers', 0, 1405048713, 1405048713, 'Active'),
        (49, 20, 1, 'Check Tension on all V belts', 'Checked Tension on all V belts', 1, 1405049210, 1405049210, 'Active'),
        (50, 20, 2, 'Check all oils in diesel motor/generator', 'Checked all oils in diesel motor/generator', 1, 1405049210, 1405049210, 'Active'),
        (51, 20, 3, 'Check all fuel filters (replace if applicable)', 'Checked all fuel filters and replaced where applicable', 1, 1405049210, 1405049210, 'Active'),
        (52, 20, 4, 'Check Bearings are lubricated', 'Checked that bearings were lubricated', 1, 1405049210, 1405049210, 'Active'),
        (53, 20, 5, 'Check all condenser and evaporator fans are operational', 'Checked all condenser and evaporator fans were operational', 1, 1405049210, 1405049210, 'Active'),
        (54, 20, 6, 'Check electric standby is operational (if applicable)', 'Checked electric standby was operational', 0, 1405049235, 1405049235, 'Active')");

        $this->db->query("INSERT INTO `miniant_order_tasks` (`id`, `sortorder`, `name`, `completed_description`, `required`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 1, 'Company stickers', 'Placed company stickers on equipment', 1, 0, 0, 'Active'),
        (2, 2, 'Clean up site', 'Cleaned up site', 1, 0, 0, 'Active'),
        (3, 3, 'Pick up our rubbish from site', 'Picked up rubbish from site', 1, 0, 0, 'Active'),
        (4, 4, 'Label old parts with job site using duct tape', 'Labelled old parts with Job Number using duct tape', 1, 0, 0, 'Active'),
        (5, 5, 'Put old parts in van to return to workshop', 'Put old parts in van to return to workshop', 1, 0, 0, 'Active'),
        (6, 6, 'All technicians to sign in and out of the client''s visitors'' book (if applicable)', 'All technicians have signed in and out of the client''s visitors'' book.', 0, 0, 0, 'Active')");

        $this->db->query("INSERT INTO `miniant_part_types` (`id`, `unit_type_id`, `name`, `for_diagnostic`, `in_template`, `instructions`, `field_type`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 18, 'Indoor PCB', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (2, 18, 'Outdoor PCB', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (3, 18, 'Fan motor outdoor', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (4, 18, 'Fan motor brackets', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (5, 18, 'Fan blade', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (6, 18, 'Water Sol valve', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (7, 18, 'Water Dump valve', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (8, 18, 'Water Pump', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (9, 18, 'Water Mgt PCB', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (10, 18, 'Dampers manual', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (11, 18, 'V Belt', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (12, 18, 'Small Pulley', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (13, 18, 'Large Pulley', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (14, 18, 'Bearing Kits', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (15, 18, 'Pillar Blocks', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (16, 18, 'Celdek Pads', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (17, 18, 'Aspen Pads', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (18, 18, 'Wire retainers', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (19, 18, 'Plastic pad holders', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (20, 18, 'Transformer', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (23, 18, 'Labour', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (24, 18, 'Dropper', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (26, 18, 'Upstand/Overflash', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (27, 18, 'Ducts Flexible', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (28, 18, 'Electrician', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (29, 19, 'Indoor PCB', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (30, 19, 'Outdoor PCB', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (31, 19, 'Fan motor outdoor', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (32, 19, 'Fan motor indoor', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (33, 19, 'Fan blade', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (34, 19, 'Thermostat Wall Controller', 0, 0, NULL, 'int', 1380612462, 1399622481, 'Active'),
        (35, 19, 'Thermostat Remote', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (37, 19, 'Reversing Valve', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (38, 19, 'Thermistor', 0, 0, NULL, 'int', 1380612462, 1399622385, 'Active'),
        (39, 19, 'Ducts Flexible', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (40, 19, 'Dampers manual', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (41, 19, 'Dampers motorised', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (47, 19, 'Contactors', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (48, 19, 'Overloads', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (49, 19, 'Filter Frame', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (50, 19, 'Filter Media', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (58, 20, 'Compressor', 1, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (60, 20, 'Filter', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (61, 20, 'Refrigerant', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (62, 20, 'Contactors', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (63, 20, 'Overloads', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (67, 20, 'Reversing Valve', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (68, 19, 'Solenoid Valve', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (69, 20, 'Solenoid Coil', 0, 0, NULL, 'int', 1380612462, 1380612462, 'Active'),
        (71, 20, 'Condenser', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (72, 20, 'Liquid Receiver', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (73, 20, 'Oil separator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (74, 20, 'Rotalock', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (75, 20, 'Solenoid valve', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (76, 20, 'TX valve', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (77, 20, 'Orifice', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (78, 19, 'Evaporator Coil', 1, 0, NULL, 'int', 0, 1399619558, 'Active'),
        (79, 20, 'CPR', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (80, 20, 'EPR', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (81, 20, 'Dual pressure controls', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (82, 20, 'Fan cycle pressure switch', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (83, 20, 'Oil pump', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (84, 20, 'Mechanical thermostat', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (85, 20, 'Suction accumulator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (86, 20, 'Electronic thermostat', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (87, 20, 'Condensor fan motor', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (88, 18, 'Evaporative fan motor', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (89, 18, 'Evaporator coils', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (90, 19, 'Condensor coils', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (95, 37, 'Power', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (96, 19, 'Compressor', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (97, 37, 'Compressor', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (98, 37, 'Condenser', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (100, 37, 'Liquid Receiver', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (101, 19, 'Oil separator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (102, 37, 'Oil separator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (103, 19, 'Rotalock', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (104, 37, 'Rotalock', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (106, 37, 'Solenoid valve', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (107, 19, 'TX valve', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (108, 37, 'TX valve', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (109, 19, 'Orifice', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (110, 18, 'Orifice', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (111, 37, 'Orifice', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (112, 20, 'Evaporator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (113, 37, 'Evaporator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (115, 37, 'EPR', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (116, 19, 'CPR', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (117, 37, 'CPR', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (118, 19, 'Suction accumulator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (119, 37, 'Suction accumulator', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (127, 19, 'Liquid Line Filter Drier', 1, 0, NULL, 'int', 0, 1399622853, 'Active'),
        (129, 19, 'Solenoid Coil', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (130, 19, 'Dual pressure controls', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (131, 19, 'Fan cycle pressure switch', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (132, 19, 'Oil pump', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (133, 19, 'Mechanical thermostat', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (134, 19, 'Electronic thermostat', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (135, 19, 'Condensor fan motor', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (141, 37, 'Reclaim Bottle', 0, 0, '# of bottles', 'int', 0, 0, 'Active'),
        (142, 37, 'Welding Rods', 0, 1, 'qty', 'int', 0, 0, 'Active'),
        (143, 37, 'Welding Equipment', 0, 1, 'yes/no', 'text', 0, 0, 'Active'),
        (144, 37, 'Nitrogen', 1, 1, '1 bottle serial number per line', 'int', 0, 0, 'Active'),
        (145, 37, 'Filter', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (146, 37, 'Refrigerant', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (147, 37, 'Contactors', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (148, 37, 'Overloads', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (149, 37, 'Sundries', 0, 1, 'e.g. 4m of electrical cables, 10 cable ties etc.', 'text', 0, 0, 'Active'),
        (150, 37, 'Freight', 0, 0, 'qty', 'int', 0, 0, 'Active'),
        (151, 37, 'Labour', 0, 0, NULL, 'int', 0, 0, 'Active'),
        (152, 37, 'Reversing Valve', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (153, 37, 'Solenoid Coil', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (154, 37, 'Others', 0, 0, NULL, 'int', 0, 0, 'Active'),
        (155, 37, 'Dual pressure controls', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (156, 37, 'Fan cycle pressure switch', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (157, 37, 'Oil pump', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (158, 37, 'Mechanical thermostat', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (159, 37, 'Electronic thermostat', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (160, 37, 'Condensor fan motor', 1, 0, NULL, 'int', 0, 0, 'Active'),
        (184, 18, 'Condensor coils', 1, 0, NULL, 'int', 1399615964, 1399615964, 'Active'),
        (186, 19, 'Remote Thermostat sensor', 0, 0, NULL, 'int', 1399619408, 1399619408, 'Active'),
        (187, 19, 'Oil Pressure Switch', 0, 0, NULL, 'int', 1399620195, 1399620195, 'Active'),
        (188, 19, 'Refrigerant', 0, 0, NULL, 'int', 1399620993, 1399620993, 'Active'),
        (189, 19, 'Reversing Valve Coil', 0, 0, NULL, 'int', 1399621259, 1399621259, 'Active'),
        (190, 19, 'Suction Line Burnout Filter Drier', 0, 0, NULL, 'int', 1399622882, 1399622882, 'Active'),
        (191, 20, 'Service Valves', 0, 0, NULL, 'int', 1399623024, 1399623024, 'Active'),
        (192, 19, 'Oil Filter', 0, 0, NULL, 'int', 1399623362, 1399623362, 'Active'),
        (194, 19, 'Oil Sump Heater', 0, 0, NULL, 'int', 1399623433, 1399623433, 'Active'),
        (195, 19, 'Labour', 0, 0, NULL, 'int', 0, 0, 'Active'),
        (196, 20, 'Labour', 0, 0, NULL, 'int', 0, 0, 'Active'),
        (197, 41, 'Refrigerant coil', 0, 0, NULL, 'int', 1404786649, 1404786649, 'Active'),
        (198, 18, 'Reclaim bottle', 0, 0, '# of bottles', 'int', 0, 0, 'Active'),
        (199, 18, 'Welding Equipment', 0, 1, 'yes/no', 'text', 0, 0, 'Active'),
        (200, 18, 'Welding Rods', 0, 1, 'qty', 'int', 0, 0, 'Active'),
        (201, 18, 'Nitrogen', 0, 1, '1 bottle serial number per line', 'int', 0, 0, 'Active'),
        (202, 18, 'Freight', 0, 0, 'qty', 'int', 0, 0, 'Active'),
        (203, 18, 'Sundries', 0, 1, 'e.g. 4m of electrical cables, 10 cable ties etc.', 'text', 0, 0, 'Active'),
        (204, 19, 'Welding Equipment', 0, 1, 'yes/no', 'text', 0, 0, 'Active'),
        (205, 19, 'Welding Rods', 0, 1, 'qty', 'int', 0, 0, 'Active'),
        (206, 19, 'Nitrogen', 0, 1, '1 bottle serial number per line', 'int', 0, 0, 'Active'),
        (207, 19, 'Freight', 0, 0, 'qty', 'int', 0, 0, 'Active'),
        (208, 19, 'Sundries', 0, 1, 'e.g. 4m of electrical cables, 10 cable ties etc.', 'text', 0, 0, 'Active'),
        (209, 20, 'Welding Equipment', 0, 1, 'yes/no', 'text', 0, 0, 'Active'),
        (210, 20, 'Welding Rods', 0, 1, 'qty', 'int', 0, 0, 'Active'),
        (211, 20, 'Nitrogen', 0, 1, '1 bottle serial number per line', 'int', 0, 0, 'Active'),
        (212, 20, 'Freight', 0, 0, 'qty', 'int', 0, 0, 'Active'),
        (213, 20, 'Sundries', 0, 1, 'e.g. 4m of electrical cables, 10 cable ties etc.', 'text', 0, 0, 'Active'),
        (214, 41, 'Welding Equipment', 0, 1, 'yes/no', 'text', 0, 0, 'Active'),
        (215, 41, 'Welding Rods', 0, 1, 'qty', 'int', 0, 0, 'Active'),
        (216, 41, 'Nitrogen', 0, 1, '1 bottle serial number per line', 'int', 0, 0, 'Active'),
        (217, 41, 'Freight', 0, 0, 'qty', 'int', 0, 0, 'Active'),
        (218, 41, 'Sundries', 0, 1, 'e.g. 4m of electrical cables, 10 cable ties etc.', 'text', 0, 0, 'Active'),
        (219, 19, 'Reclaim bottle', 0, 0, '# of bottles', 'int', 0, 0, 'Active'),
        (220, 20, 'Reclaim bottle', 0, 0, '# of bottles', 'int', 0, 0, 'Active'),
        (221, 41, 'Reclaim bottle', 0, 0, '# of bottles', 'int', 0, 0, 'Active'),
        (222, 18, 'pasta', 1, 0, NULL, 'int', 1426576796, 1426576796, 'Active'),
        (229, 18, 'eetferet', 1, 0, NULL, 'int', 1426666390, 1426666390, 'Active')");

        $this->db->query("INSERT INTO `miniant_part_type_issue_types` (`id`, `part_type_id`, `issue_type_id`, `creation_date`, `revision_date`, `status`) VALUES
        (3, 58, 6, NULL, NULL, 'Active'),
        (4, 71, 7, NULL, NULL, 'Active'),
        (5, 72, 7, NULL, NULL, 'Active'),
        (6, 73, 7, NULL, NULL, 'Active'),
        (8, 68, 7, NULL, NULL, 'Active'),
        (9, 76, 7, NULL, NULL, 'Active'),
        (10, 77, 7, NULL, NULL, 'Active'),
        (11, 78, 7, NULL, NULL, 'Active'),
        (12, 79, 7, NULL, NULL, 'Active'),
        (13, 80, 7, NULL, NULL, 'Active'),
        (14, 85, 7, NULL, NULL, 'Active'),
        (15, 81, 6, NULL, NULL, 'Active'),
        (16, 82, 6, NULL, NULL, 'Active'),
        (17, 83, 6, NULL, NULL, 'Active'),
        (18, 84, 6, NULL, NULL, 'Active'),
        (19, 86, 6, NULL, NULL, 'Active'),
        (20, 87, 6, NULL, NULL, 'Active'),
        (21, 88, 6, NULL, NULL, 'Active'),
        (22, 81, 8, NULL, NULL, 'Active'),
        (23, 84, 8, NULL, NULL, 'Active'),
        (24, 86, 8, NULL, NULL, 'Active'),
        (25, 89, 9, NULL, NULL, 'Active'),
        (26, 90, 9, NULL, NULL, 'Active'),
        (27, 76, 9, NULL, NULL, 'Active'),
        (28, 77, 9, NULL, NULL, 'Active'),
        (29, 67, 7, NULL, NULL, 'Active'),
        (37, 95, 10, NULL, NULL, 'Active'),
        (46, 3, 6, NULL, NULL, 'Active'),
        (47, 1, 6, NULL, NULL, 'Active'),
        (48, 2, 6, NULL, NULL, 'Active'),
        (50, 9, 6, NULL, NULL, 'Active'),
        (51, 8, 6, NULL, NULL, 'Active'),
        (52, 96, 6, NULL, NULL, 'Active'),
        (53, 135, 6, NULL, NULL, 'Active'),
        (54, 47, 6, NULL, NULL, 'Active'),
        (55, 41, 6, NULL, NULL, 'Active'),
        (56, 32, 6, NULL, NULL, 'Active'),
        (57, 31, 6, NULL, NULL, 'Active'),
        (58, 29, 6, NULL, NULL, 'Active'),
        (59, 132, 6, NULL, NULL, 'Active'),
        (60, 30, 6, NULL, NULL, 'Active'),
        (61, 129, 6, NULL, NULL, 'Active'),
        (63, 62, 6, NULL, NULL, 'Active'),
        (64, 69, 6, NULL, NULL, 'Active'),
        (65, 75, 6, NULL, NULL, 'Active'),
        (66, 97, 6, NULL, NULL, 'Active'),
        (67, 160, 6, NULL, NULL, 'Active'),
        (68, 147, 6, NULL, NULL, 'Active'),
        (69, 157, 6, NULL, NULL, 'Active'),
        (70, 153, 6, NULL, NULL, 'Active'),
        (71, 106, 6, NULL, NULL, 'Active'),
        (75, 17, 7, NULL, NULL, 'Active'),
        (76, 16, 7, NULL, NULL, 'Active'),
        (77, 5, 7, NULL, NULL, 'Active'),
        (78, 110, 7, NULL, NULL, 'Active'),
        (80, 11, 7, NULL, NULL, 'Active'),
        (81, 7, 7, NULL, NULL, 'Active'),
        (82, 6, 7, NULL, NULL, 'Active'),
        (83, 116, 7, NULL, NULL, 'Active'),
        (85, 33, 7, NULL, NULL, 'Active'),
        (86, 109, 7, NULL, NULL, 'Active'),
        (87, 37, 7, NULL, NULL, 'Active'),
        (88, 107, 7, NULL, NULL, 'Active'),
        (89, 75, 7, NULL, NULL, 'Active'),
        (90, 117, 7, NULL, NULL, 'Active'),
        (91, 115, 7, NULL, NULL, 'Active'),
        (92, 111, 7, NULL, NULL, 'Active'),
        (93, 152, 7, NULL, NULL, 'Active'),
        (94, 106, 7, NULL, NULL, 'Active'),
        (95, 108, 7, NULL, NULL, 'Active'),
        (115, 24, 2, NULL, NULL, 'Active'),
        (120, 17, 9, NULL, NULL, 'Active'),
        (121, 27, 2, NULL, NULL, 'Active'),
        (122, 27, 3, NULL, NULL, 'Active'),
        (124, 14, 3, NULL, NULL, 'Active'),
        (125, 16, 9, NULL, NULL, 'Active'),
        (126, 10, 7, NULL, NULL, 'Active'),
        (127, 10, 3, NULL, NULL, 'Active'),
        (128, 10, 5, NULL, NULL, 'Active'),
        (129, 24, 3, NULL, NULL, 'Active'),
        (130, 27, 7, NULL, NULL, 'Active'),
        (131, 89, 3, NULL, NULL, 'Active'),
        (132, 89, 7, NULL, NULL, 'Active'),
        (133, 5, 3, NULL, NULL, 'Active'),
        (134, 4, 3, NULL, NULL, 'Active'),
        (135, 4, 7, NULL, NULL, 'Active'),
        (136, 3, 7, NULL, NULL, 'Active'),
        (137, 1, 7, NULL, NULL, 'Active'),
        (138, 13, 3, NULL, NULL, 'Active'),
        (139, 13, 7, NULL, NULL, 'Active'),
        (140, 110, 3, NULL, NULL, 'Active'),
        (141, 19, 2, NULL, NULL, 'Active'),
        (146, 20, 6, NULL, NULL, 'Active'),
        (147, 11, 3, NULL, NULL, 'Active'),
        (148, 7, 6, NULL, NULL, 'Active'),
        (149, 8, 7, NULL, NULL, 'Active'),
        (150, 6, 6, NULL, NULL, 'Active'),
        (151, 18, 3, NULL, NULL, 'Active'),
        (152, 96, 7, NULL, NULL, 'Active'),
        (153, 47, 7, NULL, NULL, 'Active'),
        (154, 41, 7, NULL, NULL, 'Active'),
        (155, 130, 7, NULL, NULL, 'Active'),
        (156, 130, 3, NULL, NULL, 'Active'),
        (158, 130, 8, NULL, NULL, 'Active'),
        (159, 134, 6, NULL, NULL, 'Active'),
        (160, 134, 8, NULL, NULL, 'Active'),
        (161, 186, 7, NULL, NULL, 'Active'),
        (163, 127, 9, NULL, NULL, 'Active'),
        (164, 127, 7, NULL, NULL, 'Active'),
        (165, 49, 3, NULL, NULL, 'Active'),
        (167, 49, 7, NULL, NULL, 'Active'),
        (168, 132, 7, NULL, NULL, 'Active'),
        (169, 132, 11, NULL, NULL, 'Active'),
        (170, 101, 7, NULL, NULL, 'Active'),
        (171, 101, 3, NULL, NULL, 'Active'),
        (174, 188, 11, NULL, NULL, 'Active'),
        (175, 37, 5, NULL, NULL, 'Active'),
        (176, 103, 7, NULL, NULL, 'Active'),
        (177, 103, 3, NULL, NULL, 'Active'),
        (178, 189, 6, NULL, NULL, 'Active'),
        (179, 189, 7, NULL, NULL, 'Active'),
        (180, 68, 5, NULL, NULL, 'Active'),
        (181, 107, 9, NULL, NULL, 'Active'),
        (182, 191, 7, NULL, NULL, 'Active'),
        (183, 191, 5, NULL, NULL, 'Active'),
        (184, 191, 3, NULL, NULL, 'Active'),
        (185, 192, 9, NULL, NULL, 'Active'),
        (186, 194, 6, NULL, NULL, 'Active'),
        (187, 194, 7, NULL, NULL, 'Active'),
        (188, 28, 9, NULL, NULL, 'Active'),
        (189, 197, 3, NULL, NULL, 'Active')");


        $this->db->query("INSERT INTO `miniant_part_type_issue_type_steps` (`id`, `part_type_issue_type_id`, `step_id`, `required`, `needs_sq`, `immediate`, `description`, `creation_date`, `revision_date`, `status`) VALUES
        (4, 66, 2, 0, 0, 0, NULL, 0, 0, 'Active'),
        (5, 66, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (6, 66, 22, 0, 0, 0, NULL, 0, 0, 'Active'),
        (7, 76, 16, 1, 0, 0, NULL, 0, 0, 'Active'),
        (10, 76, 21, 0, 0, 0, NULL, 0, 0, 'Active'),
        (21, 120, 26, 0, 0, 0, NULL, 0, 0, 'Active'),
        (22, 75, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (23, 124, 1, 1, 1, 0, NULL, 0, 0, 'Active'),
        (24, 128, 8, 0, 0, 0, NULL, 0, 0, 'Active'),
        (25, 127, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (26, 126, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (27, 115, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (28, 129, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (29, 130, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (30, 121, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (31, 122, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (34, 21, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (35, 25, 26, 0, 0, 0, NULL, 0, 0, 'Active'),
        (36, 131, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (37, 132, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (38, 133, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (39, 77, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (40, 134, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (41, 135, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (42, 46, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (43, 136, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (44, 47, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (45, 137, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (46, 138, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (47, 139, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (48, 78, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (49, 140, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (50, 141, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (53, 82, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (54, 146, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (55, 147, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (56, 80, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (57, 148, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (58, 81, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (59, 51, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (60, 149, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (61, 150, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (62, 151, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (63, 52, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (64, 152, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (65, 54, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (66, 153, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (67, 83, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (68, 55, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (70, 154, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (71, 155, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (72, 156, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (74, 159, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (75, 161, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (76, 163, 26, 0, 0, 0, NULL, 0, 0, 'Active'),
        (77, 164, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (78, 165, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (80, 167, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (81, 59, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (83, 168, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (84, 169, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (85, 169, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (86, 171, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (87, 170, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (91, 174, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (92, 87, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (93, 175, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (94, 177, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (95, 176, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (96, 178, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (97, 179, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (98, 8, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (99, 180, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (100, 181, 3, 0, 0, 0, NULL, 0, 0, 'Active'),
        (101, 88, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (102, 5, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (103, 182, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (104, 183, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (105, 184, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (106, 186, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (107, 187, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (108, 152, 1, 0, 0, 0, NULL, 0, 0, 'Active'),
        (109, 26, 24, 0, 0, 0, NULL, 0, 0, 'Active'),
        (110, 26, 14, 0, 0, 0, NULL, 0, 0, 'Active'),
        (113, 124, 16, 0, 0, 0, NULL, 0, 0, 'Active'),
        (114, 76, 3, 0, 0, 0, NULL, 0, 0, 'Active'),
        (116, 188, 7, 0, 0, 0, NULL, 0, 0, 'Active'),
        (117, 188, 16, 1, 1, 0, NULL, 0, 0, 'Active'),
        (118, 189, 1, 1, 1, 0, NULL, 0, 0, 'Active'),
        (119, 185, 6, 1, 1, 0, NULL, 0, 0, 'Active')");

        $this->db->query("INSERT INTO `miniant_refrigerant_types` (`id`, `name`, `description`, `creation_date`, `revision_date`, `status`) VALUES
        (15, 'R404A', '', 0, 0, 'Active'),
        (16, 'R507', '', 0, 0, 'Active'),
        (17, 'R408A', '', 0, 0, 'Active'),
        (18, 'R502', '', 0, 0, 'Active'),
        (19, 'R407C', '', 0, 0, 'Active'),
        (20, 'R22', '', 0, 0, 'Active'),
        (21, 'R410A', '', 0, 0, 'Active'),
        (22, 'R134a', '', 0, 0, 'Active'),
        (23, 'SP34E', '', 0, 0, 'Active'),
        (24, 'R409A', '', 0, 0, 'Active'),
        (25, 'R12', '', 0, 0, 'Active'),
        (26, 'R123', '', 0, 0, 'Active'),
        (27, 'R11', '', 0, 0, 'Active'),
        (28, 'R717', '', 0, 0, 'Active'),
        (29, 'Other', '', 0, 0, 'Active')");

        $this->db->query("INSERT INTO `stages` (`id`, `name`, `label`, `description`, `in_checklist`, `granularity`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 'assignment_details', 'Assignment Details', 'Review details about this assignment, including notes from the office', 1, 'order', NULL, NULL, 'Active'),
        (2, 'unit_details', 'Equipment Details', 'Shows the units covered by this assignment', 1, 'assignment', NULL, NULL, 'Active'),
        (3, 'diagnostic_report', 'Diagnostic Report', 'Diagnose issues with this unit and report them here', 1, 'assignment', NULL, NULL, 'Active'),
        (4, 'required_parts', 'SQ Required Parts', 'Record the parts required for this SQ', 1, 'assignment', NULL, NULL, 'Active'),
        (5, 'dowds', 'DOWDs', 'Record the description of work done for each unit', 1, 'assignment', NULL, NULL, 'Active'),
        (7, 'postjob_checklist', 'Post-job checklist', 'The senior technician must make sure all these tasks are completed before completing this job', 1, 'order', NULL, NULL, 'Active'),
        (8, 'signature', 'Client Signature', 'Show the client/tenant this report and obtain their signature', 1, 'order', NULL, NULL, 'Active'),
        (9, 'installation_checklist', 'Installation checklist', 'Use this list to keep track of the tasks required to complete this installation. Click \"Yes\" for all completed tasks.', 1, 'assignment', NULL, NULL, 'Active'),
        (10, 'job_list', 'My Jobs', 'Use this calendar to see your next scheduled job', 0, 'order', NULL, NULL, 'Active'),
        (11, 'unit_serial', 'Equipment serial numbers', 'Record the serial number of the units you were asked to work on, if known.', 1, 'assignment', NULL, NULL, 'Active'),
        (12, 'parts_used', 'Parts used', 'Records the parts you used to repair the issues reported earlier', 1, 'assignment', NULL, NULL, 'Active'),
        (13, 'location_diagram', 'Unit location', 'Use this diagram editor and form to record location information about this unit', 1, 'assignment', NULL, NULL, 'Active'),
        (14, 'refrigerants_used', 'Refrigerants used', 'Record the refrigerant used during this job, including refrigerant reclaimed', 1, 'assignment', NULL, NULL, 'Active'),
        (15, 'photos', 'Site Photos', 'Upload photos of the job site, if required', 1, 'order', NULL, NULL, 'Active'),
        (16, 'maintenance_checklist', 'Maintenance Checklist', 'Use this list to keep track of the tasks required to complete this maintenance job. Click \"Yes\" for all completed tasks.', 1, 'assignment', NULL, NULL, 'Active'),
        (17, 'repair_checklist', 'Repair Checklist', 'Use this list to keep track of the tasks required to complete this repair job. Click \"Yes\" for all completed tasks.', 1, 'assignment', NULL, NULL, 'Active'),
        (18, 'unit_photos', 'Unit photos', 'Upload photos of the units and their various components (thermostat etc.)', 1, 'assignment', NULL, NULL, 'Active'),
        (19, 'order_dowd', 'General DOWD', 'The DOWD for an entire Job', 1, 'order', NULL, NULL, 'Active'),
        (20, 'office_notes', 'Office notes', 'Important information from the office', 1, 'order', NULL, NULL, 'Active')");

        $this->db->query("INSERT INTO `workflows` (`id`, `name`, `label`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 'breakdown', 'Breakdown', 0, 0, 'Active'),
        (2, 'installation', 'Installation', 0, 0, 'Active'),
        (3, 'maintenance', 'Maintenance', 0, 0, 'Active'),
        (4, 'repair', 'Repair', 0, 0, 'Active'),
        (5, 'service', 'Service', 0, 0, 'Active')");

        $this->db->query("INSERT INTO `workflow_stages` (`id`, `workflow_id`, `stage_id`, `stage_number`, `extra_param`, `senior_technician_only`, `required`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 1, 1, 1, NULL, 0, 1, NULL, NULL, 'Active'),
        (2, 1, 11, 3, NULL, 1, 1, NULL, NULL, 'Active'),
        (3, 1, 2, 5, NULL, 0, 1, NULL, NULL, 'Active'),
        (4, 1, 3, 7, NULL, 0, 1, NULL, NULL, 'Active'),
        (5, 1, 12, 8, NULL, 0, 1, NULL, NULL, 'Active'),
        (6, 1, 4, 10, NULL, 0, 1, NULL, NULL, 'Active'),
        (7, 1, 5, 11, NULL, 0, 1, NULL, NULL, 'Active'),
        (17, 1, 10, 16, NULL, 0, 1, NULL, NULL, 'Active'),
        (18, 1, 8, 14, NULL, 1, 1, NULL, NULL, 'Active'),
        (19, 1, 7, 13, NULL, 1, 1, NULL, NULL, 'Active'),
        (20, 1, 13, 4, NULL, 0, 1, NULL, NULL, 'Active'),
        (21, 1, 14, 9, NULL, 0, 1, NULL, NULL, 'Active'),
        (22, 1, 15, 2, 'pre-job', 1, 1, NULL, NULL, 'Active'),
        (24, 2, 1, 1, NULL, 0, 1, NULL, NULL, 'Active'),
        (25, 2, 15, 2, 'pre-job', 1, 1, NULL, NULL, 'Active'),
        (26, 2, 9, 4, NULL, 0, 1, NULL, NULL, 'Active'),
        (27, 2, 12, 5, NULL, 0, 1, NULL, NULL, 'Active'),
        (28, 2, 14, 7, NULL, 0, 1, NULL, NULL, 'Active'),
        (29, 2, 7, 10, NULL, 1, 1, NULL, NULL, 'Active'),
        (30, 2, 15, 11, 'post-job', 1, 1, NULL, NULL, 'Active'),
        (31, 2, 8, 12, NULL, 1, 1, NULL, NULL, 'Active'),
        (32, 2, 10, 14, NULL, 0, 1, NULL, NULL, 'Active'),
        (33, 3, 1, 1, NULL, 0, 1, NULL, NULL, 'Active'),
        (34, 3, 15, 2, 'pre-job', 1, 0, NULL, NULL, 'Active'),
        (35, 3, 13, 3, NULL, 0, 1, NULL, NULL, 'Active'),
        (36, 3, 2, 4, NULL, 0, 1, NULL, NULL, 'Active'),
        (37, 3, 16, 5, NULL, 0, 1, NULL, NULL, 'Active'),
        (38, 3, 3, 7, NULL, 0, 0, NULL, NULL, 'Active'),
        (39, 3, 12, 8, NULL, 0, 0, NULL, NULL, 'Active'),
        (40, 3, 14, 9, NULL, 0, 0, NULL, NULL, 'Active'),
        (41, 3, 4, 10, NULL, 0, 0, NULL, NULL, 'Active'),
        (42, 3, 5, 11, NULL, 0, 1, NULL, NULL, 'Active'),
        (44, 3, 7, 13, NULL, 1, 1, NULL, NULL, 'Active'),
        (46, 3, 8, 15, NULL, 1, 1, NULL, NULL, 'Active'),
        (47, 3, 10, 17, NULL, 0, 1, NULL, NULL, 'Active'),
        (48, 4, 1, 1, NULL, 0, 1, NULL, NULL, 'Active'),
        (49, 4, 15, 2, 'pre-job', 1, 1, NULL, NULL, 'Active'),
        (50, 4, 2, 4, NULL, 0, 1, NULL, NULL, 'Active'),
        (51, 4, 17, 5, NULL, 0, 1, NULL, NULL, 'Active'),
        (52, 4, 12, 6, NULL, 0, 1, NULL, NULL, 'Active'),
        (53, 4, 14, 7, NULL, 0, 1, NULL, NULL, 'Active'),
        (54, 4, 4, 11, NULL, 0, 1, NULL, NULL, 'Active'),
        (55, 4, 12, 9, 'diagnostic', 0, 0, NULL, NULL, 'Active'),
        (57, 4, 7, 13, NULL, 1, 1, NULL, NULL, 'Active'),
        (59, 4, 8, 14, NULL, 1, 1, NULL, NULL, 'Active'),
        (60, 4, 10, 16, NULL, 0, 1, NULL, NULL, 'Active'),
        (61, 2, 2, 3, NULL, 0, 1, NULL, NULL, 'Active'),
        (62, 1, 18, 6, NULL, 0, 1, NULL, NULL, 'Active'),
        (64, 5, 1, 1, NULL, 0, 1, NULL, NULL, 'Active'),
        (65, 5, 15, 2, 'pre-job', 1, 0, NULL, NULL, 'Active'),
        (66, 5, 13, 3, NULL, 0, 1, NULL, NULL, 'Active'),
        (67, 5, 2, 4, NULL, 0, 1, NULL, NULL, 'Active'),
        (68, 5, 16, 5, NULL, 0, 1, NULL, NULL, 'Active'),
        (69, 5, 3, 7, NULL, 0, 0, NULL, NULL, 'Active'),
        (70, 5, 12, 8, NULL, 0, 0, NULL, NULL, 'Active'),
        (71, 5, 14, 9, NULL, 0, 0, NULL, NULL, 'Active'),
        (72, 5, 4, 10, NULL, 0, 0, NULL, NULL, 'Active'),
        (73, 5, 5, 11, NULL, 0, 1, NULL, NULL, 'Active'),
        (74, 5, 7, 13, NULL, 1, 1, NULL, NULL, 'Active'),
        (76, 5, 8, 14, NULL, 1, 1, NULL, NULL, 'Active'),
        (77, 5, 10, 16, NULL, 0, 1, NULL, NULL, 'Active'),
        (78, 1, 19, 12, NULL, 1, 1, NULL, NULL, 'Active'),
        (79, 2, 19, 9, NULL, 1, 1, NULL, NULL, 'Active'),
        (80, 3, 19, 12, NULL, 1, 1, NULL, NULL, 'Active'),
        (81, 4, 19, 12, NULL, 1, 1, NULL, NULL, 'Active'),
        (82, 5, 19, 12, NULL, 1, 1, NULL, NULL, 'Active'),
        (83, 4, 13, 3, NULL, 1, 0, NULL, NULL, 'Active'),
        (84, 1, 20, 15, NULL, 1, 1, NULL, NULL, 'Active'),
        (85, 2, 20, 13, NULL, 1, 1, NULL, NULL, 'Active'),
        (86, 3, 20, 16, NULL, 1, 1, NULL, NULL, 'Active'),
        (87, 4, 20, 15, NULL, 1, 1, NULL, NULL, 'Active'),
        (88, 5, 20, 15, NULL, 1, 1, NULL, NULL, 'Active'),
        (89, 4, 3, 8, NULL, 0, 1, NULL, NULL, 'Active'),
        (90, 4, 14, 10, NULL, 0, 1, NULL, NULL, 'Active')");

        $this->db->query("INSERT INTO `workflow_stage_stages` (`id`, `workflow_stage_id`, `next_stage_id`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 1, 22, NULL, NULL, 'Active'),
        (6, 2, 20, NULL, NULL, 'Active'),
        (7, 3, 62, NULL, NULL, 'Active'),
        (8, 21, 7, NULL, NULL, 'Active'),
        (9, 4, 6, NULL, NULL, 'Active'),
        (10, 4, 7, NULL, NULL, 'Active'),
        (11, 5, 21, NULL, NULL, 'Active'),
        (12, 21, 6, NULL, NULL, 'Active'),
        (13, 6, 7, NULL, NULL, 'Active'),
        (14, 18, 84, NULL, NULL, 'Active'),
        (18, 20, 3, NULL, NULL, 'Active'),
        (20, 5, 6, NULL, NULL, 'Active'),
        (21, 5, 7, NULL, NULL, 'Active'),
        (22, 4, 5, NULL, NULL, 'Active'),
        (23, 22, 2, NULL, NULL, 'Active'),
        (27, 1, 3, NULL, NULL, 'Active'),
        (29, 24, 25, NULL, NULL, 'Active'),
        (30, 24, 61, NULL, NULL, 'Active'),
        (31, 25, 61, NULL, NULL, 'Active'),
        (32, 26, 27, NULL, NULL, 'Active'),
        (33, 27, 28, NULL, NULL, 'Active'),
        (38, 25, 31, NULL, NULL, 'Active'),
        (39, 31, 85, NULL, NULL, 'Active'),
        (40, 33, 34, NULL, NULL, 'Active'),
        (41, 33, 36, NULL, NULL, 'Active'),
        (42, 34, 35, NULL, NULL, 'Active'),
        (43, 35, 36, NULL, NULL, 'Active'),
        (44, 36, 37, NULL, NULL, 'Active'),
        (45, 37, 38, NULL, NULL, 'Active'),
        (46, 38, 39, NULL, NULL, 'Active'),
        (47, 39, 40, NULL, NULL, 'Active'),
        (48, 40, 41, NULL, NULL, 'Active'),
        (49, 41, 42, NULL, NULL, 'Active'),
        (55, 46, 86, NULL, NULL, 'Active'),
        (56, 37, 80, NULL, NULL, 'Active'),
        (57, 38, 41, NULL, NULL, 'Active'),
        (58, 39, 41, NULL, NULL, 'Active'),
        (59, 39, 42, NULL, NULL, 'Active'),
        (60, 40, 42, NULL, NULL, 'Active'),
        (62, 61, 26, NULL, NULL, 'Active'),
        (63, 29, 30, NULL, NULL, 'Active'),
        (64, 27, 79, NULL, NULL, 'Active'),
        (65, 28, 79, NULL, NULL, 'Active'),
        (66, 28, 85, NULL, NULL, 'Active'),
        (67, 27, 85, NULL, NULL, 'Active'),
        (68, 42, 86, NULL, NULL, 'Active'),
        (69, 42, 80, NULL, NULL, 'Active'),
        (70, 62, 4, NULL, NULL, 'Active'),
        (71, 7, 84, NULL, NULL, 'Active'),
        (72, 7, 78, NULL, NULL, 'Active'),
        (73, 7, 2, NULL, NULL, 'Active'),
        (74, 30, 31, NULL, NULL, 'Active'),
        (137, 65, 66, NULL, NULL, 'Active'),
        (138, 66, 67, NULL, NULL, 'Active'),
        (139, 66, 67, NULL, NULL, 'Active'),
        (140, 67, 68, NULL, NULL, 'Active'),
        (141, 68, 69, NULL, NULL, 'Active'),
        (142, 68, 88, NULL, NULL, 'Active'),
        (144, 69, 70, NULL, NULL, 'Active'),
        (145, 69, 72, NULL, NULL, 'Active'),
        (147, 70, 71, NULL, NULL, 'Active'),
        (148, 70, 72, NULL, NULL, 'Active'),
        (149, 70, 73, NULL, NULL, 'Active'),
        (150, 71, 72, NULL, NULL, 'Active'),
        (151, 71, 73, NULL, NULL, 'Active'),
        (153, 72, 73, NULL, NULL, 'Active'),
        (156, 76, 88, NULL, NULL, 'Active'),
        (161, 64, 65, NULL, NULL, 'Active'),
        (162, 73, 82, NULL, NULL, 'Active'),
        (163, 73, 88, NULL, NULL, 'Active'),
        (164, 78, 19, NULL, NULL, 'Active'),
        (168, 80, 44, NULL, NULL, 'Active'),
        (169, 48, 49, NULL, NULL, 'Active'),
        (170, 48, 50, NULL, NULL, 'Active'),
        (171, 49, 83, NULL, NULL, 'Active'),
        (172, 50, 51, NULL, NULL, 'Active'),
        (173, 51, 52, NULL, NULL, 'Active'),
        (174, 52, 53, NULL, NULL, 'Active'),
        (176, 52, 89, NULL, NULL, 'Active'),
        (177, 53, 89, NULL, NULL, 'Active'),
        (179, 54, 60, NULL, NULL, 'Active'),
        (180, 55, 54, NULL, NULL, 'Active'),
        (181, 55, 90, NULL, NULL, 'Active'),
        (182, 81, 57, NULL, NULL, 'Active'),
        (185, 59, 87, NULL, NULL, 'Active'),
        (186, 82, 74, NULL, NULL, 'Active'),
        (187, 79, 29, NULL, NULL, 'Active'),
        (188, 7, 3, NULL, NULL, 'Active'),
        (189, 27, 61, NULL, NULL, 'Active'),
        (190, 28, 61, NULL, NULL, 'Active'),
        (191, 30, 85, NULL, NULL, 'Active'),
        (192, 37, 36, NULL, NULL, 'Active'),
        (193, 37, 86, NULL, NULL, 'Active'),
        (194, 38, 42, NULL, NULL, 'Active'),
        (195, 42, 36, NULL, NULL, 'Active'),
        (196, 44, 46, NULL, NULL, 'Active'),
        (197, 83, 50, NULL, NULL, 'Active'),
        (198, 55, 81, NULL, NULL, 'Active'),
        (199, 57, 59, NULL, NULL, 'Active'),
        (200, 64, 67, NULL, NULL, 'Active'),
        (201, 68, 67, NULL, NULL, 'Active'),
        (202, 68, 82, NULL, NULL, 'Active'),
        (203, 69, 73, NULL, NULL, 'Active'),
        (204, 73, 67, NULL, NULL, 'Active'),
        (205, 74, 76, NULL, NULL, 'Active'),
        (206, 84, 17, NULL, NULL, 'Active'),
        (207, 85, 32, NULL, NULL, 'Active'),
        (208, 86, 47, NULL, NULL, 'Active'),
        (209, 87, 60, NULL, NULL, 'Active'),
        (210, 88, 77, NULL, NULL, 'Active'),
        (211, 19, 18, NULL, NULL, 'Active'),
        (212, 54, 81, NULL, NULL, 'Active'),
        (213, 55, 60, NULL, NULL, 'Active'),
        (214, 89, 55, NULL, NULL, 'Active'),
        (215, 89, 54, NULL, NULL, 'Active'),
        (216, 89, 81, NULL, NULL, 'Active'),
        (217, 89, 60, NULL, NULL, 'Active'),
        (218, 90, 54, NULL, NULL, 'Active'),
        (219, 90, 81, NULL, NULL, 'Active'),
        (220, 90, 60, NULL, NULL, 'Active')");

        $this->db->query("INSERT INTO `miniant_steps` (`id`, `name`, `past_tense`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 'Replace', 'Replaced [[part]]', 1392626060, 1392626060, 'Active'),
        (2, 'Fix', 'Fixed [[part]]', 1392626060, 1392626060, 'Active'),
        (3, 'Clean', 'Cleaned [[part]]', 1392626138, 1392626138, 'Active'),
        (4, 'Recharge with refrigerant', 'Recharged [[part]] with refrigerant', 1392626156, 1392626156, 'Active'),
        (5, 'Redesign', 'Redesigned [[part]]', 0, 0, 'Active'),
        (6, 'Readjust', 'Readjusted [[part]]', 0, 0, 'Active'),
        (7, 'Check left all operational', 'Checked left all operational', 0, 0, 'Active'),
        (8, 'Reset', 'Reset [[part]]', 0, 0, 'Active'),
        (9, 'Flush with nitrogen', 'Flushed [[part]] with nitrogen', 0, 0, 'Active'),
        (10, 'Rewire electrics', 'Rewired electrics', 0, 0, 'Active'),
        (11, 'Pressure Test', 'Pressure tested [[part]]', 0, 0, 'Active'),
        (12, 'Reclaim refrigerant', 'Reclaimed refrigerant', 0, 0, 'Active'),
        (13, 'Remove', 'Removed [[part]]', 0, 0, 'Active'),
        (14, 'Evaluate system', 'Evaluated system', 0, 0, 'Active'),
        (15, 'Locate', 'Located [[part]]', 0, 0, 'Active'),
        (16, 'Advise', 'Advised', 0, 0, 'Active'),
        (17, 'Clear', 'Cleared [[part]]', 0, 0, 'Active'),
        (18, 'Switch off', 'Switched off [[part]]', 0, 0, 'Active'),
        (19, 'Remove burnt out parts', 'Removed burnt out parts', 0, 0, 'Active'),
        (20, 'Pick up new parts', 'Picked up new parts', 0, 0, 'Active'),
        (21, 'Install new', 'Installed new [[part]]', 0, 0, 'Active'),
        (22, 'Leak test', 'Leak tested [[part]]', 0, 0, 'Active'),
        (23, 'Pump system down', 'Pumped system down', 0, 0, 'Active'),
        (24, 'Clean with brush', 'Cleaned [[part]] with brush', 0, 0, 'Active'),
        (25, 'Reinstall existing part', 'Reinstalled existing  [[part]]', 0, 0, 'Active'),
        (26, 'Wash', 'Washed [[part]]', 0, 0, 'Active')");


        $this->db->query("INSERT INTO `miniant_step_parts` (`id`, `part_type_issue_type_step_id`, `part_type_id`, `quantity`, `creation_date`, `revision_date`, `status`) VALUES
        (3, 7, 1, 1, 0, 0, 'Active'),
        (4, 7, 1, 1, 0, 0, 'Active'),
        (5, 7, 1, 1, 0, 0, 'Active'),
        (7, 9, 1, 1, 0, 0, 'Active'),
        (8, 9, 3, 3, 0, 0, 'Active'),
        (9, 16, 1, 1, 0, 0, 'Active'),
        (10, 3, 23, 14, 0, 0, 'Active'),
        (11, 21, 17, 1, 0, 0, 'Active'),
        (12, 22, 17, 1, 0, 0, 'Active'),
        (13, 23, 14, 4, 0, 0, 'Active'),
        (14, 24, 10, 1, 0, 0, 'Active'),
        (15, 25, 10, 1, 0, 0, 'Active'),
        (16, 26, 10, 1, 0, 0, 'Active'),
        (17, 29, 27, 1, 0, 0, 'Active'),
        (18, 34, 28, 5, 0, 0, 'Active'),
        (19, 39, 5, 3, 0, 0, 'Active'),
        (20, 41, 4, 3, 0, 0, 'Active'),
        (21, 42, 3, 3, 0, 0, 'Active'),
        (22, 44, 1, 1, 0, 0, 'Active'),
        (23, 47, 13, 2, 0, 0, 'Active'),
        (24, 48, 17, 2, 0, 0, 'Active'),
        (25, 50, 19, 1, 0, 0, 'Active'),
        (26, 53, 6, 2, 0, 0, 'Active'),
        (27, 54, 20, 2, 0, 0, 'Active'),
        (28, 57, 7, 2, 0, 0, 'Active'),
        (29, 59, 8, 2, 0, 0, 'Active'),
        (30, 61, 6, 2, 0, 0, 'Active'),
        (31, 62, 18, 2, 0, 0, 'Active'),
        (32, 63, 17, 8, 0, 0, 'Active'),
        (33, 65, 17, 2, 0, 0, 'Active'),
        (34, 68, 17, 2, 0, 0, 'Active'),
        (35, 74, 17, 2, 0, 0, 'Active'),
        (36, 75, 17, 2, 0, 0, 'Active'),
        (37, 77, 17, 1, 0, 0, 'Active'),
        (38, 80, 17, 2, 0, 0, 'Active'),
        (39, 81, 17, 5, 0, 0, 'Active'),
        (40, 87, 17, 3, 0, 0, 'Active'),
        (41, 92, 17, 4, 0, 0, 'Active'),
        (42, 95, 17, 2, 0, 0, 'Active'),
        (43, 96, 17, 2, 0, 0, 'Active'),
        (44, 98, 17, 2, 0, 0, 'Active'),
        (45, 101, 17, 3, 0, 0, 'Active'),
        (46, 103, 17, 2, 0, 0, 'Active'),
        (47, 106, 17, 4, 0, 0, 'Active'),
        (48, 64, 23, 6, 0, 0, 'Active'),
        (49, 108, 195, 30, 0, 0, 'Active'),
        (50, 108, 96, 1, 0, 0, 'Active'),
        (51, 109, 195, 20, 0, 0, 'Active'),
        (52, 110, 195, 30, 0, 0, 'Active'),
        (53, 110, 90, 1, 0, 0, 'Active'),
        (54, 116, 16, 3, 0, 0, 'Active'),
        (55, 119, 195, 1, 0, 0, 'Active'),
        (56, 119, 192, 1, 0, 0, 'Active')");

        $this->db->query("INSERT INTO `miniant_unitry_types` (`id`, `unit_type_id`, `name`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 18, 'Evaporative', 0, 0, 'Active'),
        (2, 19, 'R/C Wall Split', 0, 0, 'Active'),
        (3, 19, 'R/C Cassette', 0, 0, 'Active'),
        (4, 19, 'R/C Ducted', 0, 0, 'Active'),
        (5, 19, 'Package', 0, 0, 'Active'),
        (6, 19, 'Chilled water', 0, 0, 'Active'),
        (7, 19, 'RAC', 0, 0, 'Active'),
        (8, 19, 'Under Ceiling', 0, 0, 'Active')");

        $this->db->query("INSERT INTO `capabilities` (`id`, `name`, `description`, `type`, `dependson`, `creation_date`, `revision_date`, `status`) VALUES
        (11, 'servicequotes:doanything', 'Do anything to do with SQs', 'write', 1, 1376977609, 1376977609, 'Active'),
        (12, 'orders:doanything', 'Do anything to do with Jobs', 'write', 1, 1376977609, 1376977609, 'Active'),
        (13, 'servicequotes:editsqs', 'Edit all SQs', 'write', 11, 1376977609, 1376977609, 'Active'),
        (14, 'servicequotes:deletesqs', 'Delete SQs', 'write', 11, 1376977609, 1376977609, 'Active'),
        (15, 'servicequotes:viewsqs', 'View all SQs', 'read', 13, 1376977609, 1376977609, 'Active'),
        (16, 'servicequotes:writesqs', 'Create SQs', 'write', 13, 1376977609, 1376977609, 'Active'),
        (17, 'orders:editorders', 'Edit all Jobs', 'write', 12, 1376977609, 1376977609, 'Active'),
        (18, 'orders:deleteorders', 'Delete Jobs', 'write', 12, 1376977609, 1376977609, 'Active'),
        (19, 'orders:vieworders', 'View all Jobs', 'read', 12, 1376977609, 1376977609, 'Active'),
        (20, 'orders:writeorders', 'Create Jobs', 'write', 12, 1376977609, 1376977609, 'Active'),
        (22, 'orders:viewclientinfo', 'View client info on jobs', 'read', 12, 1376977609, 1376977609, 'Active'),
        (24, 'orders:allocateorders', 'Allocate Jobs to technicians', 'write', 12, 1376977609, 1376977609, 'Active'),
        (25, 'servicequotes:reviewsq', 'Review SQs', 'write', 13, 1376977609, 1376977609, 'Active'),
        (26, 'orders:revieworders', 'Review Jobs', 'write', 12, 1376977609, 1376977609, 'Active'),
        (28, 'orders:changestatustosent', 'Change status of Jobs to \"Sent\"', 'write', 13, 1376977609, 1376977609, 'Active'),
        (29, 'site:createrepair_jobnumbers', 'Create Job Numbers', 'write', 1, 1376977609, 1376977609, 'Active'),
        (30, 'site:ponumbers', 'Create PO Numbers', 'write', 1, 1376977609, 1376977609, 'Active'),
        (32, 'orders:editassignedorders', 'Edit Jobs assigned to self', 'write', 12, 1376977609, 1376977609, 'Active'),
        (34, 'orders:viewassignedorders', 'View Jobs assigned to self', 'write', 32, 1376977609, 1376977609, 'Active'),
        (39, 'site:editbrands', 'Edit brands', 'write', 1, 1378254149, 1378254149, 'Active'),
        (40, 'site:deletebrands', 'Delete brands', 'write', 1, 1378254149, 1378254149, 'Active'),
        (41, 'site:viewbrands', 'View brands', 'read', 39, 1378254149, 1378254149, 'Active'),
        (42, 'site:writebrands', 'Create brands', 'write', 39, 1378254149, 1378254149, 'Active'),
        (43, 'orders:editcalldate', 'Set the call date of the job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (44, 'orders:editappointmentdate', 'Set the appointment date of the job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (45, 'orders:manageunits', 'Manage the units of a job', 'read', 12, 1379908737, 1379908737, 'Active'),
        (46, 'orders:addmessage', 'Add a message to a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (47, 'orders:viewmessages', 'View the messages attached to a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (48, 'orders:editmessages', 'Edit the messages attached to a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (49, 'orders:deletemessages', 'Delete the messages attached to a job', 'write', 48, 1379908737, 1379908737, 'Active'),
        (50, 'orders:editbillingcontact', 'Change the billing contact associated with a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (51, 'orders:viewcontactnumber', 'View the phone number of a contact associated with a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (52, 'orders:viewcontactemail', 'View the email address of a contact associated with a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (53, 'orders:viewbusinessname', 'View the business name of a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (54, 'orders:viewbusinessaddress', 'View the business address of a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (55, 'orders:editsiteaddress', 'Change the job site address of a job', 'write', 12, 1379908737, 1379908737, 'Active'),
        (56, 'orders:changestatus', 'Change the status of a job', 'write', 12, 1379915684, 1379915684, 'Active'),
        (58, 'orders:editordertype', 'Set the type of jobs', 'write', 12, 0, 0, 'Active'),
        (59, 'orders:editequipmenttype', 'Set the equipment type of jobs', 'write', 12, 0, 0, 'Active'),
        (60, 'orders:editcustomerponumber', 'Set the customer Purchase Order number of orders', 'write', 12, 0, 0, 'Active'),
        (61, 'orders:editaccount', 'Change the account associated with a jobs', 'write', 12, 0, 0, 'Active'),
        (62, 'orders:editsitecontact', 'Change the job site contact associated with a jobs', 'write', 12, 0, 0, 'Active'),
        (66, 'orders:viewsitecontact', 'View job site contact details for jobs', 'read', 12, 0, 0, 'Active'),
        (67, 'orders:viewsiteaddress', 'View job site address details for jobs', 'read', 12, 0, 0, 'Active'),
        (68, 'orders:editlinkeddocuments', 'Edit a jobs linked documents', 'write', 12, 0, 0, 'Active'),
        (69, 'orders:viewlinkeddocuments', 'View a jobs linked documents', 'read', 68, 0, 0, 'Active'),
        (70, 'orders:editevents', 'Edit job system events', 'write', 12, 1387176470, 1387176470, 'Active'),
        (71, 'orders:writeevents', 'Create job system events', 'write', 70, 1387176510, 1387176510, 'Active'),
        (72, 'orders:deleteevents', 'Delete job system events', 'write', 70, 1387176510, 1387176510, 'Active'),
        (73, 'diagnostics:doanything', 'Do everything related to diagnostics', 'write', 1, 1387336765, 1387336765, 'Active'),
        (74, 'assignments:edit', 'Edit assignments', 'write', 73, 1387336861, 1387336861, 'Active'),
        (75, 'diagnostics:write', 'Create diagnostics', 'write', 74, 1387336861, 1387336861, 'Active'),
        (76, 'diagnostics:view', 'View diagnostics', 'read', 74, 1387336861, 1387336861, 'Active'),
        (77, 'assignments:unschedule', 'Unschedule assignments', 'write', 74, 1387336861, 1408504751, 'Active'),
        (78, 'repair_jobs:doanything', 'Do anything related to jobs', 'write', 1, 1387345047, 1387345047, 'Active'),
        (79, 'repair_jobs:edit', 'Edit jobs', 'write', 78, 1387345116, 1387345116, 'Active'),
        (80, 'repair_jobs:view', 'View jobs', 'write', 79, 1387345116, 1387345116, 'Active'),
        (81, 'repair_jobs:write', 'Create new jobs', 'write', 78, 1387345116, 1387345116, 'Active'),
        (82, 'repair_jobs:schedule', 'Schedule jobs', 'write', 78, 1387345116, 1387345116, 'Active'),
        (93, 'orders:viewbillingcontact', 'View the billing contact details for an job', 'read', 12, 1392276905, 1392276905, 'Active'),
        (95, 'orders:editothermessages', 'Edit other people''s notes', 'read', 12, 1392362458, 1392364466, 'Active'),
        (96, 'orders:deleteothermessages', 'Delete other people''s notes', 'read', 12, 1392362478, 1392364450, 'Active'),
        (97, 'orders:editunitdetails', 'Edit the details of the units of an job', 'read', 12, 1392364132, 1392364132, 'Active'),
        (98, 'orders:viewdiagnosticrules', 'View diagnostic rules/steps', 'read', 12, 0, 0, 'Active'),
        (99, 'orders:addattachment', 'Add attachments to Jobs', 'write', 12, 0, 0, 'Active'),
        (101, 'orders:editpreferredstartdate', 'Edit the starting date of a Maintenance contract', 'read', 12, 1402899595, 1402899595, 'Active'),
        (102, 'orders:editmaintenanceinterval', 'Edit the schedule of a maintenance contract', 'read', 12, 1402899636, 1402899636, 'Active'),
        (103, 'assignments:editevents', 'Edit assignment system events', 'write', 12, 1387176470, 1387176470, 'Active'),
        (104, 'diagnostics:editevents', 'Edit diagnostic system events', 'write', 12, 1387176470, 1387176470, 'Active'),
        (105, 'repair_jobs:editevents', 'Edit job system events', 'write', 12, 1387176470, 1387176470, 'Active'),
        (106, 'assignments:writeevents', 'Create assignment system events', 'write', 103, 1387176510, 1387176510, 'Active'),
        (107, 'assignments:deleteevents', 'Delete assignment system events', 'write', 103, 1387176510, 1387176510, 'Active'),
        (108, 'diagnostics:writeevents', 'Create diagnostic system events', 'write', 104, 1387176510, 1387176510, 'Active'),
        (109, 'diagnostics:deleteevents', 'Delete diagnostic system events', 'write', 104, 1387176510, 1387176510, 'Active'),
        (110, 'repair_jobs:writeeventsrepair_', 'Create job system events', 'write', 105, 1387176510, 1387176510, 'Active'),
        (111, 'repair_jobs:deleteevents', 'Delete job system events', 'write', 105, 1387176510, 1387176510, 'Active'),
        (112, 'orders:editstatuses', 'Edit job system statuses', 'write', 12, 1387176470, 1387176470, 'Active'),
        (113, 'orders:writestatuses', 'Create job system statuses', 'write', 112, 1387176510, 1387176510, 'Active'),
        (114, 'orders:deletestatuses', 'Delete job system statuses', 'write', 112, 1387176510, 1387176510, 'Active'),
        (115, 'assignments:editstatuses', 'Edit assignment system statuses', 'write', 12, 1387176470, 1387176470, 'Active'),
        (116, 'assignments:writestatuses', 'Create assignment system statuses', 'write', 115, 1387176510, 1387176510, 'Active'),
        (117, 'assignments:deletestatuses', 'Delete assignment system statuses', 'write', 115, 1387176510, 1387176510, 'Active'),
        (118, 'diagnostics:editstatuses', 'Edit diagnostic system statuses', 'write', 12, 1387176470, 1387176470, 'Active'),
        (119, 'diagnostics:writestatuses', 'Create diagnostic system statuses', 'write', 118, 1387176510, 1387176510, 'Active'),
        (120, 'diagnostics:deletestatuses', 'Delete diagnostic system statuses', 'write', 118, 1387176510, 1387176510, 'Active'),
        (121, 'repair_jobs:editstatuses', 'Edit job system statuses', 'write', 12, 1387176470, 1387176470, 'Active'),
        (122, 'repair_jobs:writestatuses', 'Create job system statuses', 'write', 121, 1387176510, 1387176510, 'Active'),
        (123, 'repair_jobs:deletestatuses', 'Delete job system statuses', 'write', 121, 1387176510, 1387176510, 'Active'),
        (124, 'assignments:recordpartsused', 'Record the parts used to complete a repair/maintenance job', 'read', 74, 1411942705, 1411942705, 'Active'),
        (126, 'reports:viewjob_sites', 'View job site report', 'read', 125, 1412650488, 1412650488, 'Active'),
        (127, 'reports:viewjobs', 'View list of past jobs', 'read', 125, 1412650503, 1412650503, 'Active'),
        (128, 'assignments:recordrefrigerantsused', 'Record the refrigerant used to complete a repair/maintenance job', 'read', 74, 1411942705, 1411942705, 'Active'),
        (129, 'site:editrefrigerant_types', 'Edit refrigerant types', 'read', 1, 1414047260, 1414047408, 'Active'),
        (130, 'site:deleterefrigerant_types', 'Delete refrigerant types', 'read', 129, 1414047295, 1414047295, 'Active'),
        (131, 'site:writerefrigerant_types', 'Create new refrigerant types', 'read', 129, 1414047333, 1414047333, 'Active'),
        (132, 'site:viewrefrigerant_types', 'View refrigerant types', 'read', 129, 1414047391, 1414047391, 'Active'),
        (133, 'maintenance_contracts:editcontracts', 'Ability to edit maintenance contracts', 'read', 1, 1415844363, 1415844363, 'Active'),
        (134, 'maintenance_contracts:viewcontracts', 'Browse maintenance contracts', 'read', 133, 1415844416, 1415844416, 'Active'),
        (135, 'maintenance_contracts:deletecontracts', 'Delete maintenance contracts', 'read', 1, 1415844450, 1415844450, 'Active'),
        (136, 'maintenance_contracts:writecontracts', 'Create new maintenance contracts', 'read', 133, 1415844870, 1415844870, 'Active'),
        (137, 'maintenance_contracts:editstatuses', 'Edit maintenance contract system statuses', 'write', 133, 1387176470, 1387176470, 'Active'),
        (138, 'maintenance_contracts:writestatuses', 'Create maintenance contract system statuses', 'write', 137, 1387176510, 1387176510, 'Active'),
        (139, 'maintenance_contracts:deletestatuses', 'Delete maintenance contract system statuses', 'write', 137, 1387176510, 1387176510, 'Active'),
        (140, 'maintenance_contracts:manageunits', 'Manage the equipment of a maintenance contract', 'read', 133, 1415930345, 1415930345, 'Active'),
        (141, 'servicequotes:editstatuses', 'Edit an SQ''s statuses', 'write', 11, 0, 0, 'Active'),
        (142, 'orders:viewdocuments', 'Ability to view job documents (Invoices)', 'read', 12, 1425020493, 1425020493, 'Active'),
        (143, 'orders:viewassignments', 'Ability to view assignments', 'read', 12, 1425020493, 1425020493, 'Active'),
        (144, 'orders:editassignments', 'Ability to edit assignments', 'write', 12, 1425020493, 1425020493, 'Active')
        ");

        $this->db->query("INSERT INTO `roles` (`id`, `parent_id`, `name`, `description`, `creation_date`, `revision_date`, `status`) VALUES
        (2, 1, 'Director', 'The company''s director', 1376531589, 1376531589, 'Active'),
        (3, 2, 'Operations Manager', 'The operations manager creates and allocates new jobs, and reviews the inputs of technicians', 1376531589, 1376531589, 'Active'),
        (4, 5, 'Technician', 'A technician uses the system on site, to complete and submit forms, and keep track of his/her job queue', 1376531589, 1428386899, 'Active'),
        (5, 3, 'Supervisor', 'A senior technician who can review the work of other technicians', 1376531589, 1376531589, 'Active'),
        (6, 2, 'Accounts', 'Accounts', 1376531589, 1376531589, 'Active'),
        (8, 2, 'Service Manager', 'Takes care of making appointments for jobs, assigning jobs to technicians, reviewing job reports from technician, and a few other things', 0, 1428386754, 'Active')");

        $this->db->query("INSERT INTO `roles_capabilities` (`id`, `role_id`, `capability_id`, `creation_date`, `revision_date`, `status`) VALUES
        (6, 2, 40, 0, 0, 'Active'),
        (7, 2, 39, 0, 0, 'Active'),
        (10, 2, 29, 0, 0, 'Active'),
        (11, 2, 30, 0, 0, 'Active'),
        (13, 2, 11, 0, 0, 'Active'),
        (14, 2, 12, 0, 0, 'Active'),
        (15, 4, 32, 0, 0, 'Active'),
        (16, 6, 28, 0, 0, 'Active'),
        (17, 6, 20, 0, 0, 'Active'),
        (18, 6, 22, 0, 0, 'Active'),
        (19, 6, 19, 0, 0, 'Active'),
        (20, 6, 29, 0, 0, 'Active'),
        (21, 6, 30, 0, 0, 'Active'),
        (22, 6, 15, 0, 0, 'Active'),
        (26, 6, 34, 0, 0, 'Active'),
        (27, 3, 15, 0, 0, 'Active'),
        (28, 3, 19, 0, 0, 'Active'),
        (29, 3, 22, 0, 0, 'Active'),
        (30, 3, 24, 0, 0, 'Active'),
        (31, 3, 26, 0, 0, 'Active'),
        (32, 3, 44, 0, 0, 'Active'),
        (33, 3, 46, 0, 0, 'Active'),
        (34, 3, 48, 0, 0, 'Active'),
        (35, 3, 50, 0, 0, 'Active'),
        (36, 3, 51, 0, 0, 'Active'),
        (37, 3, 52, 0, 0, 'Active'),
        (38, 3, 53, 0, 0, 'Active'),
        (39, 3, 54, 0, 0, 'Active'),
        (40, 3, 47, 0, 0, 'Active'),
        (41, 3, 55, 0, 0, 'Active'),
        (42, 3, 45, 0, 0, 'Active'),
        (45, 3, 17, 0, 0, 'Active'),
        (46, 3, 18, 0, 0, 'Active'),
        (48, 3, 56, 0, 0, 'Active'),
        (49, 8, 46, 0, 0, 'Active'),
        (50, 8, 24, 0, 0, 'Active'),
        (51, 8, 50, 0, 0, 'Active'),
        (53, 8, 18, 0, 0, 'Active'),
        (54, 8, 55, 0, 0, 'Active'),
        (55, 8, 48, 0, 0, 'Active'),
        (56, 8, 17, 0, 0, 'Active'),
        (57, 8, 45, 0, 0, 'Active'),
        (58, 8, 26, 0, 0, 'Active'),
        (59, 8, 44, 0, 0, 'Active'),
        (60, 8, 54, 0, 0, 'Active'),
        (61, 8, 53, 0, 0, 'Active'),
        (62, 8, 22, 0, 0, 'Active'),
        (63, 8, 52, 0, 0, 'Active'),
        (64, 8, 51, 0, 0, 'Active'),
        (65, 8, 47, 0, 0, 'Active'),
        (66, 8, 19, 0, 0, 'Active'),
        (69, 6, 43, 0, 0, 'Active'),
        (70, 6, 46, 0, 0, 'Active'),
        (71, 6, 47, 0, 0, 'Active'),
        (72, 6, 51, 0, 0, 'Active'),
        (73, 6, 52, 0, 0, 'Active'),
        (74, 6, 53, 0, 0, 'Active'),
        (75, 6, 54, 0, 0, 'Active'),
        (76, 6, 50, 0, 0, 'Active'),
        (77, 6, 55, 0, 0, 'Active'),
        (78, 6, 17, 0, 0, 'Active'),
        (79, 4, 45, 0, 0, 'Active'),
        (80, 4, 47, 0, 0, 'Active'),
        (81, 4, 46, 0, 0, 'Active'),
        (82, 6, 45, 0, 0, 'Active'),
        (83, 6, 58, 0, 0, 'Active'),
        (84, 6, 59, 0, 0, 'Active'),
        (85, 6, 60, 0, 0, 'Active'),
        (86, 6, 61, 0, 0, 'Active'),
        (87, 6, 62, 0, 0, 'Active'),
        (88, 6, 57, 0, 0, 'Active'),
        (89, 6, 63, 0, 0, 'Active'),
        (90, 6, 67, 0, 0, 'Active'),
        (91, 6, 66, 0, 0, 'Active'),
        (92, 6, 68, 1384917942, 1384917942, 'Active'),
        (93, 8, 12, 0, 0, 'Active'),
        (94, 8, 73, 0, 0, 'Active'),
        (95, 8, 78, 1387345148, 1387345148, 'Active'),
        (96, 8, 63, 0, 0, 'Active'),
        (97, 8, 39, 0, 0, 'Active'),
        (99, 8, 29, 0, 0, 'Active'),
        (100, 8, 30, 0, 0, 'Active'),
        (101, 8, 83, 0, 0, 'Active'),
        (102, 8, 3, 0, 0, 'Active'),
        (105, 4, 97, 0, 0, 'Active'),
        (106, 4, 16, 0, 0, 'Active'),
        (108, 6, 100, 0, 0, 'Active'),
        (110, 6, 93, 0, 0, 'Active'),
        (112, 6, 97, 0, 0, 'Active'),
        (113, 6, 99, 0, 0, 'Active'),
        (114, 6, 101, 0, 0, 'Active'),
        (115, 6, 102, 0, 0, 'Active'),
        (116, 8, 13, 0, 0, 'Active'),
        (117, 3, 3, 0, 0, 'Active'),
        (118, 3, 84, 0, 0, 'Active'),
        (119, 3, 65, 0, 0, 'Active'),
        (120, 3, 39, 0, 0, 'Active'),
        (121, 3, 40, 0, 0, 'Active'),
        (122, 3, 77, 0, 0, 'Active'),
        (123, 3, 73, 0, 0, 'Active'),
        (124, 3, 12, 0, 0, 'Active'),
        (125, 3, 11, 0, 0, 'Active'),
        (127, 4, 124, 0, 0, 'Active'),
        (128, 3, 125, 0, 0, 'Active'),
        (129, 4, 128, 0, 0, 'Active'),
        (131, 3, 129, 0, 0, 'Active'),
        (132, 4, 131, 0, 0, 'Active'),
        (133, 4, 132, 0, 0, 'Active'),
        (134, 3, 83, 0, 0, 'Active'),
        (135, 3, 143, 0, 0, 'Active'),
        (136, 3, 63, 0, 0, 'Active'),
        (138, 2, 9, 0, 0, 'Active'),
        (141, 2, 10, 0, 0, 'Active'),
        (144, 2, 4, 0, 0, 'Active'),
        (145, 2, 83, 0, 0, 'Active'),
        (146, 2, 85, 0, 0, 'Active'),
        (147, 2, 63, 0, 0, 'Active'),
        (148, 2, 125, 0, 0, 'Active'),
        (149, 3, 10, 0, 0, 'Active'),
        (150, 3, 9, 0, 0, 'Active'),
        (151, 2, 3, 0, 0, 'Active'),
        (152, 6, 5, 0, 0, 'Active'),
        (153, 2, 143, 0, 0, 'Active')");

        $this->db->query("INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `password`, `temp_password`, `registerkey`, `signature`, `cc_number`, `cc_type`, `cc_expiry`, `cc_name`, `creation_date`, `revision_date`, `status`, `type`) VALUES
        (14, 'Director', 'Mr.', 'director', 'sha256:1000:fQfsq91dvUt98jht2UWK46RXneVBJ+Kp:xeV0tRhwy7T0FkPS/Vm+O9lfQqaJXsS6', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428387081, 1428387081, 'Active', 'staff'),
        (15, 'Director2', 'test', '', 'sha256:1000:bFieHSgrp8ZfdZw6V1tEqdpqVUvRfGZf:Sv1PQtJ/nkSLv+5GI4fnnE9p/nBH/WnS', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428417992, 1428417992, 'Active', 'staff'),
        (17, 'Alan', 'Supervisor', 'supervisor', 'sha256:1000:FHQd0wEN+0dhG9Gs5TvTHsCcYQmDrNJz:9NAxppPsuwWnfuEx0Qf0FcLKabj/k4c3', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428636932, 1428636932, 'Active', 'staff'),
        (19, 'John', 'Technician1', 'technician1', 'sha256:1000:7mDinU6d7FQvU5zcPjvgSXyhyfj1CuoJ:n3I55frKQbRDblOiGDWJ04UBAasXLDLa', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428637898, 1428637898, 'Active', 'staff'),
        (21, 'David ', 'OpsManager', 'opsmanager', 'sha256:1000:oX1ELSPctZux4ciERVgzVORQ6hdUbUmf:YxQfHqnEH2e5uJ2o9WamT67JTBenLwUj', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428639510, 1428639510, 'Active', 'staff'),
        (22, 'Jim', 'ServiceManager', 'servicemanager', 'sha256:1000:kWXl0+Dlg587QlhVjaR+YbiKIyWKK+ig:RQjbcYOdS9Aojc3eTi39Ivos95WG6OJc', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428639679, 1428639679, 'Active', 'staff'),
        (23, 'Trevor', 'Technician2', 'technician2', 'sha256:1000:JP30aPh+P57W2EdnATZJnkr21/46zFFd:T+KJpiaEcAgF8Im4um6iQfHgEHRHIrKv', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428664123, 1429081269, 'Active', 'staff'),
        (25, 'Peter', 'Accounts1', 'accounts1', 'sha256:1000:X/575/NnUIhFWvDm771zoNzn8dNSj7xy:GCPZkFXdkguieLpF49guJ7JGr51YmKkz', NULL, NULL, '', NULL, 'visa', NULL, NULL, 1428664399, 1428664399, 'Active', 'staff')");

        $this->db->query("INSERT INTO `users_roles` (`id`, `user_id`, `role_id`, `creation_date`, `revision_date`, `status`) VALUES
        (22, 14, 2, 0, 0, 'Active'),
        (23, 15, 1, 0, 0, 'Active'),
        (25, 22, 8, 0, 0, 'Active'),
        (26, 25, 6, 0, 0, 'Active'),
        (27, 19, 4, 0, 0, 'Active'),
        (28, 23, 4, 0, 0, 'Active')");

        $this->db->query("INSERT INTO `user_contacts` (`id`, `user_id`, `type`, `contact`, `default_choice`, `creation_date`, `revision_date`, `receive_notifications`, `status`) VALUES
        (1, 3, 1, 'pep@themesolutions.com.au', 1, 1376898578, 1376898578, 1, 'Active'),
        (11, 17, 1, 'supervisor@StevesAircon.com.au', 0, 1428636962, 1428636962, 0, 'Active'),
        (12, 17, 3, '0429 458 925', 0, 1428636980, 1428636980, 0, 'Active'),
        (16, 19, 1, 'technician1@StevesAir.com.au', 0, 1428637928, 1428637928, 0, 'Active'),
        (17, 19, 2, '02 9266 5247', 0, 1428637940, 1428637940, 0, 'Active'),
        (18, 19, 3, '0429 655 114', 0, 1428637956, 1428637956, 0, 'Active'),
        (22, 21, 1, 'opsmanager@stevesaircon.com.au', 0, 1428639542, 1428639542, 0, 'Active'),
        (23, 21, 2, '02 9266 5247', 0, 1428639557, 1428639557, 0, 'Active'),
        (24, 21, 3, '0429 551 887', 0, 1428639577, 1428639577, 0, 'Active'),
        (25, 22, 2, '02 9266 5247', 0, 1428639707, 1428639707, 0, 'Active'),
        (26, 22, 1, 'servicemanager@stevesaircon.com.au', 0, 1428639731, 1428639731, 0, 'Active'),
        (27, 22, 3, '0457 956 268', 0, 1428639748, 1428639748, 0, 'Active'),
        (28, 17, 2, '02 9266 5247', 0, 1428639932, 1428639932, 0, 'Active'),
        (29, 23, 2, '02 9266 5247', 0, 1428664176, 1428664176, 0, 'Active'),
        (30, 23, 1, 'technician2@StevesAircon.com.au', 0, 1428664199, 1428664199, 0, 'Active'),
        (31, 23, 3, '0436 524 952', 0, 1428664213, 1428664213, 0, 'Active'),
        (32, 25, 1, 'accounts1@StevesAircon.com.au', 0, 1428664440, 1428664440, 0, 'Active'),
        (33, 25, 2, '02 9266 5247', 0, 1428664453, 1428664453, 0, 'Active'),
        (34, 25, 3, '0411 526 854', 0, 1428664471, 1428664471, 0, 'Active')");

        $this->db->query("INSERT INTO `types` (`id`, `name`, `description`, `entity`, `creation_date`, `revision_date`, `status`, `colour`) VALUES
        (2, 'Project manager', '', 'contact', 0, 0, 'Active', NULL),
        (3, 'Site', '', 'contact', 0, 0, 'Active', NULL),
        (4, 'Tenant', '', 'contact', 0, 0, 'Active', NULL),
        (5, 'Agent', '', 'contact', 0, 0, 'Active', NULL),
        (7, 'Property manager', '', 'contact', 0, 0, 'Active', NULL),
        (18, 'Evaporative A/C', '', 'unit', 0, 0, 'Active', NULL),
        (19, 'Refrigerated A/C', '', 'unit', 0, 0, 'Active', NULL),
        (20, 'Transport Refrigeration', '', 'unit', 0, 0, 'Active', NULL),
        (21, 'Dirty', '', 'job_issue', 0, 0, 'Active', NULL),
        (22, 'Broken', '', 'job_issue', 0, 0, 'Active', NULL),
        (23, 'Damaged', '', 'job_issue', 0, 0, 'Active', NULL),
        (24, 'Empty', '', 'job_issue', 0, 0, 'Active', NULL),
        (25, 'Jammed', '', 'job_issue', 0, 0, 'Active', NULL),
        (26, 'Burned out', '', 'job_issue', 0, 0, 'Active', NULL),
        (27, 'Breakdown', '', 'order', 0, 0, 'Active', 'yellow'),
        (28, 'Installation', '', 'order', 0, 0, 'Active', 'blue'),
        (29, 'Maintenance', '', 'order', 0, 0, 'Active', 'green'),
        (35, 'Diagnostic', '', 'assignment', 1392777153, 1392777153, 'Active', NULL),
        (36, 'Job', '', 'assignment', 1392777153, 1392777153, 'Active', NULL),
        (37, 'Other refrigeration', '', 'unit', 0, 0, 'Active', NULL),
        (38, 'order', 'Customer''s signature for the job (after diagnostics and jobs are complete)', 'signature', 0, 0, 'Active', NULL),
        (39, 'diagnostics', 'Customer''s signature for all diagnostics of a particular assignment', 'signature', 0, 0, 'Active', NULL),
        (40, 'jobs', 'Customer''s signature for all jobs of an assignment', 'signature', 0, 0, 'Active', NULL),
        (41, 'Mechanical service', '', 'unit', 0, 0, 'Active', NULL),
        (42, 'Repair', '', 'order', 1405902634, 1405902634, 'Active', 'purple'),
        (43, 'Landlord', 'Landlord', 'contact', 0, 0, 'Active', NULL),
        (44, 'Service', '', 'order', 0, 0, 'Active', 'teal'),
        (45, 'Supplier quote request', '', 'servicequote_document', 0, 0, 'Active', NULL),
        (46, 'Service Quotation', '', 'servicequote_document', 0, 0, 'Active', NULL),
        (47, 'Purchase Order', '', 'servicequote_document', 0, 0, 'Active', NULL),
        (48, 'Invoice tenancy', 'The signature of a tenancy landlord/owner (obtained after a breakdown or repair job)', 'signature', 0, 0, 'Active', NULL)");

        $this->db->query("INSERT INTO `document_types_statuses` (`id`, `status_id`, `document_type`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 21, 'assignment', NULL, NULL, 'Active'),
        (2, 23, 'assignment', NULL, NULL, 'Active'),
        (3, 24, 'assignment', NULL, NULL, 'Active'),
        (4, 27, 'assignment', NULL, NULL, 'Active'),
        (5, 30, 'assignment', NULL, NULL, 'Active'),
        (6, 32, 'assignment', NULL, NULL, 'Active'),
        (7, 34, 'assignment', NULL, NULL, 'Active'),
        (8, 35, 'assignment', NULL, NULL, 'Active'),
        (9, 37, 'assignment', NULL, NULL, 'Active'),
        (10, 38, 'assignment', NULL, NULL, 'Active'),
        (11, 39, 'assignment', NULL, NULL, 'Active'),
        (12, 41, 'assignment', NULL, NULL, 'Active'),
        (13, 42, 'assignment', NULL, NULL, 'Active'),
        (14, 43, 'assignment', NULL, NULL, 'Active'),
        (15, 46, 'assignment', NULL, NULL, 'Active'),
        (16, 47, 'assignment', NULL, NULL, 'Active'),
        (17, 49, 'assignment', NULL, NULL, 'Active'),
        (18, 50, 'assignment', NULL, NULL, 'Active'),
        (19, 51, 'assignment', NULL, NULL, 'Active'),
        (20, 52, 'assignment', NULL, NULL, 'Active'),
        (21, 56, 'assignment', NULL, NULL, 'Active'),
        (22, 18, 'diagnostic', NULL, NULL, 'Active'),
        (23, 21, 'maintenance_contract', NULL, NULL, 'Active'),
        (24, 29, 'maintenance_contract', NULL, NULL, 'Active'),
        (25, 13, 'order', NULL, NULL, 'Active'),
        (26, 21, 'order', NULL, NULL, 'Active'),
        (27, 23, 'order', NULL, NULL, 'Active'),
        (28, 24, 'order', NULL, NULL, 'Active'),
        (29, 27, 'order', NULL, NULL, 'Active'),
        (30, 28, 'order', NULL, NULL, 'Active'),
        (31, 29, 'order', NULL, NULL, 'Active'),
        (32, 36, 'order', NULL, NULL, 'Active'),
        (33, 37, 'order', NULL, NULL, 'Active'),
        (34, 44, 'order', NULL, NULL, 'Active'),
        (35, 45, 'order', NULL, NULL, 'Active'),
        (36, 48, 'order', NULL, NULL, 'Active'),
        (37, 53, 'order', NULL, NULL, 'Active'),
        (38, 54, 'order', NULL, NULL, 'Active'),
        (39, 55, 'order', NULL, NULL, 'Active'),
        (40, 11, 'order_technician', NULL, NULL, 'Active'),
        (41, 18, 'order_technician', NULL, NULL, 'Active'),
        (42, 26, 'order_technician', NULL, NULL, 'Active'),
        (43, 27, 'order_technician', NULL, NULL, 'Active'),
        (44, 33, 'order_technician', NULL, NULL, 'Active'),
        (45, 30, 'unit', NULL, NULL, 'Active'),
        (70, 16, 'invoice_tenancy', NULL, NULL, 'Active'),
        (69, 9, 'invoice_tenancy', NULL, NULL, 'Active'),
        (68, 21, 'invoice_tenancy', NULL, NULL, 'Active'),
        (67, 15, 'invoice_tenancy', NULL, NULL, 'Active'),
        (66, 20, 'invoice_tenancy', NULL, NULL, 'Active'),
        (65, 12, 'invoice_tenancy', NULL, NULL, 'Active'),
        (64, 27, 'invoice_tenancy', NULL, NULL, 'Active'),
        (63, 19, 'invoice_tenancy', NULL, NULL, 'Active'),
        (62, 15, 'order', NULL, NULL, 'Active'),
        (61, 12, 'order', NULL, NULL, 'Active'),
        (60, 21, 'servicequote', NULL, NULL, 'Active'),
        (59, 74, 'servicequote', NULL, NULL, 'Active'),
        (58, 73, 'servicequote', NULL, NULL, 'Active'),
        (57, 65, 'servicequote', NULL, NULL, 'Active'),
        (56, 69, 'servicequote', NULL, NULL, 'Active'),
        (55, 72, 'servicequote', NULL, NULL, 'Active'),
        (54, 71, 'servicequote', NULL, NULL, 'Active'),
        (53, 70, 'servicequote', NULL, NULL, 'Active'),
        (52, 19, 'servicequote', NULL, NULL, 'Active'),
        (51, 68, 'servicequote', NULL, NULL, 'Active'),
        (50, 67, 'servicequote', NULL, NULL, 'Active'),
        (49, 66, 'servicequote', NULL, NULL, 'Active'),
        (48, 75, 'servicequote', NULL, NULL, 'Active'),
        (47, 20, 'servicequote', NULL, NULL, 'Active'),
        (46, 27, 'servicequote', NULL, NULL, 'Active')");

        $this->db->query("INSERT INTO `events` (`id`, `role_id`, `name`, `description`, `system`, `creation_date`, `revision_date`, `status`) VALUES
        (1, NULL, 'create_order', 'Someone creates a new job', 'orders', 0, 0, 'Active'),
        (2, NULL, 'allocate_to_technician', 'Someone allocates the job to a technician', 'orders', 0, 0, 'Active'),
        (3, NULL, 'archive', 'Someone archives the job', 'orders', 0, 0, 'Active'),
        (4, NULL, 'cancel', 'Someone cancels the job', 'orders', 0, 0, 'Active'),
        (5, 4, 'finish_work', 'The technician has finished the work for the job', 'orders', 0, 0, 'Active'),
        (6, 6, 'sent_invoice', 'Accounts have sent the invoice the job', 'orders', 0, 0, 'Active'),
        (7, 6, 'add_job_number', 'Accounts have added a job number to the job', 'orders', 0, 0, 'Active'),
        (8, NULL, 'request_more_info', 'Someone has requested more info from the client about the job', 'orders', 0, 0, 'Active'),
        (9, NULL, 'put_on_hold', 'Someone has put the job on hold', 'orders', 0, 0, 'Active'),
        (10, 4, 'lock_for_amendment', 'Technician has locked the order to make changes.', 'orders', 0, 1409901617, 'Active'),
        (11, NULL, 'lock_for_review', 'Service Manager has locked the job to review it', 'orders', 0, 1400655753, 'Active'),
        (12, NULL, 'finish_review', 'Service Manager has finished reviewing the job', 'orders', 0, 1400655745, 'Active'),
        (13, NULL, 'schedule', 'The job has been scheduled, which means that all its diagnostics have been scheduled and allocated to technicians', 'orders', 0, 1387265157, 'Active'),
        (14, NULL, 'signed_by_client', 'The client has signed the job', 'orders', 0, 1380253096, 'Active'),
        (21, NULL, 'request_job_number', 'Someone has requested a job number for this job', 'orders', 1380270239, 1380270239, 'Active'),
        (22, 4, 'submit_for_review', 'A technician has submitted the [[document]] for review.', 'orders', 1381304749, 1381304749, 'Active'),
        (23, NULL, 'admin_prep_finished', 'Admin has finished entering initial information', 'orders', 1386291783, 1386291783, 'Active'),
        (26, NULL, 'unschedule', 'Unschedule a diagnostics', 'assignments', 1387176214, 1387265573, 'Active'),
        (27, NULL, 'schedule', 'Schedule a diagnostic', 'assignments', 1387176214, 1387265558, 'Active'),
        (30, NULL, 'unschedule', 'The job''s diagnostics have been unscheduled', 'orders', 1387265301, 1387265301, 'Active'),
        (31, NULL, 'is_complete', 'The assignment is complete', 'order_technicians', 1392779105, 1392779105, 'Active'),
        (32, NULL, 'is_approved', 'Has been approved by service manager', 'order_technicians', 1392779179, 1392779179, 'Active'),
        (34, 4, 'record_unit_details', 'The technician has recorded the details for the diagnostic''s unit', 'assignments', 1399604642, 1399604642, 'Active'),
        (35, 4, 'diagnosed', 'The technician has recorded all diagnosed issues for the unit', 'assignments', 1399604681, 1399604681, 'Active'),
        (36, 4, 'labour_estimate', 'The technician has recorded the labour estimate for this diagnostic', 'assignments', 1399604708, 1399604708, 'Active'),
        (37, NULL, 'submit_sq', 'The technician has recorded the required parts for the SQ and submitted that for review by the managers', 'assignments', 1399604745, 1399605057, 'Active'),
        (39, 4, 'start', 'The technician has arrived on site and begun the assignment', 'order_technicians', 1399605991, 1399605991, 'Active'),
        (41, NULL, 'diagnostics_completed', 'All diagnostic assignments have been completed and signed by clients', 'orders', 1399989309, 1399989309, 'Active'),
        (42, NULL, 'leaving', 'The technician is about to leave for the job', 'order_technicians', 1406102535, 1406180745, 'Active'),
        (43, NULL, 'called_office', 'Called the office about a diagnostic', 'assignments', 0, 0, 'Active'),
        (44, NULL, 'sq_approved', 'An SQ has been approved for this unit', 'assignments', 1406705220, 1406705220, 'Active'),
        (46, NULL, 'post-job_complete', 'Post-job tasks were completed', 'orders', 1406786290, 1406786290, 'Active'),
        (47, NULL, 'dowd_recorded', 'The DOWD was recorded for this diagnostic', 'assignments', 0, 0, 'Active'),
        (48, NULL, 'start', 'Work on this assignment has started', 'assignments', 0, 0, 'Active'),
        (49, NULL, 'details_recorded', 'All required details have been entered for this unit', 'units', 1410234154, 1410234154, 'Active'),
        (50, NULL, 'isolated_and_tagged_recorded', 'The unit''s isolated and tagged setting has been recorded for this assignment', 'assignments', 1411025061, 1411025061, 'Active'),
        (51, NULL, 'repairs_approved', 'Repairs were approved for this unit', 'assignments', 1412300735, 1412300735, 'Active'),
        (53, NULL, 'completed', 'The Diagnostic is complete', 'diagnostics', 1412322341, 1412322341, 'Active'),
        (55, NULL, 'no_issues_found', 'No issues were found', 'assignments', 0, 0, 'Active'),
        (56, NULL, 'parts_used_recorded', 'The parts used to fix the issues were recorded', 'assignments', 1412824296, 1412824296, 'Active'),
        (57, NULL, 'refrigerant_used_recorded', 'The refrigerant used and reclaimed were recorded', 'assignments', 1412824315, 1412824315, 'Active'),
        (58, NULL, 'prejobsite_photos_uploaded', 'Pre-job site photos were uploaded', 'orders', 0, 0, 'Active'),
        (59, NULL, 'postjobsite_photos_uploaded', 'Post-job site photos were uploaded', 'orders', 0, 0, 'Active'),
        (60, NULL, 'installation_tasks_completed', 'All installation tasks were completed', 'assignments', 0, 0, 'Active'),
        (61, NULL, 'maintenance_tasks_completed', 'All maintenance tasks were completed', 'assignments', 0, 0, 'Active'),
        (62, NULL, 'start', 'At least one technician has started working on this job', 'orders', 0, 0, 'Active'),
        (63, NULL, 'unit_serial_entered', 'The unit''s serial number was entered', 'assignments', 0, 0, 'Active'),
        (64, NULL, 'location_info_recorded', 'Location info for the unit was recorde', 'assignments', 0, 0, 'Active'),
        (65, NULL, 'unit_photos_uploaded', 'Photos of the unit were uploaded if required', 'assignments', 0, 0, 'Active'),
        (66, NULL, 'unit_work_complete', 'All unit work is complete for this job', 'orders', 0, 0, 'Active'),
        (67, NULL, 'create_maintenance_contract', 'Someone creates a new maintenance contract', 'maintenance_contracts', 0, 0, 'Active'),
        (68, NULL, 'admin_prep_finished', 'Admin has finished entering initial information', 'maintenance_contracts', 1386291783, 1386291783, 'Active'),
        (69, NULL, 'dowd_recorded', 'The DOWD was recorded for this job', 'orders', 0, 0, 'Active'),
        (70, NULL, 'set_signature_required', 'A signature is now required for this job', 'orders', 0, 0, 'Active'),
        (71, NULL, 'set_signature_not_required', 'A signature is not required for this job', 'orders', 0, 0, 'Active'),
        (72, NULL, 'office_notes_sighted', 'Office notes were read by the technician', 'orders', 0, 0, 'Active'),
        (73, NULL, 'issue_photos_hiding_setting_recorded', 'The \"Hide issue photos from client\" setting was recorded for this assignment', 'assignments', 0, 0, 'Active'),
        (74, NULL, 'repair_tasks_completed', 'All repair tasks were completed', 'orders', 0, 0, 'Active'),
        (89, NULL, 'is_complete', 'The assignment is complete', 'assignments', 0, 0, 'Active'),
        (88, NULL, 'reviewed', 'The technicians'' time on a tenancy''s job has been reviewed', 'invoice_tenancies', 0, 0, 'Active'),
        (87, NULL, 'signed_by_client', 'The invoice has been signed by the client', 'invoice_tenancies', 0, 0, 'Active'),
        (86, NULL, 'supplier_parts_received', 'All parts were received from suppliers for this SQ', 'servicequotes', 0, 0, 'Active'),
        (85, NULL, 'purchase_orders_sent', 'The purchase orders have been sent to suppliers', 'servicequotes', 0, 0, 'Active'),
        (84, NULL, 'purchase_orders_previewed', 'The purchase order(s) to be sent to supplier(s) have been previewed', 'servicequotes', 0, 0, 'Active'),
        (83, NULL, 'client_response_recorded', 'The client''s response to the SQ was recorded', 'servicequotes', 0, 0, 'Active'),
        (82, NULL, 'client_quote_sent', 'Service quotation was sent to client', 'servicequotes', 0, 0, 'Active'),
        (81, NULL, 'client_quote_previewed', 'The client quote has been previewed', 'servicequotes', 0, 0, 'Active'),
        (80, NULL, 'final_suppliers_approved', 'Final suppliers have been approved for this SQ', 'servicequotes', 0, 0, 'Active'),
        (79, NULL, 'supplier_quotes_approved', 'All supplier quotes have been approved', 'servicequotes', 0, 0, 'Active'),
        (78, NULL, 'supplier_quote_requests_sent', 'The supplier quote requests for this SQ have been sent to the suppliers', 'servicequotes', 0, 0, 'Active'),
        (77, NULL, 'supplier_quote_requests_previewed', 'The supplier quote requests for this SQ have been previewed', 'servicequotes', 0, 0, 'Active'),
        (76, NULL, 'selected_suppliers_approved', 'SQ selected suppliers have been approved', 'servicequotes', 0, 0, 'Active'),
        (75, NULL, 'required_parts_approved', 'Approve required parts', 'servicequotes', 0, 0, 'Active')");

        $this->db->query("REPLACE INTO `settings` (`id`, `name`, `value`, `status`, `creation_date`, `revision_date`) VALUES
        (1, 'Diagnostic hourly rate', '154', 'Active', 1399942739, 1399942739),
        (2, 'Diagnostic overtime hourly rate', '175', 'Active', 1399942756, 1399942756),
        (3, 'Morning overtime end', '8', 'Active', 1399942816, 1399942816),
        (4, 'Evening overtime start', '18', 'Active', 1399942832, 1399942832),
        (5, 'terms', '<p>Cash on completion of work unless prior credit arrangements are made. If insurance is involved, payment is the responsibility of the client, not the insurance company. E & OE.</p>\n<p><strong>PAYMENT TERMS strictly 14 days</strong></p>\n<p>To be read in conjunction with our terms and conditions of sale as outlined at <a target=\"_blank\" href=\"http://www.temperaturesolutions.com.au\">www.temperaturesolutions.com.au</a> under Policies, Terms and Conditions.</p>', 'Active', 1407130954, 1407131080),
        (6, 'Quote validity period', '2592000', 'Active', NULL, NULL),
        (7, 'Site name', 'Streamliner', 'Active', 1428459306, 1428459306)");

        $this->db->query("INSERT INTO `statuses` (`id`, `name`, `description`, `creation_date`, `revision_date`, `status`, `sortorder`) VALUES
        (9, 'NEEDS MORE INFO', 'The [[document]] requires more info from the client', 1378254150, 1378254150, 'Active', 2),
        (11, 'STARTED', 'The technician has started working on the [[document]]', 1378254150, 1378254150, 'Active', 5),
        (12, 'REVIEWED', 'The [[document]] has been reviewed and approved by authorised staff', 1378254150, 1378254150, 'Active', 6),
        (13, 'LOCKED FOR REVIEW', 'The [[document]] is being reviewed by admins', 1378254150, 1378254150, 'Active', 7),
        (14, 'LOCKED FOR AMENDMENT', 'The [[document]] is being amended by a technician after submission for review', 1378254150, 1378254150, 'Active', 8),
        (15, 'INVOICED', 'The [[document]] has been invoiced to the client', 1378254150, 1378254150, 'Active', 11),
        (16, 'ON HOLD', 'The [[document]] has been put on hold', 1378254150, 1378254150, 'Active', 14),
        (18, 'COMPLETE', 'The [[document]] has been completed and can now be archived', 1378254150, 1378254150, 'Active', 13),
        (19, 'ARCHIVED', 'The [[document]] has been archived', 1378254150, 1378254150, 'Active', 16),
        (20, 'CANCELLED', 'The [[document]] has been cancelled', 1378254150, 1378254150, 'Active', 15),
        (21, 'DRAFT', 'The [[document]] is in draft mode. It will be automatically deleted within 24 hours of its creation unless it is finalised', 1378946395, 1378946395, 'Active', 1),
        (22, 'JOB NUMBER ASSIGNED', 'Assigned job number', 1379917159, 1379917159, 'Active', 9),
        (23, 'SCHEDULED', 'The [[document]] has been scheduled and assigned to a technician', 1379921716, 1379921716, 'Active', 4),
        (24, 'ALLOCATED', 'Allocated to a technician', 1380077386, 1380077386, 'Active', 0),
        (26, 'SIGNED BY CLIENT', 'The [[document]] has been signed by the client', 1380095513, 1380095513, 'Active', 15),
        (27, 'AWAITING REVIEW', 'The [[document]] is waiting for review by authorised staff', 1380269213, 1380269213, 'Active', 8),
        (28, 'AWAITING JOB NUMBER', 'Waiting for job number to be assigned to this [[document]]', 1380270154, 1380270154, 'Active', 0),
        (29, 'READY FOR ALLOCATION', 'The [[document]] is ready for allocation', 1378254150, 1378254150, 'Active', 2),
        (30, 'UNIT DETAILS RECORDED', 'The [[document]]''s unit details were recorded', 0, 0, 'Active', 0),
        (31, 'LABOUR ESTIMATED', 'The labour estimate was completed', 0, 0, 'Active', 0),
        (32, 'ISSUES DIAGNOSED', 'All issues were recorded', 0, 0, 'Active', 0),
        (33, 'STARTED TRAVEL', 'The technician has started travelling', 0, 0, 'Active', 0),
        (34, 'CALLED OFFICE', 'The technician has called the office about this [[document]]', 0, 0, 'Active', 0),
        (35, 'SQ APPROVED', 'An SQ has been approved for this [[document]]', 0, 0, 'Active', 0),
        (36, 'POST-JOB COMPLETE', 'Post-job tasks were completed', 1406786549, 1406786549, 'Active', 0),
        (37, 'DOWD RECORDED', 'Description of Work Done was recorded for this [[document]]', 0, 0, 'Active', 0),
        (38, 'ISOLATED AND TAGGED SETTING RECORDED', 'The unit''s isolated and tagged setting has been recorded.', 1411024470, 1411024470, 'Active', 0),
        (39, 'REPAIRS APPROVED', 'Repairs have been approved on a unit', 0, 0, 'Active', 0),
        (41, 'NO ISSUES FOUND', 'No issues were found', NULL, NULL, 'Active', 0),
        (42, 'USED PARTS RECORDED', 'The parts used in the [[document]] were recorded', NULL, NULL, 'Active', 0),
        (43, 'USED REFRIGERANT RECORDED', 'The refrigerant used and reclaimed in the [[document]] were recorded', NULL, NULL, 'Active', 0),
        (44, 'PRE-JOB SITE PHOTOS UPLOADED', 'Pre-job site photos were uploaded', NULL, NULL, 'Active', 0),
        (45, 'POST-JOB SITE PHOTOS UPLOADED', 'Post-job site photos were uploaded', NULL, NULL, 'Active', 0),
        (46, 'INSTALLATION TASKS COMPLETED', 'All installation tasks were completed', NULL, NULL, 'Active', 0),
        (47, 'MAINTENANCE TASKS COMPLETED', 'All maintenance tasks were completed', NULL, NULL, 'Active', 0),
        (48, 'IN PROGRESS', 'The [[document]] is in progress', NULL, NULL, 'Active', 0),
        (49, 'UNIT SERIAL NUMBER ENTERED', 'The unit''s serial number has been entered', NULL, NULL, 'Active', 0),
        (50, 'LOCATION INFO RECORDED', 'The location information for this unit was recorded', NULL, NULL, 'Active', 0),
        (51, 'UNIT PHOTOS UPLOADED', 'Required photos for the unit were uploaded', NULL, NULL, 'Active', 0),
        (52, 'REQUIRED PARTS RECORDED', 'Parts required for an SQ were recorded', NULL, NULL, 'Active', 0),
        (53, 'UNIT WORK COMPLETE', 'All unit work is complete for this job', NULL, NULL, 'Active', 0),
        (54, 'SIGNATURE REQUIRED', 'A signature is required for this job', NULL, NULL, 'Active', 0),
        (55, 'OFFICE NOTES SIGHTED', 'The technician has read the office notes at the end of the job', NULL, NULL, 'Active', 0),
        (56, 'ISSUE PHOTOS HIDING SETTING RECORDED', 'The \"Hide issue photos from client\" setting was recorded for this assignment', NULL, NULL, 'Active', 0),
        (57, 'REPAIR TASKS COMPLETED', 'All repair tasks were completed', NULL, NULL, 'Active', 0),
        (75, 'SUPPLIER PARTS RECEIVED', NULL, NULL, NULL, 'Active', 0),
        (74, 'PURCHASE ORDERS SENT', NULL, NULL, NULL, 'Active', 0),
        (73, 'PURCHASE ORDERS PREVIEWED', NULL, NULL, NULL, 'Active', 0),
        (72, 'CLIENT RESPONSE RECORDED', NULL, NULL, NULL, 'Active', 0),
        (71, 'CLIENT QUOTE SENT', NULL, NULL, NULL, 'Active', 0),
        (70, 'CLIENT QUOTE PREVIEWED', NULL, NULL, NULL, 'Active', 0),
        (69, 'FINAL SUPPLIERS SELECTED', NULL, NULL, NULL, 'Active', 0),
        (68, 'SUPPLIER QUOTES RECORDED', NULL, NULL, NULL, 'Active', 0),
        (67, 'SUPPLIER QUOTE REQUEST SENT', NULL, NULL, NULL, 'Active', 0),
        (66, 'SUPPLIER QUOTE REQUEST PREVIEWED', NULL, NULL, NULL, 'Active', 0),
        (65, 'POTENTIAL SUPPLIERS SELECTED', NULL, NULL, NULL, 'Active', 0),
        (64, 'REQUIRED PARTS APPROVED', NULL, NULL, NULL, 'Active', 0)");

        $this->db->query("INSERT INTO `status_events` (`id`, `status_id`, `event_id`, `state`, `creation_date`, `revision_date`, `status`) VALUES
        (1, 26, 14, 1, 1380261675, 1380268497, 'Active'),
        (3, 21, 14, 0, 1380268541, 1380268543, 'Active'),
        (4, 9, 14, 0, 1380268605, 1380268607, 'Active'),
        (5, 21, 13, 0, 1380268849, 1380268864, 'Active'),
        (6, 9, 13, 0, 1380268869, 1380268870, 'Active'),
        (7, 12, 12, 1, 1380269067, 1380269067, 'Active'),
        (8, 21, 12, 0, 1380269095, 1380269097, 'Active'),
        (9, 13, 11, 1, 1380269120, 1380269120, 'Active'),
        (10, 21, 11, 0, 1380269246, 1380269248, 'Active'),
        (12, 13, 12, 0, 1380269282, 1380269284, 'Active'),
        (13, 27, 12, 0, 1380269291, 1380269294, 'Active'),
        (14, 14, 10, 1, 1380269335, 1380269335, 'Active'),
        (16, 16, 9, 1, 1380269393, 1380269393, 'Active'),
        (17, 13, 9, 0, 1380269405, 1380269426, 'Active'),
        (18, 14, 9, 0, 1380269423, 1380269424, 'Active'),
        (19, 21, 9, 0, 1380269441, 1380269443, 'Active'),
        (20, 9, 8, 1, 1380270014, 1380270014, 'Active'),
        (21, 21, 8, 1, 1380270020, 1380270020, 'Active'),
        (22, 18, 8, 0, 1380270027, 1380270029, 'Active'),
        (23, 19, 8, 0, 1380270034, 1380270036, 'Active'),
        (24, 22, 7, 1, 1380270087, 1380270087, 'Active'),
        (25, 21, 7, 0, 1380270095, 1380270097, 'Active'),
        (26, 28, 7, 0, 1380270190, 1380270196, 'Active'),
        (27, 28, 21, 1, 1380270244, 1380270244, 'Active'),
        (28, 22, 21, 0, 1380270256, 1380270257, 'Active'),
        (29, 15, 6, 1, 1380270273, 1380270273, 'Active'),
        (30, 21, 6, 0, 1380270277, 1380270278, 'Active'),
        (31, 18, 5, 1, 1380270317, 1380270317, 'Active'),
        (32, 21, 5, 0, 1380270321, 1380270322, 'Active'),
        (33, 20, 4, 1, 1380270352, 1380270352, 'Active'),
        (34, 19, 3, 1, 1380270365, 1380270365, 'Active'),
        (35, 21, 3, 0, 1380270371, 1380270372, 'Active'),
        (36, 9, 3, 0, 1380270378, 1380270380, 'Active'),
        (38, 13, 3, 0, 1380270412, 1380270414, 'Active'),
        (39, 14, 3, 0, 1380270417, 1380270419, 'Active'),
        (41, 24, 2, 1, 1380270439, 1380270439, 'Active'),
        (42, 21, 2, 0, 1380270446, 1380270447, 'Active'),
        (43, 21, 1, 1, 1380270459, 1380270459, 'Active'),
        (44, 23, 13, 1, 1380598419, 1380598419, 'Active'),
        (45, 27, 22, 1, 1381304888, 1381304888, 'Active'),
        (46, 14, 22, 0, 1381304899, 1381304900, 'Active'),
        (47, 21, 22, 0, 1381304912, 1381304914, 'Active'),
        (48, 27, 10, 0, 1381305071, 1381305073, 'Active'),
        (49, 21, 23, 0, 1386292505, 1386292507, 'Active'),
        (50, 29, 23, 1, 1386292886, 1386292886, 'Active'),
        (51, 23, 27, 1, 1387176936, 1387176936, 'Active'),
        (52, 24, 27, 1, 1387176947, 1387176947, 'Active'),
        (53, 21, 27, 0, 1387176952, 1387176958, 'Active'),
        (54, 24, 26, 0, 1387176970, 1387176986, 'Active'),
        (55, 23, 26, 0, 1387176983, 1387176987, 'Active'),
        (56, 21, 26, 1, 1387176996, 1387176996, 'Active'),
        (70, 29, 13, 0, 1387247765, 1387247767, 'Active'),
        (71, 23, 30, 0, 1387265314, 1387265326, 'Active'),
        (72, 24, 30, 0, 1387265317, 1387265325, 'Active'),
        (73, 29, 30, 1, 1387265323, 1387265323, 'Active'),
        (74, 24, 13, 1, 1387327405, 1387327405, 'Active'),
        (75, 27, 31, 1, 1392779126, 1392779143, 'Active'),
        (76, 12, 32, 0, 1392779191, 1392779205, 'Active'),
        (78, 30, 34, 1, 1399605251, 1399605251, 'Active'),
        (79, 32, 35, 1, 1399605323, 1399605323, 'Active'),
        (80, 31, 36, 1, 1399605340, 1399605340, 'Active'),
        (81, 27, 37, 1, 1399605376, 1399605376, 'Active'),
        (84, 18, 31, 1, 1399605949, 1399605949, 'Active'),
        (85, 11, 31, 0, 1399605956, 1399605958, 'Active'),
        (86, 11, 39, 1, 1399606003, 1399606003, 'Active'),
        (87, 26, 31, 1, 1399989218, 1399989218, 'Active'),
        (88, 23, 41, 0, 1399989329, 1399989331, 'Active'),
        (89, 24, 41, 0, 1399989337, 1399989339, 'Active'),
        (90, 27, 41, 1, 1399989364, 1399989364, 'Active'),
        (91, 28, 41, 1, 1399989419, 1399989419, 'Active'),
        (92, 33, 42, 1, 1406102564, 1406180893, 'Active'),
        (94, 34, 43, 1, 1406704122, 1406704122, 'Active'),
        (95, 35, 44, 1, 1406705231, 1406705231, 'Active'),
        (97, 36, 46, 1, 1406787260, 1406787260, 'Active'),
        (98, 37, 47, 1, 0, 0, 'Active'),
        (99, 11, 48, 1, 0, 0, 'Active'),
        (100, 30, 49, 1, 1410234206, 1410234206, 'Active'),
        (101, 21, 49, 0, 1410234221, 1410234222, 'Active'),
        (102, 38, 50, 1, 1411025072, 1411025072, 'Active'),
        (103, 39, 51, 1, 1412300766, 1412300766, 'Active'),
        (105, 18, 53, 1, 1412322349, 1412322349, 'Active'),
        (109, 41, 55, 1, 0, 0, 'Active'),
        (110, 41, 51, 0, 0, 0, 'Active'),
        (111, 41, 44, 0, 0, 0, 'Active'),
        (112, 41, 50, 0, 0, 0, 'Active'),
        (113, 42, 56, 1, 1412824329, 1412824329, 'Active'),
        (114, 43, 57, 1, 1412824341, 1412824341, 'Active'),
        (115, 44, 58, 1, 0, 0, 'Active'),
        (116, 45, 59, 1, 0, 0, 'Active'),
        (117, 46, 60, 1, 0, 0, 'Active'),
        (118, 47, 61, 1, 0, 0, 'Active'),
        (119, 48, 62, 1, 0, 0, 'Active'),
        (120, 49, 63, 1, 0, 0, 'Active'),
        (121, 50, 64, 1, 0, 0, 'Active'),
        (122, 51, 65, 1, 0, 0, 'Active'),
        (123, 52, 37, 1, 0, 0, 'Active'),
        (124, 53, 66, 1, 0, 0, 'Active'),
        (125, 21, 67, 1, 0, 0, 'Active'),
        (126, 21, 68, 0, 0, 0, 'Active'),
        (127, 29, 68, 1, 0, 0, 'Active'),
        (128, 37, 69, 1, 0, 0, 'Active'),
        (129, 54, 70, 1, 0, 0, 'Active'),
        (131, 54, 71, 0, 0, 0, 'Active'),
        (132, 55, 72, 1, 0, 0, 'Active'),
        (133, 56, 73, 1, 0, 0, 'Active'),
        (134, 57, 74, 1, 0, 0, 'Active'),
        (151, 18, 89, 1, 0, 0, 'Active'),
        (150, 27, 87, 1, 0, 0, 'Active'),
        (149, 27, 88, 0, 0, 0, 'Active'),
        (148, 12, 88, 1, 0, 0, 'Active'),
        (147, 26, 87, 1, 0, 0, 'Active'),
        (146, 75, 86, 1, 0, 0, 'Active'),
        (145, 74, 85, 1, 0, 0, 'Active'),
        (144, 73, 84, 1, 0, 0, 'Active'),
        (143, 72, 83, 1, 0, 0, 'Active'),
        (142, 71, 82, 1, 0, 0, 'Active'),
        (141, 70, 81, 1, 0, 0, 'Active'),
        (140, 69, 80, 1, 0, 0, 'Active'),
        (139, 68, 79, 1, 0, 0, 'Active'),
        (138, 67, 78, 1, 0, 0, 'Active'),
        (137, 66, 77, 1, 0, 0, 'Active'),
        (136, 65, 76, 1, 0, 0, 'Active'),
        (135, 64, 75, 1, 0, 0, 'Active')");
    }

    public function down() {

    }
}
