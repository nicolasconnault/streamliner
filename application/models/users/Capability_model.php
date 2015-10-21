<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
class Capability_Model extends MY_Model {
    /**
     * @access public
     * @var string The DB table used by this model
     */
    public $table = 'capabilities';

    /**
     * Returns all the capabilities of a given user
     * @access public
     * @param int $user_id
     * @return array Capabilities
     */
    function get_by_user_id($user_id) {

        $this->db->select('capabilities.*')->from('capabilities');
        $this->db->join('roles_capabilities', 'roles_capabilities.capability_id = capabilities.id');
        $this->db->join('roles', 'roles_capabilities.role_id = roles.id');
        $this->db->join('users_roles', 'roles.id = users_roles.role_id');
        $this->db->where('users_roles.user_id', $user_id);

        $capabilities = array();

        $query = $this->db->get();

        if ($query->conn_id->affected_rows > 0) {
            foreach ($query->result() as $row) {
                $capabilities[$row->id] = $row;
            }
        }

        return $capabilities;
    }

    /**
     * Given a capability ID, returns the category part of the capability name.
     * @access public
     * @param int $id Capability ID
     * @return string
     */
    function get_category($id) {
        $cap = $this->get($id);
        $parts = explode(':', $cap->name);
        return ucfirst($parts[0]);
    }

    /**
     * Returns whether or not the requested capability is covered by the given array of capabilities. It returns true
     * if at least one capability it depends on is part of the capabilities array.
     * @access public
     * @param int $cap_id
     * @param array $capabilities The array of capabilities to search
     * @return bool
     */
    function is_included_in($cap_id, $capabilities) {
        $cap = $this->get($cap_id);


        foreach ($capabilities as $testcap) {
            if ($testcap->id == $cap->dependson || $testcap->id == $cap->id) {
                return true;
            }
        }

        if (!empty($cap->dependson)) {
            $dependcap = $this->get($cap->dependson);
            return $this->capability_model->is_included_in($dependcap->id, $capabilities);
        }

        return false;
    }

    /**
     * Given a capability id, returns an array of all capabilities that depend on it, directly or indirectly.
     * @access public
     * @param int $cap_id The Capability ID
     * @param array $capabilities Used in recursion
     * @param bool $first_call Used in recursion
     * @param array $allcaps Array of all capabilities: can be passed to this function to avoid extra SQL
     * @return array
     */
    function get_dependents($cap_id, &$capabilities, &$caps_to_check=array(), $first_call=true, $allcaps=null) {

        static $checkedcaps = array();

        if ($first_call) {
            // Re-initialise the static array between each root call to this recursive function
            $checkedcaps = array();
            $caps_to_check = $capabilities;
        }

        if (is_null($allcaps)) {
            $allcaps = $this->get();
        }

        foreach ($allcaps as $allcap) {
            if (!empty($allcap->dependson)) {
                if ($allcap->dependson == $cap_id && !isset($capabilities[$allcap->id])) {
                    $allcap->label = $this->get_label($allcap);
                    $capabilities[$allcap->id] = $allcap;
                    if (!isset($checkedcaps[$allcap->id])) {
                        $caps_to_check[$allcap->id] = $allcap;
                    }
                }
            }
        }

        // For unchecked caps added to $capabilities, check if they have dependents, and if they do add them to $capabilities also
        foreach ($caps_to_check as $capability_id => $capability) {
            unset($caps_to_check[$capability_id]);

            if ($capability_id != $cap_id && $this->has_dependents($capability_id, $allcaps)) {

                $checkedcaps[$capability_id] = 1;
                $this->get_dependents($capability_id, $capabilities, $caps_to_check, false, $allcaps);

                // Some capabilities quickly lead to access to all caps, so we don't need to keep iterating
                if (count($allcaps) == count($capabilities)) {
                    return;
                }
            }
        }
        // There is no return value because the function acts directly on the passed $capabilities array (by reference)
    }

    /**
     * Function designed to reduce DB queries: fetch a capability by name from the $allcaps var already used by various algorithms here.
     * @access private
     * @param string $name
     * @param array $allcaps
     * @return object
     */
    function _get_cap_by_name($name, $allcaps=null) {
        if (is_null($allcaps)) {

            $allcaps = $this->get();
        }

        foreach ($allcaps as $cap) {
            if ($cap->name == $name) {
                return $cap;
            }
        }
        return false;
    }

    /**
     * Returns whether or not the requested capability has dependent capabilities
     * @access public
     * @param int $cap-id
     * @return bool
     */
    function has_dependents($cap_id, $allcaps=null) {

        if (is_null($allcaps)) {
            $allcaps = $this->get();
        }

        foreach ($allcaps as $allcap) {
            if (!empty($allcap->dependson)) {
                if ($allcap->dependson == $cap_id) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * This quickly builds up a nested array of capabilities, useful for nested lists and dropdown menus
     * @access public
     * @static
     * @param array $allcaps An array of all capabilities to be organised in a nested array
     * @param array $exclude an optional 2-dimensional array of capabilities and their dependents which should be exluded from this array
     * @return array Nested capabilities
     */
    public static function get_nested_caps($allcaps=null, $exclude=array()) {
        $ci = get_instance();
        if (is_null($allcaps)) {
            $allcaps = $ci->capability_model->get();
        }

        $capabilities = array();
        $dependencemap = array();

        foreach ($allcaps as $cap) {
            $capabilities[$cap->id] = $cap;
            if ($cap->dependson) {
                $dependencemap[$cap->id] = $cap->dependson;
            }
        }
        $retval = self::_build_nested_caps($capabilities, $dependencemap, array(), array(), null, $exclude);

        return $retval;
    }

    /**
     * Private recursive function used for building the nested array of caps
     * @access private
     * @static
     * @param array $capabilities Flat array of capabilities
     * @param array $dependencemap An array containing all the dependence relationships between capabilities array(parent_id => array(children_ids))
     * @param array $children An array that gets built gradually as the recursive algorithm progresses
     * @param array $foundchildren
     * @param int $parent_id
     * @param array $exclude an optional 2-dimensional array of capabilities and their dependents which should be exluded from this array
     * @return array Nested capabilities
     * @todo finish documenting internals
     */
    private static function _build_nested_caps($capabilities, $dependencemap, $children=array(), $foundchildren=array(), $parent_id=null, $exclude=array()) {
        if (empty($children)) {
            $children = $capabilities;
        }

        $nestedcaps = array();

        foreach ($children as $capid => $cap) {
            // Add a $label field based on description and name
            $cap->label = Capability_Model::get_label($cap);

            foreach ($exclude as $dependent_parent => $dependents) {
                if (in_array($cap->name, $dependents) || $cap->id == $dependent_parent) {
                    continue 2;
                }
            }

            if (in_array($capid, $dependencemap)) {
                $dependents = array();

                foreach ($dependencemap as $child => $parent) {
                    if ($parent == $capid) {
                        $dependents[$child] = $capabilities[$child];
                    }
                }

                if (!in_array($capid, $foundchildren)) {
                    $cap->children = self::_build_nested_caps($capabilities, $dependencemap, $dependents, $foundchildren, $capid, $exclude);
                    if (empty($dependencemap[$capid]) || $dependencemap[$capid] == $parent_id) {
                        $nestedcaps[$capid] = $cap;
                    }

                    // 125 == site:doanything
                    if ($capid == 125) {
                        return $nestedcaps;
                    }

                }
                $foundchildren[] = $capid;
            } else if (!in_array($capid, $foundchildren)) {
                if (empty($dependencemap[$capid]) || $dependencemap[$capid] == $parent_id) {
                    $nestedcaps[$capid] = $cap;
                }
            }
            $foundchildren[] = $capid;
        }
        return $nestedcaps;
    }

    /**
     * Returns all capabilities, including an array field for the roles that include that capability.
     * This uses an SQL that searches only one level of parent-child recursion. Any capabilities not associated with a role after this need to be done through separate SQL
     * @return array
     */
    function get_with_roles() {

        $this->benchmark->mark('get_with_roles_start');
        $all_caps = $this->get();

        $this->db->select('rc.id AS rc_id, c.id AS cap_id, r.id AS role_id, r.name AS role_name, c.name AS cap_name, c.description', false)->from('roles r');
        // Using LEFT OUTER joins here because some roles may not have any capabilities but still need to be returned
        $this->db->join('roles_capabilities rc', 'r.id = rc.role_id', 'left outer');
        $this->db->join('capabilities c', 'rc.capability_id = c.id', 'left outer');
        $this->db->order_by('r.name');
        $query = $this->db->get();

        $roles = array();
        foreach ($query->result() as $row) {
            if (empty($roles[$row->role_id])) {
                $roles[$row->role_id] = array();
            }
            $roles[$row->role_id][$row->cap_id] = $row;
        }

        foreach ($roles as $role_id => $caps) {
            $role_name = '';
            $rc_id = '';

            foreach ($caps as $cap_id => $cap) {
                if (empty($role_name) && !empty($cap->role_name)) {
                    $role_name = $cap->role_name;
                }
                if (empty($rc_id) && !empty($cap->rc_id)) {
                    $rc_id = $cap->rc_id;
                }

                $capabilities = array();
                $caps_to_check = array();
                $this->get_dependents($cap->cap_id, $capabilities, $caps_to_check, true, $all_caps);
                foreach ($capabilities as $dependent_cap) {
                    if (!array_key_exists($dependent_cap->id, $caps)) {
                        $new_cap = clone($dependent_cap);
                        $new_cap->role_id = $role_id;
                        $new_cap->cap_name = $new_cap->name;
                        $new_cap->cap_id = $new_cap->id;
                        $new_cap->role_name = $role_name;
                        $new_cap->rc_id = $rc_id;
                        unset($new_cap->id);
                        unset($new_cap->name);
                        unset($new_cap->type);
                        unset($new_cap->creation_date);
                        unset($new_cap->revision_date);
                        $roles[$role_id][$new_cap->cap_id] = $new_cap;
                    }
                }
            }
        }

        // Now turn this array inside out
        $caps_with_roles = array();
        foreach ($roles as $role_id => $caps) {
            foreach ($caps as $cap_id => $cap) {
                if (empty($caps_with_roles[$cap_id])) {
                    $caps_with_roles[$cap_id] = array();
                }
                $role = new stdClass();
                $role->id = $role_id;
                $role->name = $cap->role_name;
                $caps_with_roles[$cap_id][$role_id] = $role;
            }
        }

        $this->benchmark->mark('get_with_roles_end');
        return array('roles' => $roles, 'capabilities' => $caps_with_roles);
    }

    /**
     * Given the id of a capability and an array of nested capabilities, recursively searches the array for all parents of child capability, and returns them in an array
     */
    function get_parents_from_nested_caps($child_id, $nested_caps, $parents=array()) {
        $current_cap = $this->get($child_id);
        if ($current_cap->name == 'site:doanything') {
            return array();
        }

        foreach ($nested_caps as $cap) {
            $childless_cap = clone($cap);
            unset($childless_cap->children);
            $parents[] = $childless_cap;
            if (!empty($cap->children)) {
                foreach ($cap->children as $child) {
                    if ($child->id == $child_id) {
                        return $parents;
                    }
                }
                $found_parents = $this->get_parents_from_nested_caps($child_id, $cap->children, $parents);
                if (!is_null($found_parents)) {
                    return $found_parents;
                }
            }
        }
        return $parents;
    }

    /**
     * Given a capability object with name and description, returns a more useful label
     * @param object $capability
     * @return string
     */
    static public function get_label($capability) {
        if (!preg_match('/([a-z]*)\:.*/', $capability->name, $matches)) {
            add_message("The capability $capability->name is not formatted correctly!", 'danger');
            return null;
        }

        $label = ucfirst($matches[1]) . ' system: ' . $capability->description;
        return $label;
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            $roles = $this->role_model->get();

            $caps_with_roles = $this->get_with_roles()['capabilities'];

            foreach ($db_records as $key => $row) {
                if (!empty($caps_with_roles[$row['capability_id']])) {
                    $roles_that_have_this_cap = $caps_with_roles[$row['capability_id']];
                    $db_records[$key]['roles'] = '<ul>';

                    foreach ($roles_that_have_this_cap as $role) {
                        $db_records[$key]['roles'] .= '<li><a href="'.base_url().'users/role/user_edit/'.$role->id.'">'.$role->name .'</a></li>';
                    }

                    $db_records[$key]['roles'] .= '</ul>';
                } else {
                    $db_records[$key]['roles'] = '';
                }
            }
        };
    }
}

