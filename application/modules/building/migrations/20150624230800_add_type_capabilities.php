<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_type_capabilities extends CI_Migration {

    public function up() {
        $edit_cap_id = $this->capability_model->add(array('name' => 'site:edittypes', 'description' => 'Edit Types', 'dependson' => 1));
        $write_cap_id = $this->capability_model->add(array('name' => 'site:writetypes', 'description' => 'Create Types', 'dependson' => $edit_cap_id));
        $view_cap_id = $this->capability_model->add(array('name' => 'site:viewtypes', 'description' => 'Browse Types', 'dependson' => $write_cap_id));
        $delete_cap_id = $this->capability_model->add(array('name' => 'site:deletetypes', 'description' => 'Delete Types', 'dependson' => $edit_cap_id));
        $manager_role = $this->role_model->get(array('name' => 'Manager'), true);
        $this->role_model->add_capability($manager_role->id, 'site:edittypes');
        $this->role_model->add_capability($manager_role->id, 'site:writetypes');
        $this->role_model->add_capability($manager_role->id, 'site:viewtypes');
        $this->role_model->add_capability($manager_role->id, 'site:deletetypes');
    }

    public function down() {

    }
}
