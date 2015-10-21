$(function() {
    $('.editable').each(function(key, field) {
        $(field).editable({
            ajaxOptions: {
                type: 'POST',
                dataType: 'json'
            },
            params: {
                supplier_id: $(field).attr('data-supplier_id'),
                part_id: $(field).attr('data-part_id')
            },
            display: function(value, response) {
                if (value.length > 0) {
                    var float_value = parseFloat(value);
                    if ($(this).attr('data-name') == 'availability') {
                        $(this).html(value);
                    } else {
                        $(this).html('$' + float_value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    }
                }
            },
            success: function(response, new_value) {
                if(!response.success) return response.msg;
            }
        });
    });

});
