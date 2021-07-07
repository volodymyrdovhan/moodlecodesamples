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
 * IntelliCart coupons page.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or lat
 */

use local_intellicart\components\coupons\persistent\coupon;
use local_intellicart\components\coupons\tables\coupons_table;
use local_intellicart\components\coupons\output\coupons_index;
use local_intellicart\facades\bulkactions;
use local_intellicart\facades\tablefilter;

require('../../../config.php');
require($CFG->dirroot . '/local/intellicart/locallib.php');

$view = optional_param('view', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);

require_login();
local_intellicart_enable('enablecoupons');

$context = context_system::instance();
require_capability('local/intellicart:managecoupons', $context);

// Process filter.
$filter = tablefilter::get_filter('intellicart_couponsstatus_filter');

$title = get_string('coupons', 'local_intellicart');
$PAGE->set_url('/local/intellicart/coupons/index.php', array('view' => $filter, 'search' => $search));
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

$PAGE->navbar->add(get_string('dashboard', 'local_intellicart'), new moodle_url('/local/intellicart/dashboard/index.php'));
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Process bulkactions.
bulkactions::process(
    '\\local_intellicart\\components\\coupons\\persistent\\coupon',
    ['local/intellicart:editcoupons']
);

$table = new coupons_table('coupons_table', $search, $filter);
$renderer = $PAGE->get_renderer('local_intellicart');

$params = [
        'title' => $renderer->print_breadcrumbs([$title ]),
        'search' => $search,
        'filter' => $filter,
        'search_panel' => $renderer->print_search_panel(
                $search,
                $filter,
                coupon::INSTANCES_NAME,
                has_capability('local/intellicart:editcoupons', context_system::instance()),
                has_capability('local/intellicart:couponsimport', context_system::instance())
        ),
        'tablehtml' => $table->export_for_template($renderer)
];

$renderable = new coupons_index($params);

echo $OUTPUT->header();

echo $renderer->render($renderable);

$PAGE->requires->js_call_amd('local_intellicart/intellicart_bulkactions', 'init', []);

echo $OUTPUT->footer();
