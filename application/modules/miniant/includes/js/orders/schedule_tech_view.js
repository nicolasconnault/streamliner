$(document).ready(function() {
    $("#ajaxtable").dataTable( {
        "bLengthChange": false,
        "asStripClasses": ['odd', 'even'],
        "iDisplayLength": 20,
        "sDom": "<'row'<'span8'l><'span8'>i>rt<'row'<'span8'><'span8'>>",
        "bJQueryUI": false,
        "sPaginationType": 'bootstrap'
    });
});
