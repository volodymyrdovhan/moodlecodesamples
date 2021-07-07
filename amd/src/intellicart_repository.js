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
 * Retrieves intellicart from the server.
 *
 * @module     local_intellicart/intellicart_repository
 * @class      intellicart_repository
 * @package    local_intellicart
 * @copyright  2018 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/notification'], function(Ajax, Notification) {
    /**
     * Retrieve a list of announcements from the server.
     *
     * @param {object} args The request arguments
     * @return {object} jQuery promise
     */
    var query = function(args) {

        var request = {
            methodname: 'local_intellicart_get_intellicart_products',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    /**
     * Get the number of products from the server.
     *
     * @param {object} args The request arguments
     * @return {object} jQuery promise
     */
    var countProducts = function(args) {
        var request = {
            methodname: 'local_intellicart_get_intellicart_products_count',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    /**
     * Delete product.
     *
     * @param {int} id The product id
     * @return {object} jQuery promise
     */
    var deleteItem = function(id) {
        var args = {
            id: id
        };

        var request = {
            methodname: 'local_intellicart_delete_intellicart_product',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    return {
        query: query,
        countProducts: countProducts,
        deleteItem: deleteItem
    };
});
