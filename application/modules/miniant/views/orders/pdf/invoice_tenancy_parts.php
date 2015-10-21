<table style="font-weight: normal; width: 98%" cellspacing="0" cellpadding="10" border="0">
    <thead>
        <tr>
            <th style="font-weight: bold" width="10%">Quantity</th>
            <th style="font-weight: bold" width="45%">Item</th>
            <th style="font-weight: bold" width="45%">Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $i => $part) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
                <td width="10%"><?=(empty($part->quantity)) ? '' : $part->quantity?></td>
                <td width="45%"><?=$part->part_name?></td>
                <td width="45%"><?=$part->description?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br />
<br />
