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
 * Observer for inserting manager relations.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\observers;

defined('MOODLE_INTERNAL') || die();

use local_intellicart\components\coupons\persistent\coupon;
use local_intellicart\event\local_intellicart_coupon_created;
use local_intellicart\vendors;


/**
 * Observer for inserting manager relations.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class insertmanagerrelations {

    public static function coupon_created(local_intellicart_coupon_created $event) {

        // Insert vendors relations.
        if (vendors::enabled() and $event->objectid) {
            $data = new \stdClass();
            $data->id = $event->objectid;

            vendors::insert_manager_relations($data, coupon::INSTANCE_NAME);
        }

        return true;
    }

}
