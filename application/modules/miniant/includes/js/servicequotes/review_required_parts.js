$(function() {
    refresh_parts_table();

    $('button#new_part_button').popover({
        html: true,
        trigger: 'click',
        title: 'New part',
        content: function () {
            open_popovers.push(this);
            return $('#new_part_popup .content').html();
        }
    });
    $('button#new_part_button').on('shown.bs.popover', function() {
        setup_conditional_form_elements();
    });
});

function refresh_parts_table() {
    $('#servicequote_parts tbody tr.partrow').remove();

    if ($('input[name=servicequote_id]').val() == '') {
        return false;
    }

    var servicequote_id = $('input[name=servicequote_id]').val();
    if (undefined == servicequote_id || servicequote_id == 0) {
        return false;
    }


    $.post(base_url+'miniant/servicequotes/servicequote_ajax/get_parts/'+servicequote_id, function(data, response, xhr) {
        $('#servicequote_parts tbody tr.partrow').remove();

        if (!isNull(data.parts) && data.parts.length > 0) {
            $.each(data.parts, function (key, part) {

                var row = document.createElement('tr');
                $(row).attr('data-part_id', part.id);
                $(row).attr('data-quantity', part.quantity);
                $(row).attr('data-part_name', part.part_name);
                $(row).attr('data-part_type_id', part.part_type_id);
                $(row).attr('data-part_number', part.part_number);
                $(row).attr('data-description', part.description);

                var cells = '<td>'+part.quantity+'</td><td>'+part.part_name+'</td><td>'+part.part_number+'</td><td>'+part.description+'</td>';
                $(row).html(cells);

                $(row).append(get_actions_cell());

                $(row).addClass('partrow');
                $(row).attr('id', 'partrow_'+part.id);
                $('#servicequote_parts tbody').prepend(row);

                // Update the part_type dropdowns in case a new one was just added
                $.post(base_url+'miniant/servicequotes/servicequote_ajax/get_part_types_dropdown', { part_type_id: part.part_type_id }, function (data) {
                    var part_types_dropdown = null;

                    if (part.type == 'Refrigerated A/C') {
                        part_types_dropdown = $('select[name="part_type_id_ref"]');
                    } else if (part.type == 'Evaporated A/C') {
                        part_types_dropdown = $('select[name="part_type_id_evap"]');
                    } else {
                        part_types_dropdown = $('select[name="part_type_id"]');
                    }

                    part_types_dropdown.find('option').remove();
                    $.each(data, function(key, item) {
                        part_types_dropdown.prepend('<option value="'+key+'">'+item+'</option>');
                    });
                }, 'json');
            });
        } else {
            data.parts = [];
        }

    }, 'json');
}

function add_new_part_row() {
    $('#new_part_button').parent().hide();
    $('#new_part_row').show();
    $('#submit_button').attr('disabled', 'disabled');
}

function empty_new_part_row() {
    $('select[name=new_part_type_id]').val('').parent().removeClass('danger');
}

function cancel_part() {
    empty_new_part_row();
    $('#new_part_button').parent().show();
    // $('#new_part_row').hide();
    $('#submit_button').removeAttr('disabled');
    refresh_parts_table();
}

function remove_part(part_id) {

    var servicequote_id = $('input[name=servicequote_id]').val();

    if (servicequote_id > 0) {
        var answer = confirm('Are you sure you want to remove this part from this servicequote? This cannot be undone.');

        if (answer == true) {
            $.post(base_url+'miniant/servicequotes/servicequote_ajax/remove_part', { part_id: part_id, servicequote_id: servicequote_id }, function(data, response, xhr) {
                print_message(data.message, data.type, 'partstable');
                cancel_part();
                refresh_parts_table();
            }, 'json');
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function add_part(part) {
    var new_row = document.createElement('tr');

    $(new_row).attr('data-part_id', part.id);
    $(new_row).attr('data-quantity', part.quantity);
    $(new_row).attr('data-part_name', part.part_name);
    $(new_row).attr('data-part_type_id', part.part_type_id);
    $(new_row).attr('data-part_number', part.part_part_number);
    $(new_row).attr('data-description', part.description);

    $(new_row).append('<td>'+part.quantity+'</td><td>'+part.part_name+'</td><td>'+part.part_number+'</td><td>'+part.description+'</td>');

    $(new_row).append(get_actions_cell());

    $(new_row).addClass('partrow');
    $('tr#new_part_row').before(new_row);
    refresh_parts_table();
}


function submit_new_part() {

    errors_found = validate_popover_form($('#new_part_form'));

    if (errors_found.length == 0) {
        var part_type_id = $('#new_part_form select[name=new_part_type_id][disabled!="disabled"]').val();
        var new_part_type = null;

        if (undefined === part_type_id) {
            part_type_id = $('#autocomplete_part_type_id').val();
        }

        if (part_type_id.length == 0) {
            new_part_type = $('#autocomplete_part_type').val();
        }

        $.post(base_url+'miniant/servicequotes/servicequote_ajax/add_part', {
                servicequote_id: $('#new_part_form input[name=new_servicequote_id]').val(),
                part_type_id: part_type_id,
                new_part_type: new_part_type,
                quantity: $('#new_part_form input[name=new_quantity]').val(),
                part_number: $('#new_part_form input[name=new_part_number]').val(),
                description: $('#new_part_form textarea[name=new_description]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                add_part(data.part);
                $('button#new_part_button').popover('hide');
            },
            'json'
        );
    } else {
        error_msg = "Please check the following errors:\n";
        $(errors_found).each(function(key, item) {
            error_msg += "- "+item.message+"\n";
        });
        alert(error_msg);
    }
}

function get_actions_cell() {
    var actions_cell = document.createElement('td');
    $(actions_cell).addClass('actions');

    var delete_button = document.createElement('button');
    $(delete_button).addClass('btn').addClass('btn-danger').addClass('delete');
    $(delete_button).html('<i class="fa fa-trash-o"></i> Remove');
    $(delete_button).attr('type', 'button');
    $(delete_button).on('click', function() {
        var part_id = $(this).parent().parent().attr('data-part_id');
        remove_part(part_id);
    });

    var edit_button = document.createElement('button');
    $(edit_button).addClass('btn').addClass('btn-info').addClass('edit');
    $(edit_button).html('<i class="fa fa-pencil"></i> Edit');
    $(edit_button).attr('type', 'button');
    set_edit_button_popover(edit_button);

    var actions_cell = document.createElement('td');
    $(actions_cell).append(edit_button);
    $(actions_cell).append('&nbsp;');
    $(actions_cell).append(delete_button);
    return actions_cell;
}

function set_edit_button_popover(button) {
    $(button).popover({
        html: true,
        trigger: 'click',
        title: 'Edit part',
        placement: 'left',
        content: function () {
            open_popovers.push(this);
            var part = $(this).parent().parent();
            $('#edit_part_form input[name="part_id"]').attr('value', $(part).attr('data-part_id'));
            $('#edit_part_form select[name="part_type_id"] option[value="'+$(part).attr('data-part_type_id')+'"]').attr('selected', 'selected');
            $('#edit_part_form input[name="part_number"]').attr('value', $(part).attr('data-part_number'));
            $('#edit_part_form textarea[name="description"]').html($(part).attr('data-description'));
            $('#edit_part_form input[name="quantity"]').attr('value', $(part).attr('data-quantity'));
            return $('#edit_part_popup .content').html();
        }
    });

    $(button).on('shown.bs.popover', function() {
        setup_conditional_form_elements();
    });
}

function submit_edit_part() {

    errors_found = validate_popover_form($('#edit_part_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/servicequotes/servicequote_ajax/edit_part', {
                id: $('#edit_part_form input[name=part_id]').val(),
                servicequote_id: $('#edit_part_form input[name=servicequote_id]').val(),
                part_type_id: $('#edit_part_form select[name=part_type_id][disabled!="disabled"]').val(),
                quantity: $('#edit_part_form input[name=quantity]').val(),
                part_number: $('#edit_part_form input[name=part_number]').val(),
                description: $('#edit_part_form textarea[name=description]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);

                refresh_parts_table();
            },
            'json'
        );
    } else {
        error_msg = "Please check the following errors:\n";
        $(errors_found).each(function(key, item) {
            error_msg += "- "+item.message+"\n";
        });
        alert(error_msg);
    }
};
