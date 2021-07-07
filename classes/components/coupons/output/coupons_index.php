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
 * Class containing dashboard data for intellicart plugin
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\components\coupons\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use local_intellicart\util;

/**
 * Class containing coupons list for intellicart plugin
 *
 * @package    local_intellicart
 * @author     IntelliBoard Inc
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coupons_index implements renderable, templatable {

    var $params = null;

    public function __construct($params = null) {
        $this->params = $params;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return [
            'tabs'      => $output->print_manage_tabs('coupons'),
            'page-type' => 'coupons-index',
            'lms-type'  => util::get_lms_type(),
            'title'     => $this->params['title'],
            'content'   => $output->render_from_template('local_intellicart/page-index', $this->params)
        ];
    }
}
