<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Unit_details extends Stage_controller {

    public function index($assignment_id) {
        // If this stage is completed, redirect to the next uncompleted unit, if any
        if ($assignment = $this->stage_conditions_model->get_next_uncompleted_assignment_for_stage($assignment_id, 'unit_details')) {
            $this->assignment = (object) $this->assignment_model->get_values($assignment->id);
        } else {
            $this->assignment = (object) $this->assignment_model->get_values($assignment_id);
        }

        if (empty($this->assignment)) {
            add_message('This job is no longer on record.', 'warning');
            redirect(base_url());
        }

        $order = (object) $this->order_model->get_values($this->assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);
        $technician_id = parent::get_technician_id($assignment_id);
        $is_technician = user_has_role($this->session->userdata('user_id'), 'Technician');

        parent::update_time($order->id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'unit_details', 'param' => $assignment_id, 'module' => 'miniant'));

        require_capability('orders:editunitdetails');

        $this->order_time_model->finish_time($order->id, $technician_id);
        $this->order_time_model->add(array('time_start' => time(), 'technician_id' => $technician_id, 'order_id' => $order->id));

        $units = $this->get_units($assignment_id, $technician_id, $order->senior_technician_id);

        if (empty($units)) {
            add_message('There are no units associated with this job!', 'danger');
            redirect(base_url('miniant/stages/assignment_details/index/'.$assignment_id));
        }

        $notes = $this->message_model->get_with_author_names(array('document_type' => 'order', 'document_id' => $order->id));

        foreach ($units as $key => $unit) {
            $unit_data = (array) $unit;

            foreach ($unit_data as $var => $val) {
                if (!is_array($val)) form_element::$default_data[$var.'_'.$unit->assignment_id] = $val;
            }

            if ($unit->unit_type_id == $this->unit_model->get_type_id('Evaporative A/C')) {
                form_element::$default_data['brand_id_evap_'.$unit->assignment_id] = $unit->brand_id;
            }
            if ($unit->unit_type_id == $this->unit_model->get_type_id('Refrigerated A/C')) {
                form_element::$default_data['brand_id_ref_'.$unit->assignment_id] = $unit->brand_id;
            }

            // Get assignment of parent order
            $units[$key]->diagnostic_assignment = $this->assignment_model->get_diagnostic_assignment($unit->assignment_id);
            $units[$key]->assignment = (object) $this->assignment_model->get_values($units[$key]->assignment_id);
            $units[$key]->notes = $this->message_model->get_with_author_names(array('document_type' => 'assignment', 'document_id' => $unit->assignment_id));
            $units[$key]->details_recorded = $this->unit_model->has_statuses($unit->id, array('UNIT DETAILS RECORDED'));

            if ($order_type == 'Repair') {
                $photos = array();

                if (!empty($units[$key]->diagnostic_assignment->diagnostic_id)) {
                    $units[$key]->parent_diagnostic = (object) $this->diagnostic_model->get_values($units[$key]->diagnostic_assignment->diagnostic_id);
                    $units[$key]->issues = $this->diagnostic_issue_model->get(array('diagnostic_id' => $units[$key]->parent_diagnostic->id));

                    if (!$units[$key]->parent_diagnostic->bypassed) {
                        $has_required_diagnostics = true;
                    }
                }

                if (!empty($units[$key]->issues) && !$units[$key]->diagnostic_assignment->hide_issue_photos) {
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

            }

            /*
             * I don't think we need this dialog at all
             *
            $this->load->library('Dialog');
            $this->dialog->initialise(array());

            $this->dialog->add_question(array(
                'id' => 'unit_details_recorded',
                'shown' => !$this->unit_model->has_statuses($unit->id, array('UNIT DETAILS RECORDED')),
                'text' => 'Have the details of this unit been recorded by the senior technician ('.$order->senior_technician_first_name.')?',
                'answers' => array(
                    array(
                        'text' => 'Yes',
                        'ids_to_show' => array('start_diagnostic'),
                    ))
                ));

                // $next_page_url = ($order->order_type == 'Installation') ? base_url() . 'orders/technician/installation_checklist/'.$unit->assignment_id : base_url() . 'orders/technician/diagnostic_report/'.$unit->assignment_id;
                $this->dialog->add_question(array(
                    'id' => 'start_diagnostic',
                    'shown' => $this->unit_model->has_statuses($unit->id, array('UNIT DETAILS RECORDED')),
                    'text' => 'Details have been recorded for this unit',
                    'answers' => array(
                        array(
                            'text' => 'Continue',
                            'url' => $this->workflow_manager->get_next_url(),
                        ))
                    ));
            $units[$key]->junior_technician_dialog = $this->dialog->output();
            */
        }

        if ($this->input->post()) {
            foreach ($this->input->post() as $key => $val) {
                form_element::$default_data[$key] = $val;
            }
        }

        $this->load_stage_view(array(
             'units' => $units,
             'jstoload' => array('signature_pad'),
             'is_senior_technician' => $technician_id == $order->senior_technician_id,
             'is_maintenance' => $order_type == 'Maintenance',
             'is_technician' => user_has_role($this->session->userdata('user_id'), 'Technician'),
             'is_service' => $order_type == 'Service',
             'is_repair' => $order_type == 'Repair',
             'notes' => $notes,
        ));
    }

    public function process() {
        $assignment_id = $this->input->post('assignment_id');
        $assignment = $this->assignment_model->get($assignment_id);
        $unit = $this->unit_model->get($assignment->unit_id);
        $order = $this->order_model->get($assignment->order_id);
        $order_type = $this->order_model->get_type_string($order->order_type_id);

        $this->load->library('Miniant_Workflow_manager', array(), 'workflow_manager');
        $this->workflow_manager->initialise(array('workflow' => $order_type, 'stage' => 'unit_details', 'param' => $assignment_id, 'module' => 'miniant'));

        $technician_id = $this->session->userdata('user_id');
        $order_technician = $this->order_technician_model->get(array('order_id' => $order->id, 'technician_id' => $technician_id), true);

        if ($technician_id != $order->senior_technician_id) {
            redirect($this->workflow_manager->get_next_url());
            return false;
        }

        $evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
        $ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
        $trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
        $other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
        $mech_unit_id = $this->unit_model->get_type_id('Mechanical services');


        $form_data = $this->get_form_data($unit->unit_type_id, $assignment_id, $this->input->post(), $order_type);

        $redirect_url = base_url().'miniant/stages/unit_details/index/'.$assignment->id;

        foreach ($form_data['required'] as $field => $description) {
            $this->form_validation->set_rules($field, $description, 'trim|required');
        }

        $success = $this->form_validation->run();
        $action_word = 'updated';

        if (!$success) {
            return $this->index($assignment->id);
        }

        $unit_data = $form_data['fields'];

        if ($unit->unit_type_id == $evap_unit_id || $unit->unit_type_id == $ref_unit_id) {
            $unit_data['brand_id'] = (!empty($unit_data['brand_id_evap'])) ? $unit_data['brand_id_evap'] : $unit_data['brand_id_ref'];
            unset($unit_data['brand_id_ref']);
            unset($unit_data['brand_id_evap']);
        }

        if ($unit_data['unitry_type_id'] == $this->unitry_type_model->get(array('name' => 'R/C Cassette'), true)->id) {
            $unit_data['room_size'] = $unit_data['room_size_cassette'];
            unset($unit_data['room_size_cassette']);
        }

        if (!empty($unit->id)) {
            if (!empty($unit_data["refrigerant_type_other"])) {
                $name = $unit_data["refrigerant_type_other"];
                if (!($refrigerant_type = $this->refrigerant_type_model->get(array('name' => strtoupper($name))))) {
                    $this->refrigerant_type_model->add(array('name' => strtoupper($name)));
                }
                unset($unit_data["refrigerant_type_other"]);
            }

            if (!$this->unit_model->edit($unit->id, $unit_data)) {
                add_message('Could not update this unit!', 'error');
                redirect($redirect_url);
            }
        }

        add_message("Unit $unit->id has been successfully $action_word!", 'success');
        trigger_event('details_recorded', 'unit', $unit->id, false, 'miniant');
        trigger_event('record_unit_details', 'assignment', $assignment_id, false, 'miniant');

        redirect($this->workflow_manager->get_next_url());
    }

    public function get_form_data($unit_type_id, $assignment_id, $data, $order_type) {
        $evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
        $ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
        $trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
        $other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
        $mech_unit_id = $this->unit_model->get_type_id('Mechanical services');

        $refrigerant_type_other_id = $this->refrigerant_type_model->get(array('name' => 'Other'), true)->id;

        $form_data = array('required' => array(), 'fields' => array());

        $form_data['required'] = array('assignment_id' => "Assignment id", 'outdoor_unit_'.$assignment_id => 'Outdoor unit', 'area_serving_'.$assignment_id => 'Area serving');
        $form_data['area_serving_'.$assignment_id] = 'Area serving';


        switch ($unit_type_id) {
            case $evap_unit_id:
                $form_data['required']['brand_id_evap_'.$assignment_id] = 'Brand';
                $form_data['required']["electrical_$assignment_id"] = 'Mains power';
                $form_data['required']["filter_pad_type_$assignment_id"] = 'Pad type';
                $form_data['required']["pad_size_$assignment_id"] = 'Pad size';
                $form_data['required']["plenium_dropper_size_$assignment_id"] = 'Plenium dropper size';
                $form_data['required']["fan_motor_model_$assignment_id"] = 'Fan motor model';
                $form_data['required']["fan_motor_make_$assignment_id"] = 'Fan motor make';
                $form_data['required']["supply_air_diffuser_quantity_$assignment_id"] = 'Quantity of supply air diffusers';

                if ($data["filter_pad_type_$assignment_id"] == 'Celdek') {
                    $form_data['required']["water_distribution_type_groove_$assignment_id"] = 'Water distribution type groove';
                }

                break;
            case $ref_unit_id:
                $unitry_type_id = $data['unitry_type_id_'.$assignment_id];

                $cassette_id = $this->unitry_type_model->get(array('name' => 'R/C Cassette'), true)->id;
                $wallsplit_id = $this->unitry_type_model->get(array('name' => 'R/C Wall Split'), true)->id;
                $ducted_id = $this->unitry_type_model->get(array('name' => 'R/C Ducted'), true)->id;
                $under_id = $this->unitry_type_model->get(array('name' => 'Under Ceiling'), true)->id;
                $rac_id = $this->unitry_type_model->get(array('name' => 'RAC'), true)->id;

                $form_data['required']['unitry_type_id_'.$assignment_id] = 'Unitry type';
                $form_data['required']['brand_id_ref_'.$assignment_id] = 'Brand';
                $form_data['required']["refrigerant_type_id_$assignment_id"] = 'Refrigerant type';
                $form_data['required']['outdoor_model_'.$assignment_id] = 'Outdoor Model';
                $form_data['required']['outdoor_serial_number_'.$assignment_id] = 'Outdoor serial number';
                $form_data['required']['indoor_model_'.$assignment_id] = 'Indoor Model';

                if ($data["refrigerant_type_id_$assignment_id"] == $refrigerant_type_other_id) {
                    $form_data['required']["refrigerant_type_other_$assignment_id"] = 'Other Refrigerant type';
                }

                $form_data['required']["electrical_$assignment_id"] = 'Mains power';
                $form_data['required']["kilowatts_$assignment_id"] = 'Condensing cooling capacity';
                $form_data['required']["filter_size_$assignment_id"] = 'Filter size (LxHxW)';

                if (!empty($data["filter_size_$assignment_id"])) {
                    $form_data['required']["filter_type_$assignment_id"] = 'Filter type';

                    if (!empty($data["filter_type_$assignment_id"]) && $data["filter_type_$assignment_id"] != 'Media') {
                        $form_data['required']["filter_outside_frame_dimensions_$assignment_id"] = 'Filter outside frame dimensions (LxHxW)';
                    }
                }

                if ($unitry_type_id == $cassette_id) {
                    $form_data['required']['room_size_cassette_'.$assignment_id] = 'Room size (L x W x H)';
                }

                if (!in_array($unitry_type_id, array($cassette_id, $wallsplit_id, $rac_id, $under_id))) {
                    $this->form_validation->set_rules("supply_air_diffuser_quantity_$assignment_id", 'Quantity of supply air diffusers', 'trim|required|integer');
                    $this->form_validation->set_message('integer', 'You must enter a whole number (e.g., 2, 24) in the Quantity of supply air diffusers');
                }

                if ($order_type == 'Installation') {
                    $form_data['required']['indoor_serial_number_'.$assignment_id] = 'Indoor Serial number';
                    $form_data['required']['outdoor_serial_number_'.$assignment_id] = 'Outdoor Serial number';
                }

                break;
            case $trans_unit_id:
                unset($form_data['required']["brand_id_$assignment_id"]);
                $form_data['required']['brand_other_'.$assignment_id] = 'Specify brand';
                $form_data['required']["refrigerant_type_id_$assignment_id"] = 'Refrigerant type';

                if ($data["refrigerant_type_id_$assignment_id"] == $refrigerant_type_other_id) {
                    $form_data['required']["refrigerant_type_other_$assignment_id"] = 'Other Refrigerant type';
                }

                $form_data['required']["electrical_$assignment_id"] = 'Electric drive';
                $form_data['required']["kilowatts_$assignment_id"] = 'Condensing cooling capacity';
                $form_data['required']["outdoor_serial_number_$assignment_id"] = 'Outdoor Serial Number';
                $form_data['required']["indoor_serial_number_$assignment_id"] = 'Indoor Serial Number';
                $form_data['required']["thermostat_type_$assignment_id"] = 'Thermostat type';
                $form_data['required']["thermostat_brand_$assignment_id"] = 'Thermostat brand name';

                if ($data["thermostat_type_$assignment_id"] == 'Electric') {
                    $form_data['required']["thermostat_model_$assignment_id"] = 'Thermostat model';
                }

                $form_data['required']["vehicle_registration_$assignment_id"] = 'Car rego No';
                $form_data['required']["vehicle_type_$assignment_id"] = 'Car type';
                $form_data['required']["palette_size_$assignment_id"] = 'Palette size of truck';
                $form_data['required']["chassis_no_$assignment_id"] = 'Chassis No of vehicle';
                $form_data['required']["engine_no_$assignment_id"] = 'Engine No of vehicle (on compliance plate)';
                $form_data['required']["vehicle_year_$assignment_id"] = 'Year of vehicle';
                $form_data['required']["indoor_model_$assignment_id"] = 'Indoor Model';
                $form_data['required']["outdoor_model_$assignment_id"] = 'Outdoor Model';
                $form_data['required']["indoor_evaporator_model_$assignment_id"] = 'Indoor evaporator Model';
                $form_data['required']["indoor_evaporator_serial_$assignment_id"] = 'Indoor evaporator serial number';
                $form_data['required']["outdoor_condensing_unit_model_$assignment_id"] = 'Outdoor condensing unit Model';
                $form_data['required']["outdoor_condensing_unit_serial_$assignment_id"] = 'Outdoor condensing unit serial number';
                $form_data['required']["drive_type_$assignment_id"] = 'Type of drive';
                $form_data['required']["temperature_application_$assignment_id"] = 'Temperature application';
                $form_data['required']["aperture_size_$assignment_id"] = 'Temperature application';
                $form_data['required']["refrigerated_box_dimensions_$assignment_id"] = 'Internal dimensions of refrigerated box';
                $form_data['required']["insulation_thickness_$assignment_id"] = 'Insulation thickness';
                $form_data['required']["insulation_type_$assignment_id"] = 'Type of insulation';
                $form_data['required']["door_openings_aperture_size_$assignment_id"] = 'Aperture size of door openings';
                $form_data['required']["floor_thickness_$assignment_id"] = 'Thickness of floor';
                $form_data['required']["floor_type_$assignment_id"] = 'Type of floor';
                unset($form_data['required']["area_serving_$assignment_id"]);
                break;
            case $other_unit_id:
                $form_data['required']["indoor_evaporator_model_$assignment_id"] = 'Indoor evaporator Model';
                $form_data['required']["indoor_evaporator_qty_$assignment_id"] = 'Indoor evaporator qty';
                $form_data['required']["condensing_unit_brand_$assignment_id"] = 'Brand of condensing unit';
                $form_data['required']["outdoor_condenser_qty_$assignment_id"] = 'Outdoor condenser qty';
                $form_data['required']["refrigerant_type_id_$assignment_id"] = 'Refrigerant type';
                $form_data['required']['description_'.$assignment_id] = 'Unit description';

                if ($data["refrigerant_type_id_$assignment_id"] == $refrigerant_type_other_id) {
                    $form_data['required']["refrigerant_type_other_$assignment_id"] = 'Other Refrigerant type';
                }

                $form_data['required']["electrical_$assignment_id"] = 'Mains power';
                $form_data['required']["thermostat_type_$assignment_id"] = 'Thermostat type';
                $form_data['required']["thermostat_brand_$assignment_id"] = 'Thermostat brand name';

                if ($data["thermostat_type_$assignment_id"] == 'Electric') {
                    $form_data['required']["thermostat_model_$assignment_id"] = 'Thermostat model';
                }

                unset($form_data['required']['brand_id_'.$assignment_id]);
                $form_data['required']['brand_other_'.$assignment_id] = 'Manufacture brand';
                break;
            case $mech_unit_id:
                $form_data['required']["electrical_$assignment_id"] = 'Mains power';
                $form_data['required']['outdoor_model_'.$assignment_id] = 'Outdoor Model';
                $form_data['required']['outdoor_serial_number_'.$assignment_id] = 'Outdoor serial number';
                $form_data['required']['indoor_model_'.$assignment_id] = 'Indoor Model';
                $form_data['required']['apparatus_type_'.$assignment_id] = 'Type of apparatus';
                $form_data['required']['description_'.$assignment_id] = 'Unit description';
                $form_data['required']['outdoor_fan_motor_serial_'.$assignment_id] = 'Outdoor fan motor serial number';
                $form_data['required']['indoor_fan_motor_serial_'.$assignment_id] = 'Indoor fan motor serial number';
                $form_data['required']['outdoor_fan_motor_model_'.$assignment_id] = 'Outdoor fan motor model';
                $form_data['required']['indoor_fan_motor_model_'.$assignment_id] = 'Indoor fan motor model';
                $form_data['required']['sheetmetal_duct_size_'.$assignment_id] = 'Sheetmetal duct size';
                $form_data['required']['diffusion_grille_face_size_'.$assignment_id] = 'Diffusion grille face size';
                $form_data['required']['diffusion_grille_face_type_'.$assignment_id] = 'Diffusion grille face metal or plastic';
                $form_data['required']['diffusion_cushion_head_size_'.$assignment_id] = 'Diffusion cushion head size';
                $form_data['required']['diffusion_cushion_head_type_'.$assignment_id] = 'Diffusion cushion head metal or plastic';
                $form_data['required']['flexible_duct_size_'.$assignment_id] = 'Flexible duct size';
                $form_data['required']['fire_damper_size_'.$assignment_id] = 'Fire damper size';
                $form_data['required']['vsd_'.$assignment_id] = 'Variable Speed Drive (VSD)';
                $form_data['required']['vsd_brand_'.$assignment_id] = 'Brand/Make of VSD';

                if ($data["vsd_$assignment_id"] == 'Yes') {
                    $form_data['required']['vsd_model_'.$assignment_id] = 'Model of VSD';
                    $form_data['required']['vsd_serial_'.$assignment_id] = 'Serial number of VSD';
                }

                $form_data['required']['dropper_size_'.$assignment_id] = 'Dropper size';

                unset($form_data['required']['brand_id_'.$assignment_id]);
                $form_data['required']['brand_other_'.$assignment_id] = 'Manufacture brand';
                break;
            default:
        }

        foreach ($data as $field => $value) {
            if (in_array($field, array('unit_id', 'assignment_id', 'button'))) {
                continue;
            }

            if (preg_match('/([a-z_]*)_'.$assignment_id.'/', $field, $matches)) {
                $form_data['fields'][$matches[1]] = $value;
            } else {
                $form_data['fields'][$field] = $value;
            }
        }

        return $form_data;
    }
}
