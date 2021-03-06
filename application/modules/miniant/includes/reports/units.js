$(function() {
    $('.unit-info').popover({html: true, placement: 'left', trigger: 'hover', content: function() {
        open_popovers.push(this);
        var content = '<table class="table table-condensed">';
        $.each($.evalJSON($(this).attr('data-attributes')), function(key, value) {
            content += '<tr><th>'+key+'</th><td>'+value+'</td></tr>';
        });
        content += '</table>';
        return content;
    }});

    // DataTable
    $('#units-table tfoot th').each( function () {
        var title = $('#units-table thead th').eq( $(this).index() ).text();
        if (title == 'Select' || title == 'More info') {
            return;
        }
        $(this).html( '<input type="text" placeholder="Search" size="12" />' );
    } );

    var table = $('#units-table').DataTable({
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
              var r = $('#units-table tfoot tr');
              r.find('th').each(function(){
                $(this).css('padding', 8);
              });
              $('#units-table thead').append(r);
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
});

