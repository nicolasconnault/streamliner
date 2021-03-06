$(function() {
    var radios = [];

    if ($('input.no-issues-radio').length == 1) {
        radios.push($('input.no-issues-radio'));
    }

    $.each(radios, function(key, item) {
        var diagnostic_id = item.attr('data-diagnostic_id');
        var assignment_id = $('table.diagnostic-issues[data-diagnostic_id='+diagnostic_id+']').attr('data-assignment_id');

        if (!$('input#no-issues-found-'+diagnostic_id).prop('checked') && !$('input#issues-found-'+diagnostic_id).prop('checked')) {
            disable_issue_table(diagnostic_id);
            $('#dialog_no_issues-'+assignment_id).hide();
        } else if ($('input#no-issues-found-'+diagnostic_id).prop('checked')) {
            disable_issue_table(diagnostic_id);
            $('#dialog_no_issues-'+assignment_id).show();
        } else if ($('input#issues-found-'+diagnostic_id).prop('checked')) {
            enable_issue_table(diagnostic_id);
            $('#dialog_no_issues-'+assignment_id).hide();
        }
    });

    $('input.no-issues-radio').on('change', function(event) {
        $(this).blur();
        var diagnostic_id = $(this).attr('data-diagnostic_id');
        var assignment_id = $('table.diagnostic-issues[data-diagnostic_id='+diagnostic_id+']').attr('data-assignment_id');
        disable_issue_table(diagnostic_id);
        $('#dialog-'+assignment_id).hide();
        $('#dialog_no_issues-'+assignment_id).show();
    });

    $('input.issues-found-radio').on('change', function(event) {
        $(this).blur();
        var diagnostic_id = $(this).attr('data-diagnostic_id');
        var assignment_id = $('table.diagnostic-issues[data-diagnostic_id='+diagnostic_id+']').attr('data-assignment_id');
        enable_issue_table(diagnostic_id);
        $('#dialog-'+assignment_id).show();
        $('#dialog_no_issues-'+assignment_id).hide();
    });

    // 1. Select component type (other fields are disabled)
    $('.part_type_dropdown').on('change', function(event) {
        var unit_id = $(this).attr('data-unit_id');
        $(this).blur();

        if ($(this).val() == 0) {
            reset_issue_type_dropdown(unit_id);
        } else {
            $.post(base_url+'miniant/stages/diagnostic_report/get_issue_types/'+$(this).val(), function(data) {
                if (data.issue_types.length > 0) {
                    $('.issue_type_dropdown[data-unit_id='+unit_id+'] option').remove();
                    $('.issue_type_dropdown[data-unit_id='+unit_id+']').append('<option value="0">-- Select One --</option>');

                    for (key in data.issue_types) {
                        var issue_type = data.issue_types[key];
                        $('.issue_type_dropdown[data-unit_id='+unit_id+']').append('<option value="'+issue_type.id+'" data-issue_type_id="'+issue_type.issue_type_id+'">'+
                            issue_type.name+'</option>');
                    }
                }
            }, 'json');
        }
    });

    // 2. Select issue type
    $('.issue_type_dropdown').on('change', function(event) {
        var unit_id = $(this).attr('data-unit_id');
        $(this).blur();

        if ($(this).val() == 0) {
            $('button.new-issue-button.unit-'+unit_id).attr('disabled', 'disabled');
        } else {
            $('button.new-issue-button.unit-'+unit_id).removeAttr('disabled');
        }
    });

    $('button.new-issue-button').click(function() {
        var table = $(this).closest('table');
        var assignment_id = table.attr('data-assignment_id');
        var diagnostic_id = $(this).attr('data-diagnostic_id');

        // Check that all the fields are completed
        var unit_id = $(this).attr('data-unit_id');
        if ($('select[data-unit_id='+unit_id+']').val() == 0) {
            print_message('Make sure you select a component type, and issue and a suggested action before adding this issue', 'danger');
            return false;
        }

        // Save the issue with AJAX
        $.post(base_url+'miniant/stages/diagnostic_report/add_diagnostic_issue', {
            diagnostic_id: diagnostic_id,
            part_type_id: $('.part_type_dropdown[data-unit_id='+unit_id+']').val(),
            issue_type_id: $('.issue_type_dropdown[data-unit_id='+unit_id+'] option:selected').attr('data-issue_type_id'),
            can_be_fixed_now: $('.can_be_fixed_now_dropdown[data-unit_id='+unit_id+']').val(),

        }, function(data) {
            print_message('The issue was successfully recorded', 'success');
            var can_be_fixed_now = $('.can_be_fixed_now_dropdown[data-unit_id='+data.diagnostic_issue.unit_id+'] option:selected');

            var newrow = '<tr data-can_be_fixed_now="'+can_be_fixed_now.val()+'" data-diagnostic_issue_id="'+data.diagnostic_issue.id+'">' +
                '<td>'+$('.part_type_dropdown[data-unit_id='+data.diagnostic_issue.unit_id+'] option:selected').text() + '</td>' +
                '<td>'+$('.issue_type_dropdown[data-unit_id='+data.diagnostic_issue.unit_id+'] option:selected').text() + '</td>' +
                '<td>'+can_be_fixed_now.text() + '</td>' +
                '<td>'+data.upload_view+'</td>' +
                '<td><button class="btn btn-danger delete-issue-button">Delete</button></td>' +
                '</tr>';

            $('#unit-'+data.diagnostic_issue.unit_id+' table.diagnostic-issues > tbody').prepend(newrow);

            $('#dialog-'+assignment_id).show();
            $('#dialog-'+assignment_id+' #continue').hide();

            reset_part_type_dropdown(data.diagnostic_issue.unit_id);
            reset_dialog(assignment_id, diagnostic_id);
            $('#dialog-'+assignment_id+' #recorded_all_issues').show();
            $.post(base_url+'events/undo_event', {event_name: 'no_issues_found', system: 'assignment', document_id: assignment_id, module: 'miniant'});

        }, 'json');

    });

    $('table.diagnostic-issues').delegate('button.delete-issue-button', 'click', function() {
        var table = $(this).closest('table');
        var assignment_id = table.attr('data-assignment_id');
        var diagnostic_id = table.attr('data-diagnostic_id');

        if (deletethis()) {
            $.post(base_url+'miniant/stages/diagnostic_report/delete_diagnostic_issue', { diagnostic_issue_id: $(this).parent().parent().attr('data-diagnostic_issue_id') }, function(data) {
                print_message('The issue was successfully deleted');
                $('tr[data-diagnostic_issue_id='+data.diagnostic_issue_id+']').remove();

                reset_dialog(assignment_id, diagnostic_id);
                if ($('table.diagnostic-issues tr[data-diagnostic_issue_id]').length > 0) {
                    $('#dialog-'+assignment_id+' #recorded_all_issues').show();
                }

            }, 'json');
        }
    });
});

function reset_part_type_dropdown(unit_id) {
    $('.part_type_dropdown[data-unit_id='+unit_id+']').val(0);
    reset_issue_type_dropdown(unit_id);
}

function reset_issue_type_dropdown(unit_id) {
    $('.issue_type_dropdown[data-unit_id='+unit_id+'] option').remove();
    $('.issue_type_dropdown[data-unit_id='+unit_id+']').append('<option value="0">-- Select a Component Type first --</option>');
    $('button.new-issue-button.unit-'+unit_id).attr('disabled', 'disabled');
}

/**
 * Make sure that both repair and SQ questions have been answered before showing the continue button
 */
function reset_dialog(assignment_id, diagnostic_id) {
    $.post(base_url+'events/undo_event', {event_name: 'diagnosed', system: 'assignment', document_id: assignment_id, module: 'miniant'});
    $.post(base_url+'events/undo_event', {event_name: 'issue_photos_hiding_setting_recorded', system: 'assignment', document_id: assignment_id, module: 'miniant'});
    $.post(base_url+'events/undo_event', {event_name: 'repairs_approved', system: 'assignment', document_id: assignment_id, module: 'miniant'});
    $.post(base_url+'events/undo_event', {event_name: 'sq_approved', system: 'assignment', document_id: assignment_id, module: 'miniant'});
    $.post(base_url+'events/undo_event', {event_name: 'completed', system: 'diagnostic', document_id: diagnostic_id, module: 'miniant'});
    $.post(base_url+'events/undo_event', {event_name: 'isolated_and_tagged_recorded', system: 'assignment', document_id: assignment_id, module: 'miniant'});
    $('#dialog-'+assignment_id+' .undo').click();
    $('#dialog-'+assignment_id+' .question').hide();
}

function disable_issue_table(diagnostic_id) {
    $('table.diagnostic-issues[data-diagnostic_id='+diagnostic_id+']').css('opacity', '0.5');
    $('table.diagnostic-issues').find('input,select,button,textarea').attr('disabled', 'disabled');
}

function enable_issue_table(diagnostic_id) {
    $('table.diagnostic-issues[data-diagnostic_id='+diagnostic_id+']').css('opacity', '1');
    $('table.diagnostic-issues').find('input,select,button,textarea').removeAttr('disabled');

}
