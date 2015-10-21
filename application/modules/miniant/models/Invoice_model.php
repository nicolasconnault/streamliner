<?php
class Invoice_Model extends MY_Model {
    use has_statuses;
    public $table = 'miniant_invoices';
}
