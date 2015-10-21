<table cellpadding="8">
<tr><th width="400" style="background-color: #CCC;">Invoice #</th>
    <td><?=$servicequote_id ?></td></tr>
<tr><th width="400" style="background-color: #CCC;">Job #</th>
    <td>J<?=$order_id ?></td></tr>
<tr><th width="400" style="background-color: #CCC;">Date</th>
    <td><?=unix_to_human(time()); ?></td></tr>
<tr><th width="400" style="background-color: #CCC;">Client</th>
    <td><?=$client_details->account_name ?></td></tr>
<tr><th width="400" style="background-color: #CCC;">Email</th>
    <td><?=$client_details->email ?></td></tr>
<tr><th width="400" style="background-color: #CCC;">Job site address</th>
    <td><?=$client_details->job_site_address ?></td></tr>
<tr><th width="400" style="background-color: #CCC;">Quote valid until</th>
    <td><?=unix_to_human($valid_until) ?></td></tr>
</table>
<br /><br />
