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
class Workflow_conditions_model extends CI_Model {
    public static $is_senior_technician = null;

    public function __construct() {
        $ci = get_instance();
        $ci->load->model('miniant/assignment_model');
        $ci->load->model('miniant/order_model');
        $ci->load->model('miniant/diagnostic_issue_model');
        $ci->load->model('miniant/part_type_model');
        $ci->load->model('miniant/issue_type_model');
        $ci->load->model('miniant/repair_task_model');
    }

    public function __call($name, $args) {
        if (preg_match('/check_([0-9a-z\-\_]*)/', $name)) {
            return true;
        }
        echo "Calling object method '$name' " . implode(', ', $args). "\n";
    }

    protected function is_senior_technician($assignment_id) {
        $ci = get_instance();
        if (is_null(Workflow_conditions_model::$is_senior_technician)) {
            $assignment = $ci->assignment_model->get_values($assignment_id);
            $order = (object) $this->order_model->get_values($assignment->order_id);
            $technician_id = $this->session->userdata('user_id');
            Workflow_conditions_model::$is_senior_technician = $technician_id == $order->senior_technician_id;
        }

        return Workflow_conditions_model::$is_senior_technician;
    }
}
