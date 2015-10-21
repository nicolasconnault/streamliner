<?php

class Nav_building extends MY_Controller {
    public function setup() {
        return array(
            'Overview' => array('title' => 'Overview', 'link' => "building/schedule", 'caps' => array('building:viewjobsites'), 'icon' => 'calendar'),
            'Job sites' => array('title' => 'Job sites', 'link' => "building/job_sites",  'caps' => array('building:viewjobsites'), 'icon' => 'home'),
            'Tradies' => array('title' => 'Tradies', 'link' => "building/tradesmen", 'caps' => array('building:viewtradesmen'), 'icon' => 'briefcase'),
            'Types' => array('title' => 'Types', 'link' => "types/browse", 'caps' => array('site:viewtypes'), 'icon' => 'cubes'),
        );
    }
}
