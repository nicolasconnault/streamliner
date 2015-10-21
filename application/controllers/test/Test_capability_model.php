<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once(APPPATH . '/controllers/test/Toast.php');

class test_capability_model extends Toast {

	function __construct() {
		parent::__construct(__FILE__);
	}

	function _pre() {
		$this->_ci =& get_instance();

		$this->_ci->load->model('users/user_model');
		$this->_ci->load->model('users/role_model');
		$this->_ci->load->model('users/capability_model');
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }

    function tearDown() {
		$this->_ci =& get_instance();

		$this->_ci->load->model('users/user_model');
		$this->_ci->load->model('users/role_model');
		$this->_ci->load->model('users/capability_model');
        $this->_ci->db = $this->_ci->load->database('unittest', true);
    }


	public function test_included() {
		$this->_assert_True(class_exists('user_model'), __LINE__);
		$this->_assert_True(class_exists('capability_model'), __LINE__);
		$this->_assert_True(class_exists('role_model'), __LINE__);
	}

    public function test_get_by_user_id() {
        $this->load_users();
        $this->load_roles_capabilities();
        $this->load_users_roles();
        $this->_ci->db->flush_cache();
        $this->_assert_equals(count($this->_ci->capability_model->get_by_user_id(1)), 29, __LINE__);
        $this->_assert_equals(count($this->_ci->capability_model->get_by_user_id(2)), 4, __LINE__);
    }

    public function test_get_category() {
        $this->load_roles();
		$this->_ci->db->flush_cache();
        $capcategory = $this->_ci->capability_model->get_category(1);
        $this->_assert_equals($capcategory, 'Site', __LINE__);
    }

    public function test_is_included_in() {
        $this->load_roles();
		$this->_ci->db->flush_cache();
        $admincap = new stdClass();
        $admincap->id = 1;

        $this->_assert_True($this->_ci->capability_model->is_included_in(2, array($admincap)), __LINE__);
        $this->_assert_True($this->_ci->capability_model->is_included_in(3, array($admincap)), __LINE__);
        $this->_assert_True($this->_ci->capability_model->is_included_in(4, array($admincap)), __LINE__);
        $this->_assert_True($this->_ci->capability_model->is_included_in(5, array($admincap)), __LINE__);

        $enquirycap = new stdClass();
        $enquirycap->id = 2;

        $this->_assert_False($this->_ci->capability_model->is_included_in(1, array($enquirycap)), __LINE__);

        $this->_assert_True($this->_ci->capability_model->is_included_in(5, array($enquirycap)), __LINE__);
    }

    public function test_get_dependents() {
        $this->load_roles();
		$this->_ci->db->flush_cache();

        $dependents = array();
        $this->_ci->capability_model->get_dependents(1, $dependents);
        $this->_assert_equals(count($dependents), 66, __LINE__);

        $dependents = array();
        $this->_ci->capability_model->get_dependents(2, $dependents);
        $this->_assert_equals(count($dependents), 8, __LINE__);
    }

    public function test_get_cap_by_name() {
        $this->load_capabilities();
		$this->_ci->db->flush_cache();
        $cap = $this->_ci->capability_model->_get_cap_by_name('users:editusers');
        $this->_assert_equals('users:editusers', $cap->name, __LINE__);
    }

    public function test_has_dependents() {
        $this->load_roles();
		$this->_ci->db->flush_cache();
        $this->_assert_True($this->_ci->capability_model->has_dependents(1), __LINE__);
        $this->_assert_True($this->_ci->capability_model->has_dependents(2), __LINE__);
        $this->_assert_False($this->_ci->capability_model->has_dependents(4), __LINE__);
        $this->_assert_True($this->_ci->capability_model->has_dependents(3), __LINE__);
        $this->_assert_False($this->_ci->capability_model->has_dependents(5), __LINE__);
    }

    public function test_get_nested_caps() {
        $this->load_roles();
		$this->_ci->db->flush_cache();
        $nested_caps = $this->_ci->capability_model->get_nested_caps();
        $this->_assert_equals(count($nested_caps), 1, __LINE__);
        $this->_assert_equals('users:editusers', $nested_caps[1]->children[2]->children[3]->name, __LINE__);
        $this->_assert_equals('users:deleteusers', $nested_caps[1]->children[2]->children[4]->name, __LINE__);
        $this->_assert_equals('users:writeusers', $nested_caps[1]->children[2]->children[3]->children[6]->name, __LINE__);
        $this->_assert_equals('users:viewusers', $nested_caps[1]->children[2]->children[3]->children[5]->name, __LINE__);
    }

    public function test_get_with_roles() {
        $this->load_roles();
		$this->_ci->db->flush_cache();
        $caps_with_roles = $this->_ci->capability_model->get_with_roles();
        $this->_assert_equals(8, count($caps_with_roles['roles']), __LINE__);
        $this->_assert_equals(67, count($caps_with_roles['roles'][1]), __LINE__);
        $this->_assert_equals(62, count($caps_with_roles['roles'][2]), __LINE__);
        $this->_assert_equals(21, count($caps_with_roles['roles'][3]), __LINE__);

        $this->_assert_equals(68, count($caps_with_roles['capabilities']), __LINE__);
        $this->_assert_equals(2, count($caps_with_roles['capabilities'][1]), __LINE__);
        $this->_assert_equals(3, count($caps_with_roles['capabilities'][2]), __LINE__);
        $this->_assert_equals(3, count($caps_with_roles['capabilities'][3]), __LINE__);
        $this->_assert_equals(3, count($caps_with_roles['capabilities'][4]), __LINE__);
        $this->_assert_equals(3, count($caps_with_roles['capabilities'][5]), __LINE__);
    }

    public function test_get_parents_from_nested_caps() {
        $this->load_roles();
        $this->load_capabilities();
		$this->_ci->db->flush_cache();

        $nested_caps = $this->_ci->capability_model->get_nested_caps();
        $parents = $this->_ci->capability_model->get_parents_from_nested_caps(5, $nested_caps);
        $this->_assert_equals(3, count($parents), __LINE__);
        $parents = $this->_ci->capability_model->get_parents_from_nested_caps(4, $nested_caps);
        $this->_assert_equals(2, count($parents), __LINE__);
        $parents = $this->_ci->capability_model->get_parents_from_nested_caps(3, $nested_caps);
        $this->_assert_equals(2, count($parents), __LINE__);
        $parents = $this->_ci->capability_model->get_parents_from_nested_caps(1, $nested_caps);
        $this->_assert_empty($parents, __LINE__);
    }

    public function test_get_all() {
        $this->load_roles();
		$this->_ci->db->flush_cache();
        $caps = $this->_ci->capability_model->get();
        $this->_assert_equals(count($caps), 67, __LINE__);
    }

    public function test_get_label() {
        $this->load_capabilities();
		$this->_ci->db->flush_cache();

        $this->_assert_equals('Site system: Do anything', $this->_ci->capability_model->get_label($this->_ci->capability_model->get(1)), __LINE__);
        $this->_assert_equals('Users system: Do anything to do with users', $this->_ci->capability_model->get_label($this->_ci->capability_model->get(2)), __LINE__);
        $this->_assert_equals('Users system: Edit user accounts', $this->_ci->capability_model->get_label($this->_ci->capability_model->get(3)), __LINE__);
        $this->_assert_equals('Users system: Delete user accounts', $this->_ci->capability_model->get_label($this->_ci->capability_model->get(4)), __LINE__);
        $this->_assert_equals('Users system: View user accounts', $this->_ci->capability_model->get_label($this->_ci->capability_model->get(5)), __LINE__);

    }
}
/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
