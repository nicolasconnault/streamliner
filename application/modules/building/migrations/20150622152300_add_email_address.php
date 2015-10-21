<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_email_address extends CI_Migration {

    public function up() {
        $this->db->query("INSERT INTO settings (name, value) VALUES ('Admin email address', 'robert@g2buildingco.com.au')");
    }

    public function down() {

    }
}
