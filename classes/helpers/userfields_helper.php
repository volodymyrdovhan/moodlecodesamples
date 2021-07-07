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
 * IntelliCart helper for user fields.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc.
 * @copyright  2021 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\helpers;
defined('MOODLE_INTERNAL') || die();

class userfields_helper {

    /**
     * @param $alias
     * @return array|string|string[]
     */
    public static function get_name_fields_sql($alias = '', $fieldprefix = '') {

        if (class_exists('\core_user\fields')) {
            $userfieldsapi = \core_user\fields::for_name();
            $userfields = $userfieldsapi->get_sql($alias, false, $fieldprefix, '', false)->selects;
        } else {
            $userfields = get_all_user_name_fields(true, $alias, null, $fieldprefix);
        }

        return $userfields;
    }

    /**
     * @return mixed
     */
    public static function get_name_fields() {

        if (class_exists('\core_user\fields')) {
            $fields = \core_user\fields::get_name_fields();
        } else {
            $fields = get_all_user_name_fields();
        }

        return $fields;
    }

    /**
     * @return mixed
     */
    public static function get_picture_fields($tableprefix = '', $idalias = 'id', $fieldprefix = '') {

        if (class_exists('\core_user\fields')) {
            $userfieldsapi = \core_user\fields::for_userpic(\context_system::instance(), false)->with_userpic();
            $fields = $userfieldsapi->get_sql($tableprefix, false, $fieldprefix, $idalias, false)->selects;
        } else {
            $fields = \user_picture::fields($tableprefix, self::get_name_fields(), $idalias, $fieldprefix);
        }

        return $fields;
    }

}
