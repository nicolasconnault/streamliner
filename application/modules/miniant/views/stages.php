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
?>
<?php $this->load->view('template/header'); ?>
<?php $this->load->view('template/topbar'); ?>
<div id="left-menu-bar">
    <table><tr><td class="left-menu-trigger"><a class="left-menu-trigger"><i class="fa-navicon"></i></a></td></tr></table>
</div>
<div id="main-row" class="row">
    <div class="col-md-12 with-left-menu">
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
        <?php $this->load->view($content_view); ?>

    <?php if (!empty($office_messages)) { ?>
        <div id="office_messages" class="modal" tabindex="-1" role="dialog" arial-labelledby="office-messages-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="office-messages-label">Office notes</h4>
                    </div>
                    <div class="modal-body">
                        <?php foreach ($office_messages as $message) : ?>
                            <p><?=$message->message?></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    <?php } ?>
    </div>
</div>
<?php $this->load->view('template/footer'); ?>

<?php
    $this->load->view('stages/navigation', array('current_stage' => $current_stage, 'stages' => $stages, 'extra_param' => $extra_param, 'office_messages' => $office_messages));
?>

<script type="text/javascript">
//<![CDATA[
$(function() {
    // Fire jPanelMenu
    var jPM = $.jPanelMenu({
        menu: '#left-menu',
        trigger: '.left-menu-trigger',
        duration: 300,
        openPosition: '25%'
    });

    jPM.on();
});
//]]>
</script>
