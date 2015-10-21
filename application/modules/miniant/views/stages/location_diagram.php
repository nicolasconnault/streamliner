<?php
$first_unit = reset($units);
$selected_unit_id = $this->session->userdata('selected_unit_id');
$first_unit_id = reset($units)->id;
if (empty($selected_unit_id)) {
    $selected_unit_id = $first_unit_id;
}

$diagram_file = base_url()."application/modules/miniant/files/location_diagrams/$order->id.png";
$diagram_file_exists = file_exists(APPPATH . '/modules/miniant/files/location_diagrams/'.$order->id.'.png');
?>

<div class="panel panel-primary">
    <div class="panel-heading"><h3>Unit location: <?=$this->order_model->get_reference_id($order->id)?></h3></div>
    <div class="panel-body">
        <div class="panel panel-primary">
            <div class="panel-heading"><?=get_title(array('title' => 'Location diagram', 'help' =>
                "
                <ol>
                <li>clearly draw and identify the front and back of the building, and other features that will help locating the correct unit</li>
                <li>draw a box for each unit</li>
                <li>number each box according to the allocated unit</li>
                <li>save your diagram</li>
                <li>record the reference of the current unit in the Location Info section below the diagram</li>
                </ol>"))?>
                </div>
            <div class="panel-body">
                <?php if ($is_senior_technician) : ?>
                    <div class="editable-diagram" <?php if ($diagram_file_exists) { ?> style="display: none" <?php } ?>>
                        <canvas style="border:1px solid #777" class="canvas" width="700" height="400"></canvas><br />
                        <p>
                            <button class="clear-button btn btn-info btn-sm" style="color: #000;"><i class="fa fa-trash-o"></i>Clear diagram</button>
                            <button class="set-to-pen btn btn-sm btn-info" style="color: #000;"><i class="fa fa-pencil"></i>Pen</button>
                            <button class="set-to-eraser btn btn-sm btn-info" style="color: #000;"><i class="fa fa-eraser"></i>Eraser</button>
                            <button class="save-diagram btn btn-primary">Record diagram</button>
                        </p>
                    </div>

                    <div class="diagram" <?php if (!$diagram_file_exists) { ?> style="display: none" <?php } ?>>
                        <img src="<?=$diagram_file?>" style="width: 700px;height:400px" />

                        <p>
                            <button class="new-diagram btn btn-info btn-sm"><i class="fa fa-file"></i>Edit diagram</button>
                        </p>
                    </div>

                    <script type="text/javascript">
                    //<![CDATA[
                    $(function() {

                        var canvas = document.querySelector("canvas");
                        var signaturePad = new SignaturePad(canvas, {minWidth: 1, maxWidth: 1});

                        $('.clear-button').on('click', function() {
                            signaturePad.clear();
                        });

                        $('.set-to-pen').css('background-color', '#6BD0EE');

                        $('.set-to-eraser').on('click', function() {
                            signaturePad.minWidth = 15;
                            signaturePad.maxWidth = 15;
                            signaturePad.penColor = "rgb(255, 255, 255)";
                            $('.set-to-eraser').css('background-color', '#6BD0EE');
                            $('.set-to-pen').css('background-color', '#5BC0DE');
                        });

                        $('.set-to-pen').on('click', function() {
                            signaturePad.minWidth = 1;
                            signaturePad.maxWidth = 1;
                            signaturePad.penColor = "rgb(0, 0, 0)";
                            $('.set-to-pen').css('background-color', '#6BD0EE');
                            $('.set-to-eraser').css('background-color', '#5BC0DE');
                        });

                        $('.save-diagram').on('click', function() {
                            var dataurl = signaturePad.toDataURL();

                            $.post(base_url+'miniant/stages/location_diagram/record_diagnostic_diagram', {dataurl: dataurl, order_id: <?=$order->id?>, assignment_id: <?=$assignment->id?>}, function(data) {
                                print_message(data.message, data.type);
                                // Replace canvas and buttons with diagram image
                                var diagram_img = $('.diagram img');
                                $(diagram_img).attr('src',  base_url+'application/modules/miniant/files/location_diagrams/<?=$order->id?>.png?' + new Date().getTime());
                                $(diagram_img).css('width',  700);
                                $(diagram_img).css('height',  400);
                                $('.editable-diagram').hide();
                                $('.diagram').show();
                            }, 'json');
                        });

                        $('.new-diagram').on('click', function() {
                            $.post(base_url+'miniant/stages/location_diagram/get_diagnostic_diagram', {order_id: <?=$order->id?>}, function(data) {
                                $('.diagram').hide();
                                $('.editable-diagram').show();
                                signaturePad.fromDataURL(data.dataurl);
                            }, 'json');
                        });
                    });
                    //]]>
                    </script>
                <?php else : ?>
                <div class="diagram" <?php if (!$diagram_file_exists) { ?> style="display: none" <?php } ?>>
                    <img src="<?=$diagram_file?>" style="width: 700px;height: 400px" />
                </div>
                <?php if (!$diagram_file_exists) : ?>
                    <p>The senior technician has not yet drawn the location diagram for this unit.</p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel panel-primary">
            <div class="panel-heading"><?=get_title(array('title' => 'Location info', 'help' => 'Draw a diagram of this job site, including a number for each unit being worked on. Then save that reference number for each unit.'))?></div>
            <div class="panel-body">
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
                            <?php if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($unit->assignment_id, 'location_diagram')) : ?>
                                <div id="unit-<?=$unit->id?>" data-unit_id="<?=$unit->id?>" class="unit-content tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>The <?=$uncompleted_stage->stage_label?> for this unit must be completed first.</p>
                                            <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$unit->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div id="unit-<?=$unit->id?>" class="row tab-pane <?php if ($unit->id == $selected_unit_id) echo 'active'?>">
                                    <div class="col-md-12">
                                        <?php if ($is_senior_technician) : ?>
                                            <form method="post" action="<?=base_url()?>miniant/stages/location_diagram/process">
                                            <input type="hidden" name="assignment_id" value="<?=$unit->assignment->id?>" />
                                            <input type="hidden" name="order_id" value="<?=$unit->assignment->order_id?>" />
                                            <p><label for="location_token">Location reference #</label> <input size="1" type="text" name="location_token" value="<?=$unit->assignment->location_token?>" />
                                                <input type="submit" value="Record location reference" class="btn btn-primary" />
                                            </p>
                                            </form>
                                        <?php else : ?>
                                            <?php if (!$is_senior_technician) : ?>
                                                <?php if ($this->assignment_model->has_statuses($unit->assignment_id, array('LOCATION INFO RECORDED'))) : ?>
                                                    <p><label for="location_token">Location reference #</label> <span><?=$unit->assignment->location_token?></span></p>
                                                    <p><a href="<?=$this->workflow_manager->get_next_url()?>" class="btn btn-primary">Continue</a></p>
                                                <?php else : ?>
                                                    <p>Location information has not yet been recorded for this unit. Please refresh this page after the senior technician has finished drawing the diagram and recording the unit's location reference number.</p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif;?>

                                    </div>
                                </div> <!-- .row -->
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div> <!-- .panel-body -->
</div> <!-- .panel -->
<script type="text/javascript">
//<[CDATA[
var assignment_id = <?=$assignment->id?>;
$(function() {
    $('.unit-tab').on('click', function(event) {
        $.post(base_url+'miniant/stages/stage/set_selected_unit/'+$(this).attr('data-unit_id'));
    });
});
//]]>
</script>
