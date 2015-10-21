<span>This document is a request for <?=$supplier->name?> to quote the following parts. Please email us your quotes including a price per item and a bulk price.</span>
<br /><br />
<table cellspacing="0" cellpadding="10" border="0">
    <thead>
        <tr><th width="10%">Quantity</th><th width="30%">Details</th><th width="30%">Model number</th><th width="30%">Other info</th></tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $i => $part) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
                <td width="10%"><?=$part->quantity?></td>
                <td width="30%"><?=$part->part_name?></td>
                <td width="30%"><?=$part->part_number?></td>
                <td width="30%"><?=$part->description?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<style type="text/css">

th {
    font-weight: bold;
    border-bottom: 1px solid #555;
}
</style>
