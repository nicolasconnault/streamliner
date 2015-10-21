<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
class Workflow_model extends MY_Model {
    public $table = 'workflows';

    public function get_stages($workflow_id) {
        $this->db->join('stages', 'workflow_stages.stage_id = stages.id', 'LEFT OUTER');
        $this->db->select('workflow_stages.*');
        $this->db->order_by('stage_number');
        $this->select_foreign_table_fields('stages');

        return $this->workflow_stage_model->get(array('workflow_id' => $workflow_id));
    }

    public function get_workflow_stages($workflow_id) {
        $this->db->join('workflow_stages', 'workflow_stage_stages.workflow_stage_id = workflow_stages.id', 'LEFT OUTER');
        $this->db->join('stages', 'workflow_stages.stage_id = stages.id', 'LEFT OUTER');
        $this->db->select('workflow_stage_stages.*');
        $this->select_foreign_table_fields('stages');

        return $this->workflow_stage_stage_model->get(array('workflow_id' => $workflow_id));
    }
}
