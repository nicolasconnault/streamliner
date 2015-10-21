$(function() {
    $('ul.nav-tabs li.tab-order_units a').tab('show');
    $('#order_units').show();
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
    $('#submitforallocation_button').off('click');
    $('#submitforallocation_button').on('click', function(event) {
        if (!has_units) {
            event.preventDefault();
            display_message('warning', 'Please add at least one unit under the Equipment section', this);
            return false;
        }
    });
}

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
                if (isEmpty(unit.location)) {
                    unit.location = '';
                }

                var row = document.createElement('tr');
                $(row).attr('data-unit_id', unit.id);
                $(row).attr('data-brand_id', unit.brand_id);
                $(row).attr('data-brand_other', unit.brand_other);
                $(row).attr('data-unit_type_id', unit.unit_type_id);
                $(row).attr('data-area_serving', unit.area_serving);
                $(row).attr('data-tenancy_id', unit.tenancy_id);

                var brand = (!isNull(unit.brand) && unit.brand != 'Other') ? unit.brand : unit.brand_other;

                var cells = '<td>'+unit.type+'</td><td>'+unit.tenancy+'</td><td>'+brand+'</td><td>'+unit.area_serving+'</td>';
                $(row).html(cells);

                $(row).append(get_actions_cell(locked));

                $(row).addClass('unitrow');
                $(row).attr('id', 'unitrow_'+unit.id);
                $('#order_units tbody').prepend(row);

                // Update the brand dropdowns in case a new one was just added
                $.post(base_url+'miniant/brands/get_brands_dropdown', { unit_type_id: unit.unit_type_id }, function (data) {
                    var brands_dropdown = null;

                    if (unit.type == 'Refrigerated A/C') {
                        brands_dropdown = $('select[name="brand_id_ref"]');
                    } else if (unit.type == 'Evaporated A/C') {
                        brands_dropdown = $('select[name="brand_id_evap"]');
                    } else {
                        brands_dropdown = $('select[name="brand_id"]');
                    }

                    brands_dropdown.find('option').remove();
                    $.each(data, function(key, item) {
                        brands_dropdown.prepend('<option value="'+key+'">'+item+'</option>');
                    });
                }, 'json');
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
    $('select[name=new_brand_id]').val('').parent().removeClass('danger');
    $('select[name=new_unit_type_id]').val('').parent().removeClass('danger');
    $('select[name=new_tenancy_id]').val('').parent().removeClass('danger');
    $('input[name=new_area_serving]').val('').parent().removeClass('danger');
}

function cancel_unit() {
    empty_new_unit_row();
    $('#new_unit_button').parent().show();
    $('#new_unit_row').hide();
    $('#submit_button').removeAttr('disabled');
    refresh_units_table();
}

function remove_unit(unit_id) {

    var order_id = $('input[name=order_id]').val();

    if (order_id > 0) {
        var answer = confirm('Are you sure you want to remove this unit from this Job? This cannot be undone.');

        if (answer == true) {
            $.post(base_url+'miniant/orders/order_ajax/remove_unit', { unit_id: unit_id, order_id: order_id }, function(data, response, xhr) {
                print_message(data.message, data.type, 'unitstable');
                cancel_unit();
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
    $(new_row).attr('data-unit_id', unit.id);
    $(new_row).attr('data-brand_id', unit.brand_id);
    $(new_row).attr('data-tenancy_id', unit.tenancy_id);
    $(new_row).attr('data-brand_other', unit.brand_other);
    $(new_row).attr('data-unit_type_id', unit.unit_type_id);
    var brand = (!isNull(unit.brand) && unit.brand != 'Other') ? unit.brand : unit.brand_other;
    $(new_row).append('<td>'+unit.type+'</td><td>'+unit.tenancy+'</td><td>'+brand+'</td><td>'+unit.area_serving+'</td>');

    $(new_row).append(get_actions_cell());

    $(new_row).addClass('unitrow');
    $('tr#new_unit_row').before(new_row);
    refresh_units_table();
}


function submit_new_unit() {

    errors_found = validate_popover_form($('#new_unit_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/add_unit', {
                order_id: $('#new_unit_form input[name=new_order_id]').val(),
                unit_type_id: $('#new_unit_form select[name=new_unit_type_id][disabled!="disabled"]').val(),
                tenancy_id: $('#new_unit_form select[name=new_tenancy_id][disabled!="disabled"]').val(),
                brand_id: $('#new_unit_form select[name=new_brand_id][disabled!="disabled"]').val(),
                brand_id_ref: $('#new_unit_form select[name=new_brand_id_ref][disabled!="disabled"]').val(),
                brand_id_evap: $('#new_unit_form select[name=new_brand_id_evap][disabled!="disabled"]').val(),
                brand_other: $('#new_unit_form input[name=new_brand_other]').val(),
                brand_other_evap: $('#new_unit_form input[name=new_brand_other_evap]').val(),
                brand_other_ref: $('#new_unit_form input[name=new_brand_other_ref]').val(),
                area_serving: $('#new_unit_form input[name=new_area_serving]').val(),
                site_address_id: $('select[name=site_address_id]').val(),
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
            $('#edit_unit_form select[name="brand_id"] option[value="'+$(unit).attr('data-brand_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name="brand_id_ref"] option[value="'+$(unit).attr('data-brand_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form select[name="brand_id_evap"] option[value="'+$(unit).attr('data-brand_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name="brand_other"]').attr('value', $(unit).attr('data-brand_other'));
            $('#edit_unit_form input[name="brand_other_evap"]').attr('value', $(unit).attr('data-brand_other'));
            $('#edit_unit_form input[name="brand_other_ref"]').attr('value', $(unit).attr('data-brand_other'));
            $('#edit_unit_form select[name="tenancy_id"] option[value="'+$(unit).attr('data-tenancy_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name="area_serving"]').attr('value', $(unit).attr('data-area_serving'));
            return $('#edit_unit_popup .content').html();
        }
    });

    $(button).on('shown.bs.popover', function() {
        setup_conditional_form_elements();
    });
}

function submit_edit_unit() {

    errors_found = validate_popover_form($('#edit_unit_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/edit_unit', {
                id: $('#edit_unit_form input[name=unit_id]').val(),
                order_id: $('#edit_unit_form input[name=order_id]').val(),
                unit_type_id: $('#edit_unit_form select[name=unit_type_id][disabled!="disabled"]').val(),
                brand_id: $('#edit_unit_form select[name=brand_id][disabled!="disabled"]').val(),
                brand_id_ref: $('#edit_unit_form select[name=brand_id_ref][disabled!="disabled"]').val(),
                brand_id_evap: $('#edit_unit_form select[name=brand_id_evap][disabled!="disabled"]').val(),
                brand_other: $('#edit_unit_form input[name=brand_other]').val(),
                brand_other_evap: $('#edit_unit_form input[name=brand_other_evap]').val(),
                brand_other_ref: $('#edit_unit_form input[name=brand_other_ref]').val(),
                tenancy_id: $('#edit_unit_form select[name=tenancy_id][disabled!="disabled"]').val(),
                area_serving: $('#edit_unit_form input[name=area_serving]').val(),
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

