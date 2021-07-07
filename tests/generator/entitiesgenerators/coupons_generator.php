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
 * Coupons data generator
 *
 * @package    local_itnellicart
 * @category   test
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../local_intellicart_base_generator.php');

defined('MOODLE_INTERNAL') || die();

use local_intellicart\components\coupons\persistent\coupon;

/**
 * coupons data generator class
 *
 * @package    local_itnellicart
 * @category   test
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupons_generator implements local_intellicart_base_generator {

    public function create($record = null) {

        $record = (object)$record;

        if (!isset($record->name)) {
            $record->code = coupon::generate_coupon_code();
        }      

        $coupon = new coupon(0, $record);
        $coupon->save();

        return $coupon;
    }
}
