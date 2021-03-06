$(function() {
    $('ul.nav-tabs li.tab-maintenance_contract_units a').tab('show');
    $('#maintenance_contract_units').show();
    $('#account_details').hide();
    $('.popover-markup').hide();
    refresh_units_table();

    $('button#new_unit_button').popover({
        html: true,
        trigger: 'click',
        title: 'New unit',
        content: function () {
            open_popovers.push(this);
            return $('#new_unit_popup .content').html();
        }
    });
    $('button#new_unit_button').on('shown.bs.popover', function() {
        setup_conditional_form_elements();
    });
});

function toggle_submit_button_state(has_units) {
    $('#submitforallocation_button,#tobescheduled_button').off('click');
    $('#submitforallocation_button,#tobescheduled_button').on('click', function(event) {
        // Does the maintenance_contract have any Transport units?
        var has_transport = false;

        var clicked_button = $(this);

        $('.unitrow').each(function(key, row) {
            if ($(row).attr('data-unit_type_string') == 'Transport Refrigeration') {
                has_transport = true;
                event.preventDefault();

                $( "#dialog-confirm" ).dialog({
                    resizable: false,
                    height:140,
                    modal: true,
                    buttons: {
                        "Use this address": function() {
                            $(this).dialog( "close" );
                            // Insert a hidden input with the name of the clicked submit button, or else it won't be sent
                            var clicked_button_input = document.createElement('input');
                            $(clicked_button_input).attr('type', 'hidden');
                            $(clicked_button_input).attr('name', clicked_button.attr('name'));
                            $(clicked_button_input).val('1');
                            $('#maintenance_contract_edit_form').append(clicked_button_input);
                            $('#maintenance_contract_edit_form').submit();
                        },
                        "Enter a different address": function() {
                            $(this).dialog( "close" );
                            $('ul.nav-tabs li.tab-account_details a').tab('show');
                            $('#account_details').show();
                            $('#maintenance_contract_units').hide();
                        }
                    }
                });

                $('#dialog-confirm p').html('<i class="fa fa-warning"></i> The current site address is '+ $('select[name="site_address_id"] option:selected').text() + ".");
                $('#dialog-confirm').show();
            }
        });

        if (!has_units) {
            event.preventDefault();
            display_message('warning', 'Please add at least one unit under the Equipment section', this);
            return false;
        }
    });
}

function refresh_units_table() {
    $('#maintenance_contract_units tbody tr.unitrow').remove();

    if ($('input[name=maintenance_contract_id]').val() == '') {
        return false;
    }


    var maintenance_contract_id = $('input[name=maintenance_contract_id]').val();
    if (undefined == maintenance_contract_id || maintenance_contract_id == 0) {
        return false;
    }

    $.post(base_url+'miniant/maintenance_contracts_ajax/get_units/'+maintenance_contract_id, function(data, response, xhr) {
        $('#maintenance_contract_units tbody tr.unitrow').remove();

        if (!isNull(data.current_units) && data.current_units.length > 0) {
            $.each(data.current_units, function (key, unit) {
                if (isNull(unit.unitry_type)) {
                    unit.unitry_type = '';
                }
                if (isNull(unit.description)) {
                    unit.description = '';
                }
                var row = document.createElement('tr');
                $(row).attr('data-maintenance_contract_id', unit.maintenance_contract_id);
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

                $(row).append(get_actions_cell(locked));

                $(row).addClass('unitrow');
                $(row).attr('id', 'unitrow_'+unit.id);
                $('#maintenance_contract_units tbody').prepend(row);
            });
        } else {
            data.current_units = [];
        }

        toggle_submit_button_state(data.current_units.length > 0);

    }, 'json');
}

function add_new_unit_row() {
    $('#new_unit_button').parent().hide();
    $('#new_unit_row').show();
    $('#submit_button').attr('disabled', 'disabled');
}

function empty_new_unit_row() {
    $('select,textarea,input').val('').parent().removeClass('danger');
}

function cancel_unit() {
    empty_new_unit_row();
    $('#new_unit_button').parent().show();
    $('#new_unit_row').hide();
    $('#submit_button').removeAttr('disabled');
    refresh_units_table();
}

function remove_unit(unit_id) {

    var maintenance_contract_id = $('input[name=maintenance_contract_id]').val();

    if (maintenance_contract_id > 0) {
        var answer = confirm('Are you sure you want to remove this unit from this maintenance_contract? This cannot be undone.');

        if (answer == true) {
            $.post(base_url+'miniant/maintenance_contracts_ajax/remove_unit', { unit_id: unit_id, maintenance_contract_id: maintenance_contract_id }, function(data, response, xhr) {
                print_message(data.message, data.type, 'unitstable');
                // cancel_unit();
                refresh_units_table();
            }, 'json');
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function add_unit(unit) {
    var new_row = document.createElement('tr');
    $(new_row).attr('data-maintenance_contract_id', unit.maintenance_contract_id);
    $(new_row).attr('data-unit_id', unit.id);
    $(new_row).attr('data-brand_id', unit.brand_id);
    $(new_row).attr('data-tenancy_id', unit.tenancy_id);
    $(new_row).attr('data-brand_other', unit.brand_other);
    $(new_row).attr('data-unit_type_id', unit.unit_type_id);
    $(new_row).attr('data-unit_type_string', unit.type);
    $(new_row).attr('data-unitry_type_id', unit.unitry_type_id);
    $(new_row).attr('data-area_serving', unit.area_serving);
    $(new_row).attr('data-site_address_id', unit.site_address_id);
    $(new_row).attr('data-description', unit.description);
    $(new_row).attr('data-outdoor_unit_location', unit.outdoor_unit_location);
    $(new_row).attr('data-electrical', unit.electrical);
    $(new_row).attr('data-kilowatts', unit.kilowatts);
    $(new_row).attr('data-vehicle_registration', unit.vehicle_registration);
    $(new_row).attr('data-vehicle_type', unit.vehicle_type);
    $(new_row).attr('data-palette_size', unit.palette_size);
    $(new_row).attr('data-chassis_no', unit.chassis_no);
    $(new_row).attr('data-engine_no', unit.engine_no);
    $(new_row).attr('data-vehicle_year', unit.vehicle_year);
    $(new_row).attr('data-aperture_size', unit.aperture_size);

    var brand = (!isNull(unit.brand) && unit.brand != 'Other') ? unit.brand : unit.brand_other;

    var cells = '<td>'+unit.type+'</td><td>'+unit.tenancy+'</td><td>'+brand+'</td><td>'+unit.area_serving+'</td>';
    $(new_row).append(cells);

    $(new_row).append(get_actions_cell());

    $(new_row).addClass('unitrow');
    $('tr#new_unit_row').before(new_row);
    refresh_units_table();

}

function submit_new_unit() {
    errors_found = validate_popover_form($('#new_unit_form'));

    if (errors_found.length == 0) {

        $.post(base_url+'miniant/maintenance_contracts_ajax/add_unit', {
                maintenance_contract_id: $('#new_unit_form input[name=new_maintenance_contract_id]').val(),
                unit_type_id: $('#new_unit_form select[name=new_unit_type_id][disabled!="disabled"]').val(),
                unitry_type_id: $('#new_unit_form select[name=new_unitry_type_id][disabled!="disabled"]').val(),
                tenancy_id: $('#new_unit_form select[name=new_tenancy_id][disabled!="disabled"]').val(),
                brand_id: $('#new_unit_form select[name=new_brand_id][disabled!="disabled"]').val(),
                brand_id_ref: $('#new_unit_form select[name=new_brand_id_ref][disabled!="disabled"]').val(),
                brand_id_evap: $('#new_unit_form select[name=new_brand_id_evap][disabled!="disabled"]').val(),
                brand_other: $('#new_unit_form input[name=new_brand_other]').val(),
                area_serving: $('#new_unit_form input[name=new_area_serving]').val(),
                site_address_id: $('select[name=site_address_id]').val(),
                description: $('#new_unit_form textarea[name=new_description]').val(),
                outdoor_unit_location: $('#new_unit_form select[name=new_outdoor_unit_location][disabled!="disabled"]').val(),
                electrical: $('#new_unit_form select[name=new_electrical][disabled!="disabled"]').val(),
                kilowatts: $('#new_unit_form input[name=new_kilowatts]').val(),
                vehicle_registration: $('#new_unit_form input[name=new_vehicle_registration]').val(),
                vehicle_type: $('#new_unit_form select[name=new_vehicle_type][disabled!="disabled"]').val(),
                palette_size: $('#new_unit_form select[name=new_palette_size][disabled!="disabled"]').val(),
                chassis_no: $('#new_unit_form input[name=new_chassis_no]').val(),
                engine_no: $('#new_unit_form input[name=new_engine_no]').val(),
                vehicle_year: $('#new_unit_form input[name=new_vehicle_year]').val(),
                aperture_size: $('#new_unit_form input[name=new_aperture_size]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                add_unit(data.unit);
                $('button#new_unit_button').popover('hide');
            },
            'json'
        );
    } else {
        error_msg = "Please check the following errors:\n";
        $(errors_found).each(function(key, item) {
            error_msg += "- "+item.message+"\n";
        });
        alert(error_msg);
    }
}

function get_actions_cell(locked) {
    var actions_cell = document.createElement('td');
    $(actions_cell).addClass('actions');

    if (locked) {
        return actions_cell;
    }

    var delete_button = document.createElement('button');
    $(delete_button).addClass('btn').addClass('btn-danger').addClass('delete');
    $(delete_button).html('<i class="fa fa-trash-o"></i> Remove');
    $(delete_button).attr('type', 'button');
    $(delete_button).on('click', function() {
        var unit_id = $(this).parent().parent().attr('data-unit_id');
        remove_unit(unit_id);
    });

    var edit_button = document.createElement('button');
    $(edit_button).addClass('btn').addClass('btn-info').addClass('edit');
    $(edit_button).html('<i class="fa fa-pencil"></i> Edit');
    $(edit_button).attr('type', 'button');
    set_edit_button_popover(edit_button);

    var actions_cell = document.createElement('td');
    $(actions_cell).append(edit_button);
    $(actions_cell).append('&nbsp;');
    $(actions_cell).append(delete_button);
    return actions_cell;
}

function set_edit_button_popover(button) {
    $(button).popover({
        html: true,
        trigger: 'click',
        title: 'Edit unit',
        placement: 'left',
        content: function () {
            open_popovers.push(this);
            var unit = $(this).parent().parent();
            $('#edit_unit_form input[name="unit_id"]').attr('value', $(unit).attr('data-unit_id'));
            $('#edit_unit_form select[name="unit_type_id"] option[value="'+$(unit).attr('data-unit_type_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name="unitry_type_id"] option[value="'+$(unit).attr('data-unitry_type_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name="brand_id"] option[value="'+$(unit).attr('data-brand_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name="brand_id_ref"] option[value="'+$(unit).attr('data-brand_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name="brand_id_evap"] option[value="'+$(unit).attr('data-brand_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name="brand_other"]').attr('value', $(unit).attr('data-brand_other'));
            $('#edit_unit_form select[name="tenancy_id"] option[value="'+$(unit).attr('data-tenancy_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name="brand_other"]').attr('value', $(unit).attr('data-brand_other'));
            $('#edit_unit_form input[name="area_serving"]').attr('value', $(unit).attr('data-area_serving'));
            $('#edit_unit_form select[name="outdoor_unit_location"] option[value="'+$(unit).attr('data-outdoor_unit_location')+'"]').attr('selected', 'selected');
            $('#edit_unit_form textarea[name="description"]').html($(unit).attr('data-description'));
            $('#edit_unit_form select[name=electrical] option[value="'+$(unit).attr('data-electrical')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name=kilowatts]').attr('value', $(unit).attr('data-kilowatts'));
            $('#edit_unit_form input[name=vehicle_registration]').attr('value', $(unit).attr('data-vehicle_registration'));
            $('#edit_unit_form select[name=vehicle_type] option[value="'+$(unit).attr('data-vehicle_type')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name=palette_size] option[value="'+$(unit).attr('data-palette_size')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name=chassis_no]').attr('value', $(unit).attr('data-chassis_no'));
            $('#edit_unit_form input[name=engine_no]').attr('value', $(unit).attr('data-engine_no'));
            $('#edit_unit_form input[name=vehicle_year]').attr('value', $(unit).attr('data-vehicle_year'));
            $('#edit_unit_form input[name=aperture_size]').attr('value', $(unit).attr('data-aperture_size'));

            return $('#edit_unit_popup .content').html();
        }
    });

    $(button).on('shown.bs.popover', function() {
        setup_conditional_form_elements();
    });
}

function delete_unit_location_document(button) {
    if (confirm('Are you sure you want to delete this photo?')) {
        var unit_id = $('input[name="unit_id"]').val();
        $.post(base_url+'miniant/maintenance_contracts_ajax/delete_unit_location_document/'+unit_id, function(data) {
            print_message(data.message, data.type);
        },'json');

        var file_input = $('#edit_unit_form input[name=location_document]');
        file_input.show();
        file_input.siblings('button,br,a').remove();
    }
}

function submit_edit_unit() {

    errors_found = validate_popover_form($('#edit_unit_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/maintenance_contracts_ajax/edit_unit', {
                id: $('#edit_unit_form input[name=unit_id][disabled!="disabled"]').val(),
                maintenance_contract_id: $('#edit_unit_form input[name=maintenance_contract_id][disabled!="disabled"]').val(),
                unit_type_id: $('#edit_unit_form select[name=unit_type_id][disabled!="disabled"]').val(),
                unitry_type_id: $('#edit_unit_form select[name=unitry_type_id][disabled!="disabled"]').val(),
                brand_id: $('#edit_unit_form select[name=brand_id][disabled!="disabled"]').val(),
                brand_id_ref: $('#edit_unit_form select[name=brand_id_ref][disabled!="disabled"]').val(),
                brand_id_evap: $('#edit_unit_form select[name=brand_id_evap][disabled!="disabled"]').val(),
                brand_other: $('#edit_unit_form input[name=brand_other]').val(),
                tenancy_id: $('#edit_unit_form select[name=tenancy_id][disabled!="disabled"]').val(),
                brand_other: $('#edit_unit_form input[name=brand_other]').val(),
                area_serving: $('#edit_unit_form input[name=area_serving][disabled!="disabled"]').val(),
                description: $('#edit_unit_form textarea[name=description]').val(),
                outdoor_unit_location: $('#edit_unit_form select[name=outdoor_unit_location]').val(),
                electrical: $('#edit_unit_form select[name=electrical][disabled!="disabled"]').val(),
                kilowatts: $('#edit_unit_form input[name=kilowatts]').val(),
                vehicle_registration: $('#edit_unit_form input[name=vehicle_registration]').val(),
                vehicle_type: $('#edit_unit_form select[name=vehicle_type][disabled!="disabled"]').val(),
                palette_size: $('#edit_unit_form select[name=palette_size][disabled!="disabled"]').val(),
                chassis_no: $('#edit_unit_form input[name=chassis_no]').val(),
                engine_no: $('#edit_unit_form input[name=engine_no]').val(),
                vehicle_year: $('#edit_unit_form input[name=vehicle_year]').val(),
                aperture_size: $('#edit_unit_form input[name=aperture_size]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);

                refresh_units_table();
                $('button.edit').popover('hide');
            },
            'json'
        );
    } else {
        error_msg = "Please check the following errors:\n";
        $(errors_found).each(function(key, item) {
            error_msg += "- "+item.message+"\n";
        });
        alert(error_msg);
    }
};
