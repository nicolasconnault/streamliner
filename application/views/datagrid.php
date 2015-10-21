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
<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($report_title_options)?></div>
    <div id="<?=$uri_segment_1?>table">
        <table id="ajaxtable" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
        <?php foreach ($table_headings as $field => $label): ?>
                    <th class="<?=$field?>"><?=$label?></th>

        <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
/*<![CDATA[ */
function add() {
    var prefix = ('<?=$uri_segment_1?>' == 'site') ? '<?=$uri_segment_2?>' : '<?=$uri_segment_1?>/<?=$uri_segment_2?>';
    if ('<?=$module?>') {
        prefix = '<?=$module?>/' + prefix;
    }

    url  = '<?=base_url()?>'+prefix+'/add';
    <?php if ($url_param !== false) : ?>
        url += '/<?=$url_param?>';
    <?php endif; ?>
    window.location = url;
}
<?=$datagrid_callbacks?>

$(document).ready(function() {
    var action_column_position = '<?=$action_column_position?>';

    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        <?php foreach ($columns as $field => $column) : ?>
            $('th.<?=$column->get_aliased_field()?>').css('width', '<?=$column->get_width()?>px');
        <?php endforeach;?>
        $('.actions').css('width', '120px');

        <?php if ($action_icons_menu) : ?>
            // Change the action dropdown to a dropup for the last 5 rows in the table, to prevent them from being clipped by the table edge
            oTable = object.oInstance;
            var api = this.api();
            var current_rows = api.rows( {page:'current'} ).data();

            // Output the data for the visible rows to the browser's console
            $.each(current_rows, function(key, item) {
                if (current_rows.length - key <= 5) {
                    var action_icon = (action_column_position == 'Left') ? item[0] : item.slice(-1)[0];
                    console.log(action_icon);
                    var matches = String(action_icon).match(/row-dropdown-([0-9]*)/);
                    $('#'+matches[0]).addClass('dropup');
                }
            });
        <?php endif; ?>
        // extra_datatable_setup(oTable);
    }

    var ajaxtable = setup_datagrid(<?=$ajaxtable_params?>, setup_table, '<?=$action_column_position?>');

    // post_datatable_setup(ajaxtable);
});

//]]>
</script>

