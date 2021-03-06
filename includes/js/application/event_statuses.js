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
$(function() {
    reload_status_table();
});

function reload_status_table() {
    $('#events_table tbody tr').remove();

    $.post(base_url+'events/get_statuses/'+event_id, function(data, response, xhr) {
        $.each(data.event_statuses, function(key, item) {
            add_status_event_row(item);
        });
    }, 'json');
}

function update_field(dropdown, fieldname) {
    var row = $(dropdown).parent().parent();
    status_event_id = $(row).attr('data-id');
    var state = $(row).find('select[name="state"]').val();
    var status_id = $(row).find('select[name="status_id"]').val();

    if (status_event_id > 0) {
        $.post(base_url+'events/update_status_event_field',
            { id: status_event_id, field: fieldname, value: $(dropdown).val() },
            function(data, response, xhr) {
                print_message(data.message, data.type);
            }, 'json'
        );
    } else {
        create_status_event(status_id, state);
        reload_status_table();
    }
    $('i[title="Add"]').parent().removeAttr('disabled');
}

function remove_status_event(status_event_id) {
    if (null != status_event_id) {
        $.post(base_url+'events/delete_status_event/'+status_event_id,
            function(data, response, xhr) {
                print_message(data.message, data.type);
            }, 'json'
        );
        reload_status_table();
    } else {
        $('tr[data-id="null"]').remove();
        $('i[title="Add"]').parent().removeAttr('disabled');
    }
}

function create_status_event(status_id, state) {
    $.post(base_url+'events/create_status_event/',
        { status_id: status_id, state: state, event_id: event_id},
        function(data, response, xhr) {
            print_message(data.message, data.type);
            reload_status_table();
        }, 'json'
    );
}

function add() {
    add_status_event_row();
    $('i[title="Add"]').parent().attr('disabled', 'disabled');
}

function add_status_event_row(status_event) {

    if (undefined == status_event) {
        var status_event = {
            id: null,
            status_id: null,
            state: 1
        };
    }

    var newrow = document.createElement('tr');
    if (null != status_event.id) {
        $(newrow).attr('data-id', status_event.id);
    } else {
        $(newrow).attr('data-id', 'null');
    }

    var statuscell = document.createElement('td');
    status_id_dropdown = $('#hidden_dropdowns select[name="status_id"]').clone().show().val(status_event.status_id);
    if (null == status_event.id) {
        $(status_id_dropdown).prepend('<option value="">-- Select a Status --</option>');
        $(status_id_dropdown).val('');
    }
    $(statuscell).html(status_id_dropdown);

    var valuecell = document.createElement('td');
    $(valuecell).html($('#hidden_dropdowns select[name="state"]').clone().show().val(status_event.state));

    var actioncell = document.createElement('td');
    $(actioncell).addClass('actions');

    $(actioncell).html('<button type="button" class="btn btn-danger btn-sml btn-icon" onclick="remove_status_event('+status_event.id+')"><i class="fa fa-trash-o remove_unit"></i>Remove</button>');

    $(newrow).append(statuscell);
    $(newrow).append(valuecell);
    $(newrow).append(actioncell);
    $('#events_table tbody').append(newrow);
}
