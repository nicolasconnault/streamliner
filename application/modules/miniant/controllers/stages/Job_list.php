<?php
require_once(APPPATH.'/modules/miniant/controllers/stages/Stage.php');

class Job_list extends Stage_controller {
    public function index() {
        $this->config->set_item('replacer', array('miniant' => null, 'job_list' => 'Current jobs'));
        $this->config->set_item('exclude', array('index'));

        $title_options = array('title' => 'Task schedule',
                               'help' => 'Keep track of your scheduled tasks using this calendar',
                               'icons' => array());

        $pageDetails = array('title' => 'Task schedule',
                             'title_options' => $title_options,
                             'content_view' => 'stages/job_list',
                             'csstoload' => array('fullcalendar'),
                             'feature_type' => 'Custom Feature',
                             'module' => 'miniant',
                             'jstoload' => array(
                                 'fullcalendar/fullcalendar',
                                 'stages/job_list',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);
    }

}
