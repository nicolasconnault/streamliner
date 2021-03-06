$(function() {
    update_senior_technician_options();
    $('select[name="technician_id[]"]').on('change', add_or_remove_technician);

});

function add_or_remove_technician(event) {
    $(this).blur();
    var removed = event.removed;
    var added = event.added;

    if (undefined == removed) {
        removed = {id: null};
    }
    if (undefined == added) {
        added = {id: null};
    }

    $.post(base_url+'miniant/orders/assignments/add_or_remove_technician', {
            removed_id: removed.id,
            added_id: added.id,
            order_id: $('input[name="order_id"]').val(),
            assignment_id: $('input[name="assignment_id"]').val(),
            unit_id: $('input[name="unit_id"]').val(),
        },
        function(data) {
        update_senior_technician_options();
    }, 'json');

}

function update_senior_technician_options() {

    $('select[name="senior_technician_id"] option').remove();

    $.post(base_url+'miniant/orders/order_ajax/get_assigned_technicians', {order_id: $('input[name="order_id"]').val()}, function(data) {
         $.each(data.technicians, function(key, technician) {
             var selected = (technician.is_senior) ? ' selected="selected" ' : '';
             $('select[name="senior_technician_id"]').append('<option '+selected+' value="'+technician.id+'">'+technician.first_name+'</option>');
         });
    }, 'json'
    );
}
