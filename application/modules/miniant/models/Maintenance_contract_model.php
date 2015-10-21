<?php
require_once(APPPATH.'/core/has_statuses_trait.php');

class Maintenance_contract_model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_maintenance_contracts';

    public function get_values($maintenance_contract_id) {
        static $maintenance_contract_values = array();

        if (!empty($maintenance_contract_values[$maintenance_contract_id])) {
            return $maintenance_contract_values[$maintenance_contract_id];
        }

        $this->db->from($this->table);
        $this->db->join('accounts', 'accounts.id = '.$this->table.'.account_id');
        $this->db->join('contacts billing_contact', 'billing_contact.id = '.$this->table.'.billing_contact_id', 'LEFT OUTER');
        $this->db->join('contacts property_manager_contact', 'property_manager_contact.id = '.$this->table.'.property_manager_contact_id', 'LEFT OUTER');
        $this->db->join('addresses billing_address', 'billing_address.account_id = accounts.id AND billing_address.type_id = (SELECT id FROM types WHERE entity = "address" AND name = "Billing")', 'LEFT OUTER', false);
        $this->db->join('addresses site_address', 'site_address.id = '.$this->table.'.site_address_id', 'LEFT OUTER');

        $this->db->where(''.$this->table.'.id', $maintenance_contract_id);
        $this->db->select(''.$this->table.'.*');

        $this->select_foreign_table_fields('accounts');
        $this->select_foreign_table_fields('contacts', 'billing_contact');
        $this->select_foreign_table_fields('contacts', 'property_manager_contact');
        $this->select_foreign_table_fields('addresses', 'billing_address');
        $this->select_foreign_table_fields('addresses', 'site_address');

        $query = $this->db->get();
        $result = $query->result();
        if (empty($result)) {
            $return_value = false;
        } else {
            $values = (array) $result[0];

            $values['units'] = $this->get_units($maintenance_contract_id);
            $values['reference_id'] = $this->get_reference_id($maintenance_contract_id);
            $values['statuses'] = $this->get_statuses($maintenance_contract_id);
            $values['tenancies'] = $this->get_tenancies($maintenance_contract_id);
            $return_value = $values;
        }
        $maintenance_contract_values[$maintenance_contract_id] = $return_value;
        return $maintenance_contract_values[$maintenance_contract_id];
    }

    public function get_units($maintenance_contract_id) {
        $records = $this->maintenance_contract_unit_model->get(compact('maintenance_contract_id'));
        $units = array();
        foreach ($records as $record) {
            $units[] = $this->unit_model->get($record->unit_id);
        }
        return $units;
    }

    public function add_unit($unit_id, $maintenance_contract_id) {
        if (!($this->maintenance_contract_unit_model->get(compact('unit_id', 'maintenance_contract_id'), true))) {
            $this->maintenance_contract_unit_model->add(compact('unit_id', 'maintenance_contract_id'));
        }
        return true;
    }

    public function remove_unit($unit_id, $maintenance_contract_id) {
        if ($this->maintenance_contract_unit_model->get(compact('unit_id', 'maintenance_contract_id'), true)) {
            $this->maintenance_contract_unit_model->delete(compact('unit_id', 'maintenance_contract_id'));
        }
        return true;
    }

    /**
     * Business rule: A maintenance order is "current" if its preferred start date is between the 1st and last day of the "next schedule month"
     * The "next schedule month" is one schedule interval (3, 6 or 12 months) later than the last completed maintenance order
     */
    public function needs_new_order($maintenance_contract_id) {
        $maintenance_contract = $this->get_from_cache($maintenance_contract_id);

        $first_second = date('m-01-Y 00:00:00', $maintenance_contract->next_maintenance_date);
        $last_second  = date('m-t-Y 12:59:59', $maintenance_contract->next_maintenance_date);

        $this->db->limit(1);
        if ($latest_order = $this->get_last_order($maintenance_contract_id)) {
            return $latest_order->preferred_start_date >= $first_second && $latest_order->preferred_start_date <= $last_second;
        } else {
            return true;
        }
    }

    public function update_next_maintenance_date($maintenance_contract_id, $last_maintenance_date) {
        $maintenance_contract = $this->get_from_cache($maintenance_contract_id);

        // Number of days each year: 365.25. Average days per month = 365.25 / 12 = 30.4375
        $date_second_offset = $maintenance_contract->schedule_interval * 30.4375 * 24 * 60 * 60;

        $this->edit($maintenance_contract_id, array('next_maintenance_date', $last_maintenance_date + $date_second_offset));
    }

    /**
     * This can lead to duplicate orders, only use after checking for existing orders!
     */
    public function generate_order($maintenance_contract_id) {
        $maintenance_contract = $this->get_from_cache($maintenance_contract_id);

        $new_order = new stdClass();
        $new_order->maintenance_contract_id = $maintenance_contract_id;
        $new_order->account_id = $maintenance_contract->account_id;
        $new_order->order_type_id = $this->order_model->get_type_id('Maintenance');
        $new_order->site_address_id = $maintenance_contract->site_address_id;
        $new_order->billing_contact_id = $maintenance_contract->billing_contact_id;
        $new_order->site_contact_id = $maintenance_contract->site_contact_id;

        return $this->order_model->add($new_order);
    }

    public function get_labelled_dropdown() {
        $this->load->model('miniant/miniant_account_model', 'account_model');
        $dropdown = $this->get_dropdown('account_id');
        foreach ($dropdown as $contract_id => $contract_label) {
            if (empty($contract_id)) {
                continue;
            }

            $contract = $this->get_from_cache($contract_id);
            $dropdown[$contract_id] = $contract_id . " (". $this->account_model->get($contract->account_id)->name . ")";
        }

        return $dropdown;
    }

    public function get_last_order($maintenance_contract_id) {
        return $this->order_model->get(array('maintenance_contract_id' => $maintenance_contract_id), true, 'preferred_start_date DESC');
    }

    public function get_last_order_date($maintenance_contract_id) {
        $last_job = $this->get_last_order($maintenance_contract_id);
        return $last_job->creation_date;
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            $statuses = array();
            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {
                $statuses = $this->maintenance_contract_model->get_statuses($row['maintenance_contract_id'], false);

                $allstatuses = $this->status_model->get_for_document_type('maintenance_contract');
                if (empty($allstatuses)) {
                    $db_records[$key]['statuses'] = '';
                    continue;
                }

                if (has_capability('maintenance_contracts:editstatuses')) {
                    $db_records[$key]['statuses'] = '<select data-nonSelectedText="No Statuses" data-callback="miniant/maintenance_contracts_ajax/update_statuses/'.$row['maintenance_contract_id'].'" ' .
                        'data-maintenance_contract_id="'.$row['maintenance_contract_id'].'" class="multiselect" multiple="multiple">';

                    foreach ($allstatuses as $status) {
                        $selected = '';
                        if (!empty($statuses)) {
                            foreach ($statuses as $maintenance_contract_stasus) {
                                if ($maintenance_contract_stasus->status_id == $status->id) {
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

                switch ($db_records[$key]['schedule_interval']) {
                    case 1:
                        $db_records[$key]['schedule_interval'] = 'Monthly';
                        break;
                    case 3:
                        $db_records[$key]['schedule_interval'] = 'Quarterly';
                        break;
                    case 6:
                        $db_records[$key]['schedule_interval'] = 'Six-monthly';
                        break;
                    case 12:
                        $db_records[$key]['schedule_interval'] = 'Yearly';
                        break;
                    default:
                        $db_records[$key]['schedule_interval'] = "Every ".$db_records[$key]['schedule_interval']. " months";
                }
            }

            $db_records[$key]['site_address_id'] = $this->address_model->get_formatted_address($db_records[$key]['site_address_id']);
        };
    }

    public function get_tenancies($maintenance_contract_id) {
        $this->load->model('miniant/miniant_account_model', 'account_model');
        $tenancies = array();
        $maintenance_contract = $this->maintenance_contract_model->get_from_cache($maintenance_contract_id);
        $account = $this->account_model->get($maintenance_contract->account_id);

        return $this->tenancy_model->get(array('account_id' => $account->id));
    }


    public function get_reference_id($maintenance_contract_id) {
        return "MC$maintenance_contract_id";
    }
}
