<?php
$static_displayvalue = (empty($attachment->filename_original)) ? '' : anchor($attachment->url, $attachment->filename_original, array('target' => '_blank')) . nbs(2);
echo form_open_multipart(base_url().'building/job_site_attachments/process_edit/', array('id' => 'job_site_attachment_edit_form', 'class' => 'form-horizontal'));
echo form_hidden('id', $job_site_attachment_id);
echo form_hidden('job_site_id', $job_site_id);

print_form_container_open();
print_input_element(array(
    'label' => 'Description',
    'name' => 'description',
    'size' => 30,
    'required' => false)
);
print_file_element(array(
    'label' => 'PDF Attachment',
    'name' => 'attachment',
    'render_static' => !has_capability('building:viewjobsites') || !empty($attachment->filename_original),
    'show' => true,
    'static_displayvalue' => $static_displayvalue,
    'required' => false,
));

print_submit_container_open();
print_submit_button();
print_cancel_button(base_url().'building/job_site_attachments/browse/html/'.$job_site_id);
print_submit_container_close();
print_form_container_close();
echo form_close();
?>
