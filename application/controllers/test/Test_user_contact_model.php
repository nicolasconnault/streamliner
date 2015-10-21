<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_user_contact_model extends Toast {
	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
		$this->_ci =& get_instance();

		$this->_ci->load->model('users/user_model');
		$this->_ci->load->model('users/user_contact_model');

        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

	public function test_included() {
		$this->_assert_True(class_exists('user_model'));
		$this->_assert_True(class_exists('user_contact_model'));
	}

    function test_get_by_user_id_all_types() {
        $this->load_users();
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();

        $contacts = $this->_ci->user_contact_model->get_by_user_id(2, null, false);
        $this->_assert_equals(2, count($contacts));
        $this->_assert_equals(USERS_CONTACT_TYPE_EMAIL, $contacts[0]->type);
        $this->_assert_equals(USERS_CONTACT_TYPE_PHONE, $contacts[1]->type);

        $this->_assert_equals(array(), $this->_ci->user_contact_model->get_by_user_id(5));
    }

    function test_get_by_user_id_only_defaults() {
        $this->load_users();
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();

        $contacts = $this->_ci->user_contact_model->get_by_user_id(1, null, false, false, true);
        $this->_assert_True(is_array($contacts));
        $this->_assert_equals(USERS_CONTACT_TYPE_EMAIL, $contacts[0]->type);
    }

    function test_get_by_user_id_only_one_type() {
        $this->load_users();
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();

        $contacts = $this->_ci->user_contact_model->get_by_user_id(2, USERS_CONTACT_TYPE_PHONE, false);
        $this->_assert_True(is_array($contacts));
        $this->_assert_equals(USERS_CONTACT_TYPE_PHONE, $contacts[0]->type);
    }


	function test_delete_contact() {
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();
		$this->_assert_True($this->_ci->user_contact_model->delete(1));
        $contact2 = $this->_ci->user_contact_model->get(2);
        $this->_assert_equals(1, $contact2->default_choice);
        $contact3 = $this->_ci->user_contact_model->get(7);
        $this->_assert_equals(0, $contact3->default_choice);

	}

    function test_delete_contact_with_multiple_defaults() {
        // Explicitly set emails 1 and 3 to default: the second one should not become default when 1 is deleted
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();
        $this->_ci->user_contact_model->edit(7, array('default_choice' => 1));
		$this->_assert_True($this->_ci->user_contact_model->delete(1));
        $contact2 = $this->_ci->user_contact_model->get(2);
        $this->_assert_equals(0, $contact2->default_choice);
        $contact3 = $this->_ci->user_contact_model->get(7);
        $this->_assert_equals(1, $contact3->default_choice);

        // Now delete email 3, email 2 should become default
		$this->_assert_True($this->_ci->user_contact_model->delete(7));
        $contact2 = $this->_ci->user_contact_model->get(2);
        $this->_assert_equals(1, $contact2->default_choice);

    }

    function test_set_as_default() {
        $this->load_users();
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();

        // Set second email address as default and the other two as not default
        $this->_assert_True($this->_ci->user_contact_model->set_as_default(2, false));
        $contact1 = $this->_ci->user_contact_model->get(1);
        $this->_assert_equals(0, $contact1->default_choice);
        $contact2 = $this->_ci->user_contact_model->get(2);
        $this->_assert_equals(1, $contact2->default_choice);
        $contact3 = $this->_ci->user_contact_model->get(7);
        $this->_assert_equals(0, $contact3->default_choice);

        // Attempt to set email 3 as default using soft option: should fail, because email2 is already default
        $this->_assert_False($this->_ci->user_contact_model->set_as_default(7, true));
        $contact1 = $this->_ci->user_contact_model->get(1);
        $this->_assert_equals(0, $contact1->default_choice);
        $contact2 = $this->_ci->user_contact_model->get(2);
        $this->_assert_equals(1, $contact2->default_choice);
        $contact3 = $this->_ci->user_contact_model->get(7);
        $this->_assert_equals(0, $contact3->default_choice);

        // Explictly set email 2 as non default: no email address should be default for user 1 now
        $this->_ci->user_contact_model->edit(2, array('default_choice' => 0));
        // Attempt to set email 3 as default using soft option: should succeed, because no other emails are default
        $this->_assert_True($this->_ci->user_contact_model->set_as_default(7, true));
        $contact1 = $this->_ci->user_contact_model->get(1);
        $this->_assert_equals(0, $contact1->default_choice);
        $contact2 = $this->_ci->user_contact_model->get(2);
        $this->_assert_equals(0, $contact2->default_choice);
        $contact3 = $this->_ci->user_contact_model->get(7);
        $this->_assert_equals(1, $contact3->default_choice);

        // Try setting a contact that doesn't exist as default
        $this->_assert_False($this->_ci->user_contact_model->set_as_default(33, false));
    }

	function test_add_contact() {
		$this->_ci->db->truncate('user_contacts');
		$this->_ci->db->flush_cache();

		$insert_data = array(
                'user_id' => 1,
			    'type' => USERS_CONTACT_TYPE_EMAIL,
                'contact' => 'email54@test.com');
		$contact_id = $this->_ci->user_contact_model->add($insert_data);

		$this->_assert_equals($contact_id, 1, 'contact id = 1');
	}

	function test_edit_contact() {
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();
		$insert_data = array(
			    'contact' => 'impossible@test.com',
			);
		$contact = $this->_ci->user_contact_model->edit(1, $insert_data);
		$this->_assert_True($contact);
        $contact = $this->_ci->user_contact_model->get(array('id' => 1), true);

        $this->_assert_equals($contact->contact, 'impossible@test.com');
	}
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
