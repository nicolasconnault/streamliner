<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">

    <?php
    if ($uncompleted_stage = $this->stage_conditions_model->get_first_uncompleted_and_required_stage($assignment->id, 'order_dowd')) : ?>
        <p>The <?=$uncompleted_stage->stage_label?> must be completed first.</p>
        <p><a class="btn btn-primary" href="<?=base_url()?>miniant/stages/<?=$uncompleted_stage->stage_name?>/index/<?=$uncompleted_stage->assignment_id?>"><?=$uncompleted_stage->stage_label?></a></p>
    <?php else: ?>

        <?php echo form_open(base_url().'miniant/stages/order_dowd/process/', array('id' => 'order_dowd_edit_form', 'class' => 'order_dowd_edit_form form-horizontal')); ?>
            <div>
        <?php
            $default_dowd_id = (empty($order->dowd_id)) ? $dowd->id : $order->dowd_id;
            $default_dowd_text = (empty($order->dowd_text)) ? $dowd->description : $order->dowd_text;

            print_hidden_element(array('name' => 'order_id', 'default_value' => $order->id));
            print_hidden_element(array('name' => 'assignment_id', 'default_value' => $assignment->id));
            print_dropdown_element(array(
                'name' => "dowd_id",
                'label' => 'DOWD template',
                'classes' => array('dowd_template'), 'options' => $dowds_dropdown,
                'default_value' => $default_dowd_id,
                'extra_html' => array('data-order_id' => $order->id),
                'required' => true));

            print_textarea_element(array(
                'placeholder' => 'Description',
                'label' => 'Description',
                'name' => "dowd_text",
                'default_value' => $default_dowd_text,
                'cols' => 55,'rows' => 5,
                'extra_html' => array('data-order_id' => $order->id),
                'required' => true));

            print_submit_container_open();
            echo form_submit('button', 'Save DOWD', 'id="submit_button" class="btn btn-primary"');
            print_form_container_close();
            echo '</div>';
            echo form_close();
            ?>
    <?php endif; ?>
    </div>
</div>
