$(function() {
    var sr_id = $('input[name=order_id]').val();

    if ($('input[name="first_name"]').attr('type')) {
        $('.sigpad').signaturePad({drawOnly:true, validateFields: false, lineTop: 80});
    }
    refresh_units_table();
});

function lock_main_fields() {

}
function unlock_main_fields() {

}
