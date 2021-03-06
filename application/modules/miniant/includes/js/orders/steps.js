var required_parts_dropdown = [];
var open_popovers = [];

$(function () {
    $('#tree').find('li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');

    $('#tree').delegate('li.parent_li > span', 'click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            switch ($(this).attr('class')) {
                case 'part_type':
                    setup_issue_type_popovers($(this).attr('data-id'));
                    break;
                case 'issue_type':
                    setup_step_popovers($(this).attr('data-id'));
                    break;
                case 'step':
                    setup_required_part_popovers($(this).attr('data-id'));
                    break;
            }
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });

    $('.popover-markup').hide();

    setup_part_type_popovers();

    $('#tree').delegate('a.delete', 'click', function(e) {
        if (deletethis()) {
            var li_element = $(this).parent();
            var matches = li_element.attr('id').match(/([a-z_]*)_([0-9]*)/);

            $.post(base_url+'miniant/orders/steps/delete_tree_element', {element_type: matches[1], element_id: matches[2]}, function (data) {
                print_message(data.message, data.type);
                li_element.remove();
            }, 'json'
            );
        }
    });

    $('#tree').delegate('form', 'submit', function(e) {
        e.preventDefault();
        $(this).find('a.btn-success').click();
    });
});

/**
 * @param int part_type_id Optional param to narrow down the creation of popovers to just one part type
 */
function setup_part_type_popovers(part_type_id) {

    $('span.part_type.new').popover({
        html: true,
        trigger: 'click',
        title: 'New Component type',
        content: function () {
            open_popovers.push(this);
            var matches = $(this).parent().parent().parent().attr('id').match(/unit_type_([0-9]*)/);
            $('#new_part_type_form input[name="unit_type_id"]').val(matches[1]);
            $('#new_part_type_form input[name="for_diagnostic"]').prop('checked', true);
            return $('#new_part_type_popup .content').html();
        }
    });

    var selector = 'a.part_type.edit';

    if (part_type_id !== undefined) {
        selector = '#part_type_'+part_type_id+' '+selector;
    }

    $(selector).popover({
        html: true,
        trigger: 'click',
        title: 'Edit Component type',
        content: function () {
            open_popovers.push(this);
            var part_type = $(this).siblings('span.part_type')[0];
            $('#edit_part_type_form input[name="unit_type_id"]').val($(part_type).attr('data-unit_type_id'));
            $('#edit_part_type_form input[name="for_diagnostic"]').val($(part_type).attr('data-for_diagnostic'));
            $('#edit_part_type_form textarea[name="instructions"]').text($(part_type).attr('data-instructions'));
            $('#edit_part_type_form input[name="id"]').val($(part_type).attr('data-id'));
            $('#edit_part_type_form input[name="name"]').attr('value', $(part_type).attr('data-name'));
            return $('#edit_part_type_popup .content').html();
        }
    });
}

function setup_issue_type_popovers(part_type_id) {

    $('#part_type_'+part_type_id).find('span.issue_type.new').popover({
        html: true,
        trigger: 'click',
        title: 'New issue type',
        content: function () {
            open_popovers.push(this);
            var matches = $(this).parent().parent().parent().attr('id').match(/part_type_([0-9]*)/);
            $('#new_part_type_issue_type_form input[name="part_type_id"]').val(matches[1]);
            return $('#new_part_type_issue_type_popup .content').html();
        }
    });

    $('#part_type_'+part_type_id).find('a.part_type_issue_type.edit').popover({
        html: true,
        trigger: 'click',
        title: 'Edit Issue type',
        content: function () {
            open_popovers.push(this);
            var issue_type = $(this).siblings('span.issue_type')[0];
            $('#edit_part_type_issue_type_form input[name="part_type_id"]').val($(issue_type).attr('data-part_type_id'));
            $('#edit_part_type_issue_type_form select[name="issue_type_id"] option[value="'+$(issue_type).attr('data-issue_type_id')+'"]').attr('selected', 'selected');
            $('#edit_part_type_issue_type_form input[name="id"]').val($(issue_type).attr('data-id'));
            return $('#edit_part_type_issue_type_popup .content').html();
        }
    });
}

function setup_step_popovers(issue_type_id) {

    $('#part_type_issue_type_'+issue_type_id).find('span.step.new').popover({
        html: true,
        trigger: 'click',
        title: 'New step',
        content: function () {
            open_popovers.push(this);
            var matches = $(this).parent().parent().parent().attr('id').match(/part_type_issue_type_([0-9]*)/);
            $('#new_part_type_issue_type_step_form input[name="part_type_issue_type_id"]').val(matches[1]);
            return $('#new_part_type_issue_type_step_popup .content').html();
        }
    });

    $('#part_type_issue_type_'+issue_type_id).find('a.step.edit').popover({
        html: true,
        trigger: 'click',
        title: 'Edit step',
        content: function () {
            open_popovers.push(this);
            var step = $(this).siblings('span.step')[0];
            $('#edit_part_type_issue_type_step_form input[name="part_type_issue_type_id"]').val($(step).attr('data-part_type_issue_type_id'));
            $('#edit_part_type_issue_type_step_form input[name="required"]').attr('checked', $(step).attr('data-required') == 1);
            $('#edit_part_type_issue_type_step_form input[name="needs_sq"]').attr('checked', $(step).attr('data-needs_sq') == 1);
            $('#edit_part_type_issue_type_step_form input[name="immediate"]').attr('checked', $(step).attr('data-immediate') == 1);
            $('#edit_part_type_issue_type_step_form input[name="id"]').val($(step).attr('data-id'));
            $('#edit_part_type_issue_type_step_form select[name="step_id"] option[value="'+$(step).attr('data-step_id')+'"]').attr('selected', 'selected');
            return $('#edit_part_type_issue_type_step_popup .content').html();
        }
    });
}

function setup_required_part_popovers(step_id) {
    $('#step_'+step_id).find('span.required_part.new').popover({
        html: true,
        trigger: 'click',
        title: 'New required part/labour',
        content: function () {
            open_popovers.push(this);
            var matches = $(this).parent().parent().parent().attr('id').match(/step_([0-9]*)/);
            $('#new_required_part_form input[name="part_type_issue_type_step_id"]').val(matches[1]);
            $('#new_required_part_form input[name="for_diagnostic"]').val(0);
            var matches = $(this).parents('li[id^=unit_type_]').attr('id').match(/unit_type_([0-9]*)/);
            var unit_type_id = matches[1];

            $('#new_required_part_form select[name="part_type_id"] option').remove();

            if (required_parts_dropdown.length > 0) {
                $.each(required_parts_dropdown, function(key, item) {
                    $('#new_required_part_form select[name="part_type_id"]').append('<option value="'+item.id+'">'+item.name+'</option>');
                });
            } else {
                $.post(base_url+'miniant/orders/steps/get_required_parts_dropdown', {unit_type_id: unit_type_id}, function(data) {
                    $.each(data.parts, function(key, item) {
                        $('#new_required_part_form select[name="part_type_id"]').append('<option value="'+item.id+'">'+item.name+'</option>');
                    });
                    required_parts_dropdown = data.parts;
                }, 'json');
            }

            return $('#new_required_part_popup .content').html();
        }
    });

    $('#step_'+step_id).find('a.required_part.edit').popover({
        html: true,
        trigger: 'click',
        title: 'Edit required part/labour',
        content: function () {
            open_popovers.push(this);
            var required_part = $(this).siblings('span.required_part')[0];
            $('#edit_required_part_form input[name="part_type_issue_type_step_id"]').val($(required_part).attr('data-part_type_issue_type_step_id'));
            $('#edit_required_part_form input[name="part_type_id"]').val($(required_part).attr('data-part_type_id'));
            $('#edit_required_part_form input[name="for_diagnostic"]').val(1);
            $('#edit_required_part_form select[name="quantity"] option[value='+$(required_part).attr('data-quantity')+']').attr('selected', true);
            $('#edit_required_part_form input[name="id"]').val($(required_part).attr('data-id'));
            var matches = $('li[id^=unit_type_]').attr('id').match(/unit_type_([0-9]*)/);
            var unit_type_id = matches[1];

            $('#edit_required_part_form select[name="part_type_id"] option').remove();

            if (required_parts_dropdown.length > 0) {
                $.each(required_parts_dropdown, function(key, item) {
                    var selected = '';
                    if (item.id == $(required_part).attr('data-part_type_id')) {
                        selected = ' selected="selected"';
                    }

                    $('#edit_required_part_form select[name="part_type_id"]').append('<option value="'+item.id+'"'+selected+'>'+item.name+'</option>');
                });
            } else {
                $.post(base_url+'miniant/orders/steps/get_required_parts_dropdown', {unit_type_id: unit_type_id}, function(data) {
                    $.each(data.parts, function(key, item) {
                        var selected = '';
                        if (item.id == $(required_part).attr('data-part_type_id')) {
                            selected = ' selected="selected"';
                        }

                        $('#edit_required_part_form select[name="part_type_id"]').append('<option value="'+item.id+'"'+selected+'>'+item.name+'</option>');
                    });
                    required_parts_dropdown = data.parts;
                }, 'json');
            }

            return $('#edit_required_part_popup .content').html();
        }
    });
}

function submit_new_part_type() {

    errors_found = validate_popover_form($('#new_part_type_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/add_part_type', {
                unit_type_id: $('#new_part_type_form input[name=unit_type_id]').val(),
                name: $('#new_part_type_form input[name=name]').val(),
                instructions: $('#new_part_type_form textarea[name=instructions]').val(),
                for_diagnostic: $('#new_part_type_form input[name=for_diagnostic]').is(':checked')
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                add_tree_element(data.part_type, 'part_type', 'part_type', $('#new_part_type_form input[name=unit_type_id]').val());
                $('span.part_type.new').popover('hide');
                setup_part_type_popovers(data.part_type.id);
                setup_issue_type_popovers(data.part_type.id);
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

function submit_edit_part_type() {

    errors_found = validate_popover_form($('#edit_part_type_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/edit_part_type', {
                name: $('#edit_part_type_form input[name=name]').val(),
                id: $('#edit_part_type_form input[name=id]').val(),
                instructions: $('#edit_part_type_form textarea[name=instructions]').val(),
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                $('.part_type[data-id='+data.part_type.id+'] span.element_name').html(data.part_type.name);
                $('.part_type[data-id='+data.part_type.id+']').attr('data-name', data.part_type.name);
                $('.part_type[data-id='+data.part_type.id+']').attr('data-id', data.part_type.id);
                $('.part_type[data-id='+data.part_type.id+']').attr('data-instructions', data.part_type.instructions);
                $('a.part_type.edit').popover('hide');
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

function submit_new_part_type_issue_type() {

    errors_found = validate_popover_form($('#new_part_type_issue_type_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/add_issue_type', {
                part_type_id: $('#new_part_type_issue_type_form input[name=part_type_id]').val(),
                issue_type_id: $('#new_part_type_issue_type_form select[name=issue_type_id]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                add_tree_element(data.issue_type, 'part_type_issue_type', 'issue_type', $('#new_part_type_issue_type_form input[name=part_type_id]').val());
                $('span.issue_type.new').popover('hide');
                setup_issue_type_popovers(data.issue_type.part_type_id);
                setup_step_popovers(data.issue_type.id);
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

function submit_edit_part_type_issue_type() {

    errors_found = validate_popover_form($('#edit_part_type_issue_type_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/edit_part_type_issue_type', {
                issue_type_id: $('#edit_part_type_issue_type_form select[name=issue_type_id]').val(),
                part_type_id: $('#edit_part_type_issue_type_form input[name=part_type_id]').val(),
                id: $('#edit_part_type_issue_type_form input[name=id]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                if (data.part_type_issue_type === undefined) {
                    return false;
                }
                $('.issue_type[data-id='+data.part_type_issue_type.id+'] span.element_name').html(data.part_type_issue_type.name);
                $('.issue_type[data-id='+data.part_type_issue_type.id+']').attr('data-issue_type_id', data.part_type_issue_type.issue_type_id);
                $('#part_type_issue_type_'+data.part_type_issue_type.id+' a.part_type_issue_type.edit').popover('hide');
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

function submit_new_part_type_issue_type_step() {

    errors_found = validate_popover_form($('#new_part_type_issue_type_step_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/add_step', {
                part_type_issue_type_id: $('#new_part_type_issue_type_step_form input[name=part_type_issue_type_id]').val(),
                step_id: $('#new_part_type_issue_type_step_form select[name=step_id]').val(),
                required: $('#new_part_type_issue_type_step_form input[name=required]').is(':checked'),
                needs_sq: $('#new_part_type_issue_type_step_form input[name=needs_sq]').is(':checked'),
                immediate: $('#new_part_type_issue_type_step_form input[name=immediate]').is(':checked')
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                add_tree_element(data.step, 'part_type_issue_type_step', 'step', $('#new_part_type_issue_type_step_form input[name=part_type_issue_type_id]').val());
                $('span.step.new').popover('hide');
                setup_step_popovers(data.step.part_type_issue_type_id);
                setup_required_part_popovers(data.step.id);
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

function submit_edit_part_type_issue_type_step() {

    errors_found = validate_popover_form($('#edit_part_type_issue_type_step_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/edit_part_type_issue_type_step', {
                step_id: $('#edit_part_type_issue_type_step_form select[name=step_id]').val(),
                part_type_issue_type_id: $('#edit_part_type_issue_type_step_form input[name=part_type_issue_type_id]').val(),
                required: ($('#edit_part_type_issue_type_step_form input[name=required]').is(':checked')) ? 1 : 0,
                needs_sq: ($('#edit_part_type_issue_type_step_form input[name=needs_sq]').is(':checked')) ? 1 : 0,
                immediate: ($('#edit_part_type_issue_type_step_form input[name=immediate]').is(':checked')) ? 1 : 0,
                id: $('#edit_part_type_issue_type_step_form input[name=id]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                var id = data.part_type_issue_type_step.id;
                $('.step[data-id='+id+'] span.element_name').html(data.part_type_issue_type_step.name);
                $('.step[data-id='+id+']').attr('data-step_id', data.part_type_issue_type_step.step_id);
                $('.step[data-id='+id+']').attr('data-required', data.part_type_issue_type_step.required);
                $('.step[data-id='+id+']').attr('data-needs_sq', data.part_type_issue_type_step.needs_sq);
                $('.step[data-id='+id+']').attr('data-immediate', data.part_type_issue_type_step.immediate);
                $('#step_'+id+' a.step.edit').popover('hide');
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


function submit_new_required_part() {

    errors_found = validate_popover_form($('#new_required_part_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/add_required_part', {
                part_type_id: $('#new_required_part_form select[name=part_type_id]').val(),
                part_type_issue_type_step_id: $('#new_required_part_form input[name=part_type_issue_type_step_id]').val(),
                quantity: $('#new_required_part_form select[name=quantity]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                add_tree_element(data.required_part, 'required_part', 'required_part', $('#new_required_part_form input[name=part_type_issue_type_step_id]').val());
                $('#new_required_part_li_'+data.required_part.part_type_issue_type_step_id+' span.required_part.new').popover('hide');
                setup_required_part_popovers(data.required_part.part_type_issue_type_step_id);
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

function submit_edit_required_part() {

    errors_found = validate_popover_form($('#edit_required_part_form'));

    if (errors_found.length == 0) {
        $.post(base_url+'miniant/orders/steps/edit_required_part', {
                part_type_id: $('#edit_required_part_form select[name=part_type_id]').val(),
                part_type_issue_type_step_id: $('#edit_required_part_form input[name=part_type_issue_type_step_id]').val(),
                quantity: $('#edit_required_part_form select[name=quantity]').val(),
                id: $('#edit_required_part_form input[name=id]').val()
            },
            function (data, response, xhr) {
                print_message(data.message, data.type);
                var id = data.required_part.id;
                $('.required_part[data-id='+id+'] span.element_name').html(data.required_part.name + ' ('+data.required_part.quantity+')');
                $('.required_part[data-id='+id+']').attr('data-part_type_id', data.required_part.part_type_id);
                $('.required_part[data-id='+id+']').attr('data-part_type_issue_type_step_id', data.required_part.part_type_issue_type_step_id);
                $('.required_part[data-id='+id+']').attr('data-quantity', data.required_part.quantity);
                $('#required_part_'+id+' a.required_part.edit').popover('hide');
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

function add_tree_element(new_element, element_type, element_label, parent_id) {
    var child_element = '';
    var child_element_label = '';
    var child_element_icon = '';

    if (element_type ==  'part_type_issue_type_step' ) {
        element_type = 'step';
    }

    switch (element_label) {
        case 'part_type' :
            child_element = 'issue_type';
            child_element_label = 'issue type';
            child_element_icon = 'cog';
            break;
        case 'issue_type' :
            child_element = 'step';
            child_element_label = 'step';
            child_element_icon = 'exclamation-triangle';
            break;
        case 'step' :
            child_element = 'required_part';
            child_element_label = 'required part/labour';
            child_element_icon = 'footsteps';
            break;
    }
    var html = '<li id="'+element_type+'_'+new_element.id+'" class="parent_li" style="display: list-item;">' +
        '<span class="'+element_label+'" title="Collapse this branch" ';

    for (key in new_element) {
        html += ' data-'+key+'="'+new_element[key]+'" ';
    }

    html += '>' +
        '<i class="fa fa-'+child_element_icon+'"></i><span class="element_name"> ' + new_element.name + '</span></span>' +
        '<a style="margin-left: 5px" class="btn btn-sm btn-warning delete">Delete</a>' +
        '<a style="margin-left: 5px" class="'+element_type+' btn btn-sm btn-info edit">Edit</a>';

    if (element_label != 'required_part') {
        html += '<ul class="'+child_element+'s">' +
            '<li id="new_'+child_element+'_li_'+new_element.id+'">' +
                '<span class="'+child_element+' new" data-original-title="" title="">' +
                    '<i class="fa fa-plus"></i>' +
                    ' New '+child_element_label +
                '</span>' +
            '</li>' +
        '</li></ul>';
    }

    html += '</li>';

    var element = $(html);
    $(element).insertAfter('#new_'+element_label+'_li_'+parent_id);

    switch (element_label) {
        case 'part_type':
            setup_issue_type_popovers(parent_id);
            break;
        case 'issue_type':
            setup_step_popovers(parent_id);
            break;
        case 'step':
            setup_required_part_popovers(parent_id);
            break;
    }
}
