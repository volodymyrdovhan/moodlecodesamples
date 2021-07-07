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
 * @category   task
 * @author     IntelliBoard Inc.
 * @copyright  2021 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_intellicart\task;
defined('MOODLE_INTERNAL') || die();

/**
 * Task to process intellicart waitlist.
 *
 * @author     IntelliBoard Inc.
 * @copyright  2021 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class waitlist_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('waitlist_task', 'local_intellicart');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB, $PAGE;

        if (!get_config('local_intellicart', 'enablewaitlist')) {
            return;
        }

        mtrace("Intellicart Waitlist CRON started!");

        $waitlistduration = get_config('local_intellicart', 'waitlist_duration');
        $expirationdate = time() - $waitlistduration;

        // Remove expired items.
        $items = $DB->get_records_sql("
              SELECT *
                FROM {local_intellicart_waitlist}
               WHERE sent = :sent AND timemodified < :expirationdate
            ORDER BY timemodified ASC",
                ['sent' => 1, 'expirationdate' => $expirationdate]);

        $products = [];
        if (count($items)) {
            foreach ($items as $item) {
                $products[$item->productid] = (isset($products[$item->productid])) ? $products[$item->productid] + 1 : 1;
                $DB->delete_records('local_intellicart_waitlist', ['id' => $item->id]);
            }
        }
        if (count($items)) {
            mtrace("Intellicart Waitlist [" . count($items) . "] items removed!");
        }

        // Send available seats.
        $i = 0;
        if (count($products)) {

            $PAGE->set_context(\context_system::instance());

            foreach ($products as $productid => $seats) {
                $items = $DB->get_records_sql("
              SELECT *
                FROM {local_intellicart_waitlist}
               WHERE productid = :productid AND sent = :sent
            ORDER BY timemodified ASC LIMIT $seats OFFSET 0",
                        ['productid' => $productid, 'sent' => 0]);

                if (count($items)) {
                    foreach ($items as $item) {

                        $item->sent = 1;
                        $id = \local_intellicart\waitlist::save_list_item($item);

                        // Send waitlist notification.
                        if ((int)$id > 0) {
                            \local_intellicart\notification::send_waitlist_notification($item);
                        }

                        $i++;
                    }
                }
            }
        }
        if ($i) {
            mtrace("Intellicart Waitlist [" . $i . "] items sent!");
        }

        mtrace("Intellicart Waitlist CRON completed!");
    }

}
