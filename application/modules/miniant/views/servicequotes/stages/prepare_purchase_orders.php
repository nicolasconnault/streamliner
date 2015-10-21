<?php echo form_open(base_url().'miniant/servicequotes/servicequote/process_purchase_orders', array('id' => 'prepare_purchase_orders_form')); ?>
<br />
<br />
<table id="servicequote_parts" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>Qty</th><th>Part type</th><th>Model</th><th>Other info</th><th>Photos to include</th>
            <?php foreach ($suppliers as $supplier) : ?>
                <th><?=$supplier->name?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parts as $part) : ?>
            <tr class="part-row">
                <td><?=$part->quantity?></td>
                <td><?=$part->part_name?></td>
                <td><?=$part->part_number?></td>
                <td><?=$part->description?></td>
                <td><?php $this->load->view('photo_gallery', array('photos' => $part->photos, 'id' => $part->id, 'checkboxes' => true, 'unique_id' => $part->id)) ?></td>
            <?php foreach ($suppliers as $supplier) : ?>
                <td>
                    <?php
                    $params = array(
                        'name' => "quoting_supplier_ids[$part->id][$supplier->id]",
                        'show_label' => false,
                        'value' => 1,
                        'class' => 'part-checkbox',
                        'checked' => !empty($supplier_quotes[$part->id][$supplier->id])
                        );

                    if (!empty($supplier_quotes[$part->id][$supplier->id]->request_sent_date) || $review_only) {
                        $params['disabled'] = true;
                        $params['title'] = "This quote request has already been sent, you cannot remove it now.";
                    }

                    print_checkbox_element($params);
                    ?>
                </td>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<br />
<?php if ($review_only) : ?>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/record_client_response/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/record_received_parts/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php else : ?>
    <input type="submit" class="btn btn-primary" value="Preview supplier purchase orders" name="preview" />
    <input type="submit" <?php if (!$this->servicequote_model->has_statuses($servicequote_id, array('PURCHASE ORDERS PREVIEWED'))) { ?> disabled="disabled" <?php } ?> class="btn btn-primary" value="Send supplier purchase orders" name="send" />
    <input type="hidden" name="servicequote_id" value="<?=$servicequote_id?>" />
    <?php $this->load->view('servicequotes/formatting_options'); ?>
<?php endif; ?>
<?php echo form_close(); ?>
