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
 * Persistent base plugin.
 *
 *
 * @package    local_intellicart
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

namespace local_intellicart\persistent;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class base
 *
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

abstract class base extends \core\persistent {

    /** The table name. */
    const TABLE = null;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Create an instance of this class.
     *
     * @param int $id If set, this is the id of an existing record, used to load the data.
     * @param null $record If set will be passed to {@link self::from_record()}.
     * @throws \coding_exception
     */
    public function __construct($id = 0, $record = null) {
        if ($record) {
            $record = $this->clean_record($record);
        }
        parent::__construct($id, $record);
    }

    /**
     * @param $record
     * @return stdClass
     * @throws \coding_exception
     */
    protected function clean_record($record) {
        $properties = static::properties_definition();
        $data = new stdClass();
        foreach ($record as $key => $value) {
            if (isset($properties[$key])) {
                $data->{$key} = $value;
            } else if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $data;
    }

    /**
     * Check if item exists
     *
     * @return bool
     * @throws \coding_exception
     */
    public function exists(): bool {
        return ((int) $this->get('id'));
    }

    /**
     * @param string $fieldkey
     * @param string $fieldvalue
     * @param string $where
     * @param array $params
     * @param string $sort
     * @return array
     * @throws \coding_exception
     */
    public static function select_column(string $fieldkey, string $fieldvalue, string $where = '', array $params = [], string $sort = '') {
        $result = [];
        $instances = static::get_records_select($where, $params,  $sort, "$fieldkey, $fieldvalue");
        foreach ($instances as $instance) {
            $result[$instance->get($fieldkey)] = $instance->get($fieldvalue);
        }
        return $result;
    }

    /**
     * @return string[]
     */
    public static function get_statuses() {
        return [
            self::STATUS_ACTIVE   => get_string('active', 'local_intellicart'),
            self::STATUS_INACTIVE => get_string('inactive', 'local_intellicart')
        ];
    }

}