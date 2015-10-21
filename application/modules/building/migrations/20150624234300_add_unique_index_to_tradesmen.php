<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_unique_index_to_tradesmen extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `building_tradesmen` ADD UNIQUE( `type_id`, `name`)");
    }

    public function down() {

    }
}
