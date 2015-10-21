$(function() {
    $('#prepare_purchase_orders_form input[name=preview]').on('click', function(event) {
        $('#prepare_purchase_orders_form input[name=send]').prop('disabled', false);
    });

    $('#prepare_purchase_orders_form').on('submit', function(event) {

        if ($(event.originalEvent.explicitOriginalTarget).attr('name') == 'send') {
            if (!confirm('Are you sure you are ready to email the purchase orders to the suppliers?')) {
                event.preventDefault();
            }
        }
    });
});
