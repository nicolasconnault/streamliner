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
    var url = $.url();
    var role_id = url.segment(-1);
    var input = make_autocomplete_input('add_user_role', 'add_user_role', '', 'Add a user to this role', 'users/role/get_assignable_users/'+role_id,
        function(event, ui) { // Callback called when a value is entered
            var user_id = ui.item.value;
            window.location = base_url+'users/role/add_role_to_user/'+role_id+'/'+user_id;
        }
    );

    $(input.input).popover({
        trigger: 'focus',
        placement: 'right',
        content: "Start typing the name of the user. A list of matching users will appear. Click on the user to whom you want to assign the current role",
    });

    $("#add_div .panel-body").append(input.label);
    $("#add_div .panel-body").append(input.input);
    $("#ajaxtable").dataTable( {
        "bLengthChange": false,
        "asStripClasses": ['odd', 'even'],
        "iDisplayLength": 20,
        "sDom": "<'row'<'span8'l><'span8'>ip>rt<'row'<'span8'><'span8'>>",
        "bJQueryUI": false,
        "sPaginationType": 'bootstrap'
    });
});
