<?php
class Installation_template_model extends MY_Model {
    public $table = 'miniant_installation_templates';

    public function copy_to_unit($unit_type_id, $unit_id, $unitry_type_id=null) {
        $query = '
                INSERT INTO miniant_installation_tasks (unit_id, task, sortorder, creation_date, revision_date)
                SELECT '.$unit_id.', task, sortorder, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM '.$this->table.'
                WHERE '.$this->table.'.unit_type_id = '.$unit_type_id;

        if (!is_null($unitry_type_id)) {
            $query .= ' AND '.$this->table.'.unitry_type_id = '.$unitry_type_id;
        }

        return $this->db->query($query);
    }
}
