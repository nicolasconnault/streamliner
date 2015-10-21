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
function trigger_event($event_name, $system, $document_id=null, $undo=false, $module=null) {
    $ci = get_instance();
    $user_id = $ci->session->userdata('user_id');

    // Always pluralise the $system variable
    $system = $ci->inflector->pluralize($system);

    if (!($event = $ci->event_model->get(array('name' => $event_name, 'system' => $system), true))) {
        $ci->events_log_model->add(array('user_id' => $user_id, 'event_id' => 0, 'document_id' => $document_id, 'notes' => "Event $event_name for $system system could not be found!"));
        return false;
    }

    if (!empty($event->role_id)) {
        $roles = $ci->user_model->get_roles($user_id);
        $found_role = false;

        foreach ($roles as $role) {
            if ($role->id == $event->role_id) {
                $found_role = true;
            }
        }

        if (!$found_role) {
            $role = $ci->role_model->get($event->role_id);
            throw new WrongRoleForEventException($event->name, $role->name);
        }
    }

    $notes = "Statuses: ";

    // Looks for statuses that are listening for this event, and change them accordingly
    if ($status_events = $ci->status_event_model->get(array('event_id' => $event->id))) {
        $module_prefix = (empty($module)) ? '' : $module.'/';

        $ci->load->model($module_prefix . $ci->inflector->singularize($event->system).'_model');

        foreach ($status_events as $status_event) {
            $status = $ci->status_model->get($status_event->status_id);

            $actual_state = ($undo) ? !$status_event->state : $status_event->state;
            $ci->{$ci->inflector->singularize($event->system).'_model'}->set_status($document_id, $status->name, $actual_state);
            $notes .= "$status->name ($event->system $document_id, set to $actual_state),";
        }
    }

    // Record the event in the log
    $ci->events_log_model->add(array('user_id' => $user_id, 'event_id' => $event->id, 'document_id' => $document_id, 'notes' => $notes));

    return true;
}

class WrongEventNameException extends Exception {
    public function __construct($event_name, $code = 0, Exception $previous = null) {
        $message = "The event '$event_name' does not exist in the database. Please check the calling code or create this event before calling it.";
        parent::__construct($message, $code, $previous);
    }
}

class WrongRoleForEventException extends Exception {
    public function __construct($event_name, $role_name, $code = 0, Exception $previous = null) {
        $message = "The event '$event_name' can only be called by someone with the $role_name role.";
        parent::__construct($message, $code, $previous);
    }
}
