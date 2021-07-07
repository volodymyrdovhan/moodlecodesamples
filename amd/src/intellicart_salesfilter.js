// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    local_intellicart
 * @copyright  2017 IntelliBoard
 */

define(['jquery', 'core/ajax', 'core/str', 'core/config', 'core/log', 'local_intellicart/flatpickr'],
    function($, ajax, str, mdlcfg, log, flatpickr) {

    var SalesFilter = {

        init: function(timestart_date, timefinish_date) {

            document.getElementById("daterange").flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: [timestart_date, timefinish_date],
                onReady: function(selectedDates, dateStr, instance){
                    $('<div/>', {
                        class: 'flatpickr-calendar-title',
                        text: $("#daterange").attr('title')
                    }).appendTo('.flatpickr-calendar');
                    log.info(typeof(instance));
                },
                onChange: function(dateObj, dateStr) {
                    if (dateObj.length == 2) {
                        SalesFilter.formsubmit();
                        log.info(dateStr);
                    }
                },
            });

            $('input[name="search"]').keyup(function(e){
                if(e.keyCode == 13)
                {
                    SalesFilter.formsubmit();
                }
            });

            $('.daterange-clear').click(function(){
                $("#daterange").val('');
                SalesFilter.formsubmit();
            });

            $('.search-clear').click(function(){
                $("input[name=\"search\"]").val('');
                SalesFilter.formsubmit();
                log.info(typeof(flatpickr));
            });

        },

        formsubmit: function(timestart_date, timefinish_date) {
            $('#filter_form, #groupbyFilterForm').submit();
            log.info(typeof(timefinish_date));
        }

    };

    /**
     * @alias module:local_intellicart/intellicart_salesfilter
     */
    return SalesFilter;

});