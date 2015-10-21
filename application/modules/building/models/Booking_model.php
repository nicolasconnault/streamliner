<?php
require_once(APPPATH.'/core/has_statuses_trait.php');
class Booking_model extends MY_Model {
    use has_statuses;

    public $table = 'building_bookings';

    public function __construct() {
        parent::__construct();
    }

    public function get_for_schedule($start, $end, $job_site_id=null, $confirmed) {
        $this->db->where($this->table.".booking_date BETWEEN $start AND $end", null, false);
        $this->db->where('confirmed', $confirmed);
        $this->db->order_by($this->table.'.booking_date', 'ASC');

        if (!empty($job_site_id)) {
            $this->db->where($this->table.'.job_site_id', $job_site_id);
        }

        $bookings = $this->get();

        if (empty($bookings)) {
            return array();
        }

        // Add address info for Overview schedule
        foreach ($bookings as $booking) {

        }

        return $bookings;
    }

    public function get_recipients($booking_id) {
        $booking_recipients = $this->booking_recipient_model->get(array('booking_id' => $booking_id));
        $recipients = array();
        foreach ($booking_recipients as $booking_recipient) {
            $recipients[] = $booking_recipient->user_id;
        }
        return $recipients;
    }

    public function have_recipients_changed($old_recipients, $new_recipients) {
        if (count($old_recipients) != count($new_recipients)) {
            return false;
        }

        foreach ($old_recipients as $old_recipient) {
            if (!in_array($old_recipient, $new_recipients)) {
                return false;
            }
        }

        foreach ($new_recipients as $new_recipient) {
            if (!in_array($new_recipient, $old_recipients)) {
                return false;
            }
        }

        return true;
    }

    public function delete($id_or_fields) {

        if (is_array($id_or_fields)) {
            $bookings = $this->get($id_or_fields);
        } else {
            $bookings = array($this->get($id_or_fields));
        }

        $result = parent::delete($id_or_fields);

        foreach ($bookings as $booking) {
            $recipients = $this->booking_recipient_model->get(array('booking_id' => $booking->id));
            foreach ($recipients as $recipient) {
                $this->booking_recipient_model->delete($recipient->id);
            }
        }

        $this->db->query("DELETE FROM `building_booking_recipients` WHERE booking_id NOT IN ( SELECT id FROM building_bookings)");

        return $result;
    }

    public function get_latest_bookings($number=10) {
        $this->db->limit($number);
        $this->db->join('building_tradesmen', 'building_tradesmen.id = building_bookings.tradesman_id', 'LEFT OUTER');
        $this->db->join('types', 'types.id = building_bookings.tradesman_type_id', 'LEFT OUTER');
        $select_fields = array(
            'building_bookings.*',
            'building_tradesmen.name AS tradesman_name',
            'building_tradesmen.mobile AS tradesman_mobile',
            'building_tradesmen.email AS tradesman_email',
            'types.name AS trade');

        $bookings = $this->get(null, false, 'building_bookings.creation_date DESC', $select_fields);
        if (empty($bookings)) {
            return null;
        }

        foreach ($bookings as $id => $booking) {
            $bookings[$id]->job_site_address = $this->job_site_model->get_address($booking->job_site_id);
        }

        return $bookings;
    }
}
