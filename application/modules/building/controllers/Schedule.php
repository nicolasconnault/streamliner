<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */

class Schedule extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('building' => null, 'job_sites' => array('/job_sites|Job sites')));
        $this->config->set_item('exclude', array('index', 'browse'));

        $this->config->set_item('exclude_segment', array());
    }

    public function index() {
        $this->config->set_item('replacer', array('building' => null, 'schedule' => array('/building/schedule/index|Overview schedule')));

        $title = 'Overview schedule';

        $title_options = array('title' => $title,
                               'help' => 'Keep track of all scheduled jobs with this calendar',
                               'title_icon' => 'calendar',
                               'icons' => array());
        $calendar_title_options = array('title' => 'Calendar',
                               'help' => '',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'calendar_title_options' => $calendar_title_options,
                             'content_view' => 'schedule/calendar',
                             'csstoload' => array('fullcalendar'),
                             'feature_type' => 'Custom Feature',
                             'module' => 'building',
                             'csstoload' => array('fullcalendar'),
                             'jstoload' => array(
                                 'moment',
                                 'fullcalendar',
                                 'fullcalendar.ipad',
                                 'schedule_calendar',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function get_bookings() {
        $start = human_to_unix($this->input->post('start'), true);
        $end = human_to_unix($this->input->post('end'), true);
        $confirmed = $this->input->post('confirmed');

        $bookings = array();

        $bookings = $this->booking_model->get_for_schedule($start, $end, null, $confirmed);

        $events = array();

        foreach ($bookings as $booking) {
            $recipients = $this->booking_model->get_recipients($booking->id);
            $staff = $this->user_model->get_dropdown_full_name(false);
            $job_site = $this->job_site_model->get($booking->job_site_id);

            $event = new stdClass();
            $event->id = "$booking->id";
            $event->title = $this->load->view('job_site/booking_title', compact('booking', 'confirmed', 'job_site'), true);

            $event->allDay = true;
            $event->start = unix_to_human($booking->booking_date, '%Y-%m-%d');
            $event->end = unix_to_human($booking->booking_date + 60, '%Y-%m-%d');
            $event->job_site_id = $booking->job_site_id;
            $event->confirmed = $confirmed;
            $events[] = $event;
        }

        echo json_encode($events);
    }
}
