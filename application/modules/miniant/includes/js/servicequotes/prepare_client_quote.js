$(function() {
    var servicequote_id = $('input[name=servicequote_id]').val();
    toggle_preview_button();

    $('#new-client-part').editable({
        ajaxOptions: {
            type: 'POST',
            dataType: 'json'
        },
        params: {
            servicequote_id: servicequote_id
        },
        success: function(response, new_value) {
            if(!response.success) {
                return response.msg;
            } else {
                remove_previewed_status();
                window.location = base_url + 'miniant/servicequotes/servicequote/prepare_client_quote/' + servicequote_id;
                return false;
            }
        }
    });

    $('#description-of-work').editable({
        ajaxOptions: {
            type: 'POST',
            dataType: 'json'
        },
        autotext: 'never',
        success: function(response, new_value) {
            if(!response.success) {
                return response.msg;
            } else {
                remove_previewed_status();
            }
        },
        display: function(value, response) {
            $(this).html(value.replace(/\r?\n/g, '<br />'));
        }
    });

    $('.editable').each(function(key, field) {
        $(field).editable({
            ajaxOptions: {
                type: 'POST',
                dataType: 'json'
            },
            display: function(value, response) {
                if (value.length > 0) {
                    var float_value = parseFloat(value);
                    if ($(this).attr('data-name') == 'part_name' || $(this).attr('data-name') == 'client_notes') {
                        $(this).html(value.replace(/\r?\n/g, '<br />'));
                    } else if ($(this).attr('data-name') == 'quantity') {
                        $(this).html(value);
                    } else {
                        $(this).html('$' + float_value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    }
                } else {
                    $(this).html('');
                }
            },
            success: function(response, new_value) {
                if (new_value.length > 0) {
                    $(this).parent().attr('data-ready', 'yes');
                } else {
                    $(this).parent().attr('data-ready', 'no');
                }

                if(!response.success) {
                    return response.msg;
                } else {
                    toggle_preview_button();
                    remove_previewed_status();
                }
            }
        });
    });

    $('#prepare_client_quote_form input[name=preview]').on('click', function(event) {
        $('#prepare_client_quote_form input[name=send]').prop('disabled', false);
    });

    $('#prepare_client_quote_form').on('submit', function(event) {

        if ($(event.originalEvent.explicitOriginalTarget).attr('name') == 'send') {
            if (!confirm('Are you sure you are ready to email this service quotation to the client?')) {
                event.preventDefault();
            }
        }
    });

    $('.btn.abbreviation').click(function(event) {
        event.preventDefault();
        description = $(this).attr('data_description');
        $('#description-of-work').editable('setValue', $('#description-of-work').editable('getValue', true) + "\n" + description, true);
        $('#description-of-work').editable('submit');
    });
});

function toggle_preview_button() {
    var all_parts_priced = true;
    $('.editable').each(function(key, item) {
        if ($(item).parent().attr('data-ready') == 'no') {
            all_parts_priced = false;
        }
    });

    if (all_parts_priced) {
        $('#preview-client-quote').prop('disabled', false);
    } else {
        $('#preview-client-quote').prop('disabled', true);
    }
}

function remove_previewed_status() {
    $('#prepare_client_quote_form input[name=send]').prop('disabled', true);
    $.post(base_url+'events/undo_event', { event_name: 'client_quote_previewed', system: 'servicequotes', document_id: $('input[name=servicequote_id]').val() , module: 'miniant'});
}

