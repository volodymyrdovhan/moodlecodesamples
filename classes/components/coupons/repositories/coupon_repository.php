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
 * @author     IntelliBoard Inc.
 * @copyright  2020 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\repositories;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use local_intellicart\log;
use local_intellicart\checkout;
use local_intellicart\product;
use local_intellicart\vendors;
use local_intellicart\roles;
use local_intellicart\payment;
use local_intellicart\components\coupons\services\coupons_service;
use local_intellicart\components\coupons\persistent\coupon;

/**
 * Class coupon repository
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class coupon_repository {

    const KEY_LENGTH = 16;
    const TYPE_PERCENTS = 0;
    const TYPE_CURRENCY = 1;

    /**
     * @param string $code
     * @param $result
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function coupon_apply($code = '', &$result) {
        global $USER, $DB;

        $coupon = $DB->get_record('local_intellicart_coupons', array('code' => $code, 'status' => 1));

        if (!$coupon) {
            $result = ['status' => '', 'msg' => get_string('invalidcoupon', 'local_intellicart')];
            return false;
        }

        if ($DB->get_record('local_intellicart_logs', ['instanceid' => $coupon->id, 'type' => log::TYPE_COUPON,
            'userid' => $USER->id, 'status' => log::STATUS_INCART])) {
            $result['msg'] = get_string('couponalreadyapplied', 'local_intellicart');
            return false;
        }

        // Check vendors relations.
        if (get_config('local_intellicart', 'enablevendors')) {

            $params = ['instanceid' => $coupon->id, 'type' => coupon::INSTANCE_NAME];
            $couponvendors = $DB->get_records_menu('local_intellicart_vrelations', $params,
                'vendorid', 'id, vendorid');

            if ($couponvendors && !is_siteadmin() && !has_capability('local/intellicart:isadmin',
                \context_system::instance())) {

                if (!$vendors = vendors::get_user_vendors(null, $USER->id)) {
                    $result['msg'] = get_string('invalidcouponvendor', 'local_intellicart');
                    return false;
                }

                if (!array_intersect($couponvendors, $vendors)) {
                    $result['msg'] = get_string('invalidcouponvendor', 'local_intellicart');
                    return false;
                }
            }
        }

        // Roles filtering.
        if ( get_config('local_intellicart', 'enablerolesfiltering') ) {
            $checkcoupon = roles::check_coupon($coupon->id);
            if ( $checkcoupon !== true ) {
                $result['msg'] = get_string('invalidcoupon', 'local_intellicart');
                return false;
            }
        }

        // Check coupon period.
        $now = time();
        if ($coupon->starttime > 0 and $coupon->starttime > $now) {
            $result['msg'] = get_string('couponinactive', 'local_intellicart');
            return false;
        } else if ($coupon->endtime > 0 and $coupon->endtime < $now) {
            $result['msg'] = get_string('couponisover', 'local_intellicart');
            return false;
        }

        // Check all used coupons.
        if ((int)$coupon->usedcount > 0 and (int)$coupon->usedcount <= $DB->count_records('local_intellicart_logs',
            array('instanceid' => $coupon->id, 'type' => log::TYPE_COUPON))) {
            $result['msg'] = get_string('couponmaxnumber', 'local_intellicart');
            return false;
        }

        $userusedcupon = $DB->count_records('local_intellicart_logs',
            ['instanceid' => $coupon->id, 'type' => log::TYPE_COUPON, 'userid' => $USER->id]);
        if ((int)$coupon->usedperuser > 0 and (int)$coupon->usedperuser <= $userusedcupon) {
            $result['msg'] = get_string('couponusermaxnumber', 'local_intellicart');
            return false;
        }

        // Insert coupon.
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->instanceid = $coupon->id;
        $record->type = log::TYPE_COUPON;
        $record->status = payment::STATUS_INCART;
        $record->timecreated = time();
        $record->timemodified = time();

        $DB->insert_record('local_intellicart_logs', $record);
        $result = ['status' => 'success', 'msg' => ''];
        return true;
    }

    /**
     * @param $product
     * @param $coupons
     * @return array
     * @throws \dml_exception
     */
    public static function get_product_applied_coupons($product, $coupons) {
        global $DB;

        if (coupons_service::enabled() !== true) {
            return [];
        }

        $sqlin = array();
        foreach ($coupons as $coupon) {
            $sqlin[] = $coupon->id;
        }

        return $DB->get_records_sql(
            "SELECT c.*
              FROM {local_intellicart_relations} r
              JOIN {local_intellicart_coupons} c ON c.id = r.instanceid
             WHERE r.type = :type AND
                   r.productid = :productid AND
                   c.status = 1 AND
                   r.instanceid IN (".implode(',', $sqlin).")",
            array('type' => 'coupon', 'productid' => $product->id)
        );
    }

    /**
     * Delete expired coupons from cart.
     *
     * @param  int $userid
     * @param  int $checkoutid
    */
    public static function delete_expired_applied_coupons($userid = 0, $checkoutid = 0) {
        global $USER, $DB;

        $userid = (int)$userid;
        $checkoutid = (int)$checkoutid;
        $wheres = [];
        $params = [
            'type'   => log::TYPE_COUPON,
            'now'    => time(),
            'now1'   => time(),
            'status' => 0,
            'chstatus' => payment::STATUS_INCART
        ];

        $userid = ($userid > 0) ? $userid : $USER->id;

        if ($checkoutid > 0) {
            $params['checkoutid'] = $checkoutid;
            $wheres[] = "ch.id = :checkoutid";
        } else {
            $params['userid'] = $userid;
            $wheres[] = "l.userid = :userid";
        }

        $where = (count($wheres)) ? ' AND ' . implode(" AND ", $wheres) : '';

        $sql = "SELECT l.id
                  FROM {local_intellicart_logs} l
                  JOIN {local_intellicart_checkout} ch ON ch.id = l.checkoutid
                  JOIN {local_intellicart_coupons} c ON l.instanceid = c.id AND
                    (
                        (
                            (c.starttime > 0 AND c.starttime > :now) OR
                            (c.endtime > 0 AND c.endtime < :now1)
                        )
                        OR (c.status = :status)
                    )
              WHERE l.type = :type AND ch.payment_status = :chstatus {$where}";
        $ids = $DB->get_fieldset_sql($sql, $params);

        if (!empty($ids)) {
            $qin = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $DB->delete_records_select('local_intellicart_logs', " id {$qin[0]}", $qin[1]);
        }
    }

    /**
     * @param int $userid
     * @param int $checkoutid
     * @return array
     * @throws \dml_exception
     */
    public static function get_applied_coupons($userid = 0, $checkoutid = 0) {
        global $USER, $DB;

        if (coupons_service::enabled() !== true) {
            return [];
        }

        // Delete expired coupons from cart.
        self::delete_expired_applied_coupons($userid, $checkoutid);

        $userid = ($userid) ? $userid : $USER->id;
        $wheres = $joins = [];

        if ($checkoutid) {
            $params = array('type' => log::TYPE_COUPON, 'checkoutid' => $checkoutid);
            $wheres[] = "l.checkoutid = :checkoutid";
        } else {
            $params = array('type' => log::TYPE_COUPON, 'userid' => $userid, 'status' => log::STATUS_INCART);
            $wheres[] = "l.status = :status";
            $wheres[] = "l.userid = :userid";

            // Vendors filter.
            if (get_config('local_intellicart', 'enablevendors')) {
                list($wheres, $params, $joins) = vendors::get_vendorsfilter_sqldisplay($wheres, $params, $joins, 'coupon', 'c.id');
            }

            // Roles filter.
            if ( get_config('local_intellicart', 'enablerolesfiltering') ) {
                list($wheres, $params, $joins) = roles::get_coupons_sqlrequest(
                    $wheres, $params, $joins, 'c.id');
            }
        }

        $join  = (count($joins)) ? implode(" ", $joins) : '';
        $where = (count($wheres)) ? ' AND ' . implode(" AND ", $wheres) : '';

        $params['now'] = time();
        $params['now1'] = $params['now'];

        return $DB->get_records_sql(
            "SELECT c.*, l.id as logid
               FROM {local_intellicart_logs} l
               JOIN {local_intellicart_coupons} c ON l.instanceid = c.id AND
                    (
                        (c.starttime = 0 OR c.starttime < :now) AND
                        (c.endtime = 0 OR c.endtime > :now1)
                    )
              $join
              WHERE l.type = :type $where",
            $params
        );
    }

    /**
     * @param $id
     * @return bool
     * @throws \dml_exception
     */
    public static function coupon_remove($id) {
        global $DB;

        return $DB->delete_records("local_intellicart_logs", ['id' => $id]);
    }

    /**
     * Get products from cart that applied the coupon
     * @param int $id Coupon ID
     * @param int $checkoutid checkout id or false
     */
    public static function get_cart_products_applied_coupon(int $id, $checkoutid = 0) {
        global $DB;

        if ($checkoutid > 0) {
            // When cart is empty - get checkout products.
            $productsincart = checkout::get_products_by_checkout($checkoutid);
        } else {
            $productsincart = checkout::get_products_in_cart();
        }

        $productsids = array_map(function($el) {
            return $el->id;
        }, $productsincart);

        if (!$productsincart) {
            return [];
        }

        $filter = $DB->get_in_or_equal($productsids, SQL_PARAMS_NAMED);

        $sqlparans = [
            'couponid' => $id,
            'lirtype'  => coupon::INSTANCE_NAME
        ];
        $sqlparans += $filter[1];

        return $DB->get_records_sql(
            "SELECT lip.*
               FROM {local_intellicart_relations} lir
               JOIN {local_intellicart_products} lip ON lip.id = lir.productid
              WHERE lir.instanceid = :couponid AND lir.type = :lirtype AND
                    lir.productid {$filter[0]}",
            $sqlparans
        );
    }

    /**
     * Get products assigned to coupon
     * @param string $coupon coupon code
     * @return array products
     */
    public static function get_assigned_products($couponid = 0) {
        global $DB;

        $couponid = (int)$couponid;

        if ($couponid < 1) {
            return [];
        }

        $params = ['rtype' => coupon::INSTANCE_NAME, 'rinstanceid' => $couponid];
        $sql = "SELECT p.*
                  FROM {local_intellicart_products} p
                  JOIN {local_intellicart_relations} r ON r.productid = p.id
                 WHERE r.type = :rtype AND r.instanceid = :rinstanceid";

        return $DB->get_records_sql($sql, $params);
    }

}
