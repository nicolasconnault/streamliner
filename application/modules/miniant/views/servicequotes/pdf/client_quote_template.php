<?php
$this->miniant_pdf->writeHTML($this->load->view('servicequotes/pdf/client_quote_intro', array(), true), false, false, false, false, '');

$this->miniant_pdf->print_heading('Description of works');
$description_of_work = str_replace("\n", '<br />', $servicequote->description_of_work);
$this->miniant_pdf->print_paragraph($description_of_work);

$this->miniant_pdf->print_heading($unit_type);

$this->miniant_pdf->writeHTML($this->load->view('servicequotes/pdf/client_quote', array(), true), false, false, false, false, '');

$this->miniant_pdf->print_heading('Diagnostic');

$this->miniant_pdf->print_paragraph("$servicequote->diagnostic_time hours were spent on diagnostic previous to this quotation, at the cost of ".currency_format($servicequote->diagnostic_cost). " inc. GST. Ref: J". $order_id);

$this->miniant_pdf->print_heading('Comments');

$this->miniant_pdf->writeHTML($this->load->view('servicequotes/pdf/client_quote_comments', array(), true), false, false, false, false, '');

$this->miniant_pdf->print_heading('Client\'s acknowledgment receipt', 3);

$this->miniant_pdf->print_paragraph('Kindly acknowledge below on this receipt and either fax back or email us a new work order.');

$this->miniant_pdf->SetFont($this->miniant_pdf->_config['page_font'], 'B', $this->miniant_pdf->_config['page_font_size']);
$this->miniant_pdf->writeHTML('<table cellpadding="8"><tr><td width="400" style="background-color: #000; color: #FFF;padding: 10px">SERVICE QUOTATION</td><td width="190" border="1">'.$servicequote_id.'</td></tr></table>', false, false, false, false, '');

$this->miniant_pdf->print_paragraph('We / I accept the contract terms and authorise Temperature Solutions to proceed with the above quotation.', 2);

$this->miniant_pdf->writeHTML('<br /><br />
    <table style="text-align: right;" cellpadding="8"><tr>
        <td width="10%">Name:</td><td style="border-bottom: 1px solid #000;" width="22%"></td>
        <td width="10%">Signature:</td><td style="border-bottom: 1px solid #000;" width="22%"></td>
        <td width="10%">Date:</td><td style="border-bottom: 1px solid #000;" width="22%"></td>
    </tr></table><br /><br />', false, false, false, false, '');

$this->miniant_pdf->print_paragraph('TEMPERATURE SOLUTIONS TRUST THAT INFORMATION AS REQUESTED MEETS WITH YOUR APPROVAL. ANY FURTHER QUERIES PLEASE DO NOT HESITATE TO CONTACT US AT THE ABOVE NUMBERS');
