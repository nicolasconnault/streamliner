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

    // Insert Checklist divs into popover forms
    $('#new_unit_popup .content').append('<div class="checklist"></div>');
    $('#edit_unit_popup .content').append('<div class="checklist"></div>');

    $('#units_table').delegate('a.new-task', 'click', function(event) {
        $('#tasks_list').prepend('<li style="height:35px"><input style="width: 284px; type="text" placeholder="Type task here" />' +
                '<a class="btn btn-success btn-sm pull-right save-task">Save</a></li>');

        $('#tasks_list input').focus();
        $('#tasks_list input').on('keydown', function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                save_installation_task($(this).parents('.unitrow').attr('data-unit_id'), $(this).val());
            }
        });

        $('#tasks_list a.save-task').on('click', function(event) {
            save_installation_task($(this).parents('.unitrow').attr('data-unit_id'), $('#tasks_list input').val());
        });
    });

/*
    $('#units_table').delegate('li.installation-task', 'click', function(event) {
        var task_id = $(this).attr('data-id');
        $(this).popover({
            html: true,
            trigger: 'click',
            title: 'New unit',
            content: function () {
                return $('#new_unit_popup .content').html();
            }
            });
    });
    */

    $('#task-notes').on('show.bs.modal', function(event) {
        var notes = $(event.relatedTarget).attr('data-notes');
        if (notes === 'null') {
            notes = '';
        }
        $('textarea[name="task-notes"]').val(notes);
        $('textarea[name="task-notes"]').attr('data-task_id', $(event.relatedTarget).attr('data-id'));
        $('#task-notes-label').html('Task notes: '+ $(event.relatedTarget).attr('data-name'));
    });

    $('#task-notes').on('shown.bs.modal', function(event) {
        $('textarea[name="task-notes"]').focus();
    });

    $('#save-task-notes').on('click', function(event) {
        var task_id = $('textarea[name="task-notes"]').attr('data-task_id');
        var notes = $('textarea[name="task-notes"]').val();

        $.post(base_url+'miniant/orders/order_ajax/save_installation_task_notes', {task_id: task_id, notes: notes}, function(data) {
            display_message(data.type, data.message);
            $('#task-'+task_id).attr('data-notes', notes);
            $('#task-notes').modal('hide');
        }, 'json');

    });

    $('#units_table').delegate('div.task-disable', 'click', function(event) {
        event.stopPropagation();
    });

    $('#units_table').delegate('div.task-disable input', 'change', function(event) {
        event.stopPropagation();
        var task_id = $(this).parent().attr('data-task_id');
        $.post(base_url+'miniant/orders/order_ajax/toggle_installation_task', {task_id: task_id, status: $(this).prop('checked')}, function(data) {
            display_message(data.type, data.message);
        }, 'json');
    });

});

function save_installation_task(unit_id, task) {
    $.post(base_url+'miniant/orders/order_ajax/add_installation_task', {unit_id: unit_id, task: task}, function(data) {
        display_message(data.type, data.message);
        $('#tasks_list input').parent().remove();
        $('#tasks_list').parent().remove();
        update_installation_tasks(unit_id);
    });
}

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

                var row = document.createElement('tr');
                $(row).attr('data-unit_id', unit.id);
                $(row).attr('data-unit_type_id', unit.unit_type_id);
                $(row).attr('data-unitry_type_id', unit.unitry_type_id);
                $(row).attr('data-brand_id', unit.brand_id);
                $(row).attr('data-brand_other', unit.brand_other);
                $(row).attr('data-serial_number', unit.serial_number);
                $(row).attr('data-indoor_serial_number', unit.indoor_serial_number);
                $(row).attr('data-outdoor_serial_number', unit.outdoor_serial_number);
                $(row).attr('data-tenancy_id', unit.tenancy_id);
                $(row).attr('data-area_serving', unit.area_serving);

                var brand = (!isNull(unit.brand) && unit.brand != 'Other') ? unit.brand : unit.brand_other;
                var cells = '<td>'+unit.type+'</td><td>'+unit.unitry_type+'</td><td>'+unit.tenancy+'</td><td>'+brand+'</td><td>'+unit.area_serving+'</td>';
                $(row).html(cells);

                $(row).append(get_actions_cell(locked));

                $(row).addClass('unitrow');
                $(row).attr('id', 'unitrow_'+unit.id);
                $('#order_units tbody').prepend(row);
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
    $('select[name=new_area_serving]').val('').parent().removeClass('danger');
}

function cancel_unit() {
    empty_new_unit_row();
    $('#new_unit_button').parent().show();
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
            }, 'json');
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function set_new_unit_fields(unit_id, callback) {
    $.post(base_url+'miniant/units/get_data/'+unit_id, function(data, response, xhr) {
            $('select[name=new_unit_type_id]').val(data.unit_type_id);
            $('select[name=new_tenancy_id]').val(data.tenancy_id);
            $('select[name=new_area_serving]').val(data.area_serving);
            $('input[name=unit_id]').val(data.id);

            if (undefined !== callback) {
                callback();
            }
        }, 'json'
    );
}

function submit_new_unit() {

    errors_found = validate_popover_form($('#new_unit_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/add_unit', {
                order_id: $('#new_unit_form input[name=new_order_id]').val(),
                unit_type_id: $('#new_unit_form select[name=new_unit_type_id][disabled!="disabled"]').val(),
                unitry_type_id: $('#new_unit_form select[name=new_unitry_type_id][disabled!="disabled"]').val(),
                site_address_id: $('select[name=site_address_id]').val(),
                brand_id: $('#new_unit_form select[name=new_brand_id][disabled!="disabled"]').val(),
                brand_id_ref: $('#new_unit_form select[name=new_brand_id_ref][disabled!="disabled"]').val(),
                brand_id_evap: $('#new_unit_form select[name=new_brand_id_evap][disabled!="disabled"]').val(),
                brand_other: $('#new_unit_form input[name=new_brand_other]').val(),
                serial_number: $('#new_unit_form input[name=new_serial_number]').val(),
                indoor_serial_number: $('#new_unit_form input[name=new_indoor_serial_number]').val(),
                outdoor_serial_number: $('#new_unit_form input[name=new_outdoor_serial_number]').val(),
                tenancy_id: $('#new_unit_form select[name=new_tenancy_id][disabled!="disabled"]').val(),
                area_serving: $('#new_unit_form input[name=new_area_serving]').val(),
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

function add_unit(unit) {
    var new_row = document.createElement('tr');
    $(new_row).attr('data-unit_id', unit.id);
    $(new_row).attr('data-unit_type_id', unit.unit_type_id);
    $(new_row).attr('data-unitry_type_id', unit.unitry_type_id);
    $(new_row).attr('data-brand_id', unit.brand_id);
    $(new_row).attr('data-serial_number', unit.serial_number);
    $(new_row).attr('data-indoor_serial_number', unit.indoor_serial_number);
    $(new_row).attr('data-outdoor_serial_number', unit.outdoor_serial_number);
    $(new_row).attr('data-tenancy_id', unit.tenancy_id);
    $(new_row).attr('data-area_serving', unit.area_serving);
    var brand = (!isNull(unit.brand) && unit.brand != 'Other') ? unit.brand : unit.brand_other;
    $(new_row).append('<td>'+unit.type+'</td><td>'+unit.unitry_type+'</td><td>'+unit.tenancy+'</td><td>'+brand+'</td><td>'+unit.area_serving+'</td>');

    $(new_row).append(get_actions_cell());

    $(new_row).addClass('unitrow');
    $('tr#new_unit_row').before(new_row);
    refresh_units_table();
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
            $('#edit_unit_form input[name="serial_number"]').attr('value', $(unit).attr('data-serial_number'));
            $('#edit_unit_form input[name="indoor_serial_number"]').attr('value', $(unit).attr('data-indoor_serial_number'));
            $('#edit_unit_form input[name="outdoor_serial_number"]').attr('value', $(unit).attr('data-outdoor_serial_number'));
            $('#edit_unit_form select[name="tenancy_id"] option[value="'+$(unit).attr('data-tenancy_id')+'"]').attr('selected', 'selected');
            $('#edit_unit_form input[name="area_serving"]').attr('value', $(unit).attr('data-area_serving'));

            $('#tasks_list').parent().remove();
            return $('#edit_unit_popup .content').html();
        }
    });

    $(button).on('shown.bs.popover', function() {
        setup_conditional_form_elements();

        var unit = $(this).parent().parent();
        update_installation_tasks(unit.attr('data-unit_id'));
    });
}

function update_installation_tasks(unit_id) {
    // Insert checklist from DB
    $.post(base_url+'miniant/orders/order_ajax/get_installation_checklist', {unit_id: unit_id}, function(data) {
        $('tr.unitrow select[name="unit_type_id"]').parent().after(get_checklist_html(data.checklist));

        $('#tasks_list').sortable({
            axis: 'y',
            update: function (event, ui) {
                var checklist = $(this).sortable('serialize');

                // POST to server using $.post or $.ajax
                $.post(base_url+'miniant/orders/order_ajax/save_installation_checklist',
                    { checklist: checklist, type: data.type, unit_id: unit_id }, function(data2) {

                }, 'json');
            }
        });
    }, 'json');

}

function get_checklist_html(checklist) {
    var list = '<div class="form-group"><label style="margin-bottom: 20px; padding-top: 8px;" for="tasks_list">Tasks</label>' +
               '<a class="btn btn-success btn-sm pull-right new-task"><i class="fa fa-plus"></i>New Task</a><ul id="tasks_list">';

    $.each(checklist, function(key, task) {
        var checked = (task.disabled == 1) ? 'checked="checked"' : '';

        list += '<li class="ui-state-default ui-sortable-handle installation-task" data-id="'+task.id+'" data-toggle="modal" data-name="'+task.task+
            '" data-target="#task-notes" data-notes="'+task.notes+'" id="task-'+task.id+'" data-sortorder="'+task.sortorder+'">'+
            '<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'+task.task+
            '<div data-task_id="'+task.id+'" class="task-disable pull-right">'+
            '<input '+checked+' type="checkbox" id="disable-'+task.id+'" name="disable-'+task.id+'" value="1" />'+
            '<label for="disable-'+task.id+'">N/A</label></div></li>';
    });
    list += '</ul></div>';

    return list;
}

function submit_edit_unit() {

    errors_found = validate_popover_form($('#edit_unit_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/edit_unit', {
                id: $('#edit_unit_form input[name=unit_id]').val(),
                order_id: $('#edit_unit_form input[name=order_id]').val(),
                unit_type_id: $('#edit_unit_form select[name=unit_type_id][disabled!="disabled"]').val(),
                unitry_type_id: $('#edit_unit_form select[name=unitry_type_id][disabled!="disabled"]').val(),
                brand_id: $('#edit_unit_form select[name=brand_id][disabled!="disabled"]').val(),
                brand_id_ref: $('#edit_unit_form select[name=brand_id_ref][disabled!="disabled"]').val(),
                brand_id_evap: $('#edit_unit_form select[name=brand_id_evap][disabled!="disabled"]').val(),
                serial_number: $('#edit_unit_form input[name=serial_number]').val(),
                indoor_serial_number: $('#edit_unit_form input[name=indoor_serial_number]').val(),
                outdoor_serial_number: $('#edit_unit_form input[name=outdoor_serial_number]').val(),
                brand_other: $('#edit_unit_form input[name=brand_other]').val(),
                tenancy_id: $('#edit_unit_form select[name=tenancy_id][disabled!="disabled"]').val(),
                area_serving: $('#edit_unit_form input[name=area_serving][disabled!="disabled"]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                $('button.edit').popover('hide');
                refresh_units_table();
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
