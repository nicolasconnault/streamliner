<?php
class Account_Model extends MY_Model {
    public $table = 'accounts';

    public function get_values($account_id) {
        $this->db->from($this->table);
        $this->db->join('addresses billing_address', 'billing_address.account_id = accounts.id AND billing_address.type_id = (SELECT id FROM types WHERE entity = "address" AND name = "Billing")', 'LEFT OUTER', false);

        $this->db->select($this->table.'.*');
        $this->select_foreign_table_fields('addresses', 'billing_address');

        $this->db->where($this->table.'.id', $account_id, false);
        $query = $this->db->get();
        $result = $query->result();
        if (empty($result)) {
            return false;
        } else {
            $values = (array) $result[0];

            $values['billing_contacts'] = $this->contact_model->get(array('account_id' => $account_id, 'contact_type_id' => $this->contact_model->get_type_id('Billing')));
            $values['site_contacts'] = $this->contact_model->get(array('account_id' => $account_id, 'contact_type_id' => $this->contact_model->get_type_id('Site')));
            $values['site_addresses'] = $this->get_site_addresses($account_id);

            return $values;
        }
    }

    public function get_site_addresses($account_id) {
        $addresses = $this->address_model->get(compact('account_id'));

        return $addresses;
    }

    public function get_billing_address($account_id) {
        $type_id = $this->address_model->get_type_id('Billing');
        return $this->address_model->get(compact('account_id', 'type_id'), true);
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            if (empty($db_records)) {
                return null;
            }
            foreach ($db_records as $key => $row) {
                $account = $this->get($row['account_id'], true);

                if ($account->cc_hold) {
                    $db_records[$key]['name'] = '<span class="credit-hold">'.$row['name'].'</span>';
                }
                $db_records[$key]['billing_address'] = $this->address_model->get_formatted_address($this->account_model->get_billing_address($account->id));
            }
        };
    }
}
