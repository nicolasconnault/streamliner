$(function() {
    $('.dowd_description').each(function(key, item) {
        var form_id = $(this).parents('form').attr('id');
        var dowd_id = $(this).attr('data-dowd_id');
        var diagnostic_issue_id = $(this).attr('data-diagnostic_issue_id');
        var description_textarea = $(this).find('textarea');
        if (description_textarea.val().length > 0) {
            return false;
        }

        $.post(base_url+'miniant/stages/dowds/get_dowd_description', { dowd_id: dowd_id, diagnostic_issue_id: diagnostic_issue_id}, function(data) {
            description_textarea.val(data.description);
        }, 'json');
    });
});
