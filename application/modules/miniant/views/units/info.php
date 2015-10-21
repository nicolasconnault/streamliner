<table class="table table-condensed" style="width: 300px">
    <tr><th>Equipment type</th><td><?=$unit->type?></td></tr>
    <tr><th>Brand</th><td><?=$unit->brand?></td></tr>

    <?php if ($unit->type == 'Refrigerated A/C') { ?>
        <tr><th>Indoor Model</th><td><?=$unit->indoor_model?></td></tr>
        <tr><th>Indoor Serial #</th><td><?=$unit->indoor_serial_number?></td></tr>
        <tr><th>Outdoor Model</th><td><?=$unit->outdoor_model?></td></tr>
        <tr><th>Outdoor Serial #</th><td><?=$unit->outdoor_serial_number?></td></tr>
    <?php } else { ?>
        <tr><th>Model</th><td><?=$unit->model?></td></tr>
        <tr><th>Serial #</th><td><?=$unit->serial_number?></td></tr>
    <?php } ?>
</table>
