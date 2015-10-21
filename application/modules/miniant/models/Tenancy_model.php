<?php
class Tenancy_model extends MY_Model {
    public $table = 'miniant_tenancies';

    /**
     * Get all the tenancies that are linked to this order (through assignments and units tables), and the signature if there is one
     */
    public function get_for_order($order_id) {

        $this->db->join('miniant_units', 'miniant_units.tenancy_id = '.$this->table.'.id');
        $this->db->join('miniant_assignments', 'miniant_assignments.unit_id = miniant_units.id');
        $this->db->where('miniant_assignments.order_id', $order_id);
        $this->db->group_by($this->table.'.id');
        $tenancies = $this->get(array('miniant_assignments.order_id' => $order_id), false, null, array($this->table.'.*'));

        foreach ($tenancies as $key => $tenancy) {
            if ($order_signature = $this->order_signature_model->get(array('order_id' => $order_id, 'tenancy_id' => $tenancy->id), true)) {
                $tenancies[$key]->signature = $this->signature_model->get($order_signature->signature_id);
            }
        }

        return $tenancies;
    }

    public function add($params) {
        $this->load->model('miniant/tenancy_log_model');
        $tenancy_id = parent::add($params);
        $this->tenancy_log_model->create_tenancy($tenancy_id);
        return $tenancy_id;
    }

    public function delete($tenancy_id) {
        $this->load->model('miniant/tenancy_log_model');
        parent::delete($tenancy_id);
        $this->tenancy_log_model->delete_tenancy($tenancy_id);
        return null;
    }

    public function invoice_is_signed($tenancy_id, $order_id) {
        $this->load->model('miniant/invoice_model');
        $this->load->model('miniant/invoice_tenancy_model');

        if (!($invoice = $this->invoice_model->get(array('order_id' => $order_id), true))) {
            return false;
        }

        if (!($invoice_tenancy = $this->invoice_tenancy_model->get(array('invoice_id' => $invoice->id, 'tenancy_id' => $tenancy_id), true))) {
            return false;
        }

        return !empty($invoice_tenancy->signature_id);
    }

    public function get_signature_for_order($tenancy_id, $order_id) {
        $this->load->model('miniant/invoice_model');
        $this->load->model('miniant/invoice_tenancy_model');
        $this->load->model('signature_model');

        if (!($invoice = $this->invoice_model->get(array('order_id' => $order_id), true))) {
            return false;
        }

        if (!($invoice_tenancy = $this->invoice_tenancy_model->get(array('invoice_id' => $invoice->id, 'tenancy_id' => $tenancy_id), true))) {
            return false;
        }
        if (!empty($invoice_tenancy->signature_id)) {
            return $this->signature_model->get($invoice_tenancy->signature_id);
        } else {
            return false;
        }
    }
}

