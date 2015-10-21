<table class="tbl" id="latest_bookings">
    <thead>
        <tr><th>Booking Date</th><th>Message</th><th>Job site</th><th>Trade</th><th>Tradesman</th><th>Confirmed</th></tr>
    </thead>
<?php foreach ($bookings as $booking) { ?>
    <tr>
        <td><?=unix_to_human($booking->booking_date)?></td>
        <td><?=$booking->message</td>
        <td><?=$booking->job_site_address</td>
        <td><?=$booking->trade</td>
        <td><?=$booking->tradesman_name</td>
        <td><?php echo ($booking->confirmed) ? 'Yes' : 'No'?></td>
    </tr>
<?php } ?>
</table>
