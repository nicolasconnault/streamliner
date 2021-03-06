$(function() {

    $('#task-notes').on('show.bs.modal', function(event) {
        var notes = $(event.relatedTarget).attr('data-notes');
        var name = $(event.relatedTarget).attr('data-name');
        var task_id = $(event.relatedTarget).attr('data-task_id');
        var required = $(event.relatedTarget).attr('data-required');

        update_task_notes(notes, name, task_id, required);
    });

    $('#task-notes').on('shown.bs.modal', function(event) {
        $('textarea[name="task-notes"]').focus();
    });

    $('#save-task-notes').on('click', function(event) {
        var task_id = $('textarea[name="task-notes"]').attr('data-task_id');
        var notes = $('textarea[name="task-notes"]').val();
        var required = $('textarea[name="task-notes"]').parent().hasClass('required');

        if (required && notes.length < 1) {
            event.preventDefault();
            event.stopPropagation();
            print_message('You must enter some notes!', 'danger');
            return false;
        }

        save_installation_notes(task_id, notes);

        if (required) { // This means that a NO (not satisfactory) button was clicked by a supervisor
            $.post(base_url+'miniant/stages/installation_checklist/set_task_status', {type: 'satisfactory', status: 0, installation_task_id: task_id}, function(data) {
                var verify_no_button = $('button.verify-button.tick_no[data-task_id="'+task_id+'"]');
                verify_no_button.removeClass('btn-default').addClass('btn-danger');
                verify_no_button.siblings('.tick_yes').removeClass('btn-success').addClass('btn-default');

                var completed_no_button = $('button.completed-button.tick_no[data-task_id="'+task_id+'"]');
                completed_no_button.click();
            }, 'json');
        }
    });
});

function open_task_notes(task_id, unit_id, name, notes, required, button) {
    $('#task-notes').modal('show');
    update_task_notes(notes, name, task_id, required);
}

function update_task_notes(notes, name, task_id, required) {
    if (notes === 'null') {
        notes = '';
    }
    $('textarea[name="task-notes"]').val(notes);

    if (required) {
        $('textarea[name="task-notes"]').parent().addClass('required');
    } else {
        $('textarea[name="task-notes"]').parent().removeClass('required');
    }

    $('textarea[name="task-notes"]').attr('data-task_id', task_id);
    $('#task-notes-label').html('Task notes: '+ name);
}

function save_installation_notes(task_id, notes) {
    $.post(base_url+'miniant/stages/installation_checklist/save_installation_task_notes', {task_id: task_id, notes: notes}, function(data) {
        print_message(data.message, data.type);
        $('span#task-notes-'+task_id).html(notes);
        $('button[data-task_id="'+task_id+'"]').attr('data-notes', notes);
        $('#task-notes').modal('hide');
    }, 'json');

}
