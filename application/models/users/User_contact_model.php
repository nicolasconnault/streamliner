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
class User_Contact_Model extends MY_Model {
    public $table = 'user_contacts';

    /**
     * Returns all the contact details for a given user. Specific type can be requested, and defaults only can also be requested.
     * @todo Accept multiple types, currently accepts only none or one
     * @param int $user_id The id of the user
     * @param mixed $type Null if all types are to be retrieved, otherwise integer or array of integers
     * @param bool $return_first_only If true, will only return the first record found
     * @param bool $string_only If true, will only return a string (or array of strings) containing the contact details, instead of complete objects
     * @param bool $defaults_only If true, will only return contacts that are set as default
     * @return array
     */
    public function get_by_user_id($user_id, $type=null, $return_first_only=true, $string_only=false, $defaults_only=false) {

        $params = array();
        $params['user_id'] = $user_id;

        if ($defaults_only) {
            $params['default_choice'] = 1;
        }

        if ($return_first_only) {
            $this->db->limit(1);
        }

        $this->db->order_by('default_choice DESC');

        if (!is_null($type)) {
            $params['type'] = $type;
        }

        $result = $this->get($params);

        if ($return_first_only) {
            if (empty($result)) {
                return null;
            }
            if ($string_only) {
                return $result[0]->contact;
            } else {
                return $result[0];
            }
        } else {
            if (empty($result)) {
                return array();
            } else {
                if ($string_only) {
                    $contacts = array();
                    foreach ($result as $contact) {
                        $contacts[$contact->id] = $contact->contact;
                    }
                    return $contacts;
                } else {
                    return $result;
                }
            }
        }
    }

    /**
     * In addition to deleting this contact, if it is the default_choice, assign default_choice to another contact of the same type if it exists
     * @param $contact_id ID of the contact detail
     * @return boolean true unless something goes majorly wrong in SQL
     */
    public function delete($contact_id) {

        $contact = $this->user_contact_model->get($contact_id, true);
        $result = parent::delete($contact_id);

        if (!$result) {
            return false;
        }

        if ($contact->default_choice) {
            $otherdefaultcontacts = $this->user_contact_model->get(array('user_id' => $contact->user_id,
                                                                       'default_choice' => 1,
                                                                       'type' => $contact->type), true);

            // If at least one other contact is already default, do nothing else
            if ($otherdefaultcontacts) {
                return true;
            }

            $othernondefaultcontact = $this->user_contact_model->get(array('user_id' => $contact->user_id,
                                                                         'default_choice' => 0,
                                                                         'type' => $contact->type), true);
            if (!empty($othernondefaultcontact)) {
                $this->user_contact_model->edit($othernondefaultcontact->id, array('default_choice' => 1));
            }
        }

        return true;
    }

    /**
     * Sets the default_choice for this contact detail to 1, and all other contacts of this type and for this user to 0
     *
     * @param int $contact_id The id of the contact detail
     * @param boolean $soft If true, will only set as default if no other contact of that type is already set as default
     * @return boolean True if set as default, false otherwise
     */
    public function set_as_default($contact_id, $soft=false) {

        $contact = $this->user_contact_model->get($contact_id);

        if (!$contact) {
            return false;
        }

        $this->user_contact_model->edit($contact_id, array('default_choice' => 1));

        $othercontacts = $this->user_contact_model->get(array('user_id' => $contact->user_id, 'type' => $contact->type, 'default_choice' => 1));

        // If soft option true and at least one other contact is default, cancel action
        if ($soft) {
            foreach ($othercontacts as $othercontact) {
                if ($othercontact->id != $contact->id) {
                    $this->user_contact_model->edit($contact_id, array('default_choice' => 0));
                    return false;
                }
            }
        }

        foreach ($othercontacts as $othercontact) {
            if ($othercontact->id != $contact->id) {
                $this->user_contact_model->edit($othercontact->id, array('default_choice' => 0));
            }
        }

        return true;
    }
}
