Dear Ops Manager,

<?=$this->user_model->get_name($technician_id)?> has just created a new SQ
for <?=$order_type?> job J<?=$order->id?>.

You can view it <a href="<?=base_url().'miniant/servicequotes/servicequote/review_required_parts/'.$sq_id?>">here</a>.

Regards,

Miniant Automated Mailman
