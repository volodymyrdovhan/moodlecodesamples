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
 * Class coupon persistent
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\persistent;

use local_intellicart\persistent\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Class persistent coupon
 *
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */
class coupon extends base {

    /** The table name. */
    const TABLE = 'local_intellicart_coupons';

    const INSTANCE_NAME = 'coupon';
    const INSTANCES_NAME = 'coupons';

    const TYPE_PERCENTS = 0;
    const TYPE_CURRENCY = 1;

    const KEY_LENGTH = 16;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'code' => [
                'type' => PARAM_TEXT,
                'description' => 'coupon code.',
            ],
            'starttime' => [
                'type' => PARAM_INT,
                'description' => 'Start time.',
                'default' => 0
            ],
            'endtime' => [
                'type' => PARAM_INT,
                'description' => 'End time.',
                'default' => 0
            ],
            'expiration' => [
                'type' => PARAM_INT,
                'description' => 'Expiration time.',
                'default' => 0
            ],
            'usedperuser' => [
                'type' => PARAM_TEXT,
                'description' => 'Used per user.',
                'default' => '0',
                'null' => NULL_ALLOWED,
            ],
            'usedcount' => [
                'type' => PARAM_TEXT,
                'description' => 'Total used.',
                'default' => '0',
                'null' => NULL_ALLOWED,
            ],
            'discount' => [
                'type' => PARAM_TEXT,
                'description' => 'Discount value.',
                'default' => '0',
                'null' => NULL_ALLOWED,
            ],
            'status' => [
                'type' => PARAM_INT,
                'description' => 'Coupon status.',
                'default' => self::STATUS_ACTIVE
            ],
            'type' => [
                'type' => PARAM_INT,
                'description' => 'Coupon type.',
                'default' => self::TYPE_PERCENTS,
                'choices' => [
                    self::TYPE_PERCENTS,
                    self::TYPE_CURRENCY
                ]
            ]
        ];
    }

    /**
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    protected function before_create() {
        if (empty($this->get('code'))) {
            $this->set('code', self::generate_coupon_code());
        }
    }

    /**
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    protected function before_update() {
        if (empty($this->get('code'))) {
            $this->set('code', self::generate_coupon_code());
        }
    }

    /**
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public function show() {
        if ($this->get('status') != self::STATUS_ACTIVE) {
            $this->set('status', self::STATUS_ACTIVE);
            $this->update();
        }
    }

    /**
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public function hide() {
        if ($this->get('status') != self::STATUS_INACTIVE) {
            $this->set('status', self::STATUS_INACTIVE);
            $this->update();
        }
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_create() {
        $this->trigger_event('local_intellicart_coupon_created');
    }

    /**
     * @param bool $result
     */
    protected function after_update($result) {
        $this->trigger_event('local_intellicart_coupon_updated');
    }

    /**
     * @param bool $result
     */
    protected function after_delete($result) {
        $this->trigger_event('local_intellicart_coupon_deleted');
    }

    /**
     * @param $eventname
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function trigger_event($eventname) {
        global $USER;

        $eventdata = [
            'objectid'      => $this->get('id'),
            'userid'        => $USER->id,
            'relateduserid' => $USER->id,
            'context'       => \context_system::instance(),
        ];

        $eventname = 'local_intellicart\\event\\' . $eventname;

        $event = $eventname::create($eventdata);
        $event->trigger();
    }

    /**
     * @param int $couponid
     * @throws \dml_exception
     */
    public static function delete_coupon_relations($couponid = 0) {
        global $DB;

        $DB->delete_records('local_intellicart_relations',
            ['instanceid' => $couponid, 'type' => self::INSTANCE_NAME]);
    }

    /**
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_types() {

        return [
            self::TYPE_PERCENTS => get_string('couponpercents', 'local_intellicart'),
            self::TYPE_CURRENCY => get_string('couponcurrency', 'local_intellicart')
        ];
    }

    /**
     * @return string[]
     */
    protected function types_instances() {
        return [
            self::TYPE_PERCENTS => 'type_percents',
            self::TYPE_CURRENCY => 'type_currency'
        ];
    }

    /**
     * @return mixed
     * @throws \coding_exception
     */
    public function get_type_instance() {
        $classname = '\\local_intellicart\components\coupons\services\coupontypes\\' .
            $this->types_instances()[$this->get('type')];
        return new $classname($this);
    }

    /**
     * @return bool
     * @throws \coding_exception
     */
    public function is_currency_type() {
        return ($this->get('coupontype') == self::TYPE_CURRENCY);
    }

    /**
     * @return bool
     * @throws \coding_exception
     */
    public function is_percents_type() {
        return ($this->get('coupontype') == self::TYPE_PERCENTS);
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public static function generate_coupon_code() {
        global $DB;

        $code = "";
        $codealphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codealphabet .= "0123456789";
        $max = strlen($codealphabet);

        for ($i = 0; $i < self::KEY_LENGTH; $i++) {
            if (in_array($i, array(4, 8, 12))) {
                $code .= '-';
            }
            $code .= $codealphabet[random_int(0, $max - 1)];
        }

        if ($DB->get_record(self::TABLE, ['code' => $code])) {
            $code = self::generate_coupon_code();
        }

        return $code;
    }
}
