<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_sortorder_to_document_status_types extends CI_Migration {

    public function up() {
        $fields = array('sortorder' => array('type' => 'INT', 'after' => 'document_type'));
        $this->dbforge->add_column('document_types_statuses', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('document_types_statuses', 'sortorder');
    }
}
