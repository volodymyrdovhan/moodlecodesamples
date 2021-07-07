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
 * Facade for handling editing actions.
 *
 * @package    local_intellicart
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\facades;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use local_intellicart\persistent\base;

/**
 * Facade for handling editing actions.
 *
 * @package    local_intellicart
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

final class editingactions {

    /**
     * @param base $instance
     * @param $instanceclass
     * @param $basepath
     * @param moodle_url $returnurl
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function process(base $instance, $instanceclass, $basepath, moodle_url $returnurl) {
        $action = optional_param('action', '', PARAM_TEXT);

        if (!empty($action)
                && confirm_sesskey()
                && $instance->exists()
                && self::action_valid($action)
        ) {
            $actionmethod = 'action_' . $action;

            if (method_exists(__CLASS__, $actionmethod)) {
                self::$actionmethod($instance, $instanceclass, $basepath, $returnurl);
            } elseif (method_exists(get_class($instance), $action)) {
                $instance->$action();
            }

            redirect($returnurl);
        }
    }

    /**
     * @param base $instance
     * @param $instanceclass
     * @param $basepath
     * @param moodle_url $returnurl
     * @return false
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function action_delete(base $instance, $instanceclass, $basepath, moodle_url $returnurl) {
        global $PAGE, $OUTPUT;

        $action = optional_param('action', '', PARAM_TEXT);
        if ($action !== 'delete' || !$instance->exists()) {
            return false;
        }

        $confirm = optional_param('confirm', 0, PARAM_BOOL);

        $PAGE->url->param('action', 'delete');
        if ($confirm and confirm_sesskey()) {
            $instance->delete();
            redirect($returnurl);
        }

        $strheading = get_string('delete' . $instanceclass, 'local_intellicart');

        $PAGE->navbar->add($strheading);
        $PAGE->set_title($strheading);
        $PAGE->set_heading($strheading);

        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $yesurl = new moodle_url($basepath, ['id' => $instance->get('id'), 'action' => 'delete', 'confirm' => 1, 'sesskey' => sesskey()]);
        echo $OUTPUT->confirm(
            get_string('delete' . $instanceclass . 'msg', 'local_intellicart'),
            $yesurl,
            $returnurl
        );
        echo $OUTPUT->footer();
        die;
    }

    /**
     * @param $action
     * @return bool
     */
    protected static function action_valid($action) {
        return in_array($action, ['hide', 'show', 'delete']);
    }
}