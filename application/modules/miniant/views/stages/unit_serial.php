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
?>
<?php
$first_unit = reset($units);
$address = $this->address_model->get_formatted_address($order->site_address_id);
$selected_unit_id = $this->session->userdata('selected_unit_id');
$first_unit_id = reset($units)->id;
if (empty($selected_unit_id)) {
    $selected_unit_id = $first_unit_id;
}

?>
<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <div class="message">Please record the serial number of each unit</div>

        <ul class="nav nav-tabs">
            <?php foreach ($units as $key => $unit) : ?>
                <li <?php if ($unit->id == $selected_unit_id) echo 'class="active"'?>>
                    <a class="unit-tab" href="#unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" data-toggle="tab"><?=$unit->tab_label?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tabbable">
            <div class="tab-content">
                <?php foreach ($units as $key => $unit) : ?>

                <div id="unit-<?=$unit->id?>" class="row tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                    <div class="col-md-12">
                    <?php

        $evap_unit_id = $this->unit_model->get_type_id('Evaporative A/C');
        $ref_unit_id = $this->unit_model->get_type_id('Refrigerated A/C');
        $trans_unit_id = $this->unit_model->get_type_id('Transport Refrigeration');
        $other_unit_id = $this->unit_model->get_type_id('Other refrigeration');
        $mech_unit_id = $this->unit_model->get_type_id('Mechanical services');

        echo form_open(base_url().'miniant/stages/unit_serial/process/', array('id' => 'unit_serial_edit_form', 'class' => 'form-horizontal'));
        echo form_hidden('assignment_id', $unit->assignment_id);
        echo form_hidden('unit_type_id', $unit->unit_type_id);
        print_form_container_open();
        print_dropdown_element(array(
            'label' => 'Equipment type',
            'name' => 'unit_type_id_'.$unit->assignment_id,
            'options' => $this->unit_model->get_types_dropdown(),
            'required' => true,
            'render_static' => true,
            'default_value' => $unit->unit_type_id,
            'static_displayvalue' => $this->unit_model->get_type_string($unit->unit_type_id),
        ));
        print_input_element(array(
            'label' => 'Area Serving',
            'name' => 'area_serving_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => true,
            'static_displayvalue' => $unit->area_serving,
        ));
        print_input_element(array(
            'label' => 'Brand',
            'name' => 'brand_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => true,
            'static_displayvalue' => $unit->brand,
        ));
        print_input_element(array(
            'label' => 'Serial Number',
            'name' => 'serial_number_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => false,
            'default_value' => $unit->serial_number,
            'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$evap_unit_id|$other_unit_id")
        ));
        print_input_element(array(
            'label' => 'Indoor Serial Number',
            'name' => 'indoor_serial_number_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => false,
            'default_value' => $unit->indoor_serial_number,
            'disabledif' => array('unit_type_id_'.$unit->assignment_id => "$other_unit_id|$trans_unit_id|$evap_unit_id")
        ));
        print_input_element(array(
            'label' => 'Indoor Serial Number',
            'name' => 'indoor_serial_number_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => false,
            'default_value' => $unit->indoor_serial_number,
            'disabledunless' => array('unit_type_id_'.$unit->assignment_id => $trans_unit_id)
        ));
        print_input_element(array(
            'label' => 'Outdoor Serial Number',
            'name' => 'outdoor_serial_number_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => false,
            'default_value' => $unit->outdoor_serial_number,
            'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$ref_unit_id")
        ));
        print_input_element(array(
            'label' => 'Outdoor Serial Number',
            'name' => 'outdoor_serial_number_'.$unit->assignment_id,
            'size' => '30',
            'required' => false,
            'render_static' => false,
            'default_value' => $unit->outdoor_serial_number,
            'disabledunless' => array('unit_type_id_'.$unit->assignment_id => "$trans_unit_id")
        ));
        print_submit_container_open();
        print_submit_button();
        print_submit_container_close();
        print_form_container_close();
        echo form_close();
                    ?>
                    </div>
                </div> <!-- .row -->
                <?php endforeach; ?>
            </div>
        </div>
    </div> <!-- .panel-body -->
</div> <!-- .panel -->
<script type="text/javascript">
//<[CDATA[
var assignment_id = <?=$assignment->id?>;
$(function() {
    $('.unit-tab').on('click', function(event) {
        $.post(base_url+'miniant/stages/unit_serial/set_selected_unit/'+$(this).attr('data-unit_id'));
    });
});
//]]>
</script>
