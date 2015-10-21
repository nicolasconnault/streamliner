<?php
class Export extends MY_Controller {

    public function export_documents($format='pdf', $invoice_tenancy_id, $order_id) {
        ob_end_clean();

        $this->load->model('signature_model');
        $this->load->model('miniant/abbreviation_model');
        $this->load->model('miniant/invoice_tenancy_abbreviation_model');

        $invoice_tenancy = $this->invoice_tenancy_model->get($invoice_tenancy_id);
        $signature = $this->signature_model->get($invoice_tenancy->signature_id);
        $signature_image = $this->signature_model->get_image($invoice_tenancy->signature_id);
        $invoice = $this->invoice_model->get($invoice_tenancy->invoice_id);
        $order = (object) $this->order_model->get_values($invoice->order_id);
        $tenancy = $this->tenancy_model->get($invoice_tenancy->tenancy_id);
        $order->job_site_address = $this->address_model->get_formatted_address($order->site_address_id);
        $abbreviations = $this->abbreviation_model->get_dropdown('description', false);
        $invoice_tenancy_abbreviations = $this->invoice_tenancy_abbreviation_model->get(array('invoice_tenancy_id' => $invoice_tenancy_id));

        foreach ($invoice_tenancy_abbreviations as $key => $invoice_tenancy_abbreviation) {
            $invoice_tenancy_abbreviations[$key]->text = $abbreviations[$invoice_tenancy_abbreviation->abbreviation_id];
        }

        $this->db->where('tenancy_id', $invoice_tenancy->tenancy_id);
        $units = $this->unit_model->get_from_order_id($order_id);

        foreach ($units as $key => $unit) {
            $units[$key]->assignment = (object) $this->assignment_model->get_values($units[$key]->assignment_id);

            $unit_photos = get_photos('assignment', $unit->assignment_id, $unit->id, 'miniant');
            $photos = array();
            $photos['Equipment photos'] = array();

            if (!empty($unit_photos)) {
                foreach ($unit_photos as $photo) {
                    $photos['Equipment photos'][] = $photo;
                }
            }

            if (!empty($units[$key]->assignment->diagnostic_id)) {
                $units[$key]->diagnostic = (object) $this->diagnostic_model->get_values($units[$key]->assignment->diagnostic_id);
                $units[$key]->issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $units[$key]->diagnostic->id));

                if (!$units[$key]->diagnostic->bypassed) {
                    $has_required_diagnostics = true;
                }
            }

            if (!empty($units[$key]->issues) && !$units[$key]->assignment->hide_issue_photos) {
                foreach ($units[$key]->issues as $diagnostic_issue) {
                    if ($issue_photos = get_photos('diagnostic_issue', null, $diagnostic_issue->id, 'miniant')) {
                        foreach ($issue_photos as $issue_photo) {
                            if (empty($photos["$diagnostic_issue->issue_type_name $diagnostic_issue->part_type_name"])) {
                                $photos["$diagnostic_issue->issue_type_name $diagnostic_issue->part_type_name"] = array();
                            }

                            $photos["$diagnostic_issue->issue_type_name $diagnostic_issue->part_type_name"][] = $issue_photo;
                        }
                    }
                }
            }

            $units[$key]->photos = $photos;

            $parts_used = $this->part_model->get(array('assignment_id' => $unit->assignment_id));

            foreach ($parts_used as $key2 => $part_used) {
                if (empty($part_used->part_name)) {
                    $parts_used[$key2]->part_name = $this->part_type_model->get($part_used->part_type_id)->name;
                }

                if (empty($part_used->quantity)) {
                    $part_used->quantity = $part_used->description;
                }
                if (!empty($part_used->servicequote_id) || empty($part_used->quantity)) {
                    unset($parts_used[$key2]);
                }
            }
            $units[$key]->parts_used = $parts_used;
        }

        $invoice_tenancy_parts = $this->invoice_tenancy_part_model->get(array('invoice_tenancy_id' => $invoice_tenancy_id));

        $parts = array();
        foreach ($invoice_tenancy_parts as $invoice_tenancy_part) {
            $parts[] = $this->part_model->get($invoice_tenancy_part->part_id);
        }

        $this->load->library('miniant_pdf', array('header_title' => "Invoice for $tenancy->name", 'header_font_size' => 14));
        $this->miniant_pdf->_config['page_orientation'] = 'portrait';
        $this->miniant_pdf->addpage();
        $this->miniant_pdf->setCellPadding(55);
        $this->miniant_pdf->_config['encoding'] = 'UTF-8';
        $this->miniant_pdf->SetSubject("Invoice for $tenancy->name");

        $view_params = array(
            'order' => $order,
            'order_dowd' => $this->dowd_model->get_formatted_order_dowd($order->dowd_id, $order->id),
            'invoice_tenancy' => $invoice_tenancy,
            'abbreviations' => $invoice_tenancy_abbreviations,
            'parts' => $parts,
            'tenancy' => $tenancy,
            'units' => $units,
            'signature_image' => $signature_image,
            'signature' => $signature
            );

        $this->miniant_pdf->SetFont($this->miniant_pdf->_config['page_font'], 'B', $this->miniant_pdf->_config['page_font_size']);
        $output = $this->load->view('orders/pdf/invoice_tenancy_template', $view_params, true);
        $this->miniant_pdf->writeHTML($output, false, false, false, false, '');

        $filename = 'tenancy_invoice_'.$invoice_tenancy->id.'.pdf';
        $this->miniant_pdf->output($filename, 'D');
    }
}
