<table cellpadding="8">
<tr>
    <td rowspan="4" width="50%" border="1">ATTENTION: <?=$supplier->contact?><br /><?=$supplier->name?><br /><br /> <?=str_replace(',','<br />', $supplier->address)?> <br /><br />Supplier ABN: <?=$supplier->abn?></td>
    <th style="font-weight: bold" width="25%" border="1" align="right">Purchase Order Number</th>
    <td width="25%" border="1"><?php if (!empty($purchase_order)) echo $purchase_order->id ?></td>
</tr>
<tr>
    <th style="font-weight: bold" width="25%" border="1" align="right">Job Number</th><td border="1" width="25%"><?=$order_id?></td>
</tr>
<tr>
    <th style="font-weight: bold" width="25%" border="1" align="right">Date</th><td border="1" width="25%"><?=unix_to_human(time())?></td>
</tr>
<tr>
    <td width="50%" colspan="2" border="1">TEMPERATURE SOLUTIONS<br />PO BOX 280<br />SOUTH FREMANTLE<br />WA 6162</td>
</tr>
</table>
<br /><br />
