<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_admin_email_address extends CI_Migration {

    public function up() {
        $this->load->model('setting_model');
        $this->setting_model->add(array('name' => 'Admin email address', 'value' => 'admin@temperaturesolutions.net.au'));
        $this->setting_model->add(array('name' => 'Ops manager email address', 'value' => 'service@temperaturesolutions.net.au'));
    }

    public function down() {
        $this->setting_model->delete(array('name' => 'Admin email address', 'value' => 'admin@temperaturesolutions.net.au'));
        $this->setting_model->delete(array('name' => 'Ops manager email address', 'value' => 'service@temperaturesolutions.net.au'));
    }
}
