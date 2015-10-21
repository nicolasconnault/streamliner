<?php
class Download extends MY_Controller {
    public function index() {
        if (has_capability('site:doanything')) {
            // Create the archive
            chdir('/srv/www/miniant');
            $result = exec('hg archive miniant.tar.bz2', $output);
            add_message($result);
            $data = file_get_contents('/srv/www/miniant/miniant.tar.bz2');
            $this->load->helper('download');
            force_download('miniant.tar.bz2', $data);
        }
    }
}
