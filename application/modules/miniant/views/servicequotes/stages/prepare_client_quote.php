    <?php if (!$review_only) : ?>
    <?php echo form_open(base_url().'miniant/servicequotes/servicequote/process_client_quote', array('id' => 'prepare_client_quote_form')); ?>
    <input type="hidden" name="servicequote_id" value="<?=$servicequote_id?>" />
<?php endif; ?>
<div class="row">
    <div class="col-md-12">
    <div class="panel panel-info">
        <div class="panel-heading"><h3>Parts/Labour</h3></div>
        <div class="panel-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Part type</th>
                        <th>Model</th>
                        <th>Notes</th>
                        <th>Supplier cost inc. GST</th>
                        <th>Client cost inc. GST</th>
                        <th>Photos to include</th>
                        <th style="width: 90px">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parts as $part) : ?>
                        <tr data-ready="<?=($part->ready) ? 'yes' : 'no'?>" >
                            <td><?=$part->quantity?></td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->part_name?>"
                                data-name="part_name"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Supplier price, use numbers only"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->part_name?>
                            </td>
                            <td><?=$part->part_number?></td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->client_notes?>"
                                data-name="client_notes"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Notes"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->client_notes?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->supplier_cost?>"
                                data-name="supplier_cost"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Supplier price, use numbers only"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->supplier_cost?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->client_cost?>"
                                data-name="client_cost"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Client price, use numbers only"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->client_cost?>
                            </td>
                            <td><?php $this->load->view('photo_gallery', array('photos' => $part->photos, 'id' => $part->id, 'checkboxes' => true, 'unique_id' => $part->id)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($custom_parts as $part) : ?>
                        <tr data-ready="<?=($part->ready) ? 'yes' : 'no'?>" >
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-name="quantity"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Quantity"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->quantity?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->part_name?>"
                                data-name="part_name"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Supplier price, use numbers only"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->part_name?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->part_number?>"
                                data-name="part_number"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Part model number"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->part_number?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->client_notes?>"
                                data-name="client_notes"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Notes"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->client_notes?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->supplier_cost?>"
                                data-name="supplier_cost"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Supplier price, use numbers only"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->supplier_cost?>
                            </td>
                            <td
                                data-url="miniant/servicequotes/servicequote_ajax/record_client_quote_data"
                                data-value="<?=$part->client_cost?>"
                                data-name="client_cost"
                                data-pk="<?=$part->id?>"
                                data-placeholder="Client price, use numbers only"
                                class="<?=($review_only) ? '' : 'editable'?>">
                                <?=$part->client_cost?>
                            </td>
                            <td>N/A</td>
                            <td>
                                <?php if (empty($part->supplier_quote_id)) : ?>
                                <a href="<?=base_url()?>miniant/servicequotes/servicequote/delete_part/<?=$part->id?>" onclick="return deletethis();" title="Remove this part/labour from the client's service quotation">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                                <?php endif ;?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$review_only) : ?>
                    <tr>
                        <td colspan="7">
                            <button
                                data-url="miniant/servicequotes/servicequote_ajax/add_client_quote_part"
                                data-value=""
                                data-name="part_name"
                                data-pk=""
                                data-placeholder="Name of part/labour"
                                id="new-client-part" class="btn btn-info">
                                <i class="fa fa-plus"></i>New part/labour

                            </button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
        <div class="panel panel-info">
        <div class="panel-heading"><h3>Diagnostic</h3></div>
        <div class="panel-body">
            <p>Enter the amount of time and cost of diagnostic already performed by your technicians on this particular issue.</p>
           <?php

           print_input_element(array(
                'name' => 'diagnostic_time',
                'label' => 'Time spent on diagnostic (h)',
                'required' => true,
                'info_text' => 'Enter the time spent by the technician on diagnosing this issue only',
                'default_value' => $servicequote->diagnostic_time
                ));
           print_input_element(array(
                'name' => 'diagnostic_cost',
                'label' => 'Cost of the diagnostic',
                'required' => true,
                'info_text' => 'Enter the cost of the diagnostic already invoiced to the client',
                'default_value' => $servicequote->diagnostic_cost
                ));
           ?>

        </div>
    </div>
<div class="row">
<div class="col-md-12">
<div class="panel panel-info">
    <div class="panel-heading"><h3>Description of works</h3></div>
    <div class="panel-body">
        <h4>DOWD</h4>
        <ul>
            <?php foreach ($dowds as $dowd) : ?>
            <li><?=$dowd->dowd_text?></li>
            <?php endforeach; ?>
        </ul>
        <h4>Description of works</h4>
        <div id="abbreviations">
            <?php foreach ($abbreviations as $abbreviation) : ?>
            <?php $description = "$abbreviation->description"; ?>
                <button title="<?=$abbreviation->explanation?>" class="btn btn-default abbreviation"
                    data_description="<?=$description?>">
                    <?=$abbreviation->abbreviation?>
                </button>
            <?php endforeach; ?>
        </div>
        <div
            data-url="miniant/servicequotes/servicequote_ajax/record_description_of_work"
            data-value="<?=$servicequote->description_of_work?>"
            data-name="description_of_work"
            data-type="textarea"
            data-pk="<?=$servicequote_id?>"
            data-placeholder="Layman terms description of works to be done"
            id="<?=($review_only) ? '' : 'description-of-work'?>">
            <?=$servicequote->description_of_work?>
        </div>
    </div>
</div>
</div>
</div>
<?php if ($review_only) : ?>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/select_final_suppliers/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/record_client_response/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php else : ?>

    <input type="submit" id="preview-client-quote"class="btn btn-primary" value="Preview client's service quotation" name="preview" />
    <input type="submit" <?php if (!$this->servicequote_model->has_statuses($servicequote_id, array('CLIENT QUOTE PREVIEWED'))) { ?> disabled="disabled" <?php } ?> class="btn btn-primary" value="Send client's service quotation" name="send" />
    <?php $this->load->view('servicequotes/formatting_options'); ?>
    <?php echo form_close(); ?>
<?php endif; ?>
