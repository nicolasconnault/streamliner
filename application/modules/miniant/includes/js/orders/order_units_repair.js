// This file is required because repair orders do not allow units to be edited, removed or added
$(function() {
    $('ul.nav-tabs li.tab-order_units a').tab('show');
    $('#order_units').show();
    $('#account_details').hide();
    $('.popover-markup').hide();
    refresh_units_table();
});


function refresh_units_table() {
    $('#order_units tbody tr.unitrow').remove();

    if ($('input[name=order_id]').val() == '') {
        return false;
    }

    var order_id = $('input[name=order_id]').val();
    if (undefined == order_id || order_id == 0) {
        return false;
    }

    $.post(base_url+'miniant/orders/order_ajax/get_units/'+order_id, function(data, response, xhr) {
        $('#order_units tbody tr.unitrow').remove();

        if (!isNull(data.current_units) && data.current_units.length > 0) {
            $.each(data.current_units, function (key, unit) {
                if (isNull(unit.unitry_type)) {
                    unit.unitry_type = '';
                }
                if (isNull(unit.description)) {
                    unit.description = '';
                }
                var row = document.createElement('tr');
                $(row).attr('data-order_id', unit.order_id);
                $(row).attr('data-unit_id', unit.id);
                $(row).attr('data-brand_id', unit.brand_id);
                $(row).attr('data-tenancy_id', unit.tenancy_id);
                $(row).attr('data-brand_other', unit.brand_other);
                $(row).attr('data-unit_type_id', unit.unit_type_id);
                $(row).attr('data-unit_type_string', unit.type);
                $(row).attr('data-unitry_type_id', unit.unitry_type_id);
                $(row).attr('data-area_serving', unit.area_serving);
                $(row).attr('data-description', unit.description);
                $(row).attr('data-outdoor_unit_location', unit.outdoor_unit_location);
                $(row).attr('data-location_document', unit.unit_location_document);
                $(row).attr('data-electrical', unit.electrical);
                $(row).attr('data-kilowatts', unit.kilowatts);
                $(row).attr('data-vehicle_registration', unit.vehicle_registration);
                $(row).attr('data-vehicle_type', unit.vehicle_type);
                $(row).attr('data-palette_size', unit.palette_size);
                $(row).attr('data-chassis_no', unit.chassis_no);
                $(row).attr('data-engine_no', unit.engine_no);
                $(row).attr('data-vehicle_year', unit.vehicle_year);
                $(row).attr('data-aperture_size', unit.aperture_size);

                var brand = (!isNull(unit.brand) && unit.brand != 'Other') ? unit.brand : unit.brand_other;

                var cells = '<td>'+unit.type+'</td><td>'+unit.tenancy+'</td><td>'+brand+'</td><td>'+unit.area_serving+'</td>';
                $(row).html(cells);

                $(row).addClass('unitrow');
                $(row).attr('id', 'unitrow_'+unit.id);
                $('#order_units tbody').prepend(row);
            });
        } else {
            data.current_units = [];
        }

    }, 'json');
}

