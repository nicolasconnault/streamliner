<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_can_have_issues_to_part_types extends CI_Migration {

    public function up() {
        $fields = array('can_have_issues' => array('type' => 'INT', 'after' => 'for_diagnostic', 'constraint' => 1, 'default' => 1));
        $this->dbforge->add_column('miniant_part_types', $fields);
        $this->db->query('UPDATE miniant_part_types SET can_have_issues = 0 WHERE name IN ("Labour", "Electrician", "Freight", "Nitrogen", "Power", "Reclaim bottle", "Refrigerant", "Sundries", "Welding Equipment", "Welding Rods")');
    }

    public function down() {
        $this->dbforge->drop_column('miniant_part_types', 'can_have_issues');
    }
}
