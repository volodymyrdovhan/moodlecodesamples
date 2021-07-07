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
 * IntelliCart coupon edit page.
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or lat
 */

use local_intellicart\components\coupons\persistent\coupon;
use local_intellicart\components\coupons\forms\edit_coupon_form;
use local_intellicart\components\coupons\output\coupons_edit;
use local_intellicart\facades\editingactions;

require('../../../config.php');
require($CFG->dirroot . '/local/intellicart/locallib.php');

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();
local_intellicart_enable('enablecoupons');

$context = context_system::instance();
require_capability('local/intellicart:editcoupons', $context);

$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

$PAGE->set_url('/local/intellicart/coupons/edit.php', ['id' => $id]);

$coupon = new coupon($id);

$returnurl = new moodle_url($CFG->wwwroot . '/local/intellicart/coupons/index.php');

// Process bulkactions.
editingactions::process($coupon, coupon::INSTANCE_NAME,
        '/local/intellicart/coupon/edit.php', $returnurl);

$strheading = ($coupon->get('id')) ? get_string('editcoupon', 'local_intellicart') :
    get_string('createcoupon', 'local_intellicart');

$editform = new edit_coupon_form(null, ['persistent' => $coupon], 'post', '');

if ($editform->is_cancelled()) {

    redirect($returnurl);

} else if ($data = $editform->get_data()) {

    $coupon = new coupon($id, $data);
    $coupon->save();

    redirect($returnurl);
}

$renderer = $PAGE->get_renderer('local_intellicart');
$params = [
    'title'    => $renderer->print_breadcrumbs([$strheading]),
    'formhtml' => $editform->export_for_template($renderer)
];
$renderable = new coupons_edit($params);

$PAGE->navbar->add(get_string('dashboard', 'local_intellicart'),
    new moodle_url('/local/intellicart/dashboard/index.php'));
$PAGE->navbar->add(get_string('coupons', 'local_intellicart'),
    new moodle_url('/local/intellicart/coupons/index.php'));
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

echo $OUTPUT->header();

echo $renderer->render($renderable);

echo $OUTPUT->footer();
