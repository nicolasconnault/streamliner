<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <form method="post" action="<?=base_url()?>miniant/reports/units/process_merge">
            <input type="hidden" name="address_id" value="<?=$address_id?>" />
            <input type="hidden" name="unit_id" value="<?=reset($units)->id?>" />
            <table class="table table-bordered">
                <thead>
                    <tr><th style="width: 20%">Fields</th>
                    <?php foreach ($units as $unit_id => $unit) : ?>
                        <?php if ($units[$unit_id] !== reset($units)) { ?>
                            <input type="hidden" name="unit_ids_to_delete[]" value="<?=$unit_id?>" />
                        <?php }?>

                        <th style="width: <?=round(80 / count($units))?>%">Unit <?=$unit_id?></th>
                    <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unit_fields as $unit_field => $unit_field_label) : ?>
                        <tr>
                            <th><?=$unit_field_label?></th>
                            <?php foreach ($units as $unit_id => $unit) : ?>
                            <td>
                                <?php if ($units[$unit_id] === reset($units)) {
                                    if (array_key_exists($unit_field, $unit_dropdowns[$unit_id])) {
                                        print_dropdown_element(array('options' => $unit_dropdowns[$unit_id][$unit_field], 'default_value' => $unit->$unit_field, 'name' => $unit_field, 'popover' => true));
                                    } else {
                                        print_input_element(array('name' => $unit_field, 'default_value' => $unit->$unit_field, 'popover' => true));
                                    }
                                } else {
                                    if (array_key_exists($unit_field, $unit_dropdowns[$unit_id]) && !empty($unit_dropdowns[$unit_id][$unit_field][$unit->$unit_field])) {
                                        echo $unit_dropdowns[$unit_id][$unit_field][$unit->$unit_field];
                                    } else {
                                        echo $unit->$unit_field;
                                    }
                                } ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to merge these units? Only the values on the left-most unit will be kept, all other data will be lost. This is not reversible!');">Merge</button>
        </form>
    </div>
</div>
