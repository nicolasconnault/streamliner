

ALTER TABLE `miniant_assignments`
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`priority_level_id`) REFERENCES `priority_levels` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_4` FOREIGN KEY (`unit_id`) REFERENCES `miniant_units` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_5` FOREIGN KEY (`diagnostic_id`) REFERENCES `miniant_diagnostics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_6` FOREIGN KEY (`repair_job_id`) REFERENCES `miniant_repair_jobs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_7` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON UPDATE CASCADE;

ALTER TABLE `miniant_assignment_refrigerant`
  ADD CONSTRAINT `assignment_refrigerant_ibfk_1` FOREIGN KEY (`refrigerant_type_id`) REFERENCES `miniant_refrigerant_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignment_refrigerant_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `miniant_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`unit_type_id`) REFERENCES `types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_diagnostic_issues`
  ADD CONSTRAINT `diagnostic_ibfk_1` FOREIGN KEY (`diagnostic_id`) REFERENCES `miniant_diagnostics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_issues_ibfk_1` FOREIGN KEY (`part_type_id`) REFERENCES `miniant_part_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_issues_ibfk_3` FOREIGN KEY (`issue_type_id`) REFERENCES `miniant_issue_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_issues_ibfk_4` FOREIGN KEY (`dowd_id`) REFERENCES `miniant_dowds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_diagnostic_tasks`
  ADD CONSTRAINT `diagnostic_tasks_ibfk_1` FOREIGN KEY (`diagnostic_id`) REFERENCES `miniant_diagnostics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_tasks_ibfk_2` FOREIGN KEY (`maintenance_task_id`) REFERENCES `miniant_maintenance_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_dowds`
  ADD CONSTRAINT `dowds_ibfk_1` FOREIGN KEY (`order_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_installation_tasks`
  ADD CONSTRAINT `installation_tasks_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `miniant_units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_installation_templates`
  ADD CONSTRAINT `installation_templates_ibfk_1` FOREIGN KEY (`unit_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `installation_templates_ibfk_2` FOREIGN KEY (`unitry_type_id`) REFERENCES `miniant_unitry_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `installation_templates_ibfk_3` FOREIGN KEY (`installation_task_category_id`) REFERENCES `miniant_installation_task_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_invoices`
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_invoice_tenancies`
  ADD CONSTRAINT `invoice_tenancies_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `miniant_invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_tenancies_ibfk_2` FOREIGN KEY (`tenancy_id`) REFERENCES `miniant_tenancies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_tenancies_ibfk_3` FOREIGN KEY (`signature_id`) REFERENCES `signatures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_invoice_tenancy_parts`
  ADD CONSTRAINT `invoice_tenancy_parts_ibfk_1` FOREIGN KEY (`invoice_tenancy_id`) REFERENCES `miniant_invoice_tenancies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_tenancy_parts_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `miniant_parts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_maintenance_contracts`
  ADD CONSTRAINT `maintenance_contracts_ibfk_1` FOREIGN KEY (`original_order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contracts_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contracts_ibfk_4` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contracts_ibfk_5` FOREIGN KEY (`site_address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contracts_ibfk_6` FOREIGN KEY (`billing_contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contracts_ibfk_8` FOREIGN KEY (`site_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contracts_ibfk_9` FOREIGN KEY (`property_manager_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_maintenance_contract_units`
  ADD CONSTRAINT `maintenance_contract_units_ibfk_1` FOREIGN KEY (`maintenance_contract_id`) REFERENCES `miniant_maintenance_contracts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_contract_units_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `miniant_units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_maintenance_tasks`
  ADD CONSTRAINT `maintenance_tasks_ibfk_1` FOREIGN KEY (`maintenance_task_template_id`) REFERENCES `miniant_maintenance_task_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_tasks_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `miniant_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_tasks_ibfk_3` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_maintenance_task_templates`
  ADD CONSTRAINT `maintenance_task_templates_ibfk_1` FOREIGN KEY (`unit_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`priority_level_id`) REFERENCES `priority_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_10` FOREIGN KEY (`senior_technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_11` FOREIGN KEY (`maintenance_contract_id`) REFERENCES `miniant_maintenance_contracts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_12` FOREIGN KEY (`location_diagram_id`) REFERENCES `miniant_location_diagrams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_13` FOREIGN KEY (`dowd_id`) REFERENCES `miniant_dowds` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`site_address_id`) REFERENCES `addresses` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`order_type_id`) REFERENCES `types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_6` FOREIGN KEY (`billing_contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_8` FOREIGN KEY (`parent_sq_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_9` FOREIGN KEY (`site_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_orders_tasks`
  ADD CONSTRAINT `orders_tasks_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_tasks_ibfk_2` FOREIGN KEY (`order_task_id`) REFERENCES `miniant_order_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_order_technicians`
  ADD CONSTRAINT `order_technicians_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_technicians_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_order_times`
  ADD CONSTRAINT `order_times_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_times_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_parts`
  ADD CONSTRAINT `parts_ibfk_6` FOREIGN KEY (`diagnostic_issue_id`) REFERENCES `miniant_diagnostic_issues` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`servicequote_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `parts_ibfk_2` FOREIGN KEY (`part_type_id`) REFERENCES `miniant_part_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `parts_ibfk_3` FOREIGN KEY (`assignment_id`) REFERENCES `miniant_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `parts_ibfk_4` FOREIGN KEY (`supplier_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `parts_ibfk_5` FOREIGN KEY (`supplier_quote_id`) REFERENCES `miniant_supplier_quotes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_part_types`
  ADD CONSTRAINT `part_types_ibfk_1` FOREIGN KEY (`unit_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_part_type_issue_types`
  ADD CONSTRAINT `part_type_issue_types_ibfk_1` FOREIGN KEY (`part_type_id`) REFERENCES `miniant_part_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `part_type_issue_types_ibfk_2` FOREIGN KEY (`issue_type_id`) REFERENCES `miniant_issue_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_part_type_issue_type_steps`
  ADD CONSTRAINT `part_type_issue_type_steps_ibfk_1` FOREIGN KEY (`part_type_issue_type_id`) REFERENCES `miniant_part_type_issue_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `part_type_issue_type_steps_ibfk_2` FOREIGN KEY (`step_id`) REFERENCES `miniant_steps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`servicequote_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`supplier_contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_repair_jobs`
  ADD CONSTRAINT `repair_jobs_ibfk_2` FOREIGN KEY (`diagnostic_id`) REFERENCES `miniant_diagnostics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_repair_tasks`
  ADD CONSTRAINT `repair_tasks_ibfk_1` FOREIGN KEY (`diagnostic_issue_id`) REFERENCES `miniant_diagnostic_issues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `repair_tasks_ibfk_2` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_servicequotes`
  ADD CONSTRAINT `servicequotes_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `miniant_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `servicequotes_ibfk_2` FOREIGN KEY (`diagnostic_id`) REFERENCES `miniant_diagnostics` (`id`) ON UPDATE CASCADE;

ALTER TABLE `miniant_servicequote_attachments`
  ADD CONSTRAINT `servicequote_attachments_ibfk_1` FOREIGN KEY (`servicequote_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_servicequote_documents`
  ADD CONSTRAINT `servicequote_documents_ibfk_1` FOREIGN KEY (`servicequote_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `servicequote_documents_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `servicequote_documents_ibfk_3` FOREIGN KEY (`recipient_contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_servicequote_suppliers`
  ADD CONSTRAINT `servicequote_suppliers_ibfk_1` FOREIGN KEY (`servicequote_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `servicequote_suppliers_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_supplier_quotes`
  ADD CONSTRAINT `supplier_quotes_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `miniant_parts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_quotes_ibfk_2` FOREIGN KEY (`servicequote_id`) REFERENCES `miniant_servicequotes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_quotes_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_quotes_ibfk_4` FOREIGN KEY (`purchase_order_id`) REFERENCES `miniant_purchase_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `miniant_unitry_types`
  ADD CONSTRAINT `unitry_types_ibfk_1` FOREIGN KEY (`unit_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `miniant_units`
  ADD CONSTRAINT `units_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `miniant_brands` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `units_ibfk_10` FOREIGN KEY (`refrigerant_type_id`) REFERENCES `miniant_refrigerant_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `units_ibfk_11` FOREIGN KEY (`tenancy_id`) REFERENCES `miniant_tenancies` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `units_ibfk_3` FOREIGN KEY (`unit_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `units_ibfk_7` FOREIGN KEY (`unitry_type_id`) REFERENCES `miniant_unitry_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `units_ibfk_9` FOREIGN KEY (`site_address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
