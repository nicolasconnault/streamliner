<?php
require_once(APPPATH.'models/Account_model.php');

class Miniant_Account_Model extends Account_model {

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
            $values['property_manager_contacts'] = $this->contact_model->get(array('account_id' => $account_id, 'contact_type_id' => $this->contact_model->get_type_id('Property manager')));
            $values['tenancies'] = $this->get_tenancies($account_id);
            $values['site_addresses'] = $this->get_site_addresses($account_id);

            return $values;
        }
    }

    public function get_site_addresses($account_id) {
        $addresses = $this->address_model->get(compact('account_id'));

        if (!empty($addresses)) {
            foreach ($addresses as $key => $address) {
                $addresses[$key]->orders = $this->order_model->get(array('site_address_id' => $address->id));
            }
        }

        return $addresses;
    }

    public function get_tenancies($account_id) {
        return $this->tenancy_model->get(array('account_id' => $account_id));
    }
}
