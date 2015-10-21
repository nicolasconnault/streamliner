/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
/**
 * Email selection management
 */
function update_enquirers(type, country) {
    var country_id = country.value;

    $('#'+type+'_enquirers').fadeTo("fast", 0.33);

    $.getJSON('email/fetch_user_emails/'+country_id, function(json) {
        eval('enquirers_'+type+' = new Array();');

        var enquirers_dd = $('#'+type+'_enquirers')[0];
        var firstoption = enquirers_dd.options[0];
        var enquirers_counter = 0;

        $('#'+type+'_enquirers').empty();

        $('#'+type+'_enquirers').append(firstoption);

        $.each(json, function(i, val) {
            if (val.email.length > 0 && val.email !== undefined) {
                $('#'+type+'_enquirers').append(new Option(val.name, val.email));
                eval('enquirers_'+type+'.push({email: val.email, user_id: val.user_id});');
                enquirers_counter++;
            }
        });

        $('#'+type+'_enquirers').fadeTo("fast", 1);
        $('#addallenquirers_'+type).attr('value', 'Add all ('+enquirers_counter+')');
    });
}

function addAllAdmins(textareaid) {
    for (var i = 0; i < admins.length; i++) {
        appendAddress($('#'+textareaid)[0], admins[i].email);
    }
}

function addAllEnquirers(type, textareaid) {
    eval("$.each(enquirers_"+type+", function(i, val) { appendAddress($('#'+textareaid)[0], val.email); });");
}

function appendAddress(target, address) {
    // If the target textarea already has text, make sure it ends with a comma before appending
    re = /^.*, *$/;
    if (target.value.length > 0 && !re.exec(target.value)) {
        target.value += ', ';
    }

    target.value += address + ', ';
}

/**
 * Calls a php script that generates the file, then redirects to serve_file.php for download. Rest of the page should stay as is.
 */
function download_csv(recipienttype) {
    var data = eval("enquirers_"+recipienttype+";");
    var users = new Array();
    for (var i = 0; i < data.length; i++) {
        users.push(data[i].user_id);
    }
    $.post('email/save_csv_email_list', { userids: $.toJSON(users) }, function(data) {
        window.location='/email/download_csv';
    });
}

/**
 * Attachment management
 */
function addAttachmentInput() {

    contindex = $('#attachment-td span:has(input)').length;

    var attachmentcontainer = $('<span id="attachmentcontainer'+contindex+'"></span>');
    var input = $('<input type="file" name="attachments[]" id="attachment'+contindex+'" />');
    var removeLink = $('<span onclick="removeAttachment(this)" id="removelink'+contindex+'" class="removeattachment">Remove</span>');
    var br = $('<br />');

    addattachment_span = $('#addattachment-span').remove();

    attachmentcontainer.append(input);
    attachmentcontainer.append(removeLink);
    attachmentcontainer.append(br);

    $('#attachment-td').append(attachmentcontainer);

    if ($('#attachment-td span:has(input)').length < 7) {
        $('#attachment-td').append(addanother[0]);
        $('#'+addanother.attr('id')).html('Add another attachment');
    }
}

function removeAttachment(span) {
    var index = span.id.substring(10,11);
    var containers = $('#attachment-td span:has(input)');

    for (var i = 0; i < containers.length; i++) {
        if (containers[i].id == 'attachmentcontainer'+index) {
            var deletedContainer = containers[i];
            $('#'+deletedContainer.id).remove();
            break;
        }
    }

    if ($('#attachment-td span:has(input)').length == 0) {
        $('#addattachment-span:has(input)').remove();
        $('#attachment-td').append(addattachment_span);
    } else if ($('#attachment-td span:has(input)').length == 1) {
        $(addanother.id).remove();
        $('#attachment-td').append(addattachment_span);
    } else if ($('#attachment-td span:has(input)').length < 7) {
        $('#attachment-td').append(addanother);
    }
}

function toggleSig(state) {
    if (state) {
        $('#body').attr('value', $('#body').attr('value') + signature);
    } else {
        var bodycontent = $('#body').attr('value');
        // Remove everything starting with 20 dashes: signature
        var split = bodycontent.split('\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-'); // Windows carriage returns

        $('#body').attr('value', split[0]);
    }
}

function validateForm(form) {
    var errors = false;
    var messages = '';

    if (form.subject.value.length < 1) {
        messages += 'You must enter a subject for this email' + "\n";
        errors = true;
    }

    if (form.body.value.length < 1) {
        messages += 'You must enter a message for this email' + "\n";
        errors = true;
    }

    if (form.to.value.length < 1) {
        messages += 'You must enter at least one email address to which to send this email.' + "\n";
        errors = true;
    }

    if (messages.length > 0) {
        alert(messages);
    }

    return !errors;
}
