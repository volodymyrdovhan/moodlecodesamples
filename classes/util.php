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
 * Util functions
 *
 * @package   local_intellicart
 * @copyright 2020 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart;

defined('MOODLE_INTERNAL') || die();

use local_intellicart\debug;
use local_intellicart\notification;

/**
 * @package   local_intellicart
 * @copyright 2020 IntelliBoard
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class util {

    const LMS_TYPE_MOODLE       = 'moodle';
    const LMS_TYPE_TOTARA       = 'totara';

    public static function log_payment_error($type, $subject, $data, $die = true, $identifier = '',
        $customernotify = true) {
        global $PAGE;

        $PAGE->set_context(\context_system::instance());

        $message = "$type payment Transaction Log .\n\n$subject\n\n";

        foreach ($data as $key => $value) {
            $value = (is_array($value) or is_object($value)) ? json_encode($value) : $value;
            $message .= "$key => $value\n";
        }

        debug::create(debug::TYPE_PAYMENTLOG, $message, $identifier);

        $admin = get_admin();
        $paymenterrorsemail = get_config('local_intellicart', 'paymenterrorsemail');

        // Send message to admin.
        $eventdata = new \core\message\message();
        $eventdata->courseid          = SITEID;
        $eventdata->modulename        = 'moodle';
        $eventdata->component         = 'local_intellicart';
        $eventdata->name              = 'paymenterror_notify';
        $eventdata->userfrom          = $admin;
        $eventdata->userto            = $admin;
        $eventdata->userto->email     = (!empty($paymenterrorsemail) and validate_email($paymenterrorsemail))
                                        ? $paymenterrorsemail : $admin->email;
        $eventdata->subject           = $subject;
        $eventdata->fullmessage       = $message;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';

        if (get_config('local_intellicart', 'sendpaymenterrorstoadmin')) {
            message_send($eventdata);
        }

        if ($customernotify === true) {
            notification::send_customers_payment_errors_notify($subject, $data);
        }

        if ($die) {
            die("$type payment IPN Log: $subject");
        }
    }

    /**
     * Silent exception handler.
     *
     * @return callable exception handler
     */
    public static function get_exception_handler() {
        return function($ex) {
            global $PAGE;

            $PAGE->set_context(\context_system::instance());
            $info = get_exception_info($ex);

            $logerrmsg = "payment IPN exception handler: ".$info->message;
            if (debugging('', DEBUG_NORMAL)) {
                $logerrmsg .= ' Debug: '.$info->debuginfo."\n".format_backtrace($info->backtrace, true);
            }

            debug::create(debug::TYPE_EXCEPTION, $logerrmsg);

            $admin = get_admin();
            $paymenterrorsemail = get_config('local_intellicart', 'paymenterrorsemail');

            // Send message to admin.
            $eventdata = new \core\message\message();
            $eventdata->courseid          = SITEID;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'local_intellicart';
            $eventdata->name              = 'paymenterror_notify';
            $eventdata->userfrom          = $admin;
            $eventdata->userto            = $admin;
            $eventdata->userto->email     = (!empty($paymenterrorsemail) and validate_email($paymenterrorsemail))
                                            ? $paymenterrorsemail : $admin->email;
            $eventdata->subject           = 'IntelliCart payment IPN exception handler';
            $eventdata->fullmessage       = $logerrmsg;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';

            if (get_config('local_intellicart', 'sendpaymenterrorstoadmin')) {
                message_send($eventdata);
            }

            if (http_response_code() == 200) {
                http_response_code(500);
            }

            exit(0);
        };
    }

    /**
     * Get percent value first number from second number
     * @param int $number1
     * @param int $number2
     * @return array [
     *     status - positive(true)|negative(false)
     *     percent - value
     * ]
     */
    public static function ratio_percentage($number1, $number2) {
        if ($number1 == 0 && $number1 == 2) {
            $percent = 0;
        } else if ($number1 == 0 or $number2 == 0) {
            $percent = 100.00;
        } else {
            $percent = format_float(
                abs(100 - ($number1 * 100 / $number2)), 2
            );
        }
        return [
            'percent' => $percent,
            'status' => $number1 > $number2
        ];
    }

    /**
     * Get percent value first number from second number
     * @param int $number1
     * @param int $number2
     * @return array [
     *     status - positive(true)|negative(false)
     *     percent - value
     * ]
     */
    public static function get_catalog_url($output = false) {
        if (get_config('local_intellicart', 'alternativecatalogurl') and
                !empty(get_config('local_intellicart', 'alternativecatalogurl'))) {
            return new \moodle_url(get_config('local_intellicart', 'alternativecatalogurl'));
        } else {
            return new \moodle_url('/');
        }
    }

    /**
     * @param $defaultsalespage
     * @return \moodle_url|string
     */
    public static function get_sales_url($defaultsalespage) {

        switch ($defaultsalespage) {
            case 'invoices':
                $salesurl = new \moodle_url('/local/intellicart/sales/invoices.php');
                break;
            case 'seats':
                $salesurl = new \moodle_url('/local/intellicart/sales/seats.php');
                break;
            case 'requests':
                $salesurl = new \moodle_url('/local/intellicart/sales/requests.php');
                break;
            case 'subscriptions':
                $salesurl = new \moodle_url('/local/intellicart/sales/subscriptions.php');
                break;
            case 'shipping':
                $salesurl = new \moodle_url('/local/intellicart/shipping/index.php');
                break;
            default:
                $salesurl = '';
        }

        return $salesurl;
    }

    /**
     * @return string
     */
    public static function get_lms_type() {
        global $CFG;

        return (isset($CFG->totara_version) and $CFG->totara_version) ? self::LMS_TYPE_TOTARA : self::LMS_TYPE_MOODLE;
    }

    /**
     * @return null|float
     */
    public static function get_moodle_version() {
        global $CFG;

        return (isset($CFG->release) && $CFG->release) ? (float)$CFG->release : null;
    }

    /**
     * @param string $plugintype
     * @param null $data
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function save_response($plugintype = '', $data = null) {

        if (!get_config('local_intellicart', 'enable_debugging')) {
            return;
        }

        $logdata = ($data) ? $data : $_REQUEST;
        $logdata = clean_param_array($logdata, PARAM_RAW, true);
        $logdata = (object)$logdata;
        $title = "{$plugintype} response data";

        self::log_payment_error($plugintype, "DEBUGGING", $logdata, false, $title, false);
    }

}
