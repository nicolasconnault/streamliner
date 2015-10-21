//
// Copyright 2015 SMB Streamline
//
// Contact nicolas <nicolas@smbstreamline.com.au>
//
// Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
// Public License (the "License"). You may not use this file except
// in compliance with the License. Roughly speaking, non-commercial
// users may share and modify this code, but must give credit and
// share improvements. However, for proper details please
// read the full License, available at
//  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
// and the handy reference for understanding the full license at
//  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
//
// Unless required by applicable law or agreed to in writing, any
// software distributed under the License is distributed on an
// "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
// either express or implied. See the License for the specific
// language governing permissions and limitations under the License.
//
var open_popovers = [];
function deletethis(link, message) {
    if (null == message) {
        message = 'Are you sure you want to delete this? This cannot be undone.';
    }

    var confirmed = confirm(message);
    if (confirmed == true && link != null) {
        window.location = link;
    } else if(confirmed == true) {
        return true;
    } else {
        return false;
    }
}

function print_message(message, type) {
    $('.top-left').notify({
        message: { html: message },
        type: type
    }).show();
}

function print_edit_message(section, data) {
    data = $.evalJSON(data);
    print_message(data.message, data.type, section+'message');
}

function afterDelayedEvent(eventtype, selector, action, delay) {
    $(selector).bind(eventtype, function() {
        if (typeof(window['inputTimeout']) != "undefined") {
            clearTimeout(inputTimeout);
        }
        inputTimeout = setTimeout(action, delay);
    });
}
/**
* The following are useful functions that should have been part of Javascript
*/
function isAlien(a) {
   return isObject(a) && typeof a.constructor != 'function';
}

function isArray(a) {
    return isObject(a) && a.constructor == Array;
}

function isBoolean(a) {
    return typeof a == 'boolean';
}

function isEmpty(o) {
    var i, v;
    if (isObject(o)) {
        for (i in o) {
            v = o[i];
            if (isUndefined(v) && isFunction(v)) {
                return false;
            }
        }
    }
    return true;
}

function isFunction(a) {
    return typeof a == 'function';
}

function isNull(a) {
    return typeof a == 'object' && !a;
}

function isObject(a) {
    return (a && typeof a == 'object') || isFunction(a);
}

function isString(a) {
    return typeof a == 'string';
}

function isUndefined(a) {
    return typeof a == 'undefined';
}

// Function for revealing the contents of a password field
function reveal_password(name, checkbox) {
    var reveal = $(checkbox).is(':checked');

    var password_field = $('input[name='+name+']');
    var text_password_name = 'text_' + $(password_field).attr('name');

    if ($('input[name="'+text_password_name+'"]').length == 0) {
        var text_password = document.createElement('input');
        $(text_password).attr('type', 'text');
        $(text_password).addClass('form-control password');
        $(text_password).attr('name', text_password_name);
        $(text_password).attr('value', $(password_field).attr('value'));
        $(text_password).attr('size', $(password_field).attr('size'));
        $(text_password).attr('style', $(password_field).attr('style'));
        $(password_field).after(text_password);
    } else {
        var text_password = $('input[name="'+text_password_name+'"]');
    }

    if (reveal) {
        $(text_password).attr('value', $(password_field).attr('value'));
        $(text_password).show();
        $(password_field).hide();
    } else {
        $(password_field).attr('value', $(text_password).attr('value'));
        $(text_password).hide();
        $(password_field).show();
    }
}
function print_error(element, message) {
    errorspan = document.getElementById(element+'_error');

    if (isNull(errorspan)) {
        var errorspan = document.createElement('span');
        $(errorspan).addClass('error');
        $(errorspan).attr('id', element+'_error');
        $(errorspan).text(message);
        $('#'+element).after(errorspan);
    } else {
        $(errorspan).text(message);
    }
}

function print_edit_message(section, data) {
    data = $.evalJSON(data);
    print_message(data.message, data.type, section+'message');
}

function display_message(type, message, target_element) {
    var message_element = null;

    if (undefined == target_element) {
        message_element = '#message';
        $('html, body').animate( { scrollTop: 0}, 200);
    } else {
        message_element = document.createElement('div');
        $(message_element).addClass('alert in fade');
        $(message_element).insertBefore(target_element);
    }

    $(message_element).hide();
    $(message_element).removeClass('alert-success');
    $(message_element).removeClass('alert-warning');
    $(message_element).removeClass('alert-danger');
    $(message_element).removeClass('alert-info');
    $(message_element).addClass('alert-'+type);
    $(message_element).html(message);
    $(message_element).show(200);
}

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
};

function has_capability(capname) {
    for (cap in caps) {
        if (capname == caps[cap] || caps[cap] == 'site:doanything') {
            return true;
        }
    }
    return false;
}
// HTML-BUILDING FUNCTIONS
function make_label(forwho, text) {
    var label = document.createElement('label');
    $(label).attr('for', forwho);
    $(label).text(text);
    return label;
}

function make_autocomplete_input(id, name, value, labeltext, ajaxurl, callback, unselectedcallback, inputclass, resultsclass) {
    var cache = {}, lastXhr;
    var input = make_text_input(id, name, value);
    var label = make_label(id, labeltext);

    $(input).autocomplete({
        minLength: 2,
        delay: 100,
        max: 50,
        open: function(event, ui) {
            menudisplay = true;
        },
        close: function(event, ui) {
            menudisplay = false;
        },
        select: callback,
        source: function (request, response) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }

            lastXhr = $.post(ajaxurl, request, function(data, status, xhr) {
                cache[term] = data;
                if (xhr === lastXhr) {
                    response(data);
                }
            }, 'json');
        }
    });
    return {input: input, label: label};
}

function make_text_input(id, name, value) {
    var input = document.createElement('input');
    $(input).attr('id', id);
    $(input).attr('type', 'text');
    $(input).attr('name', name);
    $(input).attr('value', value);
    $(input).attr('size', 60);
    return input;
}

function toggle_submit(element, is_not_empty) {
    is_empty = !validate_value($(element).val(), element, null, 'required');

    parent_form = $(element).closest('form');

    if (is_empty) {
        // console.log('Required element '+$(element).attr('name')+ ' is empty: disable the submit button');
        parent_form.find('input.submit_button').attr('disabled', 'disabled');
    } else {
        mustenable = true;
        // console.log('Required element '+$(element).attr('name')+ ' is not empty: check all required elements');

        parent_form.find('[data-required]').each(function(key, item) {
            if (!validate_value($(item).val(), item, null, 'required')) {
                // console.log('Required element '+$(item).attr('name') + ' is empty');
                mustenable = false;
            } else {
                // console.log('Required element '+$(item).attr('name') + ' is NOT empty');
            }
        });

        if (mustenable) {
            // console.log('Required elements are all filled: enable the submit button');
            parent_form.find('input.submit_button').removeAttr('disabled');
        } else {
            // console.log('Not all required elements are filled: disable the submit button');
            parent_form.find('input.submit_button').attr('disabled', 'disabled');
        }
    }
}

/**
 * @param jQuery trigger_element
 * @param jQuery target_element
 * @param Boolean negative D
 */
function add_disabled_event_handler(trigger_element, target_element, negative, trigger_value) {
    trigger_element.on(get_trigger_element_type(trigger_element), function() {
        toggle_target_element(trigger_element, target_element, negative, trigger_value);
    });
}

function get_trigger_element_type(trigger_element) {
    if (trigger_element.is('span')) {
        return null;
    } else if (trigger_element.is('input')) {
        switch (trigger_element.attr('type')) {
            case 'checkbox' :
            case 'radio' :
                return 'click';
            default:
                return 'keyup';
        }
    } else if (trigger_element.is('select')) {
        return 'change';
    }
}

function toggle_target_element(trigger_element, target_element, negative, trigger_value) {

    disabledby_elements = (target_element.attr('data-disabledby')) ? target_element.attr('data-disabledby').split(',') : [];
    trigger_element_type = get_trigger_element_type(trigger_element);

    must_disable = false;
    if (!isNull(trigger_value)) {
        var value_parts = trigger_value.split('|');
    }

    switch(trigger_element_type) {
        case 'click':
            must_disable = trigger_element.is(':checked');
            break;
        case 'keyup':
            must_disable = trigger_element.val().length > 0;
            if (!isNull(trigger_value)) {
                if (value_parts.length > 0) {
                    var boolean_test = false;
                    for (key in value_parts) {
                        boolean_test = boolean_test || trigger_element.val() == value_parts[key];
                    }
                    must_disable = boolean_test;
                } else {
                    must_disable = trigger_element.val() == trigger_value;
                }
            }
            break;
        case 'change':
            must_disable = trigger_element.val() > 0;

            if (!isNull(trigger_value)) {
                if (value_parts.length > 0) {
                    var boolean_test = false;
                    for (key in value_parts) {
                        boolean_test = boolean_test || trigger_element.val() == value_parts[key] || trigger_element.find('option[selected="selected"]').val() == value_parts[key];
                    }

                    must_disable = boolean_test;
                } else {
                    must_disable = trigger_element.val() == trigger_value;
                }
            }
            break;
        default: // For static elements

            trigger_element_value = trigger_element.attr('data-id');
            if (undefined === trigger_element_value) {
                return false;
            }

            must_disable = trigger_element_value.length > 0;

            if (!isNull(trigger_value)) {
                if (value_parts.length > 0) {
                    var boolean_test = false;
                    for (key in value_parts) {
                        boolean_test = boolean_test || trigger_element_value == value_parts[key];
                    }
                    must_disable = boolean_test;
                } else {
                    must_disable = trigger_element_value == trigger_value;
                }
            }

    }

    if (negative) {
        must_disable = !must_disable;
    }

    if (must_disable) {

        if (target_element.is('span')) {
            target_element.parent().parent().hide();
            return false;
        }

        target_element.attr('disabled', 'disabled');
        target_element.parents('div.form-group').addClass('disabled');

        if (target_element.attr('data-disabledby')) {
            var found_element = false;

            $(disabledby_elements).each(function(key, item) {
                if (item == trigger_element.attr('name')) {
                    found_element = true;
                }
            });

            if (!found_element) {
                disabledby_elements.push(trigger_element.attr('name'));
                target_element.attr('data-disabledby', disabledby_elements.join(','));
            }
        } else {
            target_element.attr('data-disabledby', trigger_element.attr('name'));
        }
    } else {

        if (disabledby_elements.length > 1) {
            $(disabledby_elements).each(function(key, item) {
                if (item == trigger_element.attr('name')) {
                    target_element.attr('data-disabledby', disabledby_elements.splice(key, 1).join(','));
                }
            });
        } else {
            target_element.removeAttr('disabled');
            target_element.removeAttr('data-disabledby');
            target_element.parents('div.form-group').removeClass('disabled');
        }
    }
}

function open_popup_form(link, return_link) {
    // TODO I'm trying to load up a specific page and pass an extra POST param (return_url), so that, upon completing the form on the other page, it returns to this URL
    $.post(link, {return_link: return_link}, function(data) {
        document.open();
        document.write(data);
        document.close();
        $.cache = {};
    });
}

function validate_popover_form(form_element) {
    found_errors = [];
    $('span.error').remove();

    form_element.find('textarea,select,input').each(function(key, item) {
        $(item).removeClass('warning');
        var item_is_required = false;
        var item_is_empty = !validate_value($(item).val(), item, null, 'required');
        if ($(item).attr('data-required') == '1' && $(this).attr('disabled') != 'disabled') {
            item_is_required = true;

            if (item_is_empty) {
                found_error = true;
                var element_name = ($(item).attr('placeholder')) ? $(item).attr('placeholder') : $(item).attr('data-label');
                found_errors.push({elementname: $(item).attr('name'), message: "A value for "+element_name+" is required"});
                $(item).addClass('warning');
            }
        }

        if ((!item_is_required && !item_is_empty) || item_is_required) {
            // console.log("item "+$(item).attr('name')+ " is required? " + item_is_required +'. And empty? '+item_is_empty);
            if (($(item).attr('type') == 'email' || $(item).attr('type') == 'url') && !validate_value($(item).val(), item, null, $(item).attr('type'))) {
                found_error = true;
                found_errors.push({elementname: $(item).attr('name'), message: $(item).attr('placeholder')+" must be a valid " + $(item).attr('type')});
                $(item).addClass('warning');
            }
        }
    });
    return found_errors;
}

function validate_value(validation_value, validation_element, validation_param, validation_method) {
	var methods = {

		// http://docs.jquery.com/Plugins/Validation/Methods/required
		required: function( value, element, param ) {
			if ( element.nodeName.toLowerCase() === "select" ) {
				// could be an array for select-multiple or a string, both are fine this way
				var val = $(element).val();
				return val && val.length > 0;
			}
			return $.trim(value).length > 0;
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/email
		email: function( value, element ) {
			// contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
			return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(value);
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/url
		url: function( value, element ) {
			// contributed by Scott Gonzalez: http://projects.scottsplayground.com/iri/
			return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/date
		date: function( value, element ) {
			return !/Invalid|NaN/.test(new Date(value).toString());
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/dateISO
		dateISO: function( value, element ) {
			return /^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/.test(value);
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/number
		number: function( value, element ) {
			return /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value);
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/digits
		digits: function( value, element ) {
			return /^\d+$/.test(value);
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/minlength
		minlength: function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : $.trim(value).length;
			return length >= param;
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/maxlength
		maxlength: function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : $.trim(value).length;
			return length <= param;
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/rangelength
		rangelength: function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : $.trim(value).length;
			return ( length >= param[0] && length <= param[1] );
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/min
		min: function( value, element, param ) {
			return value >= param;
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/max
		max: function( value, element, param ) {
			return value <= param;
		},

		// http://docs.jquery.com/Plugins/Validation/Methods/range
		range: function( value, element, param ) {
			return ( value >= param[0] && value <= param[1] );
		}
    };

    return methods[validation_method](validation_value, validation_element, validation_param);
}

function supports_touch() {
    return 'ontouchstart' in window || navigator.msMaxTouchPoints;
}

/**
 * This function is called once when the page loads, but it must also be called each time a popover with conditional fields is shown (shown.bs.popover event)
 */
function setup_conditional_form_elements() {
    $('[data-disabledif]').each(function(key, target_element) {
        // If the element is rendered statically, it will be a span, not an input or select
        var form_element = this;
        $($(target_element).attr('data-disabledif').split(',')).each(function(key2, element_name) {
            var split_key = element_name.split('=');
            var trigger_value = null;

            if (split_key.length == 2) {
                trigger_value = split_key[1];
                element_name = split_key[0];
            }

            trigger_element = $('[name='+element_name+'],[id='+element_name+']');
            if (undefined == trigger_element.attr('id') && !trigger_element.hasClass('form-control')) {
                alert('Could not find a trigger element with name/id of '+element_name+'!');
                return false;
            }

            if ($(target_element).is('span')) {
                target_element = $(target_element);
            } else {
                target_element = $(target_element).find('select,input,textarea,button.btn-success');
            }

            // If the trigger element is missing, it means it's disabled and we can safely ignore further conditional rules
            if ($(target_element).is('span') && undefined == trigger_element.attr('id')) {
                return false;
            }

            add_disabled_event_handler(trigger_element, target_element, false, trigger_value);
            toggle_target_element(trigger_element, target_element, false, trigger_value);
        });
    });

    $('[data-disabledunless]').each(function(key, target_element) {
        var form_element = this;

        $($(target_element).attr('data-disabledunless').split(',')).each(function(key2, element_name) {
            var split_key = element_name.split('=');
            var trigger_value = null;

            if (split_key.length == 2) {
                trigger_value = split_key[1];
                element_name = split_key[0];
            }

            trigger_element = $('[name='+element_name+'],[id='+element_name+']');
            if (undefined == trigger_element.attr('id') && !trigger_element.hasClass('form-control')) {
                alert('Could not find a trigger element with name/id of '+element_name+'!');
                return false;
            }

            if (!(target_element instanceof jQuery)) {
                if ($(target_element).is('span')) {
                    target_element = $(target_element);
                } else {
                    target_element = $(target_element).find('select,input,textarea,button.btn-success');
                }
            }
            // If the trigger element is missing, it means it's disabled and we can safely ignore further conditional rules
            if ($(target_element).is('span') && undefined == trigger_element.attr('id')) {
                return false;
            }

            add_disabled_event_handler(trigger_element, target_element, true, trigger_value);
            toggle_target_element(trigger_element, target_element, true, trigger_value);
        });
    });
}

function unix_to_human(timestamp_in_seconds) {
    return $.datepicker.formatDate('dd/mm/yy', $.datepicker.parseDate('@', timestamp_in_seconds * 1000));
}

function close_all_popovers() {
    $.each(open_popovers, function(key, item) {
        $(item).popover('hide');
    });
    open_popovers = [];
    $('.submit_button').removeAttr('disabled');

    // TODO Remove the Selected attribute of all options in the popover, and clear the values of input fields
}

function activate_datepickers() {
    if (jQuery().datepicker) {

        // Setup datepicker defaults
        $.datepicker.setDefaults({
            dateFormat: 'dd/mm/yy'
        });

        $('.date_input').datepicker({
            dateFormat: 'dd/mm/yy',
            showAnim: 'drop'
        });
    } else {
        console.error('Datepicker plugin is not loaded!');
    }

    if (jQuery().datetimepicker) {

        $('.datetime_input').datetimepicker({
            dateFormat: 'dd/mm/yy',
            showAnim: 'drop',
            minuteGrid: 10,
            stepMinute: 10,
            hourMin: 5,
            hourMax: 20,
        });
    } else {
        // console.error('Timepicker plugin addon is not loaded!');
    }
}

function activate_multiselects() {
    // Enable Select2 Multi-selects
    $('select.multiselect').select2();
}
