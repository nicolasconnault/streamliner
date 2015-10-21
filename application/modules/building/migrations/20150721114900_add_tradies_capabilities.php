<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_tradies_capabilities extends CI_Migration {

    public function up() {

        $doanything_cap = $this->capability_model->get(array('name' => 'building:doanything'), true);
        if (!$doanything_cap) {
            $doanything_cap_id = $this->capability_model->add(array('name' => 'building:doanything', 'description' => 'Do anything in the building module', 'dependson' => 1));
        } else {
            $doanything_cap_id = $doanything_cap->id;
        }

        $edit_cap_id = $this->capability_model->add(array('name' => 'building:edittradesmen', 'description' => 'Edit Tradies', 'dependson' => $doanything_cap_id));
        $write_cap_id = $this->capability_model->add(array('name' => 'building:writetradesmen', 'description' => 'Create Tradies', 'dependson' => $edit_cap_id));
        $view_cap_id = $this->capability_model->add(array('name' => 'building:viewtradesmen', 'description' => 'Browse Tradies', 'dependson' => $write_cap_id));
        $delete_cap_id = $this->capability_model->add(array('name' => 'building:deletetradesmen', 'description' => 'Delete Tradies', 'dependson' => $edit_cap_id));
        $manager_role = $this->role_model->get(array('name' => 'Manager'), true);
        $this->db->query("INSERT INTO roles_capabilities (role_id, capability_id) VALUES ($manager_role->id, $doanything_cap_id)");
    }

    public function down() {
    }
}
