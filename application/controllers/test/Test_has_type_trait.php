<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_has_type_trait extends Toast {

	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
        $this->_ci =& get_instance();
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

    public function test_get_type_id() {
        $this->load_types();
        $this->load_orders();

        $this->_assert_equals(1, $this->_ci->contact_model->get_type_id('Billing'));
        $this->_assert_equals(3, $this->_ci->contact_model->get_type_id('Site'));
	}

    public function test_get_type_string() {
        $this->load_types();
        $this->load_orders();

        $this->_assert_equals('Billing', $this->_ci->contact_model->get_type_string(1));
        $this->_assert_equals('Site', $this->_ci->contact_model->get_type_string(3));
    }

    public function test_get_types_dropdown() {
        $this->load_all();
        $dropdowns = $this->_ci->contact_model->get_types_dropdown();

        $this->_assert_equals(7, count($dropdowns));
        $this->_assert_equals('Billing', $dropdowns[1]);
        $this->_assert_equals('Site', $dropdowns[3]);
    }
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
