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
</div><!-- .col-md-12 -->
    </div><!-- .row -->
    <div id="demo-row" class="row">
    <?php if (ENVIRONMENT == 'demo') : ?>
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><?=get_title($title_options)?></div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>To fully explore this demo, follow these guided tours:</p>
                            <ol>
                               <li><button type="btn-info" id="tour-jobsite-start">Job sites</button></li>
                               <li>Tradies</li>
                               <li>Trades</li>
                               <li>Other features</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .row -->
    <script type="text/javascript">
//<[CDATA[
var tour_home = {
    id: "tour_home",
    steps: [
        {
            title: "Welcome to the Building Site Manager Demo!",
            content: "Choose one of these guided tours to learn how to use this app.",
            placement: "bottom",
            xOffset: '0px',
            arrowOffset: 'center',
            target: '#demo-row .col-md-6'
        },
    ]
};

$(function() {
    hopscotch.startTour(tour_home);
});

//]]>
</script>
    <?php endif; ?>

<div id="dashboard-widgets-row-1" class="row">
    <?php foreach (get_widgets() as $widget) : ?>
        <?php if ($widget->row == 1) { ?>
        <div class="col-lg-<?=$widget->width?>">
            <div class="panel panel-info">
                <div class="panel-heading"><h3><?=$widget->label?></h3></div>
                <div class="panel-body">
                    <?=$widget->get_html()?>
                </div>
            </div>
        </div>
        <?php } ?>
    <?php endforeach; ?>
</div><!-- /.row -->
<div id="dashboard-widgets-row-2" class="row">
    <?php foreach (get_widgets() as $widget) : ?>
        <?php if ($widget->row == 2) { ?>
        <div class="col-lg-<?=$widget->width?>">
            <div class="panel panel-info">
                <div class="panel-heading"><h3><?=$widget->label?></h3></div>
                <div class="panel-body">
                    <?=$widget->get_html()?>
                </div>
            </div>
        </div>
        <?php } ?>
    <?php endforeach; ?>
</div><!-- /.row -->
<div id="dashboard-widgets-row-3" class="row">
    <?php foreach (get_widgets() as $widget) : ?>
        <?php if ($widget->row == 3) { ?>
        <div class="col-lg-<?=$widget->width?>">
            <div class="panel panel-info">
                <div class="panel-heading"><h3><?=$widget->label?></h3></div>
                <div class="panel-body">
                    <?=$widget->get_html()?>
                </div>
            </div>
        </div>
        <?php } ?>
    <?php endforeach; ?>
</div><!-- /.row -->
