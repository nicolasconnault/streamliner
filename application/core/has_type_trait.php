<?php
/*
 * Copyright 2015 SMB Streamline
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

trait has_types {

    public $entity_type=null;

    /**
     * Document type is obtained from the name of the class from which the trait functions are called
     */
    public function get_entity_type($entity_type=null) {
        if (is_null($entity_type)) {
            return strtolower(substr(__CLASS__, 0, -6));
        } else {
            return $entity_type;
        }
    }

    public function get_type_id($type_string, $entity_type=null) {
        static $type_id = array();

        if (!empty($type_id[$type_string][$entity_type])) {
            return $type_id[$type_string][$entity_type];
        }

        $type = $this->type_model->get(array('types.name' => $this->inflector->singularize(ucfirst($type_string)), 'entity' => $this->get_entity_type($entity_type)), true, null, array('types.id'));
        if (empty($type)) {
            $entity_type = $this->get_entity_type();
            $type_string = $this->inflector->singularize(ucfirst($type_string));
            add_message("Type $entity_type.$type_string does not exist!", 'warning');
        } else {
            $type_id[$type_string] = array($entity_type => $type->id);
            return $type_id[$type_string][$entity_type];
        }
    }

    public function get_type_string($type_id) {
        static $type_string = array();

        if (!empty($type_string[$type_id])) {
            return $type_string[$type_id];
        }
        $result = $this->type_model->get(array('types.id' => $type_id), true, null, array('types.name'))->name;
        $type_string[$type_id] = $result;
        return $type_string[$type_id];

    }

    public function get_types_dropdown($null_option=true, $label_function=false, $entity_type=null) {
        $this->db->where('entity', $this->get_entity_type($entity_type));
        $this->db->order_by('name');
        return $this->type_model->get_dropdown('name', $null_option, $label_function);
    }
}
