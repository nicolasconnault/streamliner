<?php echo form_open(base_url().'miniant/servicequotes/servicequote/approve_final_suppliers') ?>
<?php print_hidden_element(array('name' => 'servicequote_id', 'default_value' => $servicequote_id)); ?>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th colspan="4" style="text-align: center;">Parts</th>
            <?php foreach ($suppliers as $supplier) : ?>
                <th colspan="3" style="text-align: center;"><?=$supplier['supplier_data']->name?></th>
            <?php endforeach; ?>
            <th rowspan="2">Selected supplier</th>
        </tr>
        <tr>
            <th>Qty</th>
            <th>Part type</th>
            <th>Model</th>
            <th>Other info</th>
            <?php foreach ($suppliers as $supplier) : ?>
                <th style="border-left: 1px solid #CCC">Unit cost</th><th>Total cost</th><th style="border-right: 1px solid #CCC">Availability</th>
            <?php endforeach ?>
        </tr>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $part_id => $part_data) :
            $first_part = reset($part_data['suppliers']);
        ?>
            <tr>
                <td><?=$first_part->quantity?></td>
                <td><?=$first_part->part_name?></td>
                <td><?=$first_part->part_number?></td>
                <td><?=$first_part->description?></td>

                <?php foreach ($part_data['suppliers'] as $supplier_id => $part) : ?>
                    <td <?php if ($part->cheapest_unit_cost) echo 'class="cheapest"'; ?> style="border-left: 1px solid #CCC"><?=$part->unit_cost?></td>
                    <td <?php if ($part->cheapest_total_cost) echo 'class="cheapest"'; ?>><?=$part->total_cost?></td>
                    <td style="border-right: 1px solid #CCC"><?=$part->availability?></td>
                <?php endforeach; ?>

                <td>
                    <?php print_dropdown_element(array(
                        'name' => 'supplier_ids['.$part_id.']',
                        'show_label' => false,
                        'options' => $part_data['suppliers_dropdown'],
                        'render_static' => $review_only,
                        'static_displayvalue' => $part_data['suppliers_dropdown'][$part->supplier_contact_id],
                        'default_value' => $part->supplier_contact_id
                        ));?>
                </td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
<br />
<?php if ($review_only) : ?>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/record_supplier_quotes/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/prepare_client_quote/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php else : ?>
    <input type="submit" class="btn btn-primary" value="Approve final suppliers" name="approve" />
<?php endif; ?>
<?php echo form_close();?>
