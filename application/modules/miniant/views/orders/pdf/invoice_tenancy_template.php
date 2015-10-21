<?php
$this->miniant_pdf->writeHTML($this->load->view('orders/pdf/invoice_tenancy_intro', array(), true), false, false, false, false, '');

$this->miniant_pdf->print_heading('Technician time');

$this->miniant_pdf->print_paragraph('Total time spent by technicians on this job: '.get_hours_and_minutes_from_seconds($invoice_tenancy->technician_time). ' hours.');

$this->miniant_pdf->print_heading('Description of work performed');

$this->miniant_pdf->print_paragraph($this->load->view('orders/pdf/invoice_tenancy_dowd', array(), true));

$this->miniant_pdf->print_paragraph($order_dowd);
foreach ($abbreviations as $abbreviation) {
    $this->miniant_pdf->print_paragraph($abbreviation->text);
}

$this->miniant_pdf->print_heading('Parts used', 1);

$this->miniant_pdf->print_paragraph($this->load->view('orders/pdf/invoice_tenancy_parts', array(), true));

$this->miniant_pdf->print_heading('Signature', 1);
$this->miniant_pdf->print_paragraph('Signed by '. $signature->first_name . ' ' . $signature->last_name . ' on ' . unix_to_human($signature->signature_date));
$this->miniant_pdf->image($signature_image, '', '', 40);
