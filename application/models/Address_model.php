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
class Address_Model extends MY_Model {
    use has_types;
    public $table = 'addresses';

    public function get_formatted_address($address_id_or_object, $format=null) {

        if (is_null($format)) {
            $format = '[[unit]][[number]] [[street]] [[street_type_short]], [[city]], [[state]] [[postcode]]';
        }

        if (is_array($address_id_or_object)) {
            $address = (object) $address_id_or_object;
        } else if (!is_numeric($address_id_or_object)) {
            $address = $address_id_or_object;
        } else if (!empty($address_id_or_object)) {
            $address = $this->get($address_id_or_object);

        } else {
            return null;
        }

        if (empty($address)) {
            return null;
        }

        if (empty($address->street_type_short)) {
            $address->street_type_short = $this->street_type_model->get_abbreviation($address->street_type);
        }

        if (empty($address->state)) {
            $address->state = 'WA';
        }

        if (isset($address->po_box_on) && $address->po_box_on) {
            $format = 'PO Box [[po_box]], [[city]], [[state]] [[postcode]]';
            $searches = array('[[po_box]]','[[city]]','[[state]]', '[[postcode]]');
            $replacements = array($address->po_box,$address->city,$address->state,$address->postcode);
        } else {
            $searches = array('[[unit]]','[[number]]','[[street]]','[[street_type_short]]','[[city]]','[[state]]','[[postcode]]');
            $replacements = array($address->unit.'/',$address->number,$address->street,$address->street_type_short,$address->city,$address->state,$address->postcode);
            if (!empty($address->unit) && empty($address->number)) {
                $replacements[0] = "Unit $address->unit";
            } else if (empty($address->unit)) {
                $replacements[0] = "";
            }
        }


        return str_replace($searches, $replacements, $format);
    }

    /**
     * Returns an array of orders linked to this address
     */
    public function get_orders($address_id) {
        return $this->order_model->get(array('site_address_id' => $address_id));
    }

    public function get_units($address_id) {
        return $this->unit_model->get(array('site_address_id' => $address_id));
    }

    function get($id_or_fields=null, $first_only=false, $order_by=null, $select_fields=array()) {
        $addresses = parent::get($id_or_fields, $first_only, $order_by, $select_fields);

        if (is_array($addresses)) {
            foreach ($addresses as $key => $address) {
                if (!empty($address->street_type)) {
                    $addresses[$key]->street_type_short = $this->street_type_model->get_abbreviation($address->street_type);
                }
            }
        } else {
            if (!empty($addresses->street_type)) {
                $addresses->street_type_short = $this->street_type_model->get_abbreviation($addresses->street_type);
            }
        }

        return $addresses;
    }

    public function get_custom_columns_callback() {
        return function(&$db_records) {

            if (empty($db_records)) {
                return null;
            }
            foreach ($db_records as $key => $row) {
                $db_records[$key]['street'] = $this->address_model->get_formatted_address($row['address_id']);
                if ($account = $this->account_model->get($row['account_id'])) {
                    $db_records[$key]['account_id'] = $account->name;
                } else {
                    $db_records[$key]['account_id'] = '';
                }
            }
        };
    }

}
