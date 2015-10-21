<?php

class Nav_miniant extends MY_Controller {
    public function setup() {
        return array(
            'Jobs' => array(
                array('title' => 'Current jobs', 'link' => "miniant/stages/job_list/index", 'caps' => array('orders:viewassignedorders'), 'icon' => 'list'),
                array('title' => 'Job list', 'link' => "miniant/orders/order",  'caps' => array('orders:vieworders'), 'icon' => 'list'),
                array('title' => 'New job', 'link' => "miniant/orders/order/add",  'caps' => array('orders:writeorders'), 'icon' => 'plus'),
                array('title' => 'Scheduling', 'link' => "miniant/orders/schedule",  'caps' => array('orders:allocateorders'), 'icon' => 'calendar'),
                array('title' => 'SQ list', 'link' => "miniant/servicequotes/servicequote",  'caps' => array('servicequotes:viewsqs'), 'icon' => 'list'),
            ),
            'Maintenance' => array(
                array('title' => 'Contract list', 'link' => "miniant/maintenance_contracts",  'caps' => array('maintenance_contracts:viewcontracts'), 'icon' => 'list'),
                array('title' => 'New contract', 'link' => "miniant/maintenance_contracts/add",  'caps' => array('maintenance_contracts:writecontracts'), 'icon' => 'plus'),
            ),
            'Administration' => array(
                array('title' => 'Accounts', 'link' => "miniant/miniant_accounts/browse", 'caps' => array('site:viewaccounts'), 'icon' => 'credit-card'),
                array('title' => 'Brands', 'link' => "miniant/brands/browse",  'caps' => array('site:viewbrands'), 'icon' => 'barcode'),
                array('title' => 'Diagnostic rules', 'link' => "miniant/orders/steps/diagnostic_rules",  'caps' => array('orders:viewdiagnosticrules'), 'icon' => 'footsteps'),
                array('title' => 'Refrigerant types', 'link' => "miniant/refrigerant_types/browse",  'caps' => array('site:editrefrigerant_types'), 'icon' => 'none'),
            ),
            'System events' => array(
                array('title' => 'Jobs', 'link' => "events/browse/html/orders",  'caps' => array('orders:viewevents'), 'icon' => 'bell'),
                array('title' => 'Assignments', 'link' => "events/browse/html/assignments",  'caps' => array('assignments:viewevents'), 'icon' => 'bell'),
                array('title' => 'Units', 'link' => "events/browse/html/units",  'caps' => array('units:viewevents'), 'icon' => 'bell'),
                array('title' => 'Jobs', 'link' => "events/browse/html/repair_jobs",  'caps' => array('repair_jobs:viewevents'), 'icon' => 'bell'),
                array('title' => 'Diagnostics',  'link' => 'events/browse/html/diagnostics', 'caps' => array('diagnostics:viewevents'), 'icon' => 'bell')
            )
        );
    }
}
