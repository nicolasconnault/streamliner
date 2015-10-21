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
require_once(APPPATH.'/core/has_type_trait.php');
class Contact_Model extends MY_Model {
    use has_types;

    public $table = 'contacts';

    /**
     * Returns a concatenated string of the contact's name.
     *
     * @param string $format A format in which to return the string.
     *                - s = salutation
     *                - f = first name
     *                - l = last name
     * @return string
     */
    function get_name($contact, $format = 'f l') {
        if (empty($contact)) {
            return null;
        }

        if (!is_object($contact)) {
            $contact = $this->get($contact);
        }

        if (!isset($contact->first_name) && isset($contact->id)) {
            $contact = $this->get($contact->id);
        }

        if (empty($contact)) {
            return 'Deleted contact';
        }

        $vars[strpos($format, 'f')] = $contact->first_name;
        $vars[strpos($format, 'l')] = $contact->surname;

        ksort($vars);

        $name = '';

        foreach ($vars as $value) {
            $name .= $value . ' ';
        }

        return rtrim($name);
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            if (empty($db_records)) {
                return null;
            }
            foreach ($db_records as $key => $row) {
                $account_id = $this->contact_model->get($row['contact_id'], true, null, array('account_id'))->account_id;

                $account = $this->account_model->get($account_id, true, null, array('cc_hold'));

                if (empty($account)) {
                    continue;
                }

                $db_records[$key]['contact_account'] = (empty($row['contact_account'])) ? '' : anchor(base_url().'accounts/edit/'.$account_id, $row['contact_account']);
                if ($account->cc_hold) {
                    $db_records[$key]['contact_account'] = '<span class="credit-hold">'.$db_records[$key]['contact_account'].'</span>';
                }
            }
        };
    }
}
