<?php echo form_open(base_url().'miniant/servicequotes/servicequote/approve_suppliers', array('id' => 'select_suppliers_form')); ?>

<table id="servicequote_suppliers" class="table table-bordered table-striped table-hover">
    <thead>
        <tr><th>Name</th><th>Contact</th><th>Email</th><?php if (!$review_only) { ?><th style="width: 50px">Select</th><?php } ?></tr>
    </thead>
    <tbody>
        <?php foreach ($suppliers as $supplier) : ?>
        <tr>
            <td><?=$supplier->name?></td>
            <td><?=$supplier->contact?></td>
            <td><?=$supplier->email?></td>
            <td><?php print_checkbox_element(array('show_label' => false, 'name' => 'supplier_contact_id['.$supplier->id.']', 'value' => 1, 'checked' => $supplier->selected));?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<br />
<?php if ($review_only) : ?>
<a href="<?=base_url()?>miniant/servicequotes/servicequote/review_required_parts/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
<a href="<?=base_url()?>miniant/servicequotes/servicequote/prepare_quote_requests/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php else : ?>
<input type="submit" class="btn btn-primary" value="Approve suppliers" />
<input type="hidden" name="servicequote_id" value="<?=$servicequote_id?>" />
<?php endif; ?>

<?php echo form_close(); ?>
