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

namespace local_intellicart\components\coupons\forms;

defined('MOODLE_INTERNAL') || die();

use local_intellicart\multilang;
use local_intellicart\components\coupons\persistent\coupon;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_coupon_form extends \core\form\persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = 'local_intellicart\\components\\coupons\\persistent\\coupon';

    /**
     * Define the catalog edit form
     */
    public function definition() {

        $mform = $this->_form;

        $required = get_string('required');

        $mform->addElement('text', 'code', get_string('code', 'local_intellicart'));
        $mform->setType('code', PARAM_TEXT);

        $mform->addElement('date_time_selector', 'starttime', get_string('starttime', 'local_intellicart'), array('optional' => true));
        $mform->setDefault('starttime', 0);

        $mform->addElement('date_time_selector', 'endtime', get_string('endtime', 'local_intellicart'), array('optional' => true));
        $mform->setDefault('endtime', 0);

        $mform->addElement('text', 'usedcount', get_string('usedcount', 'local_intellicart'));
        $mform->setType('usedcount', PARAM_INT);

        $mform->addElement('text', 'usedperuser', get_string('usedperuser', 'local_intellicart'));
        $mform->setType('usedperuser', PARAM_INT);

        $subscrarray=array();
        $subscrarray[] = $mform->createElement('radio', 'type', '', get_string('couponpercents', 'local_intellicart'), coupon::TYPE_PERCENTS);
        $subscrarray[] = $mform->createElement('radio', 'type', '', get_string('couponcurrency', 'local_intellicart'), coupon::TYPE_CURRENCY);
        $mform->addGroup($subscrarray, 'typearray', get_string('coupontype', 'local_intellicart'), [' '], false);
        $mform->setDefault('type', 0);

        $mform->addElement('text', 'discount', get_string('discountvalue', 'local_intellicart'));
        $mform->addRule('discount', $required, 'required', null, 'client');
        $mform->setType('discount', PARAM_FLOAT);

        $options = [
            coupon::STATUS_ACTIVE   => get_string('active', 'local_intellicart'),
            coupon::STATUS_INACTIVE => get_string('inactive', 'local_intellicart')
        ];
        $mform->addElement('select', 'status', get_string('status', 'local_intellicart'), $options);
        $mform->setType('status', PARAM_INT);
        $mform->setDefault('status', coupon::STATUS_ACTIVE);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    protected function extra_validation($data, $files, &$errors) {

        $newerrors = [];

        if (!empty($data->code)) {
            $existing = coupon::get_record(['code' => $data->code]);
            if (!empty($existing)) {
                if (!$data->id || (($existing->get('id')) != $data->id)) {
                    $newerrors['code'] = get_string('couponcodetaken', 'local_intellicart');
                }
            }
        }

        return $newerrors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();

        return $formhtml;
    }
}
