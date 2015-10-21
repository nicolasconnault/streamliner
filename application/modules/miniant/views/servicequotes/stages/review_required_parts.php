<table id="servicequote_parts" class="table table-bordered table-striped table-hover">
    <thead>
        <tr><th>Qty</th><th>Part type</th><th>Model</th><th>Other info</th><?php if (!$review_only) { ?><th style="width: 200px">Actions</th><?php } ?></tr>
    </thead>
    <tbody>
        <?php if (!$review_only) : ?>
        <tr id="new_part_row">
            <td colspan="5">
            <button class="btn btn-success" type="button" id="new_part_button">Add a part</button>
            </td>
        </tr>
        <?php else : ?>
            <?php foreach ($parts as $part) : ?>
            <tr>
                <td><?=$part->quantity?></td>
                <td><?=$part->part_name?></td>
                <td><?=$part->part_number?></td>
                <td><?=$part->description?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php $this->load->view('servicequotes/stages/review_required_parts_popovers'); ?>
<br />
<?php if ($review_only) : ?>
<a href="<?=base_url()?>miniant/servicequotes/servicequote/select_suppliers/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php else : ?>
<a class="btn btn-primary" href="<?=base_url()?>miniant/servicequotes/servicequote/approve_required_parts/<?=$servicequote_id?>">Approve parts</a>
<?php endif; ?>
