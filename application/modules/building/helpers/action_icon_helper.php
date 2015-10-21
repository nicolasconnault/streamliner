<?php
class DatagridActionIconCalendar extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View bookings' : $label;
        return '<a class="action-icon fa" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/calendar/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-calendar"></i>'
                 . '</a>';
    }
}
class DatagridActionIconAttachments extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View drawings for this job site' : $label;
        return '<a class="action-icon fa" href="'.base_url().$this->controller_folder.'job_site_attachments/index/html/%d/" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-file-pdf-o"></i>'
                 . '</a>';
    }
}
class DatagridActionIconDownload extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Download this drawing' : $label;
        return '<a class="action-icon fa" href="'.base_url().$this->controller_folder.'job_site_attachments/download/%d/" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-file-pdf-o"></i>'
                 . '</a>';
    }
}
