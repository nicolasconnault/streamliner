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
var url_param = '<?=$renderer->url_param?>';
var archived = 1;
var cancelled = 2;

$(function() {

    var filter_div = document.createElement('div');
    $(filter_div).addClass('datagrid-filters');

    // Archived
    var archived_checkbox = document.createElement('input');
    $(archived_checkbox).attr('type', 'checkbox');
    if (!(url_param & archived)) {
        $(archived_checkbox).prop('checked', true);
    }
    $(archived_checkbox).attr('name', 'show_archived');
    $(archived_checkbox).val('1');
    $(archived_checkbox).attr('id', 'archived_checkbox');
    $(archived_checkbox).on('change', redirect_to_filtered_url);

    var archived_label = document.createElement('label');
    $(archived_label).html('Show ARCHIVED');
    $(archived_label).attr('for', 'archived_checkbox');

    $(filter_div).append(archived_label);
    $(filter_div).append(archived_checkbox);

    // Cancelled
    var cancelled_checkbox = document.createElement('input');
    $(cancelled_checkbox).attr('type', 'checkbox');

    if (!(url_param & cancelled)) {
        $(cancelled_checkbox).prop('checked', true);
    }
    $(cancelled_checkbox).attr('name', 'show_cancelled');
    $(cancelled_checkbox).val('1');
    $(cancelled_checkbox).attr('id', 'cancelled_checkbox');
    $(cancelled_checkbox).on('change', redirect_to_filtered_url);

    var cancelled_label = document.createElement('label');
    $(cancelled_label).html('Show CANCELLED');
    $(cancelled_label).attr('for', 'cancelled_checkbox');

    $(filter_div).append(cancelled_label);
    $(filter_div).append(cancelled_checkbox);

    $('.panel-heading h3').before(filter_div);
});

function redirect_to_filtered_url() {
    var bitmask = 0;
    if (!$('#cancelled_checkbox').is(':checked')) {
        bitmask += cancelled;
    }
    if (!$('#archived_checkbox').is(':checked')) {
        bitmask += archived;
    }
    window.location = base_url + 'miniant/servicequotes/servicequote/index/html/' + bitmask;
}
