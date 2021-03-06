$(function() {
    $('table.refrigerant-table').each(function(key, table) {
        var assignment_id = $(table).attr('data-assignment_id');
        refresh_refrigerants(assignment_id);
        $(table).delegate('#add-button', 'click', function(event) {
            add_refrigerant(assignment_id);
        });

        $(table).delegate('.delete-button', 'click', function(event) {
            if (deletethis()) {
                delete_refrigerant($(this).attr('data-assignment_refrigerant_id'), assignment_id);
            }
        });
    });
});

function refresh_refrigerants(assignment_id) {
    $.post(base_url+'miniant/stages/refrigerants_used/get_refrigerants_used', { assignment_id: assignment_id }, function(data) {
        empty_refrigerants(assignment_id);

        $.each(data.refrigerants_used, function(key, item) {
            var row = $('<tr class="refrigerant-row">' +
                '<td>' + item.reclaimed_text + '</td>' +
                '<td>' + item.refrigerant_type + '</td>' +
                '<td>' + item.quantity_kg + '</td>' +
                '<td>' + item.quantity_g + '</td>' +
                '<td>' + item.bottle_serial_number + '</td>' +
                '<td class="actions">' +
                    '<button type="button" data-assignment_refrigerant_id="'+item.id+'" class="btn btn-danger delete-button">Delete</button>' +
                '</td>' +
                '</tr>');
            $('table.refrigerant-table[data-assignment_id='+assignment_id+'] tbody').prepend(row);
        });
    }, 'json');
}

function empty_refrigerants(assignment_id) {
    $('table.refrigerant-table[data-assignment_id='+assignment_id+'] tr.refrigerant-row').remove();
}

function add_refrigerant(assignment_id) {
    var add_row = $('table.refrigerant-table[data-assignment_id='+assignment_id+'] tbody tr:last-child');
    params = {
        assignment_id: assignment_id,
        refrigerant_type_id: $(add_row).find('select[name=refrigerant_type_id]').val(),
        reclaimed: $(add_row).find('select[name=used_or_reclaimed]').val(),
        quantity_kg: $(add_row).find('input[name=quantity_kg]').val(),
        quantity_g: $(add_row).find('input[name=quantity_g]').val(),
        bottle_serial_number: $(add_row).find('input[name=serial_number]').val()
    };

    $.post(base_url+'miniant/stages/refrigerants_used/add_refrigerant_used', params, function(data) {
        print_message(data.message, data.type);
        if (data.type == 'success') {
            refresh_refrigerants(assignment_id);
        }
    }, 'json');
}

function delete_refrigerant(assignment_refrigerant_id, assignment_id) {

    $.post(base_url+'miniant/stages/refrigerants_used/delete_refrigerant_used/'+assignment_refrigerant_id, function(data) {
        print_message(data.message, data.type);
        refresh_refrigerants(assignment_id);
    }, 'json');
}
