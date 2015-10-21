<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_address_model extends Toast {

	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
		$this->_ci =& get_instance();

		$this->_ci->load->model('company_model');
		$this->_ci->load->model('company_address_model');
		$this->_ci->load->model('address_model');

        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

	public function test_included() {
		$this->_assert_true(class_exists('address_model'));
		$this->_assert_true(class_exists('company_address_model'));
	}

    public function test_get_units() {
        $this->load_units();
        $this->load_orders();
        $this->load_addresses();

        $units = $this->_ci->address_model->get_units(3);
        $this->_assert_equals(3, count($units));

        $units = $this->_ci->address_model->get_units(4);
        $this->_assert_equals(1, count($units));
    }
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
