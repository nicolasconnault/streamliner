<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_has_statuses_trait extends Toast {

	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
        $this->_ci =& get_instance();
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

    public function test_get_statuses() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();
        $statuses = $this->_ci->order_model->get_statuses(1);

        $this->_assert_equals(2, count($statuses));
	}

    public function test_has_statuses() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();

        $this->_assert_true($this->_ci->order_model->has_statuses(1, array('STARTED')));
        $this->_assert_false($this->_ci->order_model->has_statuses(1, array('DRAFT')));
        $this->_assert_true($this->_ci->order_model->has_statuses(1, array('STARTED', 'DRAFT')));
        $this->_assert_false($this->_ci->order_model->has_statuses(1, array('STARTED', 'DRAFT'), 'AND'));
        $this->_assert_true($this->_ci->order_model->has_statuses(1, array('STARTED', 'ON HOLD'), 'AND'));
    }

    public function test_has_not_statuses() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();

        $this->_assert_true($this->_ci->order_model->has_not_statuses(1, array('DRAFT')));
        $this->_assert_true($this->_ci->order_model->has_not_statuses(1, array('DRAFT', 'SENT')));
        $this->_assert_false($this->_ci->order_model->has_not_statuses(1, array('DRAFT', 'STARTED')));
        $this->_assert_false($this->_ci->order_model->has_not_statuses(1, array('STARTED')));
    }

    public function test_check_statuses() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();

        $this->_assert_true($this->_ci->order_model->check_statuses(1, array('STARTED')));
        $this->_assert_true($this->_ci->order_model->check_statuses(1, array('STARTED', 'DRAFT')));
        $this->_assert_false($this->_ci->order_model->check_statuses(1, array('STARTED', 'DRAFT'), 'AND'));
        $this->_assert_true($this->_ci->order_model->check_statuses(1, array('STARTED', 'ON HOLD'), 'AND', array('DRAFT')));
        $this->_assert_true($this->_ci->order_model->check_statuses(1, array(), 'OR', array('DRAFT')));
        $this->_assert_false($this->_ci->order_model->check_statuses(1, array(), 'OR', array('STARTED')));
    }

    public function test_set_statuses() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();

        $this->_ci->order_model->set_statuses(1, array(18, 19));
        $this->_assert_true($this->_ci->order_model->check_statuses(1, array('COMPLETE', 'ARCHIVED'), 'AND'));
        $this->_assert_false($this->_ci->order_model->check_statuses(1, array('STARTED', 'ON HOLD')));
    }

    public function test_set_status() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();

        $this->_ci->order_model->set_status(1, 'COMPLETE');
        $this->_assert_true($this->_ci->order_model->has_statuses(1, array('COMPLETE')));
        $statuses = $this->_ci->order_model->get_statuses(1);
        $this->_assert_equals(3, count($statuses));

        $this->_ci->order_model->set_status(1, 'STARTED', false);
        $this->_assert_true($this->_ci->order_model->has_not_statuses(1, array('FALSE')));
        $statuses = $this->_ci->order_model->get_statuses(1);
        $this->_assert_equals(2, count($statuses));
    }

    public function test_get_status_string() {
        $this->load_statuses();
        $this->load_document_statuses();
        $this->load_orders();

        $this->_assert_equals('11,16', $this->_ci->order_model->get_status_string(1));
    }


}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
