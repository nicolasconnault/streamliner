<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_sortorder_to_steps extends CI_Migration {

    public function up() {
        $fields = array('sortorder' => array('type' => 'INT', 'after' => 'immediate', 'constraint' => 2, 'default' => 1));
        $this->dbforge->add_column('miniant_part_type_issue_type_steps', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('miniant_part_type_issue_type_steps', 'sortorder');
    }
}
