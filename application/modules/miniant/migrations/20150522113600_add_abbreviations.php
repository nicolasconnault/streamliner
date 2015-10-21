<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_abbreviations extends CI_Migration {

    public function up() {
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 10
            ),
            'abbreviation' => array(
                'type' => 'VARCHAR',
                'constraint' => 64,
            ),
            'description' => array(
                'type' => 'TEXT'
            ),
            'explanation' => array(
                'type' => 'TEXT'
            )
        );
        $this->dbforge->add_field($fields);
        $this->dbforge->add_field('id');
        $this->dbforge->create_table('miniant_abbreviations', true, array('ENGINE' => 'InnoDB'));

    }

    public function down() {
        $this->dbforge->drop_table('miniant_abbreviations');
    }
}
