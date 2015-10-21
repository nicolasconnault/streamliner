<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_users_model extends Toast {

	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
		$this->_ci =& get_instance();

		$this->_ci->load->model('users/user_model');
		$this->_ci->load->model('users/user_contact_model');
		$this->_ci->load->model('users/role_model');
		$this->_ci->load->model('users/capability_model');

        $this->_ci->db = $this->_ci->load->database('unittest', true);
        $this->reset_tables(array('users', 'roles', 'capabilities', 'users_roles', 'roles_capabilities', 'user_contacts'));
        $this->load_users();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->load_users_roles();
        $this->load_roles();
    }

	function _post() {}

	public function test_included() {
		$this->_assert_true(class_exists('user_model'), __LINE__);
	}

	function test_delete_user() {
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();
		$user = $this->_ci->user_model->delete(1);
		$this->_assert_true($user, __LINE__);

        // Check that contacts, options, addresses and role assignments have been deleted
        $contacts = $this->_ci->user_contact_model->get(array('user_id' => 1));
        $roles = $this->_ci->user_model->get_roles(1);

        $this->_assert_true(empty($contacts), __LINE__);
        $this->_assert_true(empty($roles), __LINE__);
	}

    function test_get_user_by_id() {
		$this->_ci->db->flush_cache();
		$user = $this->_ci->user_model->get_unique(1);
		$this->_assert_equals($user->id, 1, __LINE__);
	}

	function test_get_user_by_username() {
		$this->_ci->db->flush_cache();
		$user = $this->_ci->user_model->get_unique('ops');
		$this->_assert_equals($user->id, 1, __LINE__);
	}

    function test_get_user_by_email() {
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();
		$user = $this->_ci->user_model->get_unique('email1@test.com');
		$this->_assert_equals($user->id, 1, __LINE__);
	}


    public function test_select_by_unique_id() {
        // TODO implement test
    }

    public function test_get_name() {
		$this->_ci->db->flush_cache();
		$user = $this->_ci->user_model->get(1);
        $this->_assert_equals($this->_ci->user_model->get_name($user), 'Ops Manager', __LINE__);
    }

    public function test_get_capabilities() {
		$this->_ci->db->flush_cache();
        $caps = $this->_ci->user_model->get_capabilities(2);
    }

    public function test_filter_by_role() {
        $this->load_roles();
		$this->_ci->db->flush_cache();

        $this->_ci->user_model->filter_by_role(2, $this->_ci);
        $users = $this->_ci->user_model->get();
        $this->_assert_equals(2, count($users), __LINE__);
        $this->_assert_equals(6, $users[0]->id, __LINE__);
        $this->_assert_equals(4, $users[1]->id, __LINE__);
    }

    public function test_get_roles() {
		$this->_ci->db->flush_cache();

        $roles = $this->_ci->user_model->get_roles(1);
        $this->_assert_equals(2, count($roles), __LINE__);
        $this->_assert_equals('Director', $roles[0]->name, __LINE__);
        $this->_assert_equals('Operations Manager', $roles[1]->name, __LINE__);

        $roles = $this->_ci->user_model->get_roles(2);
        $this->_assert_true(is_array($roles), __LINE__);
        $this->_assert_equals('Technician', $roles[0]->name, __LINE__);
    }

    public function test_assign_role() {
		$this->_ci->db->flush_cache();

        $this->_assert_true($this->_ci->user_model->assign_role(2, 1), __LINE__);
        // Shouldn't be able to re-assign same role to same user
        $this->_assert_false($this->_ci->user_model->assign_role(2, 1), __LINE__);
    }

    public function test_unassign_role() {
		$this->_ci->db->flush_cache();
        // TODO Implement test
    }

    public function test_has_roles() {
		$this->_ci->db->flush_cache();

        $this->_assert_true($this->_ci->user_model->has_roles(1, array(1, 2), 'OR'), __LINE__);
        $this->_assert_true($this->_ci->user_model->has_roles(1, array(1, 3, 4), 'OR'), __LINE__);
        $this->_assert_true($this->_ci->user_model->has_roles(1, array(3), 'AND'), __LINE__);
    }

    public function test_get_users_by_capability() {
		$this->_ci->db->flush_cache();

        $users = $this->_ci->user_model->get_users_by_capability('orders:vieworders');

        $this->_assert_equals(4, count($users), __LINE__);
        $this->_assert_equals("dev", $users[0]->username, __LINE__);
        $this->_assert_equals(5, $users[0]->id, __LINE__);

        $users = $this->_ci->user_model->get_users_by_capability('users:deleteusers');
        $this->_assert_equals(3, count($users), __LINE__);
    }

    public function test_already_exists() {
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();

        $this->_assert_equals(1, $this->_ci->user_model->already_exists('email1@test.com'), __LINE__);
        $this->_assert_equals(2, $this->_ci->user_model->already_exists('email4@test.com'), __LINE__);
        $this->_assert_equals(1, $this->_ci->user_model->already_exists('email3@test.com', 0), __LINE__);
        $this->_assert_false($this->_ci->user_model->already_exists('email8@test.com'), __LINE__);
        $this->_assert_false($this->_ci->user_model->already_exists('email2@test.com'), __LINE__);
    }

    function test_assign_contact_data() {
        $this->load_user_contacts();
		$this->_ci->db->flush_cache();

        $data = array('user_email' => 'blahblah@test.com', 'user_email2' => 'blahblah2@test.com', 'user_phone' => '92349083', 'user_fax' => '349380493');
        $this->_ci->user_model->assign_contact_data(1, 'user_', $data);
    }

    function test_get_user_by_array() {
		$this->_ci->db->flush_cache();
		$user = $this->_ci->user_model->get(array(
			    'username' => 'ops',
			    'password' => 'demo_user1',
                'first_name' => 'Ops'
        ), true);
        $this->_assert_equals($user->id, 1, __LINE__);
    }

    function test_get_multiple() {
		$this->_ci->db->flush_cache();
        $users = $this->_ci->user_model->get(array('status' => 'Active'));
        $this->_assert_equals(5, count($users), __LINE__);
    }

    function test_get_invalid_id() {
		$this->_ci->db->flush_cache();
        $this->_assert_empty($this->_ci->user_model->get_unique(54), __LINE__);
    }

    function test_get_nonmatching() {
		$this->_ci->db->flush_cache();
        $this->_assert_false($this->_ci->user_model->get_unique('nonmatching@email.com'), __LINE__);
    }

    function test_get_all_users() {
		$this->_ci->db->flush_cache();
        $this->_assert_false($this->_ci->user_model->get_unique(), __LINE__);
        $users = $this->_ci->user_model->get();
        $this->_assert_equals(5, count($users), __LINE__);
    }

	function test_edit_user() {
		$this->_ci->db->flush_cache();
		$insert_data = array(
			    'last_name' => 'last_name 1',
			);
		$user = $this->_ci->user_model->edit(1, $insert_data);
		$this->_assert_true($user, __LINE__);
        $user = $this->_ci->user_model->get(array('id' => 1), true);

        $this->_assert_equals($user->last_name, 'last_name 1', __LINE__);
	}

	function test_add() {
        $this->reset_table('users');
		$insert_data = array(
			    'username' => 'test_user5',
			    'password' => 'demo_user5');
		$user_id = $this->_ci->user_model->add($insert_data);

		$this->_assert_equals($user_id, 1, __LINE__);
	}
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
