<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_capabilities extends CI_Migration {

    public function up() {

        $manager_role = $this->role_model->get(array('name' => 'Manager'), true);
        $this->db->query("INSERT INTO roles_capabilities (role_id, capability_id) VALUES ($manager_role->id, 127)");
    }

    public function down() {
    }
}
