<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_unit_fields extends CI_Migration {

    public function up() {
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_fan_motor_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `fan_motor_make`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `indoor_fan_motor_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `fan_motor_make`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_fan_motor_serial` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `fan_motor_make`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `indoor_fan_motor_serial` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `fan_motor_make`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `sheetmetal_duct_size` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");

        $this->db->query("ALTER TABLE `miniant_units` ADD `vsd_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `vsd_brand` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `vsd_serial` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `vsd` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `fire_damper_size` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `flexible_duct_size` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `diffusion_cushion_head_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `diffusion_cushion_head_size` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `diffusion_grille_face_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `diffusion_grille_face_size` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `outdoor_fan_motor_model`;");

        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_condensing_unit_serial` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_condensing_unit_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `indoor_evaporator_qty` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_condenser_qty` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_evaporator_qty` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_evaporator_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `outdoor_evaporator_serial` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `indoor_evaporator_model` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `indoor_evaporator_serial` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `temperature_application` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
        $this->db->query("ALTER TABLE `miniant_units` ADD `condensing_unit_brand` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `vsd_model`;");
    }

    public function down() {

    }
}
