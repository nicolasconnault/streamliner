<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_event_helper extends Toast {

	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
        $this->_ci =& get_instance();
        $this->_ci->load->model('order_model');
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

	public function test_included() {

	}

    public function test_trigger_event() {
        $this->load_orders();
        $this->load_events();
        $this->load_statuses();
        $this->load_status_events();
        $this->load_document_statuses();

        $sr_id = $this->_ci->order_model->add(array());
        trigger_event('create_order', 'order', $sr_id);

        $sr_statuses = $this->_ci->order_model->get_statuses($sr_id);
        $this->_assert_equals(1, count($sr_statuses));
        $this->_assert_equals('DRAFT', reset($sr_statuses));

        $sr_statuses = $this->_ci->order_model->get_statuses($sr_id, false);
        $this->_assert_equals('DRAFT', reset($sr_statuses)->name);

        trigger_event('allocate_to_technician', 'order', $sr_id);
        $sr_statuses = $this->_ci->order_model->get_statuses($sr_id);
        $this->_assert_equals(1, count($sr_statuses));
        $this->_assert_equals('ALLOCATED', reset($sr_statuses));
    }
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
