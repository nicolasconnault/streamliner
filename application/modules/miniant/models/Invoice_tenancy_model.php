<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
class Invoice_tenancy_model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_invoice_tenancies';

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {
                $statuses = $this->invoice_tenancy_model->get_statuses($row['invoice_tenancy_id'], false);

                $allstatuses = $this->status_model->get_for_document_type('invoice_tenancy');
                if (empty($allstatuses)) {
                    $db_records[$key]['statuses'] = '';
                    continue;
                }

                if (has_capability('orders:editstatuses')) {
                    $db_records[$key]['statuses'] = '<select data-nonSelectedText="No Statuses" data-callback="miniant/orders/documents/update_statuses/'.$row['invoice_tenancy_id'].'" ' .
                        'data-invoice_tenancy_id="'.$row['invoice_tenancy_id'].'" class="multiselect" multiple="multiple">';

                    foreach ($allstatuses as $status) {
                        $selected = '';
                        if (!empty($statuses)) {
                            foreach ($statuses as $invoice_tenancy_stasus) {
                                if ($invoice_tenancy_stasus->status_id == $status->id) {
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

                $db_records[$key]['tenancy_id'] = $this->tenancy_model->get_name($row['tenancy_id']);
                $db_records[$key]['system_time'] = ceil($row['system_time'] / 60 / 60) . ' hours';
                $db_records[$key]['technician_time'] = ceil($row['technician_time'] / 60 / 60) . ' hours';
            }
        };
    }

    public function update_abbreviations($invoice_tenancy_id, $abbreviations) {
        $this->load->model('miniant/abbreviation_model');
        $this->load->model('miniant/invoice_tenancy_abbreviation_model');
        $this->invoice_tenancy_abbreviation_model->delete(compact('invoice_tenancy_id'));

        foreach ($abbreviations as $abbreviation) {
            $this->invoice_tenancy_abbreviation_model->add(array('invoice_tenancy_id' => $invoice_tenancy_id, 'abbreviation_id' => $abbreviation));
        }
    }
}
