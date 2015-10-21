<?php
require_once(APPPATH.'libraries/Widget.php');

class Widget_building_stats extends Widget {
    public $id = 'building_stats';
    public $label = 'Building Manager Statistics (sample)';
    public $width = 6;
    public $row = 2;

    public function get_html() {
        $ci = get_instance();

        $html = '
            <table class="table" id="building_stats"><tbody>
                <tr><th>New bookings in last 30 days</th><td>43</td></tr>
                <tr><th>New jobs sites in last 30 days</th><td>12</td></tr>
                <tr><th>Archived job sites in last 30 days</th><td>14</td></tr>
        ';

        $html .= '</tbody></table>';
        return $html;
    }
}
