$(function() {
    var order_id = $('input[name=order_id]').val();

    if (!$('select[name="account_id"]').val()) {
        $('#site_contact_id_edit_link').hide();
        $('#billing_contact_id_edit_link').hide();
        $('#site_address_id_edit_link').hide();
        $('select[name="billing_contact_id"]').parent().parent().hide();
        $('select[name="site_contact_id"]').parent().parent().hide();
        $('select[name="site_address_id"]').parent().parent().hide();
    } else {
        update_account_details($('select[name=account_id]'), order_id);
    }

    $('.popover-markup').hide();

    $('select[name=site_address_id]').popover({
        html: true,
        trigger: 'manual',
        title: function () {
            return $('#newsite_address_popup .head').html();
        },
        content: function () {
            open_popovers.push(this);
            return $('#newsite_address_popup .content').html();
        }
    });

    $('select[name=site_address_id]').change(function() {
        if ($(this).val() > 0) {
            $('#site_address_id_edit_link').show();
            $('#site_address_id_edit_link').attr('data-link', base_url+'addresses/edit/'+$(this).val());
        } else {
            $('#site_address_id_edit_link').hide();
        }
    });

    $('select[name=site_contact_id]').popover({
        html: true,
        trigger: 'manual',
        title: function () {
            return $('#newsite_contact_popup .head').html();
        },
        content: function () {
            open_popovers.push(this);
            return $('#newsite_contact_popup .content').html();
        }
    });

    $('button#new-tenancy-button').popover({
        html: true,
        trigger: 'manual',
        placement: 'left',
        title: function () {
            return $('#newtenancy_popup .head').html();
        },
        content: function () {
            open_popovers.push(this);
            return $('#newtenancy_popup .content').html();
        }
    });

    $('button#new-tenancy-button').on('click', function(event) {
        event.preventDefault();
        $(this).popover('show');
    });

    $('select[name=site_contact_id]').change(function() {
        if ($(this).val() > 0) {
            $('#site_contact_id_edit_link').show();
            $('#site_contact_id_edit_link').attr('data-link', base_url+'users/contact/edit/'+$(this).val());
        } else {
            $('#site_contact_id_edit_link').hide();
        }
    });

    $('select[name=billing_contact_id]').popover({
        html: true,
        trigger: 'manual',
        title: function () {
            return $('#newbilling_contact_popup .head').html();
        },
        content: function () {
            open_popovers.push(this);
            return $('#newbilling_contact_popup .content').html();
        }
    });

    $('select[name=billing_contact_id]').change(function() {
        if ($(this).val() > 0) {
            $('#billing_contact_id_edit_link').show();
            $('#billing_contact_id_edit_link').attr('data-link', base_url+'users/contact/edit/'+$(this).val());
        } else {
            $('#billing_contact_id_edit_link').hide();
        }
    });

    $('#cancel_button').click(function(event) {
        event.preventDefault();
        window.location = base_url+'miniant/orders/order/cancel/'+ $('input[name=order_id]').val();
    });

    $('select[name=contact_user_id]').siblings('.dropdown-add-link').after($('#locked_contact_help'));

    if (order_id > 0) {
        update_account_details($('select[name=account_id]'), order_id);
        update_tenancy_table($('select[name=account_id]').val());
    } else {
        $('#billing_contact').hide();
        $('#site_contact').hide();
        $('#site_address').hide();
        $('.submit input.submit_button').attr('disabled', 'disabled');
    }

    // Before submitting, make sure that disabled fields are enabled, or their value will not be submitted
    $('#order_edit_form').submit(function(event) {
        // unlock_main_fields(); // This function is no longer around??? TODO Document the change
        return true;
    });

    $('select.popover_trigger').change(function() {
        value = $(this).val();
        if (value == '0') {
            $(this).popover('show');
        } else {
            $(this).popover('hide');
        }
    });

    $('select[name=account_id]').change(function() {
        if ($(this).val() > 0) {
            $('select[name="billing_contact_id"]').parent().parent().show();
            $('select[name="site_contact_id"]').parent().parent().show();
            $('select[name="site_address_id"]').parent().parent().show();
            $('#tenancy_table').show();
            update_tenancy_table($(this).val());
        } else {
            $('select[name="billing_contact_id"]').parent().parent().hide();
            $('select[name="site_contact_id"]').parent().parent().hide();
            $('select[name="site_address_id"]').parent().parent().hide();
            $('#tenancy_table').hide();
        }
    });

    // Prevent moving to next tab until all required fields are completed
    $('.tabbed_form_navbuttons a.next-tab[rel="2"]').on('click', function(event) {
        if ($('table#tenancy-table tbody tr').length == 0) {

            print_message('You must create at least one tenancy. If none exist, enter one with the name of the billing account.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-1 a').attr('href'));
        }

        if ($('select[name=account_id]').val().length == 0) {
            print_message('Please select a Billing account.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-1 a').attr('href'));
        }
        if ($('select[name=billing_contact_id]').val().length == 0) {
            print_message('Please select a billing contact.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-1 a').attr('href'));
        }
        if ($('select[name=site_address_id]').val().length == 0) {
            print_message('Please select a site address.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-1 a').attr('href'));
        }
    });

    // Prevent moving to next tab until all required fields are completed
    $('.tabbed_form_navbuttons a.next-tab[rel="3"]').on('click', function(event) {
        if ($('select[name=order_type_id]').val().length == 0) {
            print_message('Please select a job type.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-2 a').attr('href'));
            $('div.submit input[type=submit]').hide();
        } else if ($('select[name="order_type_id"] option:contains(Installation)').prop('selected') && $('input[name=installation_quotation_number]').val().length == 0) {
            print_message('Please enter an installation quotation number.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-2 a').attr('href'));
            $('div.submit input[type=submit]').hide();
        } else if ($('select[name="order_type_id"] option:contains(Maintenance)').prop('selected') && $('input[name=maintenance_preferred_start_date]').val().length == 0) {
            print_message('Please enter a preferred start job date.', 'warning', 'order');
            var $tabContainer = $('div.tab-container');
            $tabContainer.easytabs('select', $('li.tab-2 a').attr('href'));
            $('div.submit input[type=submit]').hide();
        }
    });

    // New tenancy form is submitted through AJAX
    $('#tenancy_table').delegate('#newtenancy_form', 'submit', function(event) {
        event.preventDefault();
        submit_newtenancy();
    });

    // If page is refreshed but form not yet submitted, reset form to empty
    if ($('select[name=account_id]').val().length > 0 && !$('#tenancy_table').is(':visible')) {
        $('select[name=account_id]').val('');
        $('select[name=account_id]').change();
    }
});

function update_from_maintenance_contract_id(maintenance_contract_dropdown) {
    var maintenance_contract_id = $(maintenance_contract_dropdown).val();

    if (maintenance_contract_id < 1) {
        $('select[name="account_id"]').val(null);
        $('select[name="account_id"]').prop('disabled', false);
        $('select[name="billing_contact_id"]').val(null);
        $('select[name="billing_contact_id"]').prop('disabled', false);
        $('select[name="billing_contact_id"]').parent().parent().hide();
        $('select[name="site_contact_id"]').val(null);
        $('select[name="site_contact_id"]').prop('disabled', false);
        $('select[name="site_contact_id"]').parent().parent().hide();
        $('select[name="site_address_id"]').val(null);
        $('select[name="site_address_id"]').prop('disabled', false);
        $('select[name="site_address_id"]').parent().parent().hide();
        $('select[name="order_type_id"]').prop('disabled', false);
        $('select[name="order_type_id"]').val(null);
        $('input[name="maintenance_preferred_start_date"]').val(null);
        $('input[name="maintenance_preferred_start_date"]').parent().parent().hide();
        $('#tenancy_table').hide();

        $('input[name="account_id"]').remove();
        $('input[name="billing_contact_id"]').remove();
        $('input[name="site_contact_id"]').remove();
        $('input[name="site_address_id"]').remove();
        $('input[name="order_type_id"]').remove();
        return false;
    }

    $.post(base_url+'miniant/miniant_accounts/get_data_from_maintenance_contract', {maintenance_contract_id: maintenance_contract_id}, function(data, response, xhrStatus) {
        $('select[name="account_id"]').val(data.account_id);
        $('select[name="billing_contact_id"]').parent().parent().show();
        $('select[name="site_contact_id"]').parent().parent().show();
        $('select[name="site_address_id"]').parent().parent().show();
        update_billing_contact_dropdown(data.billing_contacts, data.billing_contact_id);
        update_site_contact_dropdown(data.site_contacts, data.site_contact_id);
        update_site_address_dropdown(data.site_addresses, data.site_address_id);
        $('input[name="maintenance_preferred_start_date"]').parent().parent().show();
        $('input[name="maintenance_preferred_start_date"]').val(unix_to_human(data.preferred_start_date));

        $('select[name="account_id"]').prop('disabled', 'disabled');
        $('select[name="billing_contact_id"]').prop('disabled', 'disabled');
        $('select[name="site_contact_id"]').prop('disabled', 'disabled');
        $('select[name="site_address_id"]').prop('disabled', 'disabled');
        $('select[name="order_type_id"] option:contains(Maintenance)').prop('selected', 'selected');
        $('select[name="order_type_id"]').prop('disabled', 'disabled');

        $('#order_edit_form').append('<input name="account_id" value="'+data.account_id+'" type="hidden" />');
        $('#order_edit_form').append('<input name="billing_contact_id" value="'+data.billing_contact_id+'" type="hidden" />');
        $('#order_edit_form').append('<input name="site_contact_id" value="'+data.site_contact_id+'" type="hidden" />');
        $('#order_edit_form').append('<input name="site_address_id" value="'+data.site_address_id+'" type="hidden" />');
        $('#order_edit_form').append('<input name="order_type_id" value="'+$('select[name="order_type_id"]').val()+'" type="hidden" />');

        update_tenancy_table($('select[name=account_id]').val());
    }, 'json');
}

/**
 * order_id is optional, and only supplied when editing an existing job
 */
function update_account_details(account_dropdown, order_id) {
    var account_id = $(account_dropdown).val();

    if (account_id > 0) {
        $.post(base_url+'miniant/miniant_accounts/get_data', {account_id: account_id, order_id: order_id}, function(data, response, xhrStatus) {
            update_billing_contact_dropdown(data.billing_contacts, data.billing_contact_id);
            update_site_contact_dropdown(data.site_contacts, data.site_contact_id);
            update_site_address_dropdown(data.site_addresses, data.site_address_id);
        }, 'json');
    }
}

function submit_newbilling_contact() {
    return submit_new_contact('billing');
}

function submit_newsite_contact() {
    return submit_new_contact('site');
}

function submit_new_contact(type) {
    errors_found = validate_popover_form($('#new'+type+'_contact_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/create_contact', {
                first_name: $('#new'+type+'_contact_form input[name=first_name]').val(),
                surname: $('#new'+type+'_contact_form input[name=surname]').val(),
                phone: $('#new'+type+'_contact_form input[name=phone]').val(),
                phone2: $('#new'+type+'_contact_form input[name=phone2]').val(),
                mobile: $('#new'+type+'_contact_form input[name=mobile]').val(),
                mobile2: $('#new'+type+'_contact_form input[name=mobile2]').val(),
                email: $('#new'+type+'_contact_form input[name=email]').val(),
                email2: $('#new'+type+'_contact_form input[name=email2]').val(),
                website: $('#new'+type+'_contact_form input[name=website]').val(),
                order_id: $('input[name=order_id]').val(),
                account_id: $('select[name=account_id]').val(),
                type_string: type
            },
            function (data, response, xhr) {
                print_message(data.message, data.type, 'order');
                add_contact(data.contact, true, type);
                $('select[name='+type+'_contact_id]').popover('hide');
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

function submit_newsite_address() {

    errors_found = validate_popover_form($('#newsite_address_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'addresses/add', {
                type: 'Site',
                unit: $('#newsite_address_form input[name=unit]').val(),
                account_id: $('#order_edit_form select[name=account_id]').val(),
                number: $('#newsite_address_form input[name=number]').val(),
                street: $('#newsite_address_form input[name=street]').val(),
                street_type: $('#newsite_address_form input[name=street_type]').val(),
                city: $('#newsite_address_form input[name=city]').val(),
                state: $('#newsite_address_form input[name=state]').val(),
                postcode: $('#newsite_address_form input[name=postcode]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type, 'order');
                add_site_address(data.address, true);
                $('select[name=site_address_id]').popover('hide');
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

function update_order() {
    var contact_id = ($('select[name=site_contact_id]').val()) ? $('select[name=site_contact_id]').val() : $('#contact_name').attr('data-id');
    var address_id = ($('select[name=address_id]').val()) ? $('select[name=address_id]').val() : $('#address_name').attr('data-id');
    var technician_id = ($('select[name=technician_id]').val()) ? $('select[name=technician_id]').val() : $('#technician_name').attr('data-id');
    var call_date = ($('input[name=call_date]').val()) ? $('input[name=call_date]').val() : $('#call_date').html();
    var appointment_date = ($('input[name=appointment_date]').val()) ? $('input[name=appointment_date]').val() : $('#appointment_date').html();

    // If this is a new job, create it and update the order_id hidden field
    if ($('input[name=order_id]').val() == '') {
        $.post(base_url+'miniant/orders/order_ajax/create',
            {
                client_id: contact_id,
                address_id: address_id,
                technician_id: technician_id,
                call_date: call_date,
                appointment_date: appointment_date
            },
            function (data, response, xhr) {
                $('input[name=order_id]').val(data.order_id);
                refresh_units_table();
            },
            'json'
        );
    } else { // Otherwise, update the existing job with the new client_id
        $.post(base_url+'miniant/orders/order_ajax/update',
            {
                client_id: contact_id,
                address_id: address_id,
                order_id: $('input[name=order_id]').val(),
                call_date: call_date,
                appointment_date: appointment_date
            },
            function (data, response, xhr) {
                refresh_units_table();
            },
            'json'
        );
    }
    refresh_units_table();

}


// NEW FUNCTIONS
function update_site_contact_dropdown(contacts, contact_id) {
    // Empty current contact options
    $('select[name=site_contact_id] option').each(function(key, item) {
        if ($(item).val() != '' && $(item).val() != 0) {
            $(item).remove();
        }
    });

    $('#site_contact_id_edit_link').hide();

    for (key in contacts) {
        contact = contacts[key];
        add_contact(contact, contact_id == contact.id, 'site');
    }
}

function update_billing_contact_dropdown(contacts, contact_id) {
    // Empty current contact options
    $('select[name=billing_contact_id] option').each(function(key, item) {
        if ($(item).val() != '' && $(item).val() != 0) {
            $(item).remove();
        }
    });

    $('#billing_contact_id_edit_link').hide();

    for (key in contacts) {
        contact = contacts[key];
        add_contact(contact, contact_id == contact.id, 'billing');
    }
}

function update_site_address_dropdown(addresses, address_id) {
    // Empty current address options
    $('select[name=site_address_id] option').each(function(key, item) {
        if ($(item).val() != '' && $(item).val() != 0) {
            $(item).remove();
        }
    });

    for (key in addresses) {
        address = addresses[key];
        add_site_address(address, address_id == address.id);
    }
}

function add_site_address(address, selected) {
    new_option = document.createElement('option');
    full_address = address.number + ', ' + address.street + ' ' + address.street_type_short + ', ' + address.city;

    if (!isNull(address.unit)) {
        full_address = address.unit + ' ' + full_address;
    }

    if (selected) {
        $(new_option).attr('selected', 'selected');
    }
    $(new_option).val(address.id).html(full_address);
    $('select[name=site_address_id]').append(new_option);
    $('select[name=site_address_id] option[value=""]').removeAttr('selected');
}

function add_contact(contact, selected, type) {
    new_option = document.createElement('option');
    $(new_option).val(contact.id).html(contact.first_name + ' ' + contact.surname);

    if (selected) {
        $(new_option).attr('selected', 'selected');
        $('#'+type+'_contact_id_edit_link').show();
        $('#'+type+'_contact_id_edit_link').attr('data-link', base_url+'users/contact/edit/'+contact.id);
    }
    $('select[name='+type+'_contact_id]').append(new_option);
    $('select[name='+type+'_contact_id] option[value=""]').removeAttr('selected');
}

// TENANCIES
function update_tenancy_table(account_id) {
    $('#tenancy_table').show();
    $.post(base_url+'miniant/miniant_accounts/get_tenancies/'+account_id, function(data) {
        // Clear tenancy rows
        $('tr.tenancy-row').remove();
        $.each(data.tenancies, function(key, item) {
            add_tenancy(item);
        });
    }, 'json');
}

function submit_newtenancy() {
    errors_found = validate_popover_form($('#newtenancy_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/add_tenancy', {
                name: $('#newtenancy_form input[name=name]').val(),
                account_id: $('#order_edit_form select[name=account_id]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type, 'order');
                add_tenancy(data.tenancy, true);
                $('button#new-tenancy-button').popover('hide');
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

function add_tenancy(tenancy) {
    var new_tenancy_row = document.createElement('tr');
    $(new_tenancy_row).addClass('tenancy-row');
    $(new_tenancy_row).attr('data-id', tenancy.id);
    $(new_tenancy_row).attr('data-account_id', tenancy.account_id);
    $(new_tenancy_row).attr('data-name', tenancy.name);
    var remove_button = (tenancy.locked) ? '' : '<button class="btn btn-danger delete"><i class="fa fa-trash-o"></i> Remove</button></td>';
    $(new_tenancy_row).append('<td class="name">'+tenancy.name+'</td>' + '<td><button class="btn btn-info edit"><i class="fa fa-pencil"></i> Edit</button>&nbsp;'+ remove_button );
    $('table#tenancy-table tbody').append(new_tenancy_row);

    $(new_tenancy_row).find('button.edit').popover({
        html: true,
        trigger: 'click',
        title: 'Edit tenancy',
        placement: 'top',
        content: function () {
            var tenancy = $(this).parent().parent();
            $('#edittenancy_form input[name="id"]').attr('value', $(tenancy).attr('data-id'));
            $('#edittenancy_form input[name="account_id"]').attr('value', $(tenancy).attr('data-account_id'));
            $('#edittenancy_form input[name="name"]').attr('value', $(tenancy).attr('data-name'));
            return $('#edittenancy_popup .content').html();
        }
    });

    $(new_tenancy_row).find('button.edit').on('click', function(event) {
        event.preventDefault();
    });

    $(new_tenancy_row).find('button.delete').on('click', function(event) {
        var tenancy_id = $(this).parent().parent().attr('data-id');
        remove_tenancy(tenancy_id);
        event.preventDefault();
    });

    refresh_tenancies_dropdown();
}


function submit_edittenancy() {

    errors_found = validate_popover_form($('#edittenancy_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/order_ajax/edit_tenancy', {
                id: $('#edittenancy_form input[name=id]').val(),
                account_id: $('#edittenancy_form input[name=account_id]').val(),
                name: $('#edittenancy_form input[name=name]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                $('button.edit').popover('hide');
                update_tenancy_table($('#edittenancy_form input[name=account_id]').val());
                refresh_tenancies_dropdown();
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

function refresh_tenancies_dropdown() {
    if ($('select[name="tenancy_id"]').length > 0) {
        $.post(base_url+'miniant/orders/order_ajax/get_tenancy_dropdown', {account_id: $('select[name="account_id"]').val()}, function(data) {
            $('select[name="tenancy_id"],select[name="new_tenancy_id"]').each(function(key, select) {
                $(select).find('option').remove();
                $.each(data.tenancies, function(key2, tenancy) {
                    $(select).prepend('<option value="'+tenancy.id+'">'+tenancy.name+'</option>');
                });
            });
        }, 'json');
    }
}

function remove_tenancy(tenancy_id) {

    var answer = confirm('Are you sure you want to remove this tenancy from this Job? This cannot be undone.');

    if (answer == true) {
        $.post(base_url+'miniant/orders/order_ajax/remove_tenancy/'+tenancy_id, function(data, response, xhr) {
            print_message(data.message, data.type, 'tenancy-table');
            update_tenancy_table($('#account_details input[name=account_id],#account_details select[name=account_id]').val());
        }, 'json');
    } else {
        return false;
    }
}
