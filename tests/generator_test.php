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

use local_intellicart\components\discounts\persistent\discount;
use local_intellicart\components\coupons\persistent\coupon;


/**
 * PHPUnit data generator testcase
 *
 * @package    local_itnellicart
 * @category   phpunit
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_intellicart_generator_testcase extends advanced_testcase {

    /**
     * @throws coding_exception
     */
    public function test_discounts_generator() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->assertEquals(0, discount::count_records());

        $generator = $this->getDataGenerator()->get_plugin_generator('local_intellicart');
        $this->assertInstanceOf('local_intellicart_generator', $generator);

        $generator->create_discount();
        $generator->create_discount(['name' => 'Discount1', 'discount' => 50]);

        $newdiscount = $generator->create_discount(['name' => 'Discount2', 'discount' => 100]);
        $this->assertEquals(3, discount::count_records());
        $this->assertInstanceOf('local_intellicart\components\discounts\persistent\discount', $newdiscount);

        $discount = discount::get_record(['id' => $newdiscount->get('id')]);
        $this->assertEquals($discount->get('name'), $newdiscount->get('name'));
        $this->assertEquals($discount->get('discount'), $newdiscount->get('discount'));
    }

    /**
     * @throws coding_exception
     */
    public function test_coupons_generator() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->assertEquals(0, coupon::count_records());

        $generator = $this->getDataGenerator()->get_plugin_generator('local_intellicart');
        $this->assertInstanceOf('local_intellicart_generator', $generator);

        $generator->create_coupon();

        $this->assertEquals(1, coupon::count_records());

        $cp1 = [
            'code'        => 'coupon-test1',
            'starttime'   => 1,
            'endtime'     => 2,
            'expiration'  => 3,
            'usedperuser' => '10',
            'usedcount'   => '100',
            'discount'    => '15.25',
            'status'      => coupon::STATUS_ACTIVE,
            'type'        => coupon::TYPE_PERCENTS
        ];
        $coupon1 = $generator->create_coupon($cp1);

        $this->assertInstanceOf('local_intellicart\components\coupons\persistent\coupon', $coupon1);
        $this->assertEquals(2, coupon::count_records());

        $coupon1db = coupon::get_record(['id' => $coupon1->get('id')]);

        foreach (array_keys($cp1) as $k) {
            $this->assertEquals($coupon1->get($k), $coupon1db->get($k));
        }
    }
}
