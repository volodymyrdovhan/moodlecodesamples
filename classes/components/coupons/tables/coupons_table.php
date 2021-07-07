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
 * local_intellicart
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\tables;

defined('MOODLE_INTERNAL') || die();

use local_intellicart\log;
use local_intellicart\output\renderer;
use local_intellicart\vendors;
use local_intellicart\roles;
use local_intellicart\util;
use local_intellicart\payment;
use local_intellicart\components\coupons\persistent\coupon;
use moodle_url;
use pix_icon;

require_once($CFG->dirroot . '/local/intellicart/classes/output/tables/custom_table.php');

/**
 * Output coupons table
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupons_table extends \custom_table {

    public $search = '';
    public $currency = '';

    /**
     * coupons_table constructor.
     *
     * @param $uniqueid
     * @param $search
     * @param $filter
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($uniqueid, $search, $filter) {
        global $DB;

        parent::__construct($uniqueid);
        $systemcontext = \context_system::instance();
        $this->search = $search;
        $this->currency = payment::get_currency('symbol');

        if (has_capability('local/intellicart:editcoupons', $systemcontext)) {
            $this->bulkactions = [
                'bulkhide'   => get_string('hide'),
                'bulkshow'   => get_string('show'),
                'bulkdelete' => get_string('delete')
            ];
            $this->bulkactioncol = 'id';
        }

        $columns = ['code', 'starttime', 'endtime', 'discount', 'used', 'timecreated', 'actions'];
        $headers = [
            get_string('code', 'local_intellicart'),
            get_string('starttime', 'local_intellicart'),
            get_string('endtime', 'local_intellicart'),
            get_string('discount', 'local_intellicart'),
            get_string('used', 'local_intellicart'),
            get_string('created', 'local_intellicart'),
            get_string('actions', 'local_intellicart')
        ];

        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('actions');
        $this->is_collapsible = false;

        $this->define_columns($columns);
        $this->define_headers($headers);

        $fields = "c.*, l.used";
        $from = "{local_intellicart_coupons} c
                LEFT JOIN (SELECT count(id) as used, instanceid
                             FROM {local_intellicart_logs}
                            WHERE type = :ltype AND
                                  status = :lstatus
                         GROUP BY instanceid
                           ) l ON l.instanceid = c.id ";

        $where = 'c.id > 0';
        $params = ['ltype' => log::TYPE_COUPON, 'lstatus' => log::STATUS_COMPLETED];

        // Search.
        if (!empty($search)) {
            $where .= " AND " . $DB->sql_like('c.code', ':searchcode', false, false, false);
            $params['searchcode'] = '%' . $search . '%';
        }

        // Filter visibility.
        if ($filter == renderer::$filteractive) {
            $where .= ' AND c.status = :status';
            $params['status'] = 1;
        } else if ($filter == renderer::$filterinactive) {
            $where .= ' AND c.status = :status';
            $params['status'] = 0;
        }

        // Vendors filter.
        if (get_config('local_intellicart', 'enablevendors')) {
            list($from, $where, $params) =
            vendors::get_vendorsfilter_sqlrequest($from, $where, $params, 'coupon', 'c.id');
        }

        // Roles filter.
        if ( get_config('local_intellicart', 'enablerolesfiltering') ) {
            list($from, $where, $params) = roles::get_rolesfilter_sqlrequest(
                    $from, $where, $params,
                    roles::ROLES_TYPE_COUPON, 'c.id');
        }

        $this->set_sql($fields, $from, $where, $params);
        $this->define_baseurl($this->page->url);
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    public function col_starttime($values) {
        return ($values->starttime) ? userdate($values->starttime, get_string('strftimerecentfull', 'langconfig')) : '-';
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    public function col_endtime($values) {
        return ($values->endtime) ? userdate($values->endtime, get_string('strftimerecentfull', 'langconfig')) : '-';
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    public function col_timecreated($values) {
        return (!empty($values->timecreated)) ? userdate($values->timecreated,
            get_string('strftimedatefullshort', 'langconfig')) : '-';
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_discount($values) {
        return ($values->type == coupon::TYPE_CURRENCY) ? $this->currency . $values->discount :
            get_string('discountpercents', 'local_intellicart', $values->discount);
    }

    /**
     * @param $values
     * @return string
     */
    public function col_used($values) {
        return ($values->used) ? $values->used : '-';
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function col_actions($values) {
        global $OUTPUT;

        $buttons = [];

        $systemcontext = \context_system::instance();
        if (!has_capability('local/intellicart:editcoupons', $systemcontext)) {
            return '';
        }

        $urlparams = ['id' => $values->id, 'sesskey' => sesskey()];

        $aurl = new moodle_url('/local/intellicart/coupons/edit.php', $urlparams);
        $buttons[] = $OUTPUT->action_icon($aurl, new pix_icon('t/edit', get_string('edit'),
            'core', ['class' => 'iconsmall']), null);

        $aurl = new moodle_url('/local/intellicart/coupons/edit.php', $urlparams +
            ['action' => 'delete']);
        $buttons[] = $OUTPUT->action_icon($aurl, new pix_icon('t/delete', get_string('delete'),
            'core', ['class' => 'iconsmall']), null);

        if ($values->status) {
            $aurl = new moodle_url('/local/intellicart/coupons/edit.php', $urlparams + ['action' => 'hide']);
            $buttons[] = $OUTPUT->action_icon($aurl, new pix_icon('t/hide', get_string('hide'),
                'core', ['class' => 'iconsmall']), null);
        } else {
            $aurl = new moodle_url('/local/intellicart/coupons/edit.php', $urlparams +
                ['action' => 'show']);
            $buttons[] = $OUTPUT->action_icon($aurl, new pix_icon('t/show', get_string('show'),
                'core', ['class' => 'iconsmall']), null);
        }

        if (has_capability('local/intellicart:assign', $systemcontext)) {
            $aurl = new moodle_url('/local/intellicart/coupons/assignproducts.php', $urlparams);
            if (util::get_lms_type() == util::LMS_TYPE_TOTARA and
                class_exists('\core\output\flex_icon')) {
                $buttons[] = $OUTPUT->action_icon($aurl,
                    new \core\output\flex_icon('program', ['alt' => get_string('assignproducts', 'local_intellicart')]));
            } else {
                $buttons[] = $OUTPUT->action_icon($aurl, new pix_icon('i/categoryevent',
                    get_string('assignproducts', 'local_intellicart'), 'core',
                    ['class' => 'iconsmall']), null);
            }
        }

        if (has_capability('local/intellicart:managevendorsrelations', $systemcontext) and
            get_config('local_intellicart', 'enablevendors')) {
            $aurl = new moodle_url('/local/intellicart/vendors/assignvendors.php', $urlparams + ['type' => 'coupon']);
            $buttons[] = $OUTPUT->action_icon($aurl, new pix_icon('i/db', get_string('assignvendors', 'local_intellicart'),
                'core', ['class' => 'iconsmall']), null);
        }

        if (has_capability('local/intellicart:managerolesrelations', $systemcontext) and
            get_config('local_intellicart', 'enablerolesfiltering')) {
            $aurl = new moodle_url('/local/intellicart/coupons/assignroles.php',
                    $urlparams + ['type' => roles::ROLES_TYPE_COUPON]
            );
            $buttons[] = $OUTPUT->action_icon(
                    $aurl,
                    new pix_icon(
                        'i/user', get_string('assignroles', 'local_intellicart'), 'core', ['class' => 'iconsmall']
                    ),
                    null
            );
        }

        return implode(' ', $buttons);
    }
}

