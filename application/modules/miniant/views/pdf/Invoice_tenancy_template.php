<?php
$this->miniant_pdf->writeHTML($this->load->view('orders/pdf/invoice_tenancy_intro', array(), true), false, false, false, false, '');

$this->miniant_pdf->print_heading('Technician time');

$this->miniant_pdf->print_paragraph('Total time spent by technicians on this job: '.get_hours_and_minutes_from_seconds($invoice_tenancy->technician_time). ' hours.');

$this->miniant_pdf->print_heading('Description of work performed');
$this->miniant_pdf->print_paragraph($order_dowd);

$this->miniant_pdf->writeHTML($this->load->view('orders/pdf/invoice_tenancy_dowd', array(), true), false, false, false, false, '');

$this->miniant_pdf->print_heading('Parts used', 1);

$this->miniant_pdf->writeHTML($this->load->view('orders/pdf/invoice_tenancy_parts', array(), true), false, false, false, false, '');
