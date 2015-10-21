<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
require_once(APPPATH.'/core/has_type_trait.php');

class Order_Model extends MY_Model {

    use has_statuses;
    use has_types;

    public $table = 'miniant_orders';

    public function __construct() {
        parent::__construct();
    }

    public function get_values($order_id) {
        static $order_values = array();

        $this->load->model('miniant/maintenance_contract_model');
        $this->load->model('miniant/unit_model');
        $this->load->model('miniant/brand_model');
        $this->load->model('miniant/servicequote_model');
        $this->load->model('miniant/unitry_type_model');
        $this->load->model('miniant/order_attachment_model');
        $this->load->model('miniant/assignment_model');
        $this->load->model('miniant/tenancy_model');

        if (!empty($order_values[$order_id])) {
            return $order_values[$order_id];
        }

        $this->db->from($this->table);
        $this->db->join('accounts', 'accounts.id = '.$this->table.'.account_id');
        $this->db->join('contacts billing_contact', 'billing_contact.id = '.$this->table.'.billing_contact_id', 'LEFT OUTER');
        $this->db->join('contacts site_contact', 'site_contact.id = '.$this->table.'.site_contact_id', 'LEFT OUTER');
        $this->db->join('addresses billing_address', 'billing_address.account_id = accounts.id AND billing_address.type_id = (SELECT id FROM types WHERE entity = "address" AND name = "Billing")', 'LEFT OUTER', false);
        $this->db->join('addresses site_address', 'site_address.id = '.$this->table.'.site_address_id', 'LEFT OUTER');
        $this->db->join('types order_types', 'order_types.id = '.$this->table.'.order_type_id', 'LEFT OUTER');
        $this->db->join('priority_levels', 'priority_levels.id = '.$this->table.'.priority_level_id', 'LEFT OUTER');
        $this->db->join('users senior_technician', 'senior_technician.id = '.$this->table.'.senior_technician_id', 'LEFT OUTER');
        $this->db->join('miniant_location_diagrams location_diagram', 'location_diagram.id = '.$this->table.'.location_diagram_id', 'LEFT OUTER');

        $this->db->where(''.$this->table.'.id', $order_id);

        $this->db->select(''.$this->table.'.*, order_types.name AS order_type');

        $this->select_foreign_table_fields('accounts');
        $this->select_foreign_table_fields('contacts', 'billing_contact');
        $this->select_foreign_table_fields('contacts', 'site_contact');
        $this->select_foreign_table_fields('addresses', 'billing_address');
        $this->select_foreign_table_fields('addresses', 'site_address');
        $this->select_foreign_table_fields('users', 'senior_technician');
        $this->select_foreign_table_fields('miniant_location_diagrams', 'location_diagram');

        $query = $this->db->get();
        $result = $query->result();
        if (empty($result)) {
            $return_value = false;
        } else {
            $values = (array) $result[0];
            $values['attachment'] = $this->order_attachment_model->get(array('order_id' => $order_id), true);

            if (!empty($values['attachment']->filename_original)) {
                $values['attachment']->url = base_url()."application/modules/miniant/files/{$values['attachment']->directory}/{$values['attachment']->order_id}/{$values['attachment']->hash}";
            }

            $values['units'] = $this->unit_model->get_from_order_id($order_id);
            if ($assignments = $this->assignment_model->get(array('order_id' => $order_id))) {
                $values['assignments'] = array();

                foreach ($assignments as $assignment) {
                    $values['assignments'][] = $this->assignment_model->get_values($assignment->id);
                }
            }

            $values['reference_id'] = $this->get_reference_id($order_id);
            $values['statuses'] = $this->get_statuses($order_id);
            $values['tenancies'] = $this->get_tenancies($order_id);
            $return_value = $values;
        }
        $order_values[$order_id] = $return_value;
        return $order_values[$order_id];
    }

    public function get_linkable_servicequotes() {

        return $this->servicequote_model->get_dropdown('id', true, function($servicequote) {
            return $servicequote->id . ' ('. unix_to_human($servicequote->creation_date) .')';
        });
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            $this->load->model('miniant/miniant_account_model', 'account_model');
            $order_ids = array();
            $orders = array();
            $statuses = array();

            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {
                $statuses = $this->order_model->get_statuses($row['order_id'], false);

                $allstatuses = $this->status_model->get_for_document_type('order');
                if (empty($allstatuses)) {
                    $db_records[$key]['statuses'] = '';
                    continue;
                }

                if (has_capability('orders:editstatuses')) {
                    $db_records[$key]['statuses'] = '<select data-nonSelectedText="No Statuses" data-callback="miniant/orders/order_ajax/update_statuses/'.$row['order_id'].'" ' .
                        'data-order_id="'.$row['order_id'].'" class="multiselect" multiple="multiple">';

                    foreach ($allstatuses as $status) {
                        $selected = '';
                        if (!empty($statuses)) {
                            foreach ($statuses as $order_stasus) {
                                if ($order_stasus->status_id == $status->id) {
                                    $selected = 'selected="selected"';
                                    break;
                                }
                            }
                        }

                        $db_records[$key]['statuses'] .= '<option value="'.$status->id.'" '.$selected.' >'.$status->name.'</option>'."\n";

                    }

                    $db_records[$key]['statuses'] .= '</select>'."\n";
                } else {
                    if (!empty($statuses)) {
                        $db_records[$key]['statuses'] = '<ul class="sr_statuses">';

                        foreach ($statuses as $status_id => $status) {
                            $db_records[$key]['statuses'] .= "<li>$status->name</li>";
                        }
                        $db_records[$key]['statuses'] .= '</ul>';
                    } else {
                        $db_records[$key]['statuses'] = '';
                    }
                }

                $order = $this->get($row['order_id'], true, null, array('site_contact_id', 'billing_contact_id', 'account_id'));
                $site_contact_id = $order->site_contact_id;
                $billing_contact_id = $order->billing_contact_id;
                $account_id = $order->account_id;

                if (has_capability('orders:viewbillingcontact')) {
                    $db_records[$key]['billing_contact_name'] = (empty($row['billing_contact_name'])) ? ' ' : anchor(base_url().'users/contact/edit/'.$billing_contact_id, $row['billing_contact_name']);
                }

                if (has_capability('orders:viewsitecontact') && !empty($site_contact_id)) {
                    $this->db->select('IF(mobile, mobile, phone) AS site_contact_phone', false);
                    $phone = $this->contact_model->get($site_contact_id, true)->site_contact_phone;

                    $site_contact_info = (has_capability('orders:viewcontactnumber')) ? $row['site_contact_name'] . ' ('.$phone.')' : '';
                    $db_records[$key]['site_contact_name'] = (empty($row['site_contact_name'])) ? ' ' : anchor(base_url().'users/contact/edit/'.$site_contact_id, $site_contact_info);
                }

                $db_records[$key]['parent_sq_id'] = (empty($row['parent_sq_id'])) ? ' ' : anchor(base_url().'miniant/servicequotes/servicequote/index/html/0/'.$row['parent_sq_id'], $row['parent_sq_id']);

                $db_records[$key]['name'] = (empty($row['name'])) ? ' ' : anchor(base_url().'accounts/edit/'.$account_id, $row['name']);
                $db_records[$key]['order_type'] = '<span class="well well-sm '.strtolower($db_records[$key]['order_type']).'">'.$db_records[$key]['order_type'].'</span>';

                if ($this->account_model->get($account_id, true, null, array('cc_hold'))->cc_hold) {
                    $db_records[$key]['name'] = '<span class="credit-hold">'.$db_records[$key]['name'].'</span>';
                }
            }
        };
    }

    /**
     * A order can ONLY be unscheduled if:
     * 1. It has no started/completed assignments
     */
    public function can_be_unscheduled($order_id) {
        $this->load->model('miniant/assignment_model');
        if ($assignments = $this->assignment_model->get(array('order_id' => $order_id))) {

            foreach ($assignments as $assignment) {
                if ($this->assignment_model->has_statuses($assignment->id, array('STARTED', 'COMPLETE', 'ARCHIVED'))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * An order can allocate new assignments if:
     * 1. It has no associated assignments of any kind
     */
    public function has_schedulable_assignments($order_id) {
        $this->load->model('miniant/assignment_model');
        $this->db->where('appointment_date >', 0);
        $assignments = $this->assignment_model->get(array('order_id' => $order_id));
        return empty($assignments);
    }

    public function get_reference_id($order_id) {
        return "J$order_id";
    }

    /**
     * Special function for maintenance orders, to group them by year and month
     */
    public function get_maintenance_orders($yearsback = 1, $yearsforward = 1) {
        $this->load->model('miniant/maintenance_contract_model');
        $maintenance_order_type_id = $this->get_type_id('Maintenance');

        $seconds_back = $yearsback * 365 * 24 * 60 * 60;
        $seconds_forward = $yearsforward * 365 * 24 * 60 * 60;
        $current_time = time();
        $earliest_time = $current_time - $seconds_back;
        $latest_time = $current_time + $seconds_forward;
        $avg_seconds_in_a_month = 30.4375 * 24 * 60 * 60;

        $this->db->where("preferred_start_date BETWEEN $earliest_time AND $latest_time", null, false);

        $maintenance_orders = $this->get(array('order_type_id' => $maintenance_order_type_id));

        $grouped_orders = array();

        foreach ($maintenance_orders as $maintenance_order) {

            if (empty($maintenance_order->maintenance_contract_id)) {
                continue;
            }
            $maintenance_contract = $this->maintenance_contract_model->get_from_cache($maintenance_order->maintenance_contract_id);
            $last_job_date = $this->maintenance_contract_model->get_last_order_date($maintenance_contract->id);

            $beginning = ($last_job_date > $earliest_time) ? $last_job_date : $maintenance_order->preferred_start_date;

            for ($i = $beginning; $i < $latest_time; $i += $maintenance_contract->schedule_interval * $avg_seconds_in_a_month) {
                $year = date('Y', $i);
                $month = date('m', $i);

                if (empty($grouped_orders[$year])) {
                    $grouped_orders[$year] = array();
                }

                if (empty($grouped_orders[$year][$month])) {
                    $grouped_orders[$year][$month] = array();
                }

                $grouped_orders[$year][$month][] = $maintenance_order;
            }
        }

        return $grouped_orders;

    }


    // Override the parent function to copy unit associations if they exist (for Maintenance orders only)
    public function add($params) {
        $this->load->model('miniant/unit_model');
        $params = (array) $params;

        $order_type = $this->get_type_string($params['order_type_id']);
        $workflow_id = $this->workflow_model->get(array('name' => strtolower($order_type)), true)->id;

        if ($params['order_type_id'] == $this->get_type_id('Maintenance') || $params['order_type_id'] == $this->get_type_id('Service')) {
            $units = $this->unit_model->get(array('site_address_id' => $params['site_address_id']));

            if (!empty($units)) {
                $order_id = parent::add($params);

                if ($params['order_type_id'] != $this->get_type_id('Installation')) {
                    foreach ($units as $unit) {
                        $this->db->insert('miniant_assignments', array('order_id' => $order_id, 'unit_id' => $unit->id, 'workflow_id' => $workflow_id));
                    }
                }

                return $order_id;
            } else {
                return parent::add($params);
            }
        } else {
            return parent::add($params);
        }
    }

    public function get_units($order_id, $technician_id=null) {
        $this->load->model('miniant/unit_model');
        $units = array();
        return $this->unit_model->get_from_order_id($order_id, $technician_id);
    }

    public function get_total_time($order_id, $technician_id=null) {
        $this->load->model('miniant/order_time_model');

        $this->db->where('time_end >', 0);
        $order_times = $this->order_time_model->get(array('order_id' => $order_id));

        $total_time = 0;
        foreach ($order_times as $order_time) {
            $start_date = new DateTime(date('Y-m-d', $order_time->time_start));
            $end_date = new DateTime(date('Y-m-d', $order_time->time_end));
            $interval = $start_date->diff($end_date);

            if ($interval->days > 0) {

                $time = $order_time->time_end - $order_time->time_start;
                $time -= $interval->days * 12 * 60 * 60; // Remove 12 hours from every day within the interval (night time)

            } else {
                $time = $order_time->time_end - $order_time->time_start;
            }

            $total_time += $time;
        }

        return $total_time;
    }

    public function add_travel_time($assignment_id, $seconds) {
        $assignment = $this->get($assignment_id);
        return $this->edit($assignment_id, array('time_started' => $assignment->time_started + $seconds));
    }

    // If only one technician is left working on this order, set him up as the senior technician
    public function set_as_senior_if_last($assignment_id) {
        $this->load->model('miniant/assignment_model');
        $order_id = $this->assignment_model->get($assignment_id, true, null, array('order_id'))->order_id;
        $assignments = $this->assignment_model->get(array('order_id' =>$order_id));

        $technician_ids = array();
        foreach ($assignments as $assignment) {
            if (!empty($assignment->technician_id)) {
                $technician_ids[$assignment->technician_id] = $assignment->technician_id;
            }
        }

        if (count($technician_ids) == 1) {
            $this->order_model->edit($order_id, array('senior_technician_id' => reset($technician_ids)));
        }
    }

    public function get_tenancies($order_id) {
        $this->load->model('miniant/tenancy_model');
        return $this->tenancy_model->get_for_order($order_id);
    }

    public function get_tasks($order_id) {
        $this->load->model('miniant/order_task_model');

        $this->db->join('miniant_orders_tasks', 'miniant_orders_tasks.order_task_id = miniant_order_tasks.id AND miniant_orders_tasks.order_id = '.$order_id, 'LEFT OUTER');
        $this->db->select('miniant_order_tasks.*, miniant_orders_tasks.creation_date AS completed_date');
        return $this->order_task_model->get();
    }

    public function generate_repair_job($sq_id) {
        $this->load->model('miniant/servicequote_model');
        $this->load->model('miniant/assignment_model');
        $this->load->model('miniant/diagnostic_issue_model');
        $this->load->model('miniant/diagnostic_tree_model');
        $this->load->model('miniant/repair_task_model');
        $this->load->model('workflow_model');

        $servicequote = $this->servicequote_model->get($sq_id);

        if ($order = $this->order_model->get($servicequote->order_id)) {
            $sq = $this->servicequote_model->get($sq_id);

            $original_order = $this->order_model->get_from_cache($sq->order_id);

            $repair_order = new stdClass();
            $repair_order->account_id = $original_order->account_id;
            $repair_order->site_contact_id = $original_order->site_contact_id;
            $repair_order->location_diagram_id = $original_order->location_diagram_id;
            $repair_order->site_address_id = $original_order->site_address_id;
            $repair_order->billing_contact_id = $original_order->billing_contact_id;
            $repair_order->parent_sq_id = $sq_id;
            $repair_order->order_type_id = $this->order_model->get_type_id('Repair');

            $repair_order->id = $this->order_model->add($repair_order);

            // Now generate the assignment linked with the diagnostic
            $original_asignment = $this->assignment_model->get(array('diagnostic_id' => $sq->diagnostic_id), true);

            $new_assignment = new stdClass();
            $new_assignment->unit_id = $original_asignment->unit_id;
            $new_assignment->workflow_id = $this->workflow_model->get(array('name' => 'repair'), true)->id;
            $new_assignment->location_token = $original_asignment->location_token;
            $new_assignment->estimated_duration = 2;
            $new_assignment->order_id = $repair_order->id;
            $new_assignment->id = $this->assignment_model->add($new_assignment);

            // We don't override the new assignment's diagnostic_id because a repair job can lead to a new diagnostic, SQ etc.

            // Create a list of repair tasks based on diagnostic issues
            $diagnostic_issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $sq->diagnostic_id, 'can_be_fixed_now' => 0));

            foreach ($diagnostic_issues as $diagnostic_issue) {
                $steps = array();
                $part_type_issue_type = $this->diagnostic_tree_model->get_part_type_issue_type($diagnostic_issue->part_type_id, $diagnostic_issue->issue_type_id);

                if (!empty($part_type_issue_type->id)) {
                    $steps = $this->diagnostic_tree_model->get_part_type_issue_type_steps($part_type_issue_type->id);

                    foreach ($steps as $step) {
                        $repair_task = new stdClass();
                        $repair_task->diagnostic_issue_id = $diagnostic_issue->id;
                        $repair_task->step_id = $step->step_id;
                        $repair_task->assignment_id = $new_assignment->id;
                        $this->repair_task_model->add($repair_task);
                    }
                }
            }

            $this->set_status($repair_order->id, "READY FOR ALLOCATION");
        }
    }

    public function has_repairs_approved($order_id) {
        $this->load->model('miniant/assignment_model');

        if ($assignments = $this->assignment_model->get(array('order_id' => $order_id))) {
            foreach ($assignments as $assignment) {
                if ($this->assignment_model->has_repairs_approved($assignment->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function has_sq_approved($order_id) {
        $this->load->model('miniant/assignment_model');
        if ($assignments = $this->assignment_model->get(array('order_id' => $order_id))) {

            foreach ($assignments as $assignment) {
                if ($this->assignment_model->has_sq_approved($assignment->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function are_all_tenancies_signed($order_id) {
        $this->load->model('miniant/tenancy_model');
        $all_tenancies = $this->get_tenancies($order_id);
        $all_tenancies_signed = true;

        foreach ($all_tenancies as $tenancy) {
            if (!$this->tenancy_model->invoice_is_signed($tenancy->id, $order_id)) {
                $all_tenancies_signed = false;
            }
        }

        return $all_tenancies_signed;
    }

    public function expiry_check($expiry) {
        if (preg_match('|[0-3][0-9]/[0-1][0-9]|', $expiry)) {
            return $expiry;
        } else {
            return false;
        }
    }

}
