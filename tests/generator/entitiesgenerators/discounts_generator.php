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
 * Discounts data generator
 *
 * @package    local_itnellicart
 * @category   test
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../local_intellicart_base_generator.php');

defined('MOODLE_INTERNAL') || die();

use local_intellicart\components\discounts\persistent\discount;

/**
 * Discounts data generator class
 *
 * @package    local_itnellicart
 * @category   test
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class discounts_generator implements local_intellicart_base_generator {

    public function create($record = null) {

        $record = (object)$record;

        if (!isset($record->name)) {
            $record->name = 'Test discount';
        }
        if (!isset($record->discount)) {
            $record->discount = 20;
        }
        if (!isset($record->status)) {
            $record->status = discount::STATUS_ACTIVE;
        }
        if (!isset($record->type)) {
            $record->type = discount::DISCOUNTTYPE_ANY;
        }
        if (!isset($record->discounttype)) {
            $record->discounttype = discount::TYPE_PERCENTS;
        }

        $discount = new discount(0, $record);
        $discount->save();

        return $discount;
    }
}
