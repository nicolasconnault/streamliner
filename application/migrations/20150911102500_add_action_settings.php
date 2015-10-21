<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_action_settings extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `settings` ADD `field_type_id` INT(10) NOT NULL DEFAULT '1' AFTER `id`, ADD INDEX (`field_type_id`)");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `settings_field_types` (
            `id` int(10) unsigned NOT NULL,
              `field_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `creation_date` int(10) unsigned NOT NULL,
              `revision_date` int(10) unsigned NOT NULL,
              `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `settings_values` (
            `id` int(10) unsigned NOT NULL,
              `setting_id` int(10) unsigned NOT NULL,
              `value` text COLLATE utf8_unicode_ci NOT NULL,
              `description` text COLLATE utf8_unicode_ci,
              `creation_date` int(10) unsigned DEFAULT NULL,
              `revision_date` int(10) unsigned DEFAULT NULL,
              `status` enum('Active','Suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");

        $this->db->query(" ALTER TABLE `settings_field_types` ADD PRIMARY KEY (`id`); ");
        $this->db->query(" ALTER TABLE `settings_values` ADD PRIMARY KEY (`id`), ADD KEY `setting_id` (`setting_id`); ");
        $this->db->query(" ALTER TABLE `settings_field_types` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT; ");
        $this->db->query(" ALTER TABLE `settings_values` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT; ");
        $this->db->query("ALTER TABLE `settings` ADD `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `value`");
        $this->db->query("INSERT INTO `settings_field_types` (`id`, `field_type`, `creation_date`, `revision_date`, `status`) VALUES (NULL, 'text', NULL, NULL, 'Active'), (NULL, 'dropdown', NULL, NULL, 'Active'), (NULL, 'radio', NULL, NULL, 'Active'), (NULL, 'checkbox', NULL, NULL, 'Active'), (NULL, 'date', NULL, NULL, 'Active'), (NULL, 'number', NULL, NULL, 'Active')");

        $radio_field_type_id = $this->setting_field_type_model->add(array('field_type' => 'radio'));
        $this->setting_field_type_model->add(array('field_type' => 'dropdown'));
        $this->setting_field_type_model->add(array('field_type' => 'checkbox'));
        $this->setting_field_type_model->add(array('field_type' => 'text'));
        $this->setting_field_type_model->add(array('field_type' => 'date'));
        $this->setting_field_type_model->add(array('field_type' => 'number'));
        $this->setting_field_type_model->add(array('field_type' => 'textarea'));

        $action_menu_setting_id = $this->setting_model->add(array(
            'name' => 'Action icons grouped into menu',
            'value' => false,
            'description' => "If turned on, action icons will be grouped into a dropdown menu: requires one extra click, but takes a lot less room in the tables, which is great for mobile devices.",
            'field_type_id' => $radio_field_type_id
        ));
        $action_position_setting_id = $this->setting_model->add(array(
            'name' => 'Action column position',
            'value' => 'Right',
            'description' => "Sets the position of the Actions column in data tables.",
            'field_type_id' => $radio_field_type_id
        ));

        $default_value = $this->setting_value_model->add(array( 'setting_id' => $action_menu_setting_id, 'value' => 'No'));
        $this->setting_value_model->add(array( 'setting_id' => $action_menu_setting_id, 'value' => 'Yes'));
        $this->setting_model->edit($action_menu_setting_id, array('value' => $default_value));

        $default_value = $this->setting_value_model->add(array( 'setting_id' => $action_position_setting_id, 'value' => 'Right'));
        $this->setting_value_model->add(array( 'setting_id' => $action_position_setting_id, 'value' => 'Left'));
        $this->setting_model->edit($action_position_setting_id, array('value' => $default_value));
    }

    public function down() {
    }
}

