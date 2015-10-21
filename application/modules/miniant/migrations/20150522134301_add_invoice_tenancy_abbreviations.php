<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_invoice_tenancy_abbreviations extends CI_Migration {

    public function up() {
        $this->dbforge->drop_column('miniant_invoice_tenancies', 'dowd');
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 10
            ),
            'abbreviation_id' => array(
                'type' => 'INT',
                'constraint' => 9,
            ),
            'invoice_tenancy_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                'null' => false
            ),
        );
        $this->dbforge->add_field($fields);
        $this->dbforge->add_field('id');
        $this->dbforge->create_table('miniant_invoice_tenancy_abbreviations', true, array('ENGINE' => 'InnoDB'));
    }

    public function down() {
        $this->dbforge->drop_table('miniant_invoice_tenancy_abbreviations');
    }
}
