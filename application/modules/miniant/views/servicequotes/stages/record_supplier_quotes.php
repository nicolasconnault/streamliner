<?php foreach ($suppliers as $supplier) : ?>
    <div class="panel panel-info">
        <div class="panel-heading"><h3><?=$supplier['supplier_data']->name?></h3></div>
        <div class="panel-body">
            <div class="pull-right">
                <label>Set availability of all parts to:</label>
                <?php
               echo form_open(base_url().'miniant/servicequotes/servicequote/set_bulk_availability', array('id' => 'bulk_availability_form_'.$supplier['supplier_data']->id, 'class' => 'form-horizontal'));
               print_hidden_element(array('name' => 'supplier_id', 'default_value' => $supplier['supplier_data']->id));
               print_hidden_element(array('name' => 'servicequote_id', 'default_value' => $servicequote_id));
               print_dropdown_element(array(
                    'id' => 'bulk-change',
                    'options' => array(null => '-- Select one --', 'In stock' => 'In stock', 'Ex stock 7-10 working days' => 'Ex stock 7-10 working days', 'N/A' => 'N/A', 'Blank' => 'Blank'),
                    'name' => 'availability',
                    'label' => 'Set all to',
                    'extra_html' => array('onchange' => '$(this).parents(\'form\').submit()'),
                    'show_label' => false));
                ?>
            </div>
            <table id="servicequote_parts_<?=$supplier['supplier_data']->id?>" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Part type</th>
                        <th>Model</th>
                        <th>Unit cost inc. GST</th>
                        <th>Total cost inc. GST</th>
                        <th>Available
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parts as $part) : ?>
                        <tr>
                            <td><?=$part->quantity?></td>
                            <td><?=$part->part_name?></td>
                            <td><?=$part->part_number?></td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_supplier_quote"
                                data-value="<?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->unit_cost?>"
                                data-name="unit_cost"
                                data-pk="<?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->supplier_quote_id?>"
                                data-supplier_id="<?=$supplier['supplier_data']->id?>"
                                data-placeholder="Quoted price, use numbers only"
                                data-part_id="<?=$part->id?>"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->unit_cost?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_supplier_quote"
                                data-value="<?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->total_cost?>"
                                data-name="total_cost"
                                data-pk="<?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->supplier_quote_id?>"
                                data-supplier_id="<?=$supplier['supplier_data']->id?>"
                                data-placeholder="Quoted price, use numbers only"
                                data-part_id="<?=$part->id?>"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->total_cost?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_supplier_quote"
                                data-value="<?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->availability?>"
                                data-name="availability"
                                data-pk="<?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->supplier_quote_id?>"
                                data-supplier_id="<?=$supplier['supplier_data']->id?>"
                                data-placeholder="No, Yes or days (e.g. 7 days)"
                                data-part_id="<?=$part->id?>"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$supplier_parts[$supplier['supplier_data']->id][$part->id]->availability?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach ;?>

<br />
<?php if ($review_only) : ?>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/prepare_quote_requests/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/select_final_suppliers/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php else : ?>
    <a class="btn btn-primary" href="<?=base_url()?>miniant/servicequotes/servicequote/approve_supplier_quotes/<?=$servicequote_id?>">Approve quotes</a>
<?php endif; ?>
