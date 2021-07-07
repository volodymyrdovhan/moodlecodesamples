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
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\services;

defined('MOODLE_INTERNAL') || die();

use local_intellicart\components\coupons\repositories\coupon_repository;
use local_intellicart\components\coupons\persistent\coupon;

/**
 * Class coupons service
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupons_service {

    public $repository = null;

    public function __construct() {
        $this->repository = new coupon_repository();
    }

    /**
     * @return bool
     * @throws \dml_exception
     */
    public static function enabled() {
        return (bool)get_config('local_intellicart', 'enablecoupons');
    }

}
