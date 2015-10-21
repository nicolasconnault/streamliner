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
 * Redraws the form to reflect current state of DB
 */

function redraw() {
    if (user_id == 0) {
        return;
    }

    $.getJSON('users/'+uri_section+'/get_data/' + user_id, function(data) {
        $('#pagetitle td.title').text(data.title);

        $('input[name=user_id]').val(data.user_id);
        $('#user_id').text(data.user_id);
        $('input[name=first_name]').val(data.first_name);
        $('input[name=last_name]').val(data.last_name);

        if (type == 'staff') {
            $('input[name=username]').val(data.username);
            $('input[name=password]').val(data.password);
            $('textarea[name=signature]').val(data.signature);
        }

        // Empty list first before redrawing
        $('#email').text('');
        $('#phone').text('');
        $('#mobile').text('');
        $('#fax').text('');

        // Contacts
        if (isUndefined(data.user_id) || data.user_id < 1) {
            $('#email').text('Enter and save the User details first');
            $('#phone').text('Enter and save the User details first');
            $('#mobile').text('Enter and save the User details first');
            $('#fax').text('Enter and save the User details first');
        } else {
            $('#email').append(get_contact_list(data.emails, 'email'));
            $('#phone').append(get_contact_list(data.phones, 'phone'));
            $('#mobile').append(get_contact_list(data.mobiles, 'mobile'));
            $('#fax').append(get_contact_list(data.faxes, 'fax'));
        }
    });
}

function change_default_contact(e) {
    $('#'+e.data.type).loading();
    $.post('users/'+uri_section+'/update_default_contact/'+this.value, {}, function(data) {
        print_edit_message('contacts', data);
        redraw();
    });
}

function set_notification(e) {
    $('#'+e.data.type).loading();
    $.post('users/'+uri_section+'/set_notification/'+this.value+'/'+this.checked, {}, function(data) {
        print_edit_message('contacts', data);
        redraw();
    });
}

function delete_contact(e) {
    $('#'+e.data.type).loading();
    $.post('users/'+uri_section+'/delete_contact/'+e.data.contact_id, {}, function(data) {
        print_edit_message('contacts', data);
        redraw();
    });
}

function save_contact(input) {
    $.post('users/'+uri_section+'/save_contact', { user_id: user_id, field_name: input.name, value: $(input).val() }, function(data) {
        print_edit_message('contacts', data);
        data = $.evalJSON(data);
        if (data.errors.length < 1) {
            redraw();
        } else {
            $.each(data.errors, function(field, error) {
                print_error(field, error);
            });
        }
    });
}

function get_contact_list(contacts, type) {
    var contactscount = contacts.length;
    var contactlist = document.createElement('ul');

    if (contacts.length > 0) {
        $.each(contacts, function(key, contact) {

            var listitem = document.createElement('li');
            var contactinput = document.createElement('input');
            var name = type+'['+contact.id+']';
            $(contactinput).attr({
                'type': 'text',
                'name': name,
                'size': 40,
                'id': type+'_'+contact.id,
                'value': contact.contact
                });

            $(contactinput).bind('blur', function(e) {
                if ($(this).val() == '') {
                    $(this).val('New '+type+'...');
                } else {
                    $('#'+type).loading();
                    save_contact(this);
                }
            });

            $(contactinput).bind('keypress', function(e) {
                if (e.keyCode == 13) { // ENTER
                    $(this).blur();
                } else if (e.keyCode == 27) { // ESC
                    $(this).val(contact.detail);
                    $('#'+type+'_0').focus();
                }
            });

            $(listitem).append(contactinput);

            // Notification checkbox
            if (contact.type == 1) {
                notifylabel = document.createElement('label');
                notifycheckbox = document.createElement('input');
                $(notifylabel).attr('for', 'receive_notifications['+contact.id+']');
                $(notifylabel).text('Receive notifications');
                $(notifycheckbox).attr('type', 'checkbox');
                $(notifycheckbox).attr('id', 'receive_notifications['+contact.id+']');
                $(notifycheckbox).attr('name', 'receive_notifications['+contact.id+']');
                $(notifycheckbox).attr('value', contact.id);
                $(notifycheckbox).bind('click', {type: type}, set_notification);

                if (contact.receive_notifications == 1) {
                    $(notifycheckbox).attr('checked', 'checked');
                }
                $(listitem).append(notifycheckbox);
                $(listitem).append(notifylabel);
            }

            // Default checkbox
            defaultlabel = document.createElement('label');
            defaultradio = document.createElement('input');
            var iddefault = 'default_'+type+'_'+contact.id;
            $(defaultlabel).attr('for', iddefault);
            $(defaultlabel).text('Default');
            $(defaultradio).attr('type', 'radio');
            $(defaultradio).attr('id', iddefault);
            $(defaultradio).attr('name', 'default_'+type);
            $(defaultradio).attr('value', contact.id);
            $(defaultradio).bind('click', {type: type}, change_default_contact);

            if (contact.default_choice == 1) {
                $(defaultradio).attr('checked', 'checked');
            }
            $(listitem).append(defaultradio);
            $(listitem).append(defaultlabel);

            // Delete link
            if (contactscount > 1 || contact.type != 'Email') {
                deletelink = document.createElement('i');
                $(deletelink).addClass('fa fa-trash-o');
                $(deletelink).attr('title', 'Delete this '+contact.type);
                $(deletelink).bind('click', {type: type, contact_id: contact.id}, delete_contact);
                $(listitem).append(deletelink);
            }

            $(contactlist).append(listitem);
        });
    }

    // New contact
    var listitem = document.createElement('li');
    var newcontactlink = document.createElement('button');
    $(newcontactlink).addClass('newcontact btn btn-default').html('New '+type).on('click', function() {
        // Disable the new option (hide it)
        $(this).hide();
        var new_button = this;

        // Insert a new option input field before it
        var newcontact = document.createElement('li');
        var contactinput = document.createElement('input');
        var cancelbutton = document.createElement('a');
        var newcontactbutton = document.createElement('a');

        $(contactinput).attr('type', 'text');
        $(contactinput).attr('name', type+'[0]');
        $(contactinput).attr('id', type+'_0');
        $(contactinput).attr('placeholder', 'New '+type+'...');

        $(this).append(contactinput);

        $(newcontactbutton).addClass('btn').html('Add').on('click', function(event) {
            event.preventDefault();
            save_contact(contactinput);
        });

        $(cancelbutton).addClass('btn').html('Cancel').on('click', function(event) {
            $(contactinput).remove();
            $(cancelbutton).remove();
            $(newcontactbutton).remove();
            $(newcontact).remove();
            $(new_button).show();
        });

        $(newcontact).addClass('new-contact-input');
        $(newcontact).html('<i class="fa fa-blank"></i><i class="fa fa-blank"></i><i class="fa fa-blank"></i>');
        $(newcontact).append(contactinput);

        $(newcontact).append(newcontactbutton);
        $(newcontact).append(cancelbutton);
        $(this).before(newcontact);
        $(contactinput).focus();

        $(contactinput).on('keydown', function(event) {
            if (event.which == 27) { // ESC
                $(newcontact).remove();
                $(new_button).show();
            } else if (event.which == 13) { // Enter
                event.preventDefault();
                save_contact(this);
            }
        });
    });

    $(listitem).append(newcontactlink);
    $(contactlist).append(listitem);

    return contactlist;
}

function processJson(data) {
    // 'data' is the json object returned from the server

    $('textarea[name=signature]').val(data.first_name);
    $('.error').text('');
    $.each(data.errors, function(field, error) {
        print_error(field, error);
    });
    user_id = data.user_id;

    if (user_id > 0) {
        $('input[type=submit]').val('Update...');
    }

    print_edit_message('details', $.toJSON(data));
    redraw();
}


// prepare the form when the DOM is ready
$(document).ready(function() {
    // Add an id to each form element
    $('#userform input,select,textarea,password').each(function(index) {
        $(this).attr('id', $(this).attr('name'));
    });

    // Add a "reveal" checkbox next to password field
    // bind form using ajaxForm
    $('#userform').ajaxForm({
        // dataType identifies the expected content type of the server response
        dataType:  'json',
        beforeSerialize: function($form, options) {

        },
        // success identifies the function to invoke when the server response
        // has been received
        success:   processJson
    });
    redraw();

});
