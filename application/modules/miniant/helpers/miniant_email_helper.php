<?php

function email_op_managers($message) {
    $ci = get_instance();
    $ops_manager_role = $ci->role_model->get(array('name' => 'Operations Manager'), true);
    $emails = array();
    // Get list of op managers
    $ops_managers = $ci->role_model->get_users($ops_manager_role->id);

    foreach ($ops_managers as $ops_manager) {
        $emails[] = $ci->user_contact_model->get_by_user_id($ops_manager->id, USERS_CONTACT_TYPE_EMAIL, true, true, true);
    }

    $ci->email->clear(true);
    $ci->email->from($ci->setting_model->get_value('Ops manager email address'), 'Temperature Solutions', $ci->setting_model->get_value('Ops manager email address'));
    $ci->email->subject('Temperature Solutions: New SQ');
    $ci->email->message($message);
    $ci->email->to($emails);

    $email_object = clone($ci->email);

    if (ENVIRONMENT == 'demo') {
        $result = true;
    } else {
        $result = $ci->email->send();
    }

    if ($result) {
        $error_message = null;
    } else {
        $error_message = $ci->email->print_debugger();
    }

    $ci->email_log_model->log_message($email_object, __FILE__ . ' at line ' . __LINE__, $error_message, 'users', $ci->session->userdata('user_id'), 'contacts', null);
}
