<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
class Job_site_model extends MY_Model {
    use has_statuses;

    public $table = 'building_job_sites';

    public function __construct() {
        parent::__construct();
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            $statuses = array();

            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {

                $db_records[$key]['job_site_street'] = $this->get_address($row['job_site_id']);
                $statuses = $this->job_site_model->get_statuses($row['job_site_id'], false);

                $allstatuses = $this->status_model->get_for_document_type('job_site');

                if (has_capability('building:editstatuses')) {
                    $db_records[$key]['statuses'] = '<select data-nonSelectedText="No Statuses" data-callback="building/job_sites/update_statuses/'.$row['job_site_id'].'" ' .
                        'data-job_site_id="'.$row['job_site_id'].'" class="multiselect" multiple="multiple">';

                    foreach ($allstatuses as $status) {
                        $selected = '';
                        if (!empty($statuses)) {
                            foreach ($statuses as $job_site_stasus) {
                                if ($job_site_stasus->status_id == $status->id) {
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
            }
        };
    }

    public function delete($id_or_fields) {

        if (is_array($id_or_fields)) {
            $job_sites = $this->get($id_or_fields);
        } else {
            $job_sites = array($this->get($id_or_fields));
        }

        $result = parent::delete($id_or_fields);

        foreach ($job_sites as $job_site) {
            $bookings = $this->booking_model->get(array('job_site_id' => $job_site->id));
            foreach ($bookings as $booking) {
                $this->booking_model->delete($booking->id);
            }
        }

        $this->db->query("DELETE FROM `building_bookings` WHERE job_site_id NOT IN ( SELECT id FROM building_job_sites)");

        return $result;
    }

    public function get_address($job_site_id) {
        return $this->address_model->get_formatted_address($this->job_site_model->get($job_site_id));
    }
}
