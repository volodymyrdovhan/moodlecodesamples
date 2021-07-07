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
 * local_intellicart data generator
 *
 * @package    local_itnellicart
 * @category   test
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/entitiesgenerators/discounts_generator.php');
require_once(__DIR__ . '/entitiesgenerators/coupons_generator.php');

defined('MOODLE_INTERNAL') || die();

/**
 * IntelliCart data generator class
 *
 * @package    local_itnellicart
 * @category   test
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_intellicart_generator extends \component_generator_base {

    /**
     * @var number of created coupons
     */
    protected $couponscount = 0;

    /**
     * @var number of created discounts
     */
    protected $discountcount = 0;

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->couponscount = 0;
        $this->discountcount = 0;
    }

    /**
     * @param null $record
     * @return \local_intellicart\components\discounts\persistent\discount
     * @throws coding_exception
     */
    public function create_discount($record = null) {
        $discount = (new discounts_generator())->create($record);

        if ($discount->get('id')) {
            $this->discountcount++;
        }

        return $discount;
    }

    /**
     * @param null $record
     * @return \local_intellicart\components\coupons\persistent\coupon
     * @throws coding_exception
     */
    public function create_coupon($record = null) {
        $coupon = (new coupons_generator())->create($record);

        if ($coupon->get('id')) {
            $this->discountcount++;
        }

        return $coupon;
    }
}
