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
class MY_Controller extends MX_Controller {
    /**
     * @var boolean $restricted Set this to false in your controllers to bypass the authentication check: non-logged-in users can view these pages.
     */
    public $restricted = true;
    public $public_uris = array();
    public $model_aliases = array('contact' => 'user');
    public $uri_level = 2; // TODO Figure this out programmatically
    public $module=null;

    function __construct($restricted=true) {
        parent::__construct();
        $this->restricted = $restricted;

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger">', '</div>');
        if ($this->restricted) {
            $this->check_auth();
        }

        // Turn this on to profile the app
        if (!IS_AJAX) {
            $this->output->enable_profiler(false);
        }

        if ($user_login = $this->user_login_model->get(array('session_id' => session_id()), true)) {
            $this->user_login_model->edit($user_login->id, array('last_page_load' => time()));
        }

        $this->setup_module_hooks();
    }

    function check_auth() {
        if (!$this->login_model->check_session()) {
            $this->session->set_userdata(array('previous_url' => base_url().$this->uri->uri_string()));
            add_message('Your session has expired. Please login again', 'warning');
            redirect(base_url().'login');
        }
    }

    /**
     * Generic delete function for DB objects. Looks up the URI segments to determine which model to load and use
     * @param int $id
     */
    function delete($id, $model_name=null) {
        if ($this->uri_level == 1) {
            $section = $this->inflector->singularize($this->uri->segment(1));
            $subsystem = '';
        } else {
            $subsystem = $this->uri->segment(1).'/';
            $section = $this->uri->segment(2);
        }

        if (!empty($model_name)) {
            $section = $model_name;
        }

        // If we have different controllers using datagrid for the same model, it uses a model alias
        if (array_key_exists($section, $this->model_aliases)) {
            $section = $this->model_aliases[$section];
        }
        if (file_exists(APPPATH . 'models/' . $subsystem.ucfirst($section).'_model.php')) {
            $this->load->model($subsystem.$section.'_model', 'my_model');
        } else {
            $this->load->model($section.'_model', 'my_model');
        }

        $result = $this->my_model->delete($id);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "$section $id was successfully deleted";
                $json->id = $id;
                $json->type = 'success';
            } else {
                $json->message = "$section $id could not be deleted";
                $json->id = $id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }

    public function setup_module_hooks() {
        // Browse through the application/modules folder
        $modules = scandir(APPPATH.'modules');
        $top_nav = array();
        $added_links = array();

        $tours = array();

        foreach ($modules as $module) {
            if (in_array($module, array('.', '..'))) {
                continue;
            }

            $has_nav_hooks = file_exists(APPPATH.'modules/'.$module.'/controllers/Nav_'.$module.'.php');
            $has_tour_hooks = file_exists(APPPATH.'modules/'.$module.'/controllers/Tour_'.$module.'.php');

            if ($has_nav_hooks) {
                $module_nav = modules::run($module.'/nav_'.$module.'/setup');

                foreach ($module_nav as $top_menu => $links) {
                    if (array_key_exists($top_menu, $top_nav)) {
                        foreach ($links as $link) {
                            $top_nav[$top_menu][] = $link;
                            $added_links[] = $link;
                        }
                    } else {
                        $top_nav[$top_menu] = $links;
                        foreach ($links as $link) {
                            $added_links[] = $link;
                        }
                    }
                }
            }

            if ($has_tour_hooks) {
                $module_tours = modules::run($module.'/tour_'.$module.'/setup');
                if (empty($tours[$module])) {
                    $tours[$module] = array();
                }

                foreach ($module_tours as $tour) {
                    $tours[$module][] = $tour;
                }
            }
        }

        if (!in_array('users/contact/browse', $added_links)) {
            $top_nav['Administration'][] = array('title' => 'Contacts', 'link' => "users/contact/browse", 'caps' => array('users:viewcontacts'), 'icon' => 'user');
        }
        if (!in_array('settings/browse', $added_links)) {
            $top_nav['Administration'][] = array('title' => 'Settings', 'link' => "settings/browse",  'caps' => array('site:viewsettings'), 'icon' => 'cogs');
        }
        if (!in_array('types/browse', $added_links)) {
            $top_nav['Administration'][] = array('title' => 'Types', 'link' => "types/browse",  'caps' => array('site:viewtypes'), 'icon' => 'cubes');
        }
        if (!in_array('users/user/browse', $added_links)) {
            $top_nav['User management'][] = array('title' => 'Staff', 'link' => "users/user/browse", 'caps' => array('users:viewcontacts'), 'icon' => 'user');
        }
        if (!in_array('users/role/browse', $added_links)) {
            $top_nav['User management'][] = array('title' => 'Roles', 'link' => "users/role/browse",  'caps' => array('users:viewroles'), 'icon' => 'key');
        }
        if (!in_array('users/capability/browse', $added_links)) {
            $top_nav['User management'][] = array('title' => 'Capabilities', 'link' => "users/capability/browse",  'caps' => array('users:viewcapabilities'), 'icon' => 'wrench');
        }

        $this->config->set_item('top_nav', $top_nav);
        $this->config->set_item('tours', $tours);
    }
}
