<table cellpadding="8" border="1">
    <thead>
        <tr>
            <th style="font-weight: bold" width="10%">Quantity</th>
            <th style="font-weight: bold" width="22%">Details</th>
            <th style="font-weight: bold" width="22%">Model number</th>
            <th style="font-weight: bold" width="22%">Other info</th>
            <th style="font-weight: bold" width="22%">Cost</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $i => $part) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
                <td width="10%"><?=$part->quantity?></td>
                <td width="22%"><?=$part->part_name?></td>
                <td width="22%"><?=$part->part_number?></td>
                <td width="22%"><?=$part->description?></td>
                <td align="right" width="22%"><?=currency_format($part->supplier_cost)?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><th colspan="4" align="right">TOTAL (inc. GST)</th><th align="right"><?=currency_format($total_cost)?></th></tr>
    </tfoot>
</table>

