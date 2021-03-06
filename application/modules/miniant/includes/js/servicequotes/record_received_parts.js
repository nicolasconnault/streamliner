$(function() {
    $('.editable').editable({
        ajaxOptions: {
            type: 'POST',
            dataType: 'json'
        },
        format: 'dd/mm/yyyy',
        viewformat: 'dd/mm/yyyy',
        datepicker: {
            weekStart: 1
        },
        success: function(response, new_value) {
            if(!response.success) {
                return response.msg;
            }
        }
    });
});
