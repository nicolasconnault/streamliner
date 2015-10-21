<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Signature extends Stage_controller {

    public function index($assignment_id) {
        require_capability('orders:editunitdetails');

        if (!($this->assignment = $this->assignment_model->get($assignment_id))) {
            die("The assignment ID $assignment_id could not be found!");
        }

        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = parent::get_technician_id($assignment_id);
        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');
        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);
        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'signature', 'param' => $assignment_id, 'module' => 'miniant'));

        $has_required_diagnostics = false;

        $tenancies = array();

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

            if ($order_type != 'Installation') {

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

            if (empty($tenancies[$unit->tenancy_id])) {
                $tenancy = $this->tenancy_model->get($unit->tenancy_id);
                $tenancies[$unit->tenancy_id] = array('units' => array(), 'tenancy' => $tenancy, 'signature' => $this->tenancy_model->get_signature_for_order($tenancy->id, $order->id));
            }
            $tenancies[$unit->tenancy_id]['units'][] = $unit;

        }

        $site_photos = array(
            'pre-job' => get_photos('order', 'site-pre-job', $order->id, 'miniant'),
            'post-job' => get_photos('order', 'site-post-job', $order->id, 'miniant'),
        );

        $this->load_stage_view(array(
             'units' => $units,
             'jstoload' => array(
                 'jquery.signaturepad',
                 'signaturepad/flashcanvas',
                 'signaturepad/json2',
                 ),
             'module' => 'miniant',
             'csstoload' => array('jquery.signaturepad'),
             'tenancies' => $tenancies,
             'site_photos' => $site_photos,
             'terms' => $this->setting_model->get(array('name' => 'terms'), true)->value,
             'is_maintenance' => $order->order_type_id == $this->order_model->get_type_id('Maintenance'),
             'is_service' => $order->order_type_id == $this->order_model->get_type_id('Service'),
             'is_repair' => $order->order_type_id == $this->order_model->get_type_id('Repair'),
             'order_dowd' => $this->dowd_model->get_formatted_order_dowd($order->dowd_id, $order->id),
             'time_started' => @$this->order_technician_model->get_status_change_log($order_technician->id, 'STARTED')->creation_date,
        ));
    }

    public function process() {
        require_capability('orders:editunitdetails');
        $this->load->model('signature_model');

        $assignment = $this->assignment_model->get($this->input->post('assignment_id'));
        $order = $this->order_model->get($assignment->order_id);
        $technician_id = $this->session->userdata('user_id');
        $tenancy_id = $this->input->post('tenancy_id');
        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $units = $this->get_units($assignment->id, $technician_id, $order->senior_technician_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');

        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'signature', 'param' => $assignment->id, 'module' => 'miniant'));

        $errors = false;

        $signature = new stdClass();
        $signature->first_name = $this->input->post('first_name_'.$tenancy_id);
        $signature->last_name = $this->input->post('last_name_'.$tenancy_id);
        $signature->signature_text = $this->input->post('client_signature_'.$tenancy_id);
        $signature->type_id = $this->signature_model->get_type_id('Invoice tenancy');

        $signature->signature_date = time();

        if (empty($signature->first_name)) {
            add_message('Please enter the customer\'s first name before submitting the form', 'danger');
            $errors = true;
        }
        if (empty($signature->last_name)) {
            add_message('Please enter the customer\'s last name before submitting the form', 'danger');
            $errors = true;
        }

        if (empty($signature->signature_text)) {
            add_message('Please obtain the customer\'s signature before submitting the form', 'danger');
            $errors = true;
        }

        if ($errors) {
            return $this->index($assignment->id);
        }

        if (!($invoice = $this->invoice_model->get(array('order_id' => $assignment->order_id), true))) {
            $invoice = new stdClass();
            $invoice->order_id = $assignment->order_id;

            if (!($invoice->id = $this->invoice_model->add($invoice))) {
                add_message('Could not record the invoice!', 'danger');
                return $this->index($assignment->id);
            }
        }

        if (!($invoice_tenancy = $this->invoice_tenancy_model->get(array('invoice_id' => $invoice->id, 'tenancy_id' => $tenancy_id), true))) {
            $invoice_tenancy = new stdClass();
            $invoice_tenancy->invoice_id = $invoice->id;
            $invoice_tenancy->tenancy_id = $tenancy_id;
            $invoice_tenancy->system_time = 0;
            $invoice_tenancy->id = $this->invoice_tenancy_model->add($invoice_tenancy);
        }

        $invoice_tenancy->parts_used = array();

        if (empty($invoice_tenancy->signature_id)) {
            if ($signature->id = $this->signature_model->add($signature)) {
                $this->invoice_tenancy_model->edit($invoice_tenancy->id, array('signature_id' => $signature->id));
            } else {
                add_message('Could not record the signature!', 'danger');
                return $this->index($assignment->id);
            }
        }

        // Create SQ if needed, for each assignment that has the WAITING FOR APPROVAL status
        foreach ($units as $unit) {
            if ($this->assignment_model->has_statuses($unit->assignment_id, array('REQUIRED PARTS RECORDED', 'SQ APPROVED', 'AWAITING REVIEW'), 'AND')) {
                $new_servicequote = new stdClass();
                $new_servicequote->order_id = $unit->assignment->order_id;
                $new_servicequote->diagnostic_id = $unit->assignment->diagnostic_id;

                if (!$this->servicequote_model->get((array) $new_servicequote)) {
                    $this->servicequote_model->add($new_servicequote);
                }

            }

            if ($unit->tenancy_id == $tenancy_id) {
                $invoice_tenancy->parts_used += $this->part_model->get_parts_used_during_diagnostic($unit->assignment_id);
                $invoice_tenancy->system_time += $this->order_model->get_total_time($unit->assignment->order_id);
            }
        }

        foreach ($invoice_tenancy->parts_used as $part) {
            $params = array( 'invoice_tenancy_id' => $invoice_tenancy->id, 'part_id' => $part->id);
            if (!$this->invoice_tenancy_part_model->get($params)) {
                $this->invoice_tenancy_part_model->add($params);
            }
        }

        trigger_event('signed_by_client', 'invoice_tenancy', $invoice_tenancy->id, false, 'miniant');

        if ($this->order_model->are_all_tenancies_signed($order->id)) {
            trigger_event('is_complete', 'order_technician', $order_technician->id, false, 'miniant');
            $this->order_time_model->finish_time($assignment->order_id, $technician_id);

            if ($this->order_technician_model->has_statuses($order_technician->id, array('COMPLETE', 'SIGNED BY CLIENT'), 'AND')) {
                trigger_event('diagnostics_completed', 'order', $assignment->order_id, false, 'miniant');
                add_message('The client signature was recorded, the invoice was generated and will be reviewed shortly by Accounts');
                redirect($this->workflow_manager->get_next_url());
            }
        } else {
            add_message('Please obtain a signature for all the tenancies', 'warning');
            return $this->index($assignment->id);
        }
    }
}
