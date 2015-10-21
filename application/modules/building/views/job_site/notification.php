<p>Dear <?=$user->user_first_name?>,</p>

<p>A booking request has been <?php echo (empty($original_booking)) ? 'created' : 'updated'?> by <?=$this->user_model->get_name($this->session->userdata('user_id'))?>.</p>
<ul>
<li>Job site address: <?=$job_site_address?></li>
<li>Date: <?=unix_to_human($new_booking->booking_date)?></li>
<li>Message: <?=$new_booking->message?></li>
</ul>
<?=$this->config->item('signature')?>
