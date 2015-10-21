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
$(document).ready(function() {
    $("#assignable").treeview({
        animated: "fast",
        collapsed: true,
        unique: true,
        toggle: function() {
            // Toggle code here
            // console.log("%o was toggled", this);
            // No action is actually required when toggling, only item links are used
        }
    });

    $('#ajaxtable tfoot th').each( function () {
        var title = $('#ajaxtable thead th').eq( $(this).index() ).text();
        if (title == 'Tasks') {
            return;
        }
        $(this).html( '<input type="text" placeholder="Search" size="12" />' );
    } );

    var table = $('#ajaxtable').DataTable({
        "bProcessing": true,
        "bServerSide": false,
        "bLengthChange": false,
        "asStripClasses": ['odd', 'even'],
        "iDisplayLength": 20,
        "sDom": "<'row'<'span8'l><'span8'>ip>rt<'row'<'span8'><'span8'>>",
        "bJQueryUI": false,
        "sPaginationType": 'bootstrap',
        initComplete: function ()
            {
              var r = $('#ajaxtable tfoot tr');
              r.find('th').each(function(){
                $(this).css('padding', 8);
              });
              $('#ajaxtable thead').append(r);
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
