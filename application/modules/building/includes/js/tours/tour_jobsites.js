// Define the tour!
var tour_jobsite = {
    id: "tour_jobsite",
    steps: [
        {
            title: "Job Site Management Guided Tour",
            content: "This short guided tour will show you how to create and edit job sites.",
            placement: "bottom",
            xOffset: 'center',
            arrowOffset: 'center',
            target: '.panel-info .panel-heading'
        },
        {
            title: "Start of Job Sites guided tour",
            content: "Click here to open the Job Sites menu.",
            target: "#topnav-link-job-sites",
            placement: "right",
            multipage: true,
            nextOnTargetClick: true,
            onNext: function() {
                window.location=base_url+'building/job_sites';
            }
        },
        {
            title: "Job Site Management",
            content: 'Job Site Management.',
            target: "div.navbar-header",
            placement: "bottom",
            showCTAButton: true,
            multipage: true,
            ctaLabel: 'Contact us now!',
            onCTA: function() {
                alert('Gotcha!');
            }
        }
    ]
};

var hopscotch_state = hopscotch.getState();
if (hopscotch_state != null) {
    var tour_state = hopscotch_state.match(/([a-z\-\_]*):([0-9]*)/);

    var current_tour = tour_state[1];
    var current_step = tour_state[2];

    if (current_tour == 'tour_jobsite') {
        hopscotch.startTour(tour_jobsite, current_step);
    }
}

$('#tour-jobsite-start').click(function(event) {
    hopscotch.startTour(tour_jobsite);
});
