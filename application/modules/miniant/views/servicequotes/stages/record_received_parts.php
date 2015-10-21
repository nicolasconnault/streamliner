<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th>Qty</th>
            <th>Part type</th>
            <th>Model</th>
            <th>Other info</th>
            <th>Supplier</th>
            <th>PO Number</th>
            <th>Received Date</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $part) : ?>
        <tr>
            <td><?=$part->quantity?></td>
            <td><?=$part->part_name?></td>
            <td><?=$part->part_number?></td>
            <td><?=$part->description?></td>
            <td><?=$part->supplier_name?></td>
            <td><?=$part->purchase_order_id?></td>
            <td
                data-url="miniant/servicequotes/servicequote_ajax/record_part_received_date"
                data-value="<?=unix_to_human($part->part_received_date)?>"
                data-name="part_received_date"
                data-pk="<?=$part->supplier_quote_id?>"
                data-title="Select a date"
                data-type="date"
                class="<?=($review_only) ? '' : 'editable'?>">
                <?=unix_to_human($part->part_received_date)?>
            </td>
            <td
                data-url="miniant/servicequotes/servicequote_ajax/record_part_received_note"
                data-value="<?=$part->part_received_note?>"
                data-name="part_received_note"
                data-pk="<?=$part->supplier_quote_id?>"
                data-title="Enter some notes"
                data-type="textarea"
                class="<?=($review_only) ? '' : 'editable'?>">
                <?=$part->part_received_note?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br />
<?php if ($review_only) : ?>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/record_client_response/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
<?php endif; ?>
<a href="<?=base_url()?>miniant/servicequotes/servicequote/browse" class="btn btn-primary">Return to SQs</a>
