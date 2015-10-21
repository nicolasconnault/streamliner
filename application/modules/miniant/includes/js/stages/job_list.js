$(function() {

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: null
        },
        buttonText: {
            agendaWeek: 'Week',
            resourceDay: 'Day'
        },
        allDaySlot: false,
        minTime: 4,
        maxTime: 23,
        firstHour: 8,
        defaultView: 'agendaDay',
        snapMinutes: 15,
        defaultEventMinutes: 120,
        slotEventOverlap: false,
        selectable: false,
        selectHelper: true,
        resources: [],
        eventSources: [
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Repair'
                },
                color: '#53DE89',
                textColor: 'black',
                is_technician: 1
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Breakdown'
                },
                color: '#FEE833',
                textColor: 'black',
                is_technician: 1
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Maintenance'
                },
                color: 'orange',
                textColor: 'black',
                is_technician: 1
            },
            {
                url: base_url+'miniant/orders/schedule/get_appointments',
                type: 'post',
                data: {
                    order_type: 'Installation'
                },
                color: '#5bc0de',
                textColor: 'black',
                is_technician: 1
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
        eventRender: function(event, element) { // Used to display a mouseover of all event info
            trigger = (supports_touch()) ? 'click' : 'click'; // Change to hover if necessary

            $(element).on('click', function() {
                window.location = base_url + 'miniant/stages/assignment_details/index/' + event.id;
            });
        },
        loading: function(stillworking) { // Remove the side scrollbar by extending the height of the calendar after it is rendered
            if (!stillworking) {
                $('table.fc-agenda-days').siblings('div').find('div:first').css('height', '800px');
            }
        },
        eventAfterRender: function(event, element, view) {
        }
    });
});
