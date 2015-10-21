$(function() {
    $('#select-all-suppliers').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        select_all_suppliers();
    });
    $('#deselect-all-suppliers').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        deselect_all_suppliers();
    });

    $('#servicequote_parts input[type=checkbox].part-checkbox').on('click', remove_previewed_status);

    $('#prepare_quote_requests_form').on('submit', function(event) {

        // At least one supplier for each part must be selected
        $('#servicequote_parts tbody > tr.part-row').each(function(key, item) {
            if ($(item).find('.part-checkbox:checked').length == 0) {
                event.preventDefault();
                print_message('Please select at least one supplier for each part/labour', 'warning', 'order');
                return false;
            }
        });

        if ($(event.originalEvent.explicitOriginalTarget).attr('name') == 'send') {
            if (!confirm('Are you sure you are ready to email this request form to the suppliers?')) {
                event.preventDefault();
            }
        }
    });

    $('#prepare_quote_requests_form input[name=preview]').on('click', function(event) {
        $('#prepare_quote_requests_form input[name=send]').prop('disabled', false);
    });
});

function select_all_suppliers() {
    $('#servicequote_parts input[type=checkbox].part-checkbox').prop('checked', true);
    remove_previewed_status();
}

function deselect_all_suppliers() {
    $('#servicequote_parts input[type=checkbox].part-checkbox').prop('checked', false);
    remove_previewed_status();
}

function remove_previewed_status() {
    $('#prepare_quote_requests_form input[name=send]').prop('disabled', true);
    $.post(base_url+'events/undo_event', { event_name: 'supplier_quote_requests_previewed', system: 'servicequotes', document_id: $('input[name=servicequote_id]').val() , module: 'miniant'});
}
