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
var disabled_buttons = [];

$(function() {
    $('div.table-responsive button').unbind('click');
    $('.new_message_button').click(add_new_message_row);
    $('.save_new_message').click(save_message);
    $('.cancel_new_message').click(cancel_message);
    $('.messages-container').each(function(key, item) {
        var identifier = $(this).attr('data-identifier');
        refresh_messages_table(identifier);
    });

    $('div.messages-container').delegate('button.edit_message', 'click', function (event) {
        var identifier = $(this).attr('data-identifier');
        var message_id = get_message_id(this);
        $('#new_message_button_'+identifier).parent().hide();
        $('#new_message_row_'+identifier).show();
        $('#submit_button_'+identifier).attr('disabled', 'disabled');
        $('#new_message_row_'+identifier).insertBefore('#messagerow_'+message_id);
        $('#messagerow_'+message_id).remove();

        set_new_message_fields(message_id);
        toggle_buttons(identifier, true);
    });

});

function add_new_message_row(event) {
    var identifier = $(this).attr('data-identifier');
    $('#new_message_button_'+identifier).parent().hide();
    $('#new_message_row_'+identifier).show();
    $('#submit_button_'+identifier).attr('disabled', 'disabled');

    toggle_buttons(identifier, true);
}

function toggle_buttons(identifier, disabled) {
    if (disabled) {
        $('button,input,a').each(function(key, item) {

            if (!$(item).prop('disabled')) {
                $(item).prop('disabled', true);
                disabled_buttons.push(item);
            }
        });

    } else {
        $(disabled_buttons).each(function(key, item) {
            $(item).prop('disabled', false);
            disabled_buttons.splice(key, 1);
        });
    }

    $('button#save_new_message_'+identifier).prop('disabled', !disabled);
    $('button#cancel_new_message_'+identifier).prop('disabled', !disabled);
}

function save_message(event) {
    var identifier = $(this).attr('data-identifier');
    var document_id = $('#messages_'+identifier+' input[name=document_id]').val();
    var document_type = $('#messages_'+identifier+' input[name=document_type]').val();

    $.post(base_url+'messages/save_message',
        {
            document_id: document_id,
            document_type: document_type,
            message_id: $('input[name=message_id]').val(),
            message: $('#messages_'+identifier+' textarea[name=message]').val()
        },
    function (data, response, xhr) {
        print_message(data.message, data.type);
        if (data.type == 'danger') {
            $('#messages_'+identifier+' textarea[name=message]').parent().addClass('danger');
        } else {
            // empty_new_message_row(identifier);
            $('#messages_'+identifier+' input[name=message_id]').val('');
            $('#new_message_button_'+identifier).parent().show();
            $('#new_message_row_'+identifier).hide();
            $('#submit_button_'+identifier).removeAttr('disabled');
            refresh_messages_table(identifier);
        }
        toggle_buttons(identifier, false);
    },
    'json'
    );

}

function empty_new_message_row(identifier) {
    $('#messages_'+identifier+' textarea[name=message]').parent().removeClass('danger');
}

function cancel_message(event, identifier) {
    var identifier = $(this).attr('data-identifier');
    empty_new_message_row();
    $('#new_message_button_'+identifier).parent().show();
    $('#new_message_row_'+identifier).hide();
    $('#submit_button_'+identifier).removeAttr('disabled');
    toggle_buttons(identifier, false);
    refresh_messages_table(identifier);
}

function set_new_message_fields(message_id, callback) {
    $.post(base_url+'messages/get_data/'+message_id, function(data, response, xhr) {
            $('#messages_'+data.identifier+' textarea[name=message]').val(data.message);
            $('#messages_'+data.identifier+' input[name=message_id]').val(data.id);

            if (undefined !== callback) {
                callback();
            }
        }, 'json'
    );
}

function refresh_messages_table(identifier) {
    $('#messages_'+identifier+' tbody tr.messagerow').remove();

    if ($('#messages_'+identifier+' input[name=document_id]').val() == '') {
        return false;
    }

    var document_id = $('#messages_'+identifier+' input[name=document_id]').val();
    var document_type = $('#messages_'+identifier+' input[name=document_type]').val();

    $.post(base_url+'messages/get_messages',
        {
            document_id : document_id,
            document_type: document_type
        },
        function(data, response, xhr) {
            $('#messages_'+identifier+' tbody tr.messagerow').remove();

            $.each(data.messages, function (key, item) {
                var row = document.createElement('tr');
                var message = data.messages[key];
                var cells = '<td>'+message.date+'</td><td>'+message.author+'</td><td>'+message.message+'</td>';

                cells += '<td class="actions">';

                if (message.author_id == logged_in_user_id || has_capability('orders:deleteothermessages')) {
                    cells += '<button type="button" id="remove_message_'+identifier+'" data-identifier="'+identifier+'" class="btn btn-danger btn-sml btn-icon remove_message">';
                    cells += '<i class="fa fa-trash-o remove_message"></i>Remove</button>';
                }

                if (message.author_id == logged_in_user_id || has_capability('orders:editothermessages')) {
                    cells += '<button type="button" data-identifier="'+identifier+'" id="edit_message_'+identifier+'" class="btn btn-default btn-sml btn-icon edit_message"><i class="fa fa-pencil edit_message"></i>Edit</button>';
                }

                cells += '</td>';

                $(row).html(cells);
                $(row).addClass('messagerow');
                $(row).attr('id', 'messagerow_'+message.id);
                $('#messages_'+identifier+' tbody').prepend(row);

                $('button#remove_message_'+identifier).bind('click', function (event) {
                    var identifier = $(this).attr('data-identifier');
                    var message_id = get_message_id(this);
                    var document_id = $('#messages_'+identifier+' input[name=document_id]').val();

                    var document_type = $('#messages_'+identifier+' input[name=document_type]').val();

                    if (document_id > 0) {
                        var answer = confirm('Are you sure you want to remove this message from this '+document_type+'? This cannot be undone.');

                        if (answer == true) {
                            $.post(base_url+'messages/remove_message/'+message_id,
                                function(data, response, xhr) {
                                    print_message(data.message, data.type);
                                    cancel_message(null, identifier);
                                    refresh_messages_table(identifier);
                                },
                                'json'
                            );
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                });
            });
        },
        'json'
    );
}

function get_message_id(button) {
    if (matches = $(button).parents('tr.messagerow').attr('id').match(/messagerow_([0-9]*)/)) {
        return matches[1];
    } else {
        return null;
    }
}
