<?php
if (!empty($booking->tradesman_type_id)) { ?>
    <h2 class="tradesman_type"><?=$this->tradesman_model->get_type_string($booking->tradesman_type_id)?></h2>
<?php }
if (!empty($booking->tradesman_id)) { ?>
    <p class="tradesman_name"><i class="fa fa-briefcase"></i> <?=$this->tradesman_model->get_name($booking->tradesman_id)?></p>
<?php } ?>
<p class="message"><?=$booking->message?></p>
<?php if (!empty($job_site)) { ?>
    <p class="address"><i class="fa fa-home"></i>
    <?=anchor(base_url().'building/job_sites/calendar/'.$booking->job_site_id,  $this->address_model->get_formatted_address($job_site, '[[unit]][[number]] [[street]] [[street_type_short]], [[city]]'))?></p>
<?php } ?>
