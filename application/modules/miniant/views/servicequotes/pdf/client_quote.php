<br />
<table style="width: 98%" cellspacing="0" cellpadding="10" border="0">
    <thead>
        <tr>
            <th width="9%">Quantity</th>
            <th width="24%">Item</th>
            <th width="24%">Notes</th>
            <th width="15%">Available</th>
            <th width="15%">Unit price</th>
            <th width="15%">Sub-total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $i => $part) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
                <td width="9%"><?=$part->quantity?></td>
                <td width="24%"><?=$part->part_name?></td>
                <td width="24%"><?=$part->client_notes?></td>
                <td width="15%"><?=$part->availability?></td>
                <td style="text-align: right;" width="15%"><?=currency_format($part->client_cost / $part->quantity)?></td>
                <td style="text-align: right;" width="15%"><?=currency_format($part->client_cost)?></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($custom_parts as $i => $part) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
                <td width="9%"><?=$part->quantity?></td>
                <td width="24%"><?=$part->part_name?></td>
                <td width="24%"><?=$part->client_notes?></td>
                <td width="15%"><?=@$part->availability?></td>
                <td style="text-align: right;" width="15%"><?=currency_format($part->client_cost / $part->quantity)?></td>
                <td style="text-align: right;" width="15%"><?=currency_format($part->client_cost)?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" style="text-align: right;">TOTAL (inc. GST):</th>
            <td style="text-align: right;"><?=currency_format($total_cost)?></td>
        </tr>
    </tfoot>
</table>
<style type="text/css">

th {
    font-weight: bold;
    border-bottom: 1px solid #555;
}
</style>
<br />
<br />
