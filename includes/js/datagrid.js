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
/**
 * Global function for ajaxifying a html table, looking up and paginating data from a PHP page.
 * This supports text and select filters, as well as column sorts.
 * The function returns the ajaxtable object, so that it can be overridden in any way required.
 * Conventions used for this abstraction:
 * 1. filters are named (id) after the variable they send, so a variable "name" is sent by a "namefilter" input or select element.
 */
function setup_datagrid(ajaxurl, sortcolumns, filters, clickablerows, order, per_page, drawcallback, action_column_position) {
    // Apply the same class to each column's cell as the class of its heading (set up in CI view)
    var columnHeaders = [];
    $('#ajaxtable th').each(function() {
        columnHeaders.push($(this).attr('class'));
    });

    var ajaxtable = $('#ajaxtable').dataTable( {
        "processing": true,
        "serverSide": false,
        "stateSave": true,
        "stateDuration": 120,
        "pageLength": per_page,
        "lengthMenu": [ [10, 20, 50, -1], [10, 20, 50, "All"] ],
        "pagingType": "numbers",
        "order": [[1, 'asc']],
        "stripClasses": ['odd', 'even'],
        "dom": "<'row'lpi>" + "<'row'<'col-sm-12'tr>>" ,
        "jQueryUI": false,
        "ajax": {url: ajaxurl, type: 'POST'},
        // TODO Implement the following logic, which takes care of exports based on table contents
        /*
        "fnServerData": function ( sSource, aoData, fnCallback ) {

            // Add or update hidden form fields for export icons
            $.each(aoData, function(index, field) {
                var attributes = { type: 'hidden', name: field.name, value: field.value };
                if (undefined != $('#export_to_pdf')) {
                    if (($("#export_to_pdf input[name='"+field.name+"']")).length == 0) {
                        $('<input>').attr(attributes).appendTo('#export_to_pdf');
                    } else {
                        $("#export_to_pdf input[name='"+field.name+"']").attr('value', field.value);
                    }
                }
                if (undefined != $('#export_to_xml')) {
                    if ($("#export_to_xml input[name='"+field.name+"']").length == 0) {
                        $('<input>').attr(attributes).appendTo('#export_to_xml');
                    } else {
                        $("#export_to_xml input[name='"+field.name+"']").attr('value', field.value);
                    }
                }
                if (undefined != $('#export_to_csv')) {
                    if ($("#export_to_csv input[name='"+field.name+"']").length == 0) {
                        $('<input>').attr(attributes).appendTo('#export_to_csv');
                    } else {
                        $("#export_to_csv input[name='"+field.name+"']").attr('value', field.value);
                    }
                }
            });

            // Modify the link to the PDF file, so it uses the same sorting/filtering as the table
            if ($('a[class="pdf"]')) {
                var currentpdflink = $.urlParser.parse($('a[class="pdf"]').attr('href')).path;
                currentpdflink += '?';
                for (var attr in aoData) {
                    currentpdflink += aoData[attr].name + '=' + aoData[attr].value + '&';
                }
                $('a[class="pdf"]').attr('href', currentpdflink)
            }

			$.post( sSource, aoData, function (oTable) {
				// Do whatever additional processing you want on the callback, then tell DataTables
				fnCallback(oTable)
			}, 'json' );
		},
        */
        "rowCallback": function( row, data) {
            if (action_column_position == 'Left') {
                $(row).attr('id', 'row_'+data[1]);
            } else if (action_column_position == 'Right') {
                $(row).attr('id', 'row_'+data[0]);
            }

            if (clickablerows) {
                $(row).bind('mouseover', function() {
                    $(this).addClass('rowhover');
                });
                $(row).bind('mouseout', function() {
                    $(this).removeClass('rowhover');
                });

                // Instead of adding an onclick event for the entire row, do it per cell, making sure the actions cell or any cell containing a multiselect doesn't have one
                if (action_column_position == 'Left') {
                    $(row).children('td:first').each(function() {
                        $(this).addClass('actions');
                    });
                } else if (action_column_position == 'Right') {
                    $(row).children('td:last').each(function() {
                        $(this).addClass('actions');
                    });
                }

                $(row).children('td').each(function() {
                    if (!$(this).hasClass('actions') && $(this).find('.multiselect').size() == 0) {
                        $(this).bind('click', function() {
                            window.location = $('#'+$(row).attr('id')+" a[class~='edit']").attr('href');
                        });
                    }
                });
            }

            // Convert delete links to AJAX requests
            $(row).find('a[class="delete"]').bind('click', function(event) {
                event.preventDefault();
                $.getJSON(this.href, {}, function(result) {
                    print_message(result.message, result.type);
                    if (result.type == 'success') {
                        $(row).hide(200);
                    }
                });
            });
            return row;
        },
        "drawCallback": drawcallback, // This is built in application/views/datagrid.php
        "order": order,
        "initComplete": function(settings, json) {
            // Set hidden inputs for export icons
            if (undefined != $('#export_to_pdf')) {

            }
            $.each($('.multiselect'), function(key, item) {
                var callback_url = $(item).attr('data-callback');

                $(item).multiselect({
                    nonSelectedText: $(item).attr('data-nonSelectedText'),
                    onDropdownHide: function(event) {
                        $.post(base_url+callback_url, { values: $(item).val()}, function(data) {
                            print_message(data.message, data.type);
                        }, 'json');
                    }
                });
            });
        }
    });

    var table = ajaxtable.api();

    yacdf_columns = [];
    $.each(sortcolumns, function(key, item) {
        var filter_type = 'text';
        var filter_label = undefined;
        var column_data_type = 'text';

        item_name = $(table.column(key).header()).attr('class');
        if (item_name.match(/_date/)) {
            filter_type = 'range_date';
        } else {
            filter_label = 'Search';
        }

        if (item_name.match(/statuses/)) {
            filter_type = 'text';
            column_data_type = 'html';
        }

        if (item_name.match(/actions/)) {
            return;
        }

        if (item_name.match(/statuses/)) {
            yacdf_columns.push({
                column_number: key,
                filter_type: 'custom_func',
                filter_default_label: filter_label,
                filter_reset_button_text: false,
                column_data_type: column_data_type,
                custom_func: status_filter,
                custom_func_options: {test: 'yes'}
            });
        } else {
            yacdf_columns.push({
                column_number: key,
                filter_type: filter_type,
                filter_default_label: filter_label,
                filter_reset_button_text: false,
                column_data_type: column_data_type,
                html_data_type: 'text'
            });

        }
    });

    yadcf.init(table, yacdf_columns);

    return ajaxtable;
}

function status_filter(filterVal, columnVal, col_num, contents) {
    var text_value = '';
    $(contents).find('option[selected="selected"]').each(function(key, item) {
        text_value += $(item).html();
    });

    if (filterVal == '') {
        return true;
    }

    var re = new RegExp(filterVal, 'ig');
    return text_value.match(re);
}
