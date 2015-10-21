<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
class Servicequote_Model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_servicequotes';

    public function create_from_diagnostic($diagnostic_id) {
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $diagnostic_id), true);

        $sq = new stdClass();
        $sq->order_id = $assignment->order_id;
        $sq->diagnostic_id = $diagnostic_id;

        if ($existing_sq = $this->get((array) $sq, true)) {
            $sq_id = $existing_sq->id;
        } else {
            $sq_id = $this->add($sq);
        }

        foreach ($this->part_model->get(array('assignment_id' => $assignment->id, 'needs_sq' => true)) as $part) {
            $this->part_model->edit($part->id, array('servicequote_id' => $sq_id));
        }

        return $sq_id;
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            $statuses = array();

            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {
                $statuses = $this->servicequote_model->get_statuses($row['servicequote_id'], false);

                $allstatuses = $this->status_model->get_for_document_type('servicequote');
                if (empty($allstatuses)) {
                    $db_records[$key]['statuses'] = '';
                    continue;
                }

                if (has_capability('servicequotes:editstatuses')) {
                    $db_records[$key]['statuses'] = '<select data-nonSelectedText="No Statuses" data-callback="miniant/servicequotes/servicequote_ajax/update_statuses/'.$row['servicequote_id'].'" ' .
                        'data-servicequote_id="'.$row['servicequote_id'].'" class="multiselect" multiple="multiple">';

                    foreach ($allstatuses as $status) {
                        $selected = '';
                        if (!empty($statuses)) {
                            foreach ($statuses as $servicequote_stasus) {
                                if ($servicequote_stasus->status_id == $status->id) {
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

                if (has_capability('orders:editorders')) {
                    $db_records[$key]['order_id'] = anchor(base_url().'miniant/orders/order/edit/'.$db_records[$key]['order_id'], $db_records[$key]['order_id']);
                }

            }
        };
    }

    public function get_parts($servicequote_id) {
        $servicequote = $this->get($servicequote_id);
        $assignment = $this->assignment_model->get(array('diagnostic_id' => $servicequote->diagnostic_id), true);
        $this->db->where('part_type_id NOT IN (SELECT id FROM miniant_part_types WHERE name = "Labour")', null, false);
        return $this->part_model->get(array('servicequote_id' => $servicequote_id, 'assignment_id' => $assignment->id));
    }

    public function update_selected_suppliers($servicequote_id, $supplier_contact_ids) {
        $this->servicequote_supplier_model->delete(array('servicequote_id' => $servicequote_id));
        foreach ($supplier_contact_ids as $supplier_contact_id) {
            $this->servicequote_supplier_model->add(array('servicequote_id' => $servicequote_id, 'supplier_id' => $supplier_contact_id));
        }
        return true;
    }

    public function get_client_details($servicequote_id) {
        $this->load->model('miniant/miniant_account_model', 'account_model');
        $servicequote = $this->get($servicequote_id);
        $order = $this->order_model->get($servicequote->order_id);
        $account = $this->account_model->get($order->account_id);
        $contact = $this->contact_model->get(array('account_id' => $order->account_id, 'contact_type_id' => $this->contact_model->get_type_id('Billing')), true);
        $client_details = new stdClass();
        $client_details->account_id = $account->id;
        $client_details->contact_id = $contact->id;
        $client_details->account_name = $account->name;
        $client_details->first_name = $contact->first_name;
        $client_details->surname = $contact->surname;
        $client_details->phone = $contact->phone;
        $client_details->email = $contact->email;
        $client_details->job_site_address = $this->address_model->get_formatted_address($order->site_address_id);

        return $client_details;
    }
}
