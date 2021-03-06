$(function() {
    // For some reason if I run this immediately, most times it will find two instances of the brand_id dropdown, one of which will have no option selected. This timeout resovles this.
    timeoutID = window.setTimeout(run_update, 500);
});

function update_thermostat_model(id, html) {
    if (html == 'Other') {
        html = $('input[name="brand_other_'+id+'"]').val();
    }

    $('select[name="thermostat_model_'+id+'"] option[value="Same as brand"]').html(html);
}

function run_update() {
    var brand_dropdown = $('select').filter(function() {
        return this.name.match(/brand_id_(ref|evap)_*/) && !$(this).prop('disabled');
    });

    brand_dropdown.on('change', function(event) {
        $(this).blur();
        parts = this.name.match(/brand_id_(ref|evap)_([0-9]+)/);
        update_thermostat_model(parts[2], $(this).find('option:selected').html());
    })

    brand_dropdown.each(function(key, item) {
        parts = this.name.match(/brand_id_(ref|evap)_([0-9]+)/);
        update_thermostat_model(parts[2], $(this).find('option:selected').html());
    });

    $('input').filter(function() {
        return this.name.match(/brand_other_*/);
    }).on('keyup', function(event) {
        parts = this.name.match(/brand_other_([0-9]+)/);
        update_thermostat_model(parts[1], $(this).val());
    });

}
