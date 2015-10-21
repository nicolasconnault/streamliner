<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_role_model extends Toast {

	function __construct() {
		parent::__construct(__FILE__);

		$this->_ci =& get_instance();

		$this->_ci->load->model('users/user_model');
		$this->_ci->load->model('users/role_model');
		$this->_ci->load->model('users/capability_model');
	}

	function _pre() {
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

    function tearDown() {
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

	public function test_included() {
		$this->_assert_True(class_exists('user_model'), __LINE__);
		$this->_assert_True(class_exists('capability_model'), __LINE__);
		$this->_assert_True(class_exists('role_model'), __LINE__);
	}

    public function test_get_capabilities() {
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
		$this->_ci->db->flush_cache();

        $caps = $this->_ci->role_model->get_capabilities(1);
        $this->_assert_equals(1, count($caps), __LINE__);
        $this->_assert_equals('site:doanything', $caps[0]->name, __LINE__);

        $caps = $this->_ci->role_model->get_capabilities(2);
        $this->_assert_equals(9, count($caps), __LINE__);
        $this->_assert_equals('orders:doanything', $caps[0]->name, __LINE__);
        $this->_assert_equals('servicequotes:doanything', $caps[1]->name, __LINE__);
    }

    public function test_get_users() {
        $this->load_users_roles();
        $this->load_users();
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->_ci->db->flush_cache();

        $users = $this->_ci->role_model->get_users(1);
        $this->_assert_equals(1, count($users), __LINE__);
        $users = $this->_ci->role_model->get_users(2);
        $this->_assert_equals(2, count($users), __LINE__);
        $users = $this->_ci->role_model->get_users(3);
        $this->_assert_equals(1, count($users), __LINE__);
    }

    public function test_add_capability() {
        $this->reset_tables(array('roles', 'roles_capabilities'));
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->_ci->db->flush_cache();

        $this->_ci->role_model->add_capability(1, 'users:editusers');
        $caps = $this->_ci->role_model->get_capabilities(1);
        $this->_assert_equals($caps[0]->name, 'site:doanything', __LINE__);
        $this->_assert_equals($caps[1]->name, 'users:editusers', __LINE__);
        $this->_assert_equals(count($caps), 2, __LINE__);
    }

    public function test_remove_capability() {
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->_ci->role_model->remove_capability(1, 'site:doanything');
        $caps = $this->_ci->role_model->get_capabilities(1);
        $this->_assert_equals(count($caps), 0, __LINE__);
    }

    public function test_remove_wrong_capability() {
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->_ci->role_model->remove_capability(1, 'users:editusers');
        $caps = $this->_ci->role_model->get_capabilities(1);
        $this->_assert_equals(count($caps), 1, __LINE__);
    }

    public function test_duplicate() {
        $this->load_users();
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();

		$this->_ci->db->flush_cache();

        $role = $this->_ci->role_model->get(1);
        $this->_assert_equals($role->name, 'Site Admin', __LINE__);

        $newrole = $this->_ci->role_model->duplicate(1);
        $this->_assert_equals($newrole->name, 'Copy of Site Admin', __LINE__);

        $newrole2 = $this->_ci->role_model->duplicate(1, true); // Clone user assignments too
        $this->_assert_equals($newrole2->name, 'Copy of Site Admin (1)', __LINE__);

        $caps = $this->_ci->role_model->get_capabilities($newrole2->id);
        $originalcaps = $this->_ci->role_model->get_capabilities($role->id);
        $this->_assert_equals($originalcaps, $caps, __LINE__);

        $users = $this->_ci->role_model->get_users($newrole2->id);
        $originalusers = $this->_ci->role_model->get_users($role->id);
        $this->_assert_equals($originalusers, $users, __LINE__);

        $users = $this->_ci->role_model->get_users($newrole->id);
        $this->_assert_equals($users, array(), __LINE__);
    }

    function test_get_assignable_users() {
        $this->load_users();
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->load_users_roles();
		$this->_ci->db->flush_cache();
        $users = $this->_ci->role_model->get_assignable_users(1);

        $this->_assert_equals('Ops Manager', $users[0]->label, __LINE__);
        $this->_assert_equals('Tech User', $users[1]->label, __LINE__);
        $this->_assert_equals('Admin User', $users[2]->label, __LINE__);
        $this->_assert_equals('Director User', $users[3]->label, __LINE__);
        $this->_assert_True(empty($users[4]), __LINE__);

        $users = $this->_ci->role_model->get_assignable_users(2);
        $this->_assert_equals('Tech User', $users[0]->label, __LINE__);
        $this->_assert_equals('Admin User', $users[1]->label, __LINE__);
        $this->_assert_equals('Dev Guy', $users[2]->label, __LINE__);
        $this->_assert_True(empty($users[5]), __LINE__);
    }

    public function test_get_dropdown() {
        $this->load_roles();
		$this->_ci->db->flush_cache();

        $roles = $this->_ci->role_model->get_dropdown('name');

    }

    public function test_get_users_by_capabilities() {
        $this->load_users();
        $this->load_roles();
        $this->load_capabilities();
        $this->load_roles_capabilities();
        $this->load_users_roles();
		$this->_ci->db->flush_cache();

        $users = $this->_ci->role_model->get_users_by_capabilities(array('users:viewusers'), array('users:deleteusers'));
        $this->_assert_equals(0, count($users), __LINE__);
        $users = $this->_ci->role_model->get_users_by_capabilities(array('users:viewusers'));
        $this->_assert_equals(3, count($users), __LINE__);
    }
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
