<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_standard_fields extends CI_Migration {

    public function up() {
        $fields = array(
            'creation_date' => array(
                'type' => 'INT',
                'constraint' => 10,
                'null' => true
            ),
            'revision_date' => array(
                'type' => 'INT',
                'constraint' => 10,
                'null' => true
            ),
            'status' => array(
                'type' => 'ENUM("Active","Suspended")',
                'default' => 'Active',
                'null' => false
            ),
        );
        $this->dbforge->add_column('miniant_invoice_tenancy_abbreviations', $fields);
        $this->dbforge->add_column('miniant_abbreviations', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('miniant_invoice_tenancy_abbreviations', 'creation_date');
        $this->dbforge->drop_column('miniant_invoice_tenancy_abbreviations', 'revision_date');
        $this->dbforge->drop_column('miniant_invoice_tenancy_abbreviations', 'status');
        $this->dbforge->drop_column('miniant_abbreviations', 'creation_date');
        $this->dbforge->drop_column('miniant_abbreviations', 'revision_date');
        $this->dbforge->drop_column('miniant_abbreviations', 'status');
    }
}
