<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_type_to_tradesmen extends CI_Migration {

    public function up() {
        $this->db->query("INSERT INTO types (name, entity) VALUES ('Plumbers', 'tradesman'), ('Carpenters', 'tradesman'), ('Bricklayers', 'tradesman'), ('Electricians', 'tradesman'), ('Landscapers', 'tradesman'), ('Painters', 'tradesman'), ('Plasterers', 'tradesman')");
        $this->db->query("ALTER TABLE `building_tradesmen` ADD `type_id` INT(3) UNSIGNED NOT NULL AFTER `id`, ADD INDEX (`type_id`)");
    }

    public function down() {
    }
}
