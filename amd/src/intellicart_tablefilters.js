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

define(['jquery', 'local_intellicart/flatpickr', 'core/log'], function($, flatpickr, log) {

    var TableFilters = {

        tablecontainer:        '.custom-filtering-table',
        formcontainer:         '.form-table-filter',

        init: function() {
            $(TableFilters.formcontainer + ' .filters-block input, ' +
                TableFilters.formcontainer + ' .filters-block select').change(function() {
                TableFilters.cleanDownload();
                if ($(this).attr('data-input-type') != 'daterange') {
                    TableFilters.formSubmit();
                }
            });

            $(TableFilters.formcontainer + ' input[data-input-type="daterange"]').each(function() {
                var el = document.getElementById($(this).attr('id'));
                var elclass = $(this).data('class');
                el.flatpickr({
                    mode: "range",
                    dateFormat: "Y-m-d",
                    onChange: function(dateObj, dateStr) {
                        if (dateObj.length == 2) {
                            TableFilters.formSubmit();
                            log.info(typeof(dateStr));
                        }
                    },
                }).calendarContainer.classList.add(elclass);
            });

            $(TableFilters.tablecontainer + ' .report-export-panel a[data-type="download-link"]').click(function() {
                var exportformat = $(this).attr('data-download-type');
                $(TableFilters.formcontainer).find('input[name="download"]').val(exportformat);
                TableFilters.formSubmit();
            });

            $(TableFilters.tablecontainer + ' .intb-pagination a').click(function(e) {
                e.preventDefault();
                var page = parseInt($(this).text());
                page = (page > 0) ? (page-1) : 0;
                $(TableFilters.formcontainer).find('input[name="page"]').val(page);
                TableFilters.cleanDownload();
                TableFilters.formSubmit();
            });
        },


        formSubmit: function() {
            $(TableFilters.formcontainer).submit();
            log.info(typeof(flatpickr));
        },

        cleanDownload: function() {
            $(TableFilters.formcontainer).find('input[name="download"]').val('');
        }
    };

    /**
     * @alias module:local_intellicart/intellicart_tablefilters
     */
    return TableFilters;

});