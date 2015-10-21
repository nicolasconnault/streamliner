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
class Role_Model extends MY_Model {
    public $table = 'roles';
    /**
     * Returns all the capabilities associated with a role identified by role_id
     * @access public
     * @param int $role_id The ID of the role
     * @return array Array of capabilities
     */
    public function get_capabilities($role_id) {
        $caps = array();

        $this->db->select('capabilities.*')->from('capabilities')->join('roles_capabilities', 'capabilities.id = capability_id')->join('roles', 'roles.id = role_id')->where('role_id', $role_id);
        $this->db->order_by('capabilities.name');
        $query = $this->db->get();

        if ($this->db->conn_id->affected_rows > 0) {
            foreach ($query->result() as $row) {
                $row->label = $this->capability_model->get_label($row);
                $caps[] = $row;
            }
        }

        return $caps;
    }

    /**
     * Returns all the users associated with a role identified by role_id
     * @access public
     * @param int $role_id The ID of the role
     * @return array Array of users
     */
    public function get_users($role_id) {

        $users = array();

        $this->db->select('users.*')->from('users')->join('users_roles', 'users_roles.user_id = users.id')->join('roles', 'users_roles.role_id = roles.id')->where('role_id', $role_id);

        $query = $this->db->get();

        if ($this->db->conn_id->affected_rows > 0) {
            foreach ($query->result() as $row) {
                $users[] = $row;
            }
        }

        return $users;
    }

    /**
     * Given a capability name (not id!), adds that capability to the role identified by role_id
     * @access public
     * @param int $role_id The ID of the role with which the capability will be associated
     * @param string $capname The name of the capability to associate with the role
     * @return int|error The id of the roles_capabilities association, or a fatal error if the given capability name doesn't exist
     */
    public function add_capability($role_id, $capname) {
        // Find capability first


        if ($cap = $this->capability_model->get(array('name' => $capname), true)) {
            $this->db->from('roles_capabilities')->where(array('role_id' => $role_id, 'capability_id' => $cap->id));
            $this->db->get();
            if ($this->db->conn_id->affected_rows == 0) {
                return $this->db->insert('roles_capabilities', array('role_id' => $role_id, 'capability_id' => $cap->id));
            }
        } else {
            show_error("Capability $capname doesn't exist!");
            return false;
        }

        return null;
    }

    /**
     * Given a capability name (not id!), removes that capability from the role identified by role_id
     * @access public
     * @param int $role_id The ID of the role from which the capability will be removed
     * @param string $capname The name of the capability to remove from the role
     * @return true|error True if successful, or a fatal error if the given capability name doesn't exist
     */
    public function remove_capability($role_id, $capname) {


        if ($cap = $this->capability_model->get(array('name' => $capname), true)) {
            $this->db->where('capability_id', $cap->id)->where('role_id', $role_id);
            return $this->db->delete('roles_capabilities');
        }

        show_error("Capability $capname doesn't exist!");
        return false;
    }

    /**
     * Duplicates a role, including all its associations with capabilities, and (optionally) its users.
     * @access public
     * @param int $role_id The ID of the role to duplicate
     * @param boolean $copyusers Whether or not to duplicate user associations as well
     * @return stdclass|error A stdClass representing the new role, or a fatal error if the new role could not be inserted in the DB
     */
    public function duplicate($role_id, $copyusers = false) {

        $thisrole = $this->get($role_id);
        $newrole = new stdClass();
        $newrolename = "Copy of $thisrole->name";
        $newrole->name = $newrolename;

        $counter = 0;
        while ($this->get(array('name' => $newrole->name))) {
            $counter++;
            $newrole->name = $newrolename . " ($counter)";
        }

        // Insert, then add same capabilities as original role
        if ($this->db->insert('roles', $newrole)) {
            $newrole->id = $this->db->insert_id();

            $capabilities = $this->role_model->get_capabilities($role_id);

            foreach ($capabilities as $capability) {
                $this->db->insert('roles_capabilities', array('capability_id' => $capability->id, 'role_id' => $newrole->id));
            }

            if ($copyusers) {
                $users = $this->role_model->get_users($role_id);
                foreach ($users as $user) {
                    $this->db->insert('users_roles', array('user_id' => $user->id, 'role_id' => $newrole->id));
                }
            }

            return $newrole;
        } else {
            show_error('Error duplicating the role!');
            return false;
        }
    }


    /**
     * Returns an array of users who can be assigned to the given role (who are not already assigned to it).
     * @param int $role_id
     * @return array
     */
    function get_assignable_users($role_id, $search_term=null) {

        $this->db->select('first_name,  last_name, u.id');
        $this->db->where("id NOT IN (SELECT user_id FROM users_roles WHERE role_id = $role_id)", null, false);

        if (!is_null($search_term)) {
            $this->db->where("first_name LIKE '$search_term%'")->or_where("last_name LIKE '$search_term%'");
        }

        $query = $this->db->get('users u');
        $users = array();
        foreach ($query->result() as $result) {
            $user = new stdClass();
            $user->value = $result->id;
            $user->label = $this->user_model->get_name($result);
            $users[] = $user;
        }
        return $users;
    }

    public function get_dropdown($name_field, $null_option=true, $label_function=false, $optgroups=false, $optgroup_constant_prefix=null, $null_value = null, $order_by=null, $where=null) {

        $this->db->order_by('name');
        return parent::get_dropdown($name_field, $null_option, $label_function, $optgroups, $optgroup_constant_prefix=null, $null_value, $order_by, $where);
    }

    /**
     * Returns a list of users who have all of the capabilities (by name) in the $whitelist array, and none of the capabilities in the $blacklist array
     * @param array $whitelist
     * @param array $blacklist Only used to speed up the search for the whitelisted capabilities. Don't use on its own
     * @return array
     */
    public function get_users_by_capabilities($whitelist, $blacklist=array()) {

        $users = $this->user_model->get();

        foreach ($users as $key => $user) {
            foreach ($whitelist as $whitelist_cap) {
                if (!has_capability($whitelist_cap, $user->id)) {
                    unset($users[$key]);
                    continue 2;
                }
            }

            foreach ($blacklist as $blacklist_cap) {
                if (has_capability($blacklist_cap, $user->id)) {
                    unset($users[$key]);
                    continue 2;
                }
            }
        }
        return $users;
    }

    /**
     * Add some HTML to the role names (count of users with that role)
     */
    public function get_custom_columns_callback() {
        return function(&$db_records) {

            $roles = array();
            $role_ids = array();
            $role_counts = array();

            foreach ($db_records as $row) {
                $role_ids[] = $row['role_id'];
                $roles[$row['role_id']] = $row;
                $role_counts[$row['role_id']] = 0;
            }

            $this->db->select("roles.id AS role_id, COUNT(users.id) AS user_count", false);
            $this->db->join('users_roles AS ur', 'ur.role_id = roles.id');
            $this->db->join('users', 'users.id = ur.user_id');
            $this->db->group_by('roles.id');

            $roles_list = $this->role_model->get();

            if (!empty($roles_list)) {
                foreach ($roles_list as $role_info) {
                    if (!empty($role_info)) {
                        $role_counts[$role_info->role_id] = $role_info->user_count;
                    }
                }
            }

            foreach ($db_records as $key => $row) {
                if (!empty($row['role_parent_id'])) {
                    $db_records[$key]['role_parent_id'] = $this->role_model->get_name($row['role_parent_id']);
                }
                $db_records[$key]['role_name'] = $row['role_name'] .
                    '<a href="'.base_url().'users/role/user_edit/'.$row['role_id'].'">
                     <span class="badge pull-right">'.
                     $role_counts[$row['role_id']]. '</span></a>';
            }
        };
    }

    public function get_potential_parent_ids($role_id) {
        $roles = $this->get();
        $parent_ids = array();

        foreach ($roles as $role) {
            if ($role->id != $role_id) {
                $parent_ids[$role->id] = $role->name;
            }
        }

        return $parent_ids;
    }

    /**
     * In reference to the logged in user, returns if the provided role_id is lower or equal (true) or higher (false)
     */
    public function is_lower_in_hierarchy($role_id, $my_roles=array()) {
        if (empty($my_roles)) {
            $my_roles = $this->user_model->get_roles();
        }

        $parent_roles = array();

        if (empty($my_roles)) {
            return false;
        }

        $result = false;

        foreach ($my_roles as $role) {
            if (!empty($role->parent_id)) {
                if ($role->parent_id == $role_id) {
                    if (count($my_roles) == 1) {
                        return false;
                    } else {
                        $result = $result || false;
                    }
                } else {
                    $result = $result || true;
                }

                $parent_roles[$role->parent_id] = $this->get($role->parent_id);
            }
        }

        if (empty($parent_roles) || (count($parent_roles) == 1 && reset($parent_roles)->name == 'Site Admin')) {
            return true;
        } else {
            return $result && $this->is_lower_in_hierarchy($role_id, $parent_roles);
        }
    }
}

?>
