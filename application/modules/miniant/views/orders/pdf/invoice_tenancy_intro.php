<table cellpadding="8">
<tr><th width="500" style="background-color: #CCC;">Invoice Number</th>
    <td><?=$invoice_tenancy->id ?></td></tr>
<tr><th width="500" style="background-color: #CCC;">Job Number</th>
    <td>J<?=$order->id ?></td></tr>
<tr><th width="500" style="background-color: #CCC;">Customer PO Number</th>
    <td><?=$order->customer_po_number ?></td></tr>
<tr><th width="500" style="background-color: #CCC;">Date</th>
    <td><?=unix_to_human(time()); ?></td></tr>
<tr><th width="500" style="background-color: #CCC;">Client</th>
    <td><?=$tenancy->name ?></td></tr>
<tr><th width="500" style="background-color: #CCC;">Job site address</th>
    <td><?=$order->job_site_address ?></td></tr>
</table>
<br /><br />
