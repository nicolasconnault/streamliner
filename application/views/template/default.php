<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */

$top_title_options = (isset($title_options)) ?  $title_options : $top_title_options;
?>
<?php $this->load->view('template/header'); ?>
<?php $this->load->view('template/topbar'); ?>

<div id="page-wrapper">
<div class="row">
    <div class="col-lg-12">
        <?=get_title($top_title_options + array('level' => 1))?>
    </div>
    <!-- /.col-lg-12 -->
</div>
<div class="row">
    <div class="col-lg-12">
        <?php if ($this->session->flashdata('message') || $this->session->userdata('message')) :
            $message = ($this->session->userdata('message')) ? $this->session->userdata('message') : $this->session->flashdata('message');
            $message_type = ($this->session->userdata('message_type')) ? $this->session->userdata('message_type') : $this->session->flashdata('message_type');
            clear_messages();
        ?>
            <div id="message" class="alert alert-dismissable alert-<?=$message_type?>" >
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <?=$message?></div>
            <div class="notifications top-left"></div>
        <?php else : ?>
            <div class="notifications top-left"></div>
        <?php endif; ?>
        <?php if (empty($no_breadcrumbs)) echo set_breadcrumb()?>
        <?php $this->load->view($content_view); ?>
    </div>
</div>
<?php $this->load->view('template/footer'); ?>
