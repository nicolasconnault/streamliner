$(document).ready(function() {

    $('#schedule_calendar').fullCalendar({
        firstDay: 1,
        theme: true,
        timezone: 'Australia/Perth',
        columnFormat: 'ddd DD/MM',
        header: {
            left: 'prev,next,today',
            center: 'title',
            right: ''
        },
        defaultView: 'basicWeek',
        selectable: false,
        selectHelper: true,
        editable: false,
        droppable: false,
        editable: false,
        aspectRatio: 3,
        height: 600,
        eventLimit: true, // allow "more" link when too many events
        eventSources: [
            {
                url: base_url+'building/schedule/get_bookings',
                type: 'post',
                data: {
                    confirmed: '0'
                },
                color: 'orange',
                textColor: 'black'
            },
            {
                url: base_url+'building/schedule/get_bookings',
                type: 'post',
                data: {
                    confirmed: '1'
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
        eventRender: function(event, element) {
            element.find('.fc-title').html(event.title);
        },
    });

    window.setInterval(refresh_calendar, 120000);
});

function refresh_calendar() {
    display_please_wait_modal = false;
    $('#schedule_calendar').fullCalendar('refetchEvents');
    display_please_wait_modal = true;
}
