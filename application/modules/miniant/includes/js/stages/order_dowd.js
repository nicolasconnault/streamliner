$(function() {
    $('.order_dowd_edit_form select.dowd_template').on('change', function(event) {
        $(this).blur();
        var form_id = $(this).parents('form').attr('id');
        var order_id = $(this).attr('data-order_id');
        var dowd_id = $(this).val();

        $.post(base_url+'miniant/stages/order_dowd/get_dowd_description', { dowd_id: dowd_id, order_id: order_id}, function(data) {
            $('#'+form_id+' textarea[name="dowd_text"]').html(data.description);
        }, 'json');
    });

    $('.dowd_description').each(function(key, item) {
        var form_id = $(this).parents('form').attr('id');
        var dowd_id = $(this).attr('data-dowd_id');
        var order_id = $(this).attr('data-order_id');
        var description_textarea = $(this).find('textarea');
        if (description_textarea.val().length > 0) {
            return false;
        }

        $.post(base_url+'miniant/stages/order_dowd/get_dowd_description', { dowd_id: dowd_id, order_id: order_id}, function(data) {
            description_textarea.val(data.description);
        }, 'json');
    });
});
