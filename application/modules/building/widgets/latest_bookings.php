<?php
require_once(APPPATH.'libraries/Widget.php');

class Widget_building_latest_bookings extends Widget {
    public $id = 'building_latest_bookings';
    public $label = 'Latest bookings';
    public $width = 12;
    public $row = 1;

    public function get_html() {
        $ci = get_instance();
        $bookings = $ci->booking_model->get_latest_bookings(10);
        if (empty($bookings)) {
            return "<p>There are no bookings yet.</p>";
        }

        $html = '
            <table class="table table-striped table-hover" id="latest_bookings">
                <thead>
                    <tr><th>Booking Date</th><th>Message</th><th>Job site</th><th>Trade</th><th>Tradesman</th><th>Confirmed</th></tr>
                </thead>
                <tbody>
        ';
        foreach ($bookings as $booking) {
            $confirmed = ($booking->confirmed) ? 'Yes' : 'No';
            $html .= '
            <tr>
                <td>'.unix_to_human($booking->booking_date).'</td>
                <td>'.$booking->message.'</td>
                <td>'.$booking->job_site_address.'</td>
                <td>'.$booking->trade.'</td>
                <td>'.$booking->tradesman_name.'</td>
                <td>'.$confirmed.'</td>
            </tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
