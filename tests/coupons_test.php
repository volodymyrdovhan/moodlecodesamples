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
 * PHPUnit data generator tests
 *
 * @package    local_itnellicart
 * @category   phpunit
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_intellicart\components\coupons\persistent\coupon;


/**
 * PHPUnit data generator testcase
 *
 * @package    local_itnellicart
 * @category   phpunit
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_intellicart_coupons_testcase extends advanced_testcase {

    /**
     * @throws coding_exception
     */
    public function test_coupon_update() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->assertEquals(0, coupon::count_records());

        $record = (object)['code' => coupon::generate_coupon_code()];

        $coupon = new coupon(0, $record);
        $coupon->save();

        $this->assertEquals(1, coupon::count_records());

        $coupon->set('code', 'coupon-updated');
        $coupon->set('status', coupon::STATUS_ACTIVE);
        $coupon->save();

        $this->assertEquals(1, coupon::count_records());

        $coupondb = coupon::get_record(['id' => $coupon->get('id')]);

        $this->assertEquals($coupondb->get('code'), 'coupon-updated');
        $this->assertEquals($coupondb->get('status'), coupon::STATUS_ACTIVE);
    }

    /**
     * @throws coding_exception
     */
    public function test_coupon_delete() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->assertEquals(0, coupon::count_records());

        $record = (object)['code' => coupon::generate_coupon_code()];

        $coupon = new coupon(0, $record);
        $coupon->save();

        $this->assertEquals(1, coupon::count_records());

        $coupon->delete();

        $this->assertEquals(0, coupon::count_records());
    }
}
