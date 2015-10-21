<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Packages
| 2. Libraries
| 3. Helper files
| 4. Custom config files
| 5. Language files
| 6. Models
|
*/

/*
| -------------------------------------------------------------------
|  Auto-load Packges
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/

$autoload['packages'] = array(APPPATH.'third_party');


/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in the system/libraries folder
| or in your application/libraries folder.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'session', 'xmlrpc');
*/

$autoload['libraries'] = array('session','flashdata', 'database', 'form_validation', 'encrypt', 'email', 'session', 'upload', 'Exceptions', 'Inflector', 'Migration');


/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['helper'] = array('url', 'file');
*/

$autoload['helper'] = array('url', 'form', 'email', 'html', 'regexp', 'flashdata', 'string', 'cache', 'log', 'top_nav', 'form_template', 'popover_form', 'constantfinder', 'capabilities', 'info', 'date', 'navmenu', 'breadcrumb', 'title', 'json', 'event', 'tabbed_form', 'status_icon', 'file', 'photo', 'time', 'migration', 'widget');


/*
| -------------------------------------------------------------------
|  Auto-load Config files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['config'] = array('config1', 'config2');
|
| NOTE: This item is intended for use ONLY if you have created custom
| config files.  Otherwise, leave it blank.
|
*/

$url = $_SERVER['HTTP_HOST'];
$domain = substr($url, 0, 3) == 'www' ? substr($url, 4) : $url;
$autoload['config'] = array('breadcrumb', 'email');

/*
| -------------------------------------------------------------------
|  Auto-load Language files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['language'] = array('lang1', 'lang2');
|
| NOTE: Do not include the "_lang" part of your file.  For example
| "codeigniter_lang.php" would be referenced as array('codeigniter');
|
*/

$autoload['language'] = array('constants');


/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['model'] = array('model1', 'model2');
|
*/

$autoload['model'] = array('users/user_model',
                           'users/role_model',
                           'users/user_contact_model',
                           'users/capability_model',
                           'users/user_login_model',
                           'account_model',
                           'login_model',
                           'event_model',
                           'type_model',
                           'message_model',
                           'address_model',
                           'contact_model',
                           'document_statuses_model',
                           'email_log_model',
                           'event_log_model',
                           'events_log_model',
                           'file_model',
                           'priority_level_model',
                           'setting_model',
                           'setting_field_type_model',
                           'setting_value_model',
                           'stage_model',
                           'status_log_model',
                           'status_model',
                           'status_event_model',
                           'street_type_model',
                           'workflow_model',
                           'workflow_stage_model',
                           'workflow_stage_stage_model',
                           'work_time_log_model',

                           );


/* End of file autoload.php */
/* Location: ./application/config/autoload.php */
