<?php
echo form_open_multipart(base_url().'miniant/servicequotes/servicequote/process_client_response', array('id' => 'client_response_edit_form', 'class' => 'form-horizontal'));
echo '<div class="">';
echo form_hidden('servicequote_id', $servicequote_id);
print_form_container_open();

print_dropdown_element(array(
    'label' => 'Response',
    'name' => 'client_response',
    'required' => true,
    'render_static' => $review_only,
    'options' => array('On hold' => 'On hold', 'Accepted' => 'Accepted', 'Rejected' => 'Rejected')
));
print_textarea_element(array(
    'label' => 'Notes',
    'name' => 'client_response_notes',
    'render_static' => $review_only,
    'required' => false,
));

$static_displayvalue = (empty($attachment->filename_original)) ? '' : anchor($attachment->url, $attachment->filename_original, array('target' => '_blank')) . nbs(2);
$static_displayvalue .= anchor(base_url().'miniant/servicequotes/servicequote/delete_attachment/'.$servicequote_id, '<i class="fa fa-trash-o" onclick="return deletethis();" title="Delete this attachment?"></i>');
print_file_element(array(
    'label' => 'Attachment',
    'name' => 'attachment',
    'show' => true,
    'render_static' => !empty($attachment->filename_original) || $review_only,
    'static_displayvalue' => $static_displayvalue,
    'required' => false,
));
?>

<?php if ($review_only) { ?>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/prepare_client_quote/<?=$servicequote_id?>/1" class="btn btn-primary"><i class="fa fa-step-backward"></i>Previous</a>
    <a href="<?=base_url()?>miniant/servicequotes/servicequote/record_received_parts/<?=$servicequote_id?>/1" class="btn btn-primary">Next <i class="fa fa-step-forward"></i></a>
<?php } else {
    print_submit_container_open();
    print_submit_button('Save client response');

    print_submit_container_close();
}
print_form_container_close();
echo '</div>';
echo form_close();
