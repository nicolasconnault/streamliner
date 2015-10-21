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
$(function() {
    $('div.submit input[type="submit"]').hide();
    $('div.tab-container').easytabs({ animate: false });

    /*
    $('div.tab-container').on('easytabs:before', function(event) {
        var clicked = $(event.target);
        if (clicked.is('div.tab-container')) {
            return false;
        }
    });
    */

    var $tabContainer = $('div.tab-container'),
        $tabs = $tabContainer.data('easytabs').tabs;

    number_of_tabs = $('.tab-container ul.nav-tabs li.tab').length;
    $('.tab-container ul.nav-tabs li.tab').each(function(key, item) {
        var tab_index = $(this).attr('class').match(/tab tab-([0-9]*)/)[1];
        if ($('.tab-panel-'+tab_index).find('span.validation_error').length > 0) {
            $(this).addClass('tab-danger');
        }

        if ($('.tab-container ul.nav-tabs li.tab').length == tab_index) {
            $(this).find('a').on('focus', function(event) {
                $('div.submit input[type="submit"]').show();
            });
        } else {
            $(this).find('a').on('focus', function(event) {
                $('div.submit input[type="submit"]').hide();
            });
        }
    });

    $('.next-tab, .prev-tab').click(function() {
        var i = parseInt($(this).attr('rel'));
        var tabSelector = $('li.tab-' + i + ' a').attr('href');
        var number_of_tabs = $('.tab-container ul.nav-tabs li.tab').length;

        if (i == number_of_tabs) {
            $('div.submit input[type="submit"]').show();
        } else {
            $('div.submit input[type="submit"]').hide();
        }

        $tabContainer.easytabs('select', tabSelector);
        return false;
    });
});
