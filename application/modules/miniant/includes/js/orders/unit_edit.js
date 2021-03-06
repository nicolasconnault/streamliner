$(function() {
    $('#new_part_button').click(add_new_part_row);
    $('#save_new_part').click(save_part);
    $('#cancel_new_part').click(cancel_part);

    $('input[name="submit"]').click(function(event) {
        event.preventDefault();
        var btn = $(this);
        btn.button('loading');

        $.post(
            base_url+'miniant/orders/unit/process_edit',
            {
                order_unit_id: $('input[name=order_unit_id]').val(),
                order_id: $('input[name="order_id"]').val(),
                location: $('input[name="location"]').val(),
                faults: $('input[name="faults"]').val(),
                brand_id: $('select[name="brand_id"]').val(),
                unit_type_id: $('select[name="unit_type_id"]').val(),
                model: $('input[name="model"]').val(),
                serial_number: $('input[name="serial_number"]').val(),
                outdoor_model: $('input[name="outdoor_model"]').val(),
                outdoor_serial_number: $('input[name="outdoor_serial_number"]').val(),
                indoor_model: $('input[name="indoor_model"]').val(),
                outdoor_model: $('input[name="outdoor_model"]').val(),
                electrical: $('input[name="electrical"]').val(),
                gas: $('input[name="gas"]').val(),
                kilowatts: $('input[name="kilowatts"]').val()
            },
            function(data, response) {
                print_message(data.message, data.type);

                if (data.type == 'success') {
                    $('input[name=order_unit_id]').val(data.order_unit_id);
                    $('#parts-section').show();
                }

                btn.button('reset');
            },
            'json'
        );
    });

    $('#cancel_button').click(function(event) {
        event.preventDefault();
        window.location = base_url+'miniant/orders/order/edit/'+$('input[name="order_id"]').val();
    });

    refresh_parts_table();
});

function refresh_parts_table() {
    $('#parts_table tbody tr.partrow').remove();

    if ($('input[name=order_unit_id]').val() == '') {
        return false;
    }

    var order_unit_id = $('input[name=order_unit_id]').val();

    $.post(base_url+'miniant/orders/unit_ajax/get_parts/'+order_unit_id, function(data, response, xhr) {
        $('#parts_table tbody tr.partrow').remove();

        $.each(data.parts, function (key, item) {
            var row = document.createElement('tr');
            var part = data.parts[key];
            var cells = '<td>'+part.name+'</td><td>'+part.quantity+'</td>';
            cells += '<td class="actions"><button type="button" class="btn btn-danger btn-sml btn-icon" onclick="remove_part('+part.id+')">';
            cells += '<i class="fa fa-trash-o remove_part"></i>Remove</button>&nbsp;';
            cells += '<button type="button" class="btn btn-default btn-sml btn-icon" onclick="edit_part('+part.id+')"><i class="fa fa-pencil edit_part"></i>Edit</button></td>';
            $(row).html(cells);
            $(row).addClass('partrow');
            $(row).attr('id', 'partrow_'+part.id);
            $('#parts_table tbody').prepend(row);
        });

    }, 'json');
}

function add_new_part_row() {
    $('#new_part_button').parent().hide();
    $('#new_part_row').show();
    $('#submit_button').attr('disabled', 'disabled');
}

function save_part(event) {
    var part_id = $('#new_part_row input[name="part_id"]').val();
    var order_unit_id = $('input[name=order_unit_id]').val();
    var action = (part_id > 0) ? 'edit_part' : 'add_part';

    $.post(base_url+'miniant/orders/unit_ajax/'+action,
        {
            order_part_id: part_id,
            order_unit_id: order_unit_id,
            part_type_id: $('select[name=part_type_id]').val(),
            quantity: $('input[name=quantity]').val()
        },
        function (data, response, xhr) {
            print_message(data.message, data.type);
            if (data.type == 'danger') {
                $.each(data.errors, function(key, error) {
                    $('select[name='+key+'], input[name='+key+']').parent().addClass('danger');
                });
            } else {
                empty_new_part_row();
                $('input[name=part_id]').val('');
                $('#new_part_button').parent().show();
                $('#new_part_row').hide();
                $('#submit_button').removeAttr('disabled');
                refresh_parts_table();
            }
        },
        'json'
    );
}

function empty_new_part_row() {
    $('select[name=part_type_id]').val('').parent().removeClass('danger');
    $('input[name=quantity]').val('').parent().removeClass('danger');
}

function cancel_part() {
    empty_new_part_row();
    $('#new_part_button').parent().show();
    $('#new_part_row').hide();
    $('#submit_button').removeAttr('disabled');
    refresh_parts_table();
}

function remove_part(order_part_id) {

    var order_unit_id = $('input[name=order_unit_id]').val();

    if (order_unit_id > 0) {
        var answer = confirm('Are you sure you want to remove this part from this Unit? This cannot be undone.');

        if (answer == true) {
            $.post(base_url+'miniant/orders/unit_ajax/remove_part', { order_part_id: order_part_id }, function(data, response, xhr) {
                print_message(data.message, data.type);
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

function edit_part(order_part_id) {
    add_new_part_row();
    $('#new_part_row input[name="part_id"]').val(order_part_id);
    $('#new_part_row').insertBefore('#partrow_'+order_part_id);
    $('#partrow_'+order_part_id).remove();

    set_new_part_fields(order_part_id);
}

function set_new_part_fields(order_part_id, callback) {
    $.post(base_url+'miniant/orders/unit/get_part_data/'+order_part_id, function(data, response, xhr) {
            $('select[name=part_type_id]').val(data.part_type_id);
            $('input[name=quantity]').val(data.quantity);

            if (undefined !== callback) {
                callback();
            }
        }, 'json'
    );
}
