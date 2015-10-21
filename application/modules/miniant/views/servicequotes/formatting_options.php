<br />
<button class="btn btn-info" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="margin-top: 15px">
      Formatting options
    </button>
    <div class="collapse" id="collapseExample">
      <div>
        <?php print_input_element(array('name' => 'margin_left', 'default_value' => 15, 'label' => 'Left margin', 'size' => 5)); ?>
        <?php print_input_element(array('name' => 'margin_right', 'default_value' => 15, 'label' => 'Right margin', 'size' => 5)); ?>
        <?php // print_input_element(array('name' => 'margin_top', 'default_value' => 66, 'label' => 'Top margin')); ?>
        <?php // print_input_element(array('name' => 'header_margin', 'default_value' => 5, 'label' => 'Header margin')); ?>
        <?php print_input_element(array('name' => 'footer_margin', 'default_value' => 10, 'label' => 'Footer margin', 'size' => 5)); ?>
      </div>
    </div>
