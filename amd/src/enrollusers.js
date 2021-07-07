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
 * This module is compatible with core/form-autocomplete.
 *
 * @package    local_intellicart
 * @copyright  2018 SEBALE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax'], function($, Ajax) {

    return {

        /**
         * List of products.
         *
         * @param {Object} options Additional parameters to pass to the external function.
         * @return {Promise}
         */
        list: function(args) {

            var promise,

                promise = Ajax.call([{
                    methodname: 'local_intellicart_get_users_for_enrollment', args: args
                }])[0];
            return promise;
        },

        /**
         * Process the results for auto complete elements.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {Array} results An array or results.
         * @return {Array} New array of results.
         */
        processResults: function(selector, results) {
            var options = [];
            var data = JSON.parse(results.resp)[0];

            $.each(data, function(index, value) {
                options.push({
                    value: value.id,
                    label: value.name
                });
            });

            return options;
        },

        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} query The query string.
         * @param {Function} callback A callback function receiving an array of results.
         * @return {Void}
         */
        transport: function(selector, query, callback) {
            var id = $(selector).attr('data-id');
            var type = $(selector).attr('data-type');

            this.list({
                query: query,
                id: id,
                type: type,
            }).then(callback);
        }
    };

});