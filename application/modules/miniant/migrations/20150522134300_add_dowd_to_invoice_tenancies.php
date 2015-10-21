<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_dowd_to_invoice_tenancies extends CI_Migration {

    public function up() {
        $this->dbforge->add_column('miniant_invoice_tenancies', array('dowd' => array('type' => 'TEXT', 'default' => NULL, 'null' => true)));
    }

    public function down() {
        $this->dbforge->drop_column('miniant_invoice_tenancies', 'dowd');
    }
}
