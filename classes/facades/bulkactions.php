<?php
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
 * Facade for handling bulk actions for persistent objects.
 *
 * @package    local_intellicart
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\facades;

defined('MOODLE_INTERNAL') || die();

/**
 * Facade for handling bulk actions for persistent objects.
 *
 * @package    local_intellicart
 * @copyright  2021
 */
final class bulkactions {

    /**
     * @param $persistent
     * @param array $capabilities
     * @param string $returnurl
     * @param string $action
     * @param array $items
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function process($persistent, $capabilities = [], $returnurl = '', $action = '', $items = []) {
        global $PAGE, $DB;

        // Validate permissions.
        if (count($capabilities) and !has_all_capabilities($capabilities, \context_system::instance())) {
            return;
        }

        // Get bulk action and items.
        if (!$action) {
            $action = optional_param('bulkaction', false, PARAM_ALPHA);
        }
        if (!count($items)) {
            $items = optional_param_array('icbulckitems', false, PARAM_INT);
        }

        if ($action !== false
                && confirm_sesskey()
                && (is_array($items) and count($items))
                && class_exists($persistent)
                && self::action_valid($action)
        ) {

            try {
                $transaction = $DB->start_delegated_transaction();

                foreach ($items as $intanceid) {
                    $persistentaction = str_replace('bulk', '', $action);

                    $instance = new $persistent($intanceid);

                    if (method_exists(get_class($instance), $persistentaction)) {
                        $instance->$persistentaction();
                    }
                }

                $transaction->allow_commit();

            } catch(Exception $e) {
                $transaction->rollback($e);
            }

            // Prepare return url.
            if (!$returnurl) {
                $returnurl = $PAGE->url;
            }

            redirect($returnurl);
        }
    }

    /**
     * @param $action
     * @return bool
     */
    protected static function action_valid($action) {
        return in_array($action, ['bulkhide', 'bulkshow', 'bulkdelete']);
    }
}