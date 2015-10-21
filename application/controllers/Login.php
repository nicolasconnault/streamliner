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
class Login extends MX_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
    }

    function index() {
        if ($this->login_model->check_session()) {
            redirect(base_url().'home');
        }
        $this->load->helper('secure_hash');

        $rules[] = array('field' => 'username', 'label' => 'Username', 'rules' => 'trim|required');
        $rules[] = array('field' => 'password', 'label' => 'Password', 'rules' => 'required');
        $this->form_validation->set_rules($rules);

        if (!$this->form_validation->run()) {
            if ($this->input->post()) {
                add_message('There was an error in the form, please check that all fields have been correctly entered', 'danger');
            }

            $pageDetails = array('title' => 'Login',
                                 'title_options' => array('title' => 'Login'),
                                 'content_view' => 'login_view',
                                 'body_classes' => 'login',
                                 'feature_type' => 'Streamliner Core',
                                 'base_url' => $this->config->item('base_url'));
            $this->load->view('template/default', $pageDetails);
        } else {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $this->complete_login($username, $password);
        }
    }

    public function reset_password() {
        $this->load->helper('secure_hash');
        $rules[] = array('field' => 'username', 'label' => 'Username', 'rules' => 'trim');
        $rules[] = array('field' => 'email', 'label' => 'Email', 'rules' => 'trim');
        $this->form_validation->set_rules($rules);

        $pageDetails = array('title' => 'Reset your password',
                             'top_title_options' => array('title' => 'Reset your password'),
                             'content_view' => 'reset_password',
                             'wide_layout' => true,
                             'body_classes' => 'reset_password',
                             'feature_type' => 'Streamliner Core',
                             'base_url' => $this->config->item('base_url'));

        if (!$this->form_validation->run()) {
            if ($this->input->post()) {
                add_message('There was an error in the form, please check that all fields have been correctly entered', 'danger');
            }
        } else {
            $username = $this->input->post('username');
            $email = $this->input->post('email');

            if (empty($username) && empty($email)) {
                add_message('Please enter a username or an email address.', 'danger');
                $this->load->view('template/default', $pageDetails);
                return;
            }
            if (!empty($username) && !empty($email)) {
                add_message('Please enter a username or an email address, not both.', 'danger');
                $this->load->view('template/default', $pageDetails);
                return;
            }

            if (!empty($username)) {
                $user = $this->user_model->get(array('username' => $username), true);
            }

            if (!empty($email)) {
                $user_id = $this->user_model->already_exists($email);
                $user = $this->user_model->get($user_id);
            }

            if (!empty($user->id)) {
                // Get email address, set a new password and send it to user's email address
                $to = (empty($email)) ? $this->user_contact_model->get_by_user_id($user->id, USERS_CONTACT_TYPE_EMAIL)->contact : $email;
                $new_password = substr(sha1(time()), 0, 8);
                $encrypted_password = create_hash($new_password);
                $this->user_model->edit($user->id, array('password' => $encrypted_password));
                $this->send_new_password_to_user($user, $to, $new_password);
            } else {
                add_message('The details you have entered do not match those in our records. Please try again or
                    send the <a href="mailto:nicolasconnault@gmail.com">administrator</a> a message to report
                    your problem.', 'danger');
                $this->load->view('template/default', $pageDetails);
                return;
            }

            $pageDetails = array('title' => 'New password sent',
                                 'content_view' => 'new_password_sent',
                                 'body_classes' => 'new_password_sent',
                                 'feature_type' => 'Streamliner Core',
                                 'base_url' => $this->config->item('base_url'));

        }
        $this->load->view('template/default', $pageDetails);
    }


    public function send_new_password_to_user($user, $to, $new_password) {
        $this->email->clear();
        $this->email->from($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Site name') . ' Admin');
        $this->email->subject("New password");
        $this->email->message($this->load->view('emails/new_password', compact('user', 'new_password'), true));
        $this->email->to($to);

        if (ENVIRONMENT == 'demo') {
            add_message('The new password was not sent because this is a demo');
            return true;
        } else {
            return $this->email->send();
        }
    }

    public function get_credentials() {
        $this->load->helper('secure_hash');

        $email = $this->input->post('email');
        $this->form_validation->set_rules('email', 'Email address', 'trim|required|valid_email');
        $success = $this->form_validation->run();

        if ($success) {

        } else {
            add_message('Please enter a valid email address.', 'danger');
            return $this->index();
        }

        // Create a new temporary user
        $user = new stdClass();
        $random_number = rand(999,99999);
        $user->username = 'demo_user_'.$random_number;
        $password = 'password_'.$random_number;
        $user->password = create_hash($password);
        $user->registerkey = md5($user->password);
        $user->first_name = 'Demo_'.$random_number;
        $user->last_name = 'User_'.$random_number;
        $user->type = 'demo';

        if (!($user_id = $this->user_model->add($user))) {
            add_message('Your demo account could not be created. Please contact <a href="mailto:director@smbstreamline.com.au"> our technical support for assistance</a>.', 'danger');
            redirect(base_url().'login');
        }

        $contact_data['user_id'] = $user_id;
        $contact_data['type'] = USERS_CONTACT_TYPE_EMAIL;
        $contact_data['contact'] = $email;
        $this->user_contact_model->add($contact_data);

        // Add the Manager role to this user
        $manager_role = $this->role_model->get(array('name' => 'Manager'), true);
        $this->user_model->assign_role($user_id, $manager_role->id);

        // Email to admin
        $this->email->clear();
        $this->email->from($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Site name') . ' Admin');
        $this->email->subject("Streamliner demo login credentials");
        $this->email->message($this->load->view('emails/new_login_credentials', compact('user', 'email'), true));
        $this->email->to($this->setting_model->get_value('Admin email address'));

        $result = $this->email->send();

        // Email to prospect
        $this->email->clear();
        $this->email->from($this->setting_model->get_value('Admin email address'), $this->setting_model->get_value('Site name') . ' Admin');
        $this->email->subject($this->setting_model->get_value('Site Name')." demo access");
        $this->email->message($this->load->view('emails/login_credentials', compact('user'), true));
        $this->email->to($email);

        $this->email->send();

        add_message('Your demo account has been created, and we have sent the login URL to your email address. You should receive them within the next few minutes');
        redirect(base_url().'login');
    }

    public function upgrade_passwords() {
        $config_key = $this->config->item('encryption_key');
        $this->load->library('encrypt');
        $this->load->helper('secure_hash');
        $users = $this->user_model->get();

        foreach ($users as $user) {
            $password = $this->encrypt->decode($user->password);
            $new_password = create_hash($password);
            $this->user_model->edit($user->id, array('password' => $new_password));
        }
    }

    public function guest($registerkey) {
        if ($user = $this->user_model->get(array('registerkey' => $registerkey, 'status' => 'Suspended'), true)) {
            add_message('Sorry, this URL has already been used once, please apply for a new URL by completing the form below.', 'warning');
            redirect(base_url());
        }
        if (!$user = $this->user_model->get(array('registerkey' => $registerkey, 'status' => 'Active'), true)) {
            add_message('Sorry, this URL was not recognised, please make sure you copied it correctly from the email we sent you.', 'warning');
            redirect(base_url());
        }

        reload_session_caps($user->id);
        $this->user_login_model->add(array('user_id' => $user->id, 'session_id' => session_id(), 'last_page_load' => time()));

        if ($user->type == 'demo') {
            $this->user_model->edit($user->id, array('status' => 'Suspended'));
        }

        add_message('Welcome to the demo of '.$this->setting_model->get_value('Site Name').'! Please follow the guided tours to learn your way around.');
        redirect(base_url().'home');

    }

    private function complete_login($username, $password) {

        if ($this->login_model->auth_user($username, $password)) {
            $this->user_login_model->add(array('user_id' => $this->session->userdata('user_id'), 'session_id' => session_id(), 'last_page_load' => time()));

            // If this is a demo user, suspend the account immediately: it will continue to work until their session expires
            $user = $this->user_model->get(array('username' => $username), true);

            if ($user->type == 'demo') {
                $this->user_model->edit($this->session->userdata('user_id'), array('status' => 'Suspended'));
            }

            redirect(base_url().'home');
        } else {
            redirect(base_url().'login');
        }

    }
}
?>
