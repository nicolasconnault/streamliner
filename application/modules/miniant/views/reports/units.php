<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <form method="post" action="<?=base_url()?>miniant/reports/units/merge">
            <input type="hidden" name="address_id" value="<?=$address_id?>" />
        <table class="table table-condensed table-striped table-hover" id="units-table">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Unit ID</th>
                    <th>Type</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Serial</th>
                    <th>Tenancy/Owner</th>
                    <th>Area serving</th>
                    <th>More info</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Select</th>
                    <th>Unit ID</th>
                    <th>Type</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Serial</th>
                    <th>Tenancy/Owner</th>
                    <th>Area serving</th>
                    <th>More info</th>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($units as $unit) : ?>
                <tr>
                    <td><input type="checkbox" name="unit_id[]" value="<?=$unit->id?>" /></td>
                    <td><?=$unit->id?></td>
                    <td><?=$unit->type?></td>
                    <td><?=$unit->brand?></td>
                    <td><?=$unit->model?></td>
                    <td><?=$unit->serial?></td>
                    <td><?=$unit->tenancy?></td>
                    <td><?=$unit->area_serving?></td>
                    <td><button type="button" class="btn btn-info unit-info" data-toggle="popover" title="More info"
                        data-attributes="{
                            'Date created' : '<?=unix_to_human($unit->creation_date)?>',
                            'Indoor Serial #': '<?=$unit->indoor_serial_number?>',
                            'Outdoor Serial #': '<?=$unit->outdoor_serial_number?>',
                            'Indoor Model': '<?=$unit->indoor_model?>',
                            'Outdoor Model': '<?=$unit->outdoor_model?>',
                            'Description': '<?=$unit->description?>',
                            'Filter Pad type': '<?=$unit->filter_pad_type?>',
                            'Pad size': '<?=$unit->pad_size?>'
                        }"
                    >More info</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="submit" class="btn btn-primary">Merge selected units</button></p>
        </form>
    </div>
</div>
