var editing_calendar = false;
var tradesman_bookings = [];
var dragging_on = false;

$(document).ready(function() {
    activate_datepickers();

    $('body').delegate('select[name=tradesman_id]', 'change', function(event) {
        $('.alert').remove();
        load_tradesman_bookings($(this).val(), $(this).parents('form').find('input[name=booking_date]').val());
    });
    $('body').delegate('input[name=booking_date]', 'change', function(event) {
        $('.alert').remove();

    });
    $('body').delegate('select[name=tradesman_type_id]', 'change', function(event) {
        filter_tradesmen($(this).val(), $(this).parents('form').find('select[name=tradesman_id]'));
    });

    $('#calendar').fullCalendar({
        firstDay: 1,
        theme: true,
        timezone: 'Australia/Perth',
        columnFormat: 'ddd DD/MM',
        header: {
            left: 'prev,next,today',
            center: 'title',
            right: 'basicWeek,twoWeek,threeWeek'
        },
        defaultView: 'basicWeek',
        views: {
            basicWeek: {
                type: 'basic',
                duration: {weeks: 1},
                rows: 1,
                buttonText: '1 week'
            },
            twoWeek: {
                type: 'basic',
                duration: {weeks: 2},
                rows: 2,
                buttonText: '2 weeks'
            },
            threeWeek: {
                type: 'basic',
                duration: {weeks: 3},
                rows: 2,
                buttonText: '3 weeks'
            },
        },
        defaultDate: $.fullCalendar.moment(current_date*1000),
        selectable: false,
        selectHelper: true,
        editable: true,
        droppable: true, // this allows things to be dropped onto the calendar !!!
        editable: true,
        aspectRatio: 3,
        height: 600,
        eventLimit: true, // allow "more" link when too many events
        eventSources: [
            {
                url: base_url+'building/job_sites/get_bookings',
                type: 'post',
                data: {
                    confirmed: '0',
                    job_site_id: job_site_id
                },
                color: 'orange',
                textColor: 'black'
            },
            {
                url: base_url+'building/job_sites/get_bookings',
                type: 'post',
                data: {
                    confirmed: '1',
                    job_site_id: job_site_id
                },
                color: 'green',
                textColor: 'black'
            },
        ],
        loading: function(stillworking) { // Remove the side scrollbar by extending the height of the calendar after it is rendered
            if (!stillworking) {
                $('table.fc-agenda-days').siblings('div').find('div:first').css('height', '800px');
            }
        },
        eventRender: function(event, element) { // Used to display a mouseover of all event info
            $('#page-wrapper').after(event.description);
            element.find('.fc-title').html(event.title);
            $(element).draggable({delay: 100});

            $(element).on('mouseup', function(event2) {
                var mouseup = new Date();
                if (!dragging_on) {
                    activate_datepickers();
                    $('#booking-'+event.id).on('shown.bs.modal', function(e) {
                        filter_tradesmen($('#booking-'+event.id+' select[name=tradesman_type_id]').val(), $('#booking-'+event.id+' select[name=tradesman_id]'));
                    });
                    $('#booking-'+event.id).modal();
                    $('#booking-'+event.id).on('hidden.bs.modal', function(e) {
                        reset_modal();
                    });
                    editing_calendar = true;
                }
            });

            activate_multiselects();
        },
/*
        eventClick: function(event, jsEvent, view) {
            click_start = new Date();
            $('#booking-'+event.id).modal();
            editing_calendar = true;
        },
*/
        eventDragStart: function(event, jsEvent, ui, view) {
            dragging_on = true;
            $('#booking-'+event.id).modal('hide');
            reset_modal();
            editing_calendar = true;
        },
        // Prevents a single touch from firing up the dragging code, instead loads the editing code
        eventDragStop: function(event, jsEvent, ui, view) {
        },
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) { // Called when an event is moved within the calendar

            dragging_on = false;
            $.post(base_url+'building/job_sites/update_event',
                {
                    id: event.id,
                    job_site_id: event.job_site_id,
                    confirmed: event.confirmed,
                    start: event.start.format('X'),
                    title: event.title,
                    all_day: event.allDay,
                    source: event.source
                },
                function(data, response, xhrStatus) {
                    print_message(data.message, data.type);
                    $('#booking-'+event.id).modal('hide');
                    editing_calendar = false;
                },
                'json'
            );

            $('#calendar').fullCalendar('refetchEvents');
            $('#calendar').fullCalendar('rerenderEvents');
        },
        dayClick: function(date, event, view) {
            $('#new-booking').modal();
            $('#new-booking').on('hidden.bs.modal', function(event) {
                reset_modal();
            });
            $('#new-booking').find('input[name=booking_date]').val(date.format('DD/MM/YYYY'));
            editing_calendar = true;
        },
    });

    $('#building_job_sites_calendar').delegate('.modal-footer button[data-dismiss="modal"]', 'click', function() {
        editing_calendar = false;
    });

    window.setInterval(refresh_calendar, 120000);
});

function refresh_calendar() {
    if (!editing_calendar) {
        display_please_wait_modal = false;
        $('#calendar').fullCalendar('refetchEvents');
        display_please_wait_modal = true;
    }
}

function delete_booking(booking_id) {
    window.location = base_url+'building/job_sites/delete_booking/'+booking_id;
}

function validate_form(form) {
    var tradesman_id = $(form).find('select[name=tradesman_id]');
    var tradesman_type_id = $(form).find('select[name=tradesman_type_id]');
    var booking_date = $(form).find('input[name=booking_date]');
    var confirmed = $(form).find('input[name=confirmed]');

    var errors = [];

    /*
    if (tradesman_id.val() == '' && confirmed.is(':checked')) {
        errors.push({field: 'tradesman_id', description: 'Tradies', element: tradesman_id});
    }
    */

    /*
    if (tradesman_type_id.val() == '') {
        errors.push({field: 'tradesman_type_id', description: 'Trade', element: tradesman_type_id});
    }
    */

    if ($.trim($(booking_date).val()).length == 0) {
        errors.push({field: 'booking_date', description: 'Booking date', element: booking_date});
    }

    if (errors.length > 0) {
        $.each(errors, function(key, item) {
            display_message('danger', 'Please enter a value for the '+item.description+' field.', $(form).find('.form-group:first'));
            $(item.element).addClass('warning');
        });
        return false;
    }

    return true;
}

function load_tradesman_bookings(tradesman_id, current_date) {
    tradesman_bookings = [];


    $.post(base_url+'building/tradesmen/get_bookings', { id: tradesman_id },
        function(data, response, xhrStatus) {

            $.each(data, function(key, booking) {
                tradesman_bookings.push(booking.booking_date);
                var formatted_booking_date = unix_to_human(booking.booking_date);

                // Display a warning if this tradesman is already booked for the selected date
                if (formatted_booking_date == current_date) {
                    display_message('danger', 'This tradie is already booked on another site for that day!', '#new-booking select[name=tradesman_id]');
                }
            });
            building_activate_datepickers();
        },
        'json'
    );
}

function building_activate_datepickers() {
    // Re-do the datepickers: days that are already booked for a given tradesmen must be highlighted in RED
    $('.date_input').datepicker("destroy");

    $('.date_input').datepicker({
        dateFormat: 'dd/mm/yy',
        showAnim: 'drop',
        beforeShowDay: function(date) {
            var is_booked = false;

            for (item in tradesman_bookings) {
                var booking_date = new Date(tradesman_bookings[item] * 1000);

                if (booking_date.toDateString() == date.toDateString()) {
                    is_booked = true;
                }
            };

            if (is_booked) {
                return [false, 'booked', 'This tradie is already booked on that day'];
            } else {
                return [true, 'non-booked'];
            }
        }
    });
}

function reset_modal() {
    $('select[name=tradesman_id]').val('');
    $('input[name=booking_date]').val('');
    $('textarea[name=message]').val('');
    $('select[name=recipients]').val('');
    $('.alert').remove();
}

function filter_tradesmen(tradesman_type_id, tradesman_select_element) {
    $.post(base_url+'building/tradesmen/get_tradesmen', {tradesman_type_id: tradesman_type_id},
        function(data, response, xhrStatus) {
            $(tradesman_select_element).find('option').remove();
            $(tradesman_select_element).find('optgroup').remove();

            $.each(data, function(id, name) {
                var option = document.createElement('option');
                $(option).val(id);
                $(option).html(name);
                $(tradesman_select_element).prepend(option);
            });

            $(tradesman_select_element).val($(tradesman_select_element).find('option:first').val());
        }, 'json'
    );
}
