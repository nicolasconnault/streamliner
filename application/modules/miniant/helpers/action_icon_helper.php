<?php
class DatagridActionIconInvoices extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Edit invoices' : $label;
        return '<a class="invoices" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/invoices/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-money"></i> '.$formatted_label
                 . '</a>';
    }
}

class DatagridActionIconJobs extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View jobs done at this site' : $label;
        return '<a class="jobs" href="'.base_url().$this->controller_folder.'jobs/browse_by_address/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-wrench"></i> '.$formatted_label
                 . '</a>';
    }
}

class DatagridActionIconContractJobs extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View jobs done under this contract' : $label;
        return '<a class="contract-jobs" href="'.base_url().$this->controller_folder.'orders/order/index/html/0/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-wrench"></i> '.$formatted_label
                 . '</a>';
    }
}

class DatagridActionIconDocuments extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View documents sent from this SQ' : $label;
        return '<a class="documents" href="'.base_url().$this->controller_folder.'documents/index/html/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-file-pdf-o"></i> '.$formatted_label
                 . '</a>';
    }
}

class DatagridActionIconUnits extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View units on this site' : $label;
        return '<a class="units" href="'.base_url().$this->controller_folder.'units/browse_by_address/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-unit"></i> '.$formatted_label
                 . '</a>';
    }
}

class DatagridActionIconReview extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Review this assignment' : $label;
        return '<a class="review" href="'.base_url().'miniant/stages/assignment_details/index/%d" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-eye"></i> '.$formatted_label
                 . '</a>';
    }
}

class DatagridActionIconAssignments extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View assignments for this Job' : $label;
        return '<a class="assignments" href="'.base_url().$this->controller_folder.'assignments/index/html/%d" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-unit"></i> '.$formatted_label
                 . '</a>';
    }
}
