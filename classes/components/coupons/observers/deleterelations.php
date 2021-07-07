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
 * Observer for deleting coupon relations.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\observers;

defined('MOODLE_INTERNAL') || die();

use local_intellicart\event\local_intellicart_coupon_deleted;
use local_intellicart\components\coupons\persistent\coupon;
use local_intellicart\vendors;
use local_intellicart\log;

/**
 * Observer for deleting coupon relations.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class deleterelations {

    /**
     * Triggered when 'local_intellicart_coupon_deleted' event is triggered.
     *
     * @param \local_intellicart\event\local_intellicart_coupon_deleted $event
     */
    public static function coupon_deleted(local_intellicart_coupon_deleted $event) {
        global $DB;

        if (get_config('local_intellicart', 'enabled')){
            $couponid = $event->objectid;
            $type = coupon::INSTANCE_NAME;

            // Delete coupon relations.
            coupon::delete_coupon_relations($couponid);

            // Delete vendors relations.
            vendors::delete_relations($couponid, $type);

            // Delete logs.
            $DB->delete_records('local_intellicart_logs',
                [
                    'instanceid' => $couponid,
                    'type' => log::TYPE_COUPON
                ]
            );
        }
    }

}
