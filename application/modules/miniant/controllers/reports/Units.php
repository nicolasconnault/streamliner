<?php
class Units extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('units' => array('index|Units')));
        $this->config->set_item('exclude', array('index', 'browse'));
    }

    public function browse_by_address($address_id) {
        $units = $this->address_model->get_units($address_id);
        foreach ($units as $key => $unit) {
            $units[$key] = (object) $this->unit_model->get_values($unit->id);
        }
        $formatted_address = $this->address_model->get_formatted_address($address_id);

        $title = 'Units at '.$formatted_address;
        $title_options = array('title' => $title,
                               'help' => 'These are all the units that have had work done in the past at '.$formatted_address. ". If you notice some duplicates, you can merge them here.",
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'reports/units',
                             'units' => $units,
                             'address_id' => $address_id,
                             'feature_type' => 'Custom feature',
                             'jstoload' => array(
                                 'jquery/datatables/media/js/jquery.dataTables',
                                 'datagrid_paging_bootstrap',
                                 'datatable_pagination',
                                 'application/reports/units'
                                 ),
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function merge() {
        $unit_ids = $this->input->post('unit_id');
        $address_id = $this->input->post('address_id');

        $formatted_address = $this->address_model->get_formatted_address($address_id);

        if (empty($unit_ids) || count($unit_ids) < 2) {
            add_message('You must select at least 2 units for merging!', 'danger');
            redirect(base_url().'miniant/reports/units/browse_by_address/'.$address_id);
        }

        $this->db->where_in('id', $unit_ids);
        $units_array = $this->unit_model->get(null, false, null, array(), true);
        $units = array();

        foreach ($units_array as $key => $unit) {
            $units[$unit->id] = $unit;
        }

        $unit_fields = array();
        $unit_dropdowns = array();

        foreach ($units as $unit) {
            $unit_id = $unit->id;

            foreach ((array) $units[$unit_id] as $key => $value) {
                $unit_fields[$key] = ucfirst(str_replace('_', ' ', str_replace('_id', '', $key)));
            }

            if (empty($unit_dropdowns[$unit_id])) {
                $unit_dropdowns[$unit_id] = array();
            }
            $unit_dropdowns[$unit_id]['brand_id'] = $this->brand_model->get_dropdown_by_unit_type_id($units[$unit_id]->unit_type_id);
            $unit_dropdowns[$unit_id]['refrigerant_type_id'] = $this->refrigerant_type_model->get_dropdown('name');
            $unit_dropdowns[$unit_id]['electrical'] = array('Single phase' => 'Single phase', 'Three-phase' => 'Three-phase');
            $unit_dropdowns[$unit_id]['thermostat_type'] = array('Electric' => 'Electric', 'Mechanical' => 'Mechanical');
            $unit_dropdowns[$unit_id]['thermostat_model'] = array('Same as brand' => 'Same as brand', 'Universal' => 'Universal');
            $unit_dropdowns[$unit_id]['filter_pad_type'] = array('Celdek' => 'Celdek', 'Aspen' => 'Aspen');
            $unit_dropdowns[$unit_id]['water_distribution_type_groove'] = array('Front' => 'Front', 'Centre' => 'Centre', 'Back' => 'Back');
            $unit_dropdowns[$unit_id]['filter_type'] = array('Media' => 'Media', 'Disposable' => 'Disposable', 'Metal' => 'Metal');
            $unit_dropdowns[$unit_id]['vehicle_type'] = array('Truck' => 'Truck', 'Van' => 'Van');
            $unit_dropdowns[$unit_id]['palette_size'] = range(1,20);
        }

        unset($unit_fields['status']);
        unset($unit_fields['creation_date']);
        unset($unit_fields['revision_date']);
        unset($unit_fields['id']);
        unset($unit_fields['tenancy_id']);
        unset($unit_fields['site_address_id']);
        unset($unit_fields['unitry_type_id']);
        unset($unit_fields['unit_type_id']);
        unset($unit_fields['thermostat_photo']);
        unset($unit_fields['filter_outside_frame_photo']);
        unset($unit_fields['mechanical_thermostat_photo']);

        $title = 'Merging '.count($units).' units located at '.$formatted_address;
        $title_options = array('title' => $title,
                               'help' => 'Compare the values of these units, and use the left-most column as the final, merged unit. All other data in other columns will be discarded',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'reports/units_merge',
                             'units' => $units,
                             'unit_fields' => $unit_fields,
                             'unit_dropdowns' => $unit_dropdowns,
                             'address_id' => $address_id,
                             'jstoload' => array(
                                 'application/reports/units_merge'
                                 ),
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    // TODO Add ability to merge photos
    public function process_merge() {

        $post_data = $this->input->post();
        $address_id = $post_data['address_id'];
        $unit_id = $post_data['unit_id'];
        $unit_ids_to_delete = $post_data['unit_ids_to_delete'];
        unset($post_data['address_id']);
        unset($post_data['unit_id']);
        unset($post_data['unit_ids_to_delete']);

        if (empty($post_data['brand_id'])) {
            $post_data['brand_id'] = null;
        }
        if (empty($post_data['refrigerant_type_id'])) {
            $post_data['refrigerant_type_id'] = null;
        }

        $this->unit_model->edit($unit_id, $post_data);

        // Associate all records linked to deleted units with the remaining, merged unit
        foreach ($unit_ids_to_delete as $unit_id_to_delete) {
            $assignments = $this->assignment_model->get(array('unit_id' => $unit_id_to_delete));
            if (!empty($assignments)) {
                foreach ($assignments as $assignment) {
                    $this->assignment_model->edit($assignment->id, array('unit_id' => $unit_id));
                }
            }

            $installation_tasks = $this->installation_task_model->get(array('unit_id' => $unit_id_to_delete));
            if (!empty($installation_tasks)) {
                foreach ($installation_tasks as $installation_task) {
                    $this->installation_task_model->edit($installation_task->id, array('unit_id' => $unit_id));
                }
            }

            $this->unit_model->delete($unit_id_to_delete);
        }
        add_message('The units were merged successfully.');
        redirect(base_url().'miniant/reports/units/browse_by_address/'.$address_id);
    }

}
