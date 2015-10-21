<?php
$autoload['config'] = array('files');
$autoload['model'] = array(
    'miniant/assignment_model',
    'miniant/assignment_refrigerant_model',
    'miniant/brand_model',
    'miniant/diagnostic_model',
    'miniant/diagnostic_attachment_model',
    'miniant/diagnostic_issue_model',
    'miniant/dowd_model',
    'miniant/installation_task_model',
    'miniant/installation_template_model',
    'miniant/invoice_model',
    'miniant/invoice_tenancy_model',
    'miniant/invoice_tenancy_part_model',
    'miniant/issue_type_model',
    'miniant/location_diagram_model',
    'miniant/maintenance_contract_model',
    'miniant/maintenance_contract_unit_model',
    'miniant/maintenance_task_model',
    'miniant/maintenance_task_template_model',
    'miniant/order_model',
    'miniant/order_attachment_model',
    'miniant/order_signature_model',
    'miniant/order_task_model',
    'miniant/order_technician_model',
    'miniant/order_time_model',
    'miniant/orders_task_model',
    'miniant/part_model',
    'miniant/part_type_model',
    'miniant/part_type_issue_type_model',
    'miniant/purchase_order_model',
    'miniant/refrigerant_type_model',
    'miniant/repair_task_model',
    'miniant/servicequote_model',
    'miniant/servicequote_attachment_model',
    'miniant/servicequote_document_model',
    'miniant/servicequote_supplier_model',
    'miniant/stage_conditions_model',
    'miniant/step_model',
    'miniant/step_part_model',
    'miniant/supplier_quote_model',
    'miniant/tenancy_model',
    'miniant/tenancy_log_model',
    'miniant/unit_model',
    'miniant/unit_attachment_model',
    'miniant/unit_location_document_model',
    'miniant/unit_parts_model',
    'miniant/unitry_type_model',
    );
$autoload['helper'] = array('mod_capabilities', 'action_icon');