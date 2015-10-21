$(function() {
    $('div.draggable-event').each(function() {

        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn't need to have a start or end
        var eventObject = {
            title: $.trim($(this).text()), // use the element's text as the event title
            order_id: parseInt($(this).attr('data-orderid'))
        };

        // store the Event Object in the DOM element so we can get to it later
        $(this).data('eventObject', eventObject);

        // make the event draggable using jQuery UI
        $(this).draggable({
            zIndex: 999,
            revert: true,      // will cause the event to go back to its
            revertDuration: 0  //  original position after the drag
        });

    });

    $('#schedule-table tfoot th').each( function () {
        var title = $('#schedule-table thead th').eq( $(this).index() ).text();
        if (title == 'Tasks') {
            return;
        }
        $(this).html( '<input type="text" placeholder="Search" size="12" />' );
    } );

    var table = $('#schedule-table').DataTable({
        "bProcessing": true,
        "bServerSide": false,
        "bLengthChange": false,
        "asStripClasses": ['odd', 'even'],
        "iDisplayLength": 5,
        "sDom": "<'row'<'span8'l><'span8'>ip>rt<'row'<'span8'><'span8'>>",
        "bJQueryUI": false,
        "sPaginationType": 'bootstrap',
        "columnDefs": [
            { type: 'date-uk', targets: 2 }
        ],
        initComplete: function ()
            {
              var r = $('#schedule-table tfoot tr');
              r.find('th').each(function(){
                $(this).css('padding', 8);
              });
              $('#schedule-table thead').append(r);
              $('#search_0').css('text-align', 'center');
            },
    });

    // Apply the search
    table.columns().eq( 0 ).each( function ( colIdx ) {
        $( 'input', table.column( colIdx ).footer() ).on( 'keyup change', function () {
            table
                .column( colIdx )
                .search( this.value )
                .draw();
        } );
    } );

    resources = [];
    $.each(technicians, function(key, technician) {
        resources.push({id: technician.user_id, name: technician.first_name});
    });

    var original_technician = null;

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'agendaWeek,resourceDay'
        },
        buttonText: {
            agendaWeek: 'Week',
            resourceDay: 'Day'
        },
        allDaySlot: false,
        minTime: 4,
        maxTime: 24,
        firstHour: 8,
        defaultView: 'resourceDay',
        snapMinutes: 15,
        defaultEventMinutes: 120,
        slotEventOverlap: false,
        resources: resources,
        selectable: false,
        selectHelper: true,
        editable: true,
        droppable: true, // this allows things to be dropped onto the calendar !!!
        eventSources: [
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Repair'
                },
                color: '#53DE89',
                textColor: 'black'
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Breakdown'
                },
                color: '#FEE833',
                textColor: 'black'
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Maintenance'
                },
                color: 'orange',
                textColor: 'black'
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Installation'
                },
                color: '#5bc0de',
                textColor: 'black'
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Service'
                },
                color: '#d09bee',
                textColor: 'black',
                is_technician: 1
            }
        ],
        select: function(start, end, allDay,event,technician) {
            var title = prompt('Event Title:');
            if (title) {
                if(technician) {
                    $('#calendar').fullCalendar('renderEvent', {
                        title: title,
                        start: start,
                        end: end,
                        allDay: allDay,
                        resourceId:technician.id
                        },
                        true // make the event "stick"
                    );
                } else {
                    var rid = prompt('Technician ID:');
                    $('#calendar').fullCalendar('renderEvent',
                        {
                            title: title,
                            start: start,
                            end: end,
                            allDay: allDay,
                            resourceId:rid
                        },
                        true // make the event "stick"
                    );
                }
            }
            $('#calendar').fullCalendar('unselect');
        },
        eventDragStart: function(event, jsEvent, ui, view) {
            original_technician = event.resource;
        },
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) { // Called when an event is moved within the calendar
            technician = event.resource;

            // if (technician.id != original_technician.id && !confirm("Are you sure you want to re-assign this task to a different technician?")) {
            if (technician.id != original_technician.id && false) {
                event.resource = original_technician;
                revertFunc();
            } else {
                $.post(base_url+'miniant/orders/schedule/update_event',
                    {
                        document_id: event.id,
                        technician_id: event.resource.id,
                        start: event.start.toUTCString(),
                        end: event.end.toUTCString(),
                        title: event.title,
                        all_day: event.allDay,
                        source: event.source
                    },
                    function(data, response, xhrStatus) {
                        print_message(data.message, data.type);
                    },
                    'json'
                );
            }

            original_technician = null;
            $('#calendar').fullCalendar('refetchEvents');
            $('#calendar').fullCalendar('rerenderEvents');
        },
        eventRender: function(event, element) { // Used to display a mouseover of all event info
            trigger = (supports_touch()) ? 'click' : 'click'; // Change to hover if necessary

            // Add the description to the DOM for the modal dialog
            $('.panel-primary').after(event.description);
            $(element).on('click', function(event2) {
                $('#assignment-'+event.id+'-description').modal();
            });
        },
        eventAfterRender: function(event, element, view) {
            if (event.is_senior == '1') {
                $(element).addClass('is_senior');
            }
        },
        eventResize: function( event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view ) {
            $.post(base_url+'miniant/orders/schedule/update_event',
                {
                    event_id: event.id,
                    technician_id: event.resource.id,
                    start: event.start.toUTCString(),
                    end: event.end.toUTCString(),
                    title: event.title,
                    all_day: event.allDay,
                    source: event.source
                },
                function(data, response, xhrStatus) {
                    print_message(data.message, data.type);
                },
                'json'
            );
        },
        drop: function(date, allDay, event, ui, resourceDayView, test) { // this function is called when an available appointment is dropped
            // retrieve the dropped element's stored Event Object
            var originalEventObject = $(this).data('eventObject');

            // we need to copy it, so that multiple events don't have a reference to the same object
            var copiedEventObject = $.extend({}, originalEventObject);

            // assign it the date that was reported
            copiedEventObject.start = date.toUTCString();
            copiedEventObject.allDay = allDay;
            /* dropped event of resource a to a cell belonging to resource b?
            if (technician && technician.id != copiedEventObject.resourceId) {
                if (!confirm('Are you sure you want to re-assign this task to a different technician?')) {
                    copiedEventObject.resourceId = technician.id;
                }
            }
            */

            order_id = copiedEventObject.order_id;
            ajax_url = base_url + "miniant/orders/schedule/schedule_assignments";

            $.post(ajax_url, {
                    order_id : order_id,
                    technician_id: resources[0].id,
                    appointment_date: copiedEventObject.start,
                },
                function(data, response, xhrStatus) {
                    // We need to get an array of assignments_units: they all have the same length, start date and technician, and are all rendered as individual events.

                    $.each(data.newevents, function(key, newevent) {
                        var eventObject = {
                            title: copiedEventObject.title,
                            resourceId: resources[0].id,
                            start: copiedEventObject.start,
                            allDay: copiedEventObject.allDay,
                            id: newevent.id
                        };
                        $('#calendar').fullCalendar('refetchEvents');
                    });

                },
                'json'
            );

            $(this).remove();
            return false;

        },
        loading: function(stillworking) { // Remove the side scrollbar by extending the height of the calendar after it is rendered
            if (!stillworking) {
                $('table.fc-agenda-days').siblings('div').find('div:first').css('height', '800px');
            }
        }
    });
});

function unschedule(button) {
    url = null;

    if ($(button).is('[data-assignment_id]') &&
            confirm('This will unschedule this job. Are you sure you want to proceed?')) {
        url = base_url+'miniant/orders/schedule/unschedule_assignment/'+$(button).attr('data-assignment_id');
    }

    if (url != null) {
        $.post(url, function(data, response) {
            $('.modal').modal('hide');
            location.reload();
        });
    }
}
